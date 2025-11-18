<?php

namespace App\Rules;

use Closure;
use finfo;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\UploadedFile;

class SecureImage implements ValidationRule
{
    /**
     * RFC-compliant MIME types we allow by default.
     * svg, bmp, tiff, ico are intentionally excluded for security/hardening.
     *
     * @var array<string, true>
     */
    protected array $allowedMimeTypes = [
        'image/jpeg' => true,
        'image/png' => true,
        'image/webp' => true,
    ];

    /**
     * IMAGETYPE_* constants that map to allowed types.
     *
     * @var array<int, string>
     */
    protected array $imagetypeToMime = [
        IMAGETYPE_JPEG => 'image/jpeg',
        IMAGETYPE_PNG => 'image/png',
        IMAGETYPE_WEBP => 'image/webp',
    ];

    /**
     * Allowed file extensions (lowercase).
     *
     * @var array<string, true>
     */
    protected array $allowedExtensions = [
        'jpg' => true,
        'jpeg' => true,
        'png' => true,
        'webp' => true,
    ];

    /**
     * Dangerous extensions that should never be allowed.
     *
     * @var array<string, true>
     */
    protected array $dangerousExtensions = [
        'php' => true, 'php3' => true, 'php4' => true, 'php5' => true, 'phtml' => true,
        'pht' => true, 'phar' => true, 'shtml' => true, 'htaccess' => true, 'htpasswd' => true,
        'sh' => true, 'bash' => true, 'py' => true, 'rb' => true, 'pl' => true, 'exe' => true,
        'bat' => true, 'cmd' => true, 'com' => true, 'scr' => true, 'vbs' => true, 'js' => true,
        'jar' => true, 'war' => true, 'jsp' => true, 'asp' => true, 'aspx' => true, 'cgi' => true,
        'svg' => true, 'swf' => true, 'html' => true, 'htm' => true, 'xml' => true,
    ];

    /**
     * Magic bytes for each image type (first few bytes of file).
     *
     * @var array<string, array<int, string>>
     */
    protected array $magicBytes = [
        'image/jpeg' => ["\xFF\xD8\xFF"],
        'image/png' => ["\x89\x50\x4E\x47\x0D\x0A\x1A\x0A"],
        'image/webp' => ['RIFF'],
    ];

    /**
     * Maximum total pixels (width * height) allowed.
     * Prevents image bombs (e.g., decompression bombs).
     */
    protected int $maxPixelCount;

    /**
     * Maximum file size in bytes (0 = no limit from this rule).
     */
    protected int $maxFileSize;

    /**
     * Optional minimum dimensions.
     */
    protected ?int $minWidth;
    protected ?int $minHeight;

    /**
     * Optional maximum dimensions.
     */
    protected ?int $maxWidth;
    protected ?int $maxHeight;

    /**
     * @param array<int, string>|null $allowedMimeTypes Override allowed mime types (e.g., ['image/jpeg','image/png'])
     * @param int $maxPixelCount Prevent decompression bombs (default 25MP)
     * @param int $maxFileSize Maximum file size in bytes (default 10MB = 10485760)
     * @param int|null $maxWidth Optional cap on width
     * @param int|null $maxHeight Optional cap on height
     * @param int|null $minWidth Optional minimum width
     * @param int|null $minHeight Optional minimum height
     */
    public function __construct(
        ?array $allowedMimeTypes = null,
        int $maxPixelCount = 25_000_000,
        int $maxFileSize = 10_485_760,
        ?int $maxWidth = null,
        ?int $maxHeight = null,
        ?int $minWidth = null,
        ?int $minHeight = null
    ) {
        if ($allowedMimeTypes !== null) {
            $this->allowedMimeTypes = [];
            foreach ($allowedMimeTypes as $mime) {
                $this->allowedMimeTypes[$mime] = true;
            }
            $this->imagetypeToMime = array_filter(
                $this->imagetypeToMime,
                function (string $mime): bool {
                    return isset($this->allowedMimeTypes[$mime]);
                }
            );
        }

        $this->maxPixelCount = $maxPixelCount;
        $this->maxFileSize = $maxFileSize;
        $this->maxWidth = $maxWidth;
        $this->maxHeight = $maxHeight;
        $this->minWidth = $minWidth;
        $this->minHeight = $minHeight;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!$value instanceof UploadedFile) {
            $fail('The :attribute must be an uploaded file.');
            return;
        }

        if (!$value->isValid() || $value->getSize() === 0) {
            $fail('The :attribute is not a valid upload.');
            return;
        }

        $path = $value->getPathname();
        $filename = $value->getClientOriginalName();

        // SECURITY LAYER 1: Filename validation - prevent double extensions and dangerous extensions
        if (!$this->validateFilename($filename, $fail, $attribute)) {
            return;
        }

        // SECURITY LAYER 2: File size validation - prevent oversized files
        $fileSize = $value->getSize();
        if ($this->maxFileSize > 0 && $fileSize > $this->maxFileSize) {
            $fail('The :attribute file size exceeds the maximum allowed size.');
            return;
        }

        // SECURITY LAYER 3: Magic bytes verification - verify file signature
        $mimeFromMagicBytes = $this->verifyMagicBytes($path);
        if ($mimeFromMagicBytes === null) {
            $fail('The :attribute does not appear to be a valid image file.');
            return;
        }
        if (!isset($this->allowedMimeTypes[$mimeFromMagicBytes])) {
            $fail('The :attribute file type is not allowed.');
            return;
        }

        // SECURITY LAYER 4: EXIF image type verification
        $mimeFromExif = null;
        if (function_exists('exif_imagetype')) {
            $imageType = @exif_imagetype($path);
            if ($imageType !== false && isset($this->imagetypeToMime[$imageType])) {
                $mimeFromExif = $this->imagetypeToMime[$imageType];
            }
        }

        // SECURITY LAYER 5: getimagesize verification - dimensions and MIME
        $imageSize = @getimagesize($path);
        if ($imageSize === false || !isset($imageSize[0], $imageSize[1])) {
            $fail('The :attribute is not a valid image or could not be read.');
            return;
        }

        $width = (int) $imageSize[0];
        $height = (int) $imageSize[1];
        $totalPixels = $width * $height;

        // Get MIME from getimagesize if EXIF not available
        $mimeFromGetImageSize = null;
        if ($mimeFromExif === null && isset($imageSize['mime']) && is_string($imageSize['mime'])) {
            $mimeFromGetImageSize = $imageSize['mime'];
            $mimeFromExif = $mimeFromGetImageSize;
        }

        // Verify MIME consistency across all detection methods
        if ($mimeFromExif === null || !isset($this->allowedMimeTypes[$mimeFromExif])) {
            $fail('The :attribute must be a valid image (jpeg, png, webp).');
            return;
        }

        // All MIME detection methods must agree
        if ($mimeFromExif !== $mimeFromMagicBytes) {
            $fail('The :attribute file type verification failed.');
            return;
        }

        // SECURITY LAYER 6: Dimension validation - prevent decompression bombs
        if ($totalPixels <= 0) {
            $fail('The :attribute has invalid dimensions.');
            return;
        }
        if ($totalPixels > $this->maxPixelCount) {
            $fail('The :attribute image dimensions are too large.');
            return;
        }
        if ($this->maxWidth !== null && $width > $this->maxWidth) {
            $fail('The :attribute width exceeds the allowed maximum.');
            return;
        }
        if ($this->maxHeight !== null && $height > $this->maxHeight) {
            $fail('The :attribute height exceeds the allowed maximum.');
            return;
        }
        if ($this->minWidth !== null && $width < $this->minWidth) {
            $fail('The :attribute width is below the allowed minimum.');
            return;
        }
        if ($this->minHeight !== null && $height < $this->minHeight) {
            $fail('The :attribute height is below the allowed minimum.');
            return;
        }

        // SECURITY LAYER 7: finfo MIME type verification
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeFromFinfo = $finfo->file($path) ?: '';
        if ($mimeFromFinfo !== '' && !isset($this->allowedMimeTypes[$mimeFromFinfo])) {
            $fail('The :attribute file type is not allowed.');
            return;
        }
        if ($mimeFromFinfo !== '' && $mimeFromFinfo !== $mimeFromMagicBytes) {
            $fail('The :attribute file type could not be verified.');
            return;
        }

        // SECURITY LAYER 8: Framework-reported MIME validation
        $mimeFromFramework = (string) $value->getMimeType();
        if ($mimeFromFramework !== '') {
            // Check if it's a known dangerous type
            $dangerousTypes = ['text/', 'application/', 'script/', 'x-php'];
            foreach ($dangerousTypes as $dangerous) {
                if (stripos($mimeFromFramework, $dangerous) !== false) {
                    $fail('The :attribute file type is not allowed.');
                    return;
                }
            }
            // If framework reports a specific image type, it should match
            if (isset($this->allowedMimeTypes[$mimeFromFramework]) && $mimeFromFramework !== $mimeFromMagicBytes) {
                $fail('The :attribute file type does not match its contents.');
                return;
            }
        }

        // SECURITY LAYER 9: Actual image decoding verification - prevent polyglot files
        if (!$this->verifyImageDecoding($path, $mimeFromMagicBytes)) {
            $fail('The :attribute could not be decoded as a valid image.');
            return;
        }

        // SECURITY LAYER 10: Re-verify file hasn't been swapped (race condition protection)
        if (!file_exists($path) || filesize($path) !== $fileSize) {
            $fail('The :attribute file verification failed.');
            return;
        }
    }

    /**
     * Validate filename for dangerous extensions and double extensions.
     */
    protected function validateFilename(string $filename, Closure $fail, string $attribute): bool
    {
        // Normalize filename
        $filename = strtolower(trim($filename));

        // Check for null bytes (path traversal attempts)
        if (strpos($filename, "\0") !== false || strpos($filename, '%00') !== false) {
            $fail('The :attribute filename contains invalid characters.');
            return false;
        }

        // Extract extension
        $extension = pathinfo($filename, PATHINFO_EXTENSION);

        // Check for multiple extensions (e.g., image.php.jpg)
        $parts = explode('.', $filename);
        if (count($parts) > 2) {
            // Check if any part before the last is a dangerous extension
            for ($i = 0; $i < count($parts) - 1; $i++) {
                $part = strtolower($parts[$i]);
                if (isset($this->dangerousExtensions[$part])) {
                    $fail('The :attribute filename contains a prohibited extension.');
                    return false;
                }
            }
        }

        // Check final extension
        if (isset($this->dangerousExtensions[$extension])) {
            $fail('The :attribute filename contains a prohibited extension.');
            return false;
        }

        if (!isset($this->allowedExtensions[$extension])) {
            $fail('The :attribute must be a valid image file (jpg, jpeg, png, webp).');
            return false;
        }

        // Check for suspicious patterns
        $suspiciousPatterns = [
            '/\.php$/i', '/\.phtml$/i', '/\.phar$/i', '/\.htaccess$/i',
            '/\.sh$/i', '/\.exe$/i', '/\.bat$/i', '/\.cmd$/i',
            '/shell/i', '/cmd/i', '/eval/i', '/exec/i',
        ];

        foreach ($suspiciousPatterns as $pattern) {
            if (preg_match($pattern, $filename)) {
                $fail('The :attribute filename is not allowed.');
                return false;
            }
        }

        return true;
    }

    /**
     * Verify magic bytes (file signature) match expected image type.
     */
    protected function verifyMagicBytes(string $path): ?string
    {
        if (!file_exists($path) || !is_readable($path)) {
            return null;
        }

        $handle = @fopen($path, 'rb');
        if ($handle === false) {
            return null;
        }

        // Read first 12 bytes (enough for all image types)
        $header = @fread($handle, 12);
        @fclose($handle);

        if ($header === false || strlen($header) < 4) {
            return null;
        }

        // Check magic bytes for each image type
        foreach ($this->magicBytes as $mimeType => $signatures) {
            foreach ($signatures as $signature) {
                if (str_starts_with($header, $signature)) {
                    // For WebP, need to verify it contains WEBP at offset 8
                    if ($mimeType === 'image/webp') {
                        if (strlen($header) >= 12 && strpos($header, 'WEBP', 8) !== false) {
                            return $mimeType;
                        }
                    } else {
                        return $mimeType;
                    }
                }
            }
        }

        return null;
    }

    /**
     * Verify the image can actually be decoded (prevents polyglot files).
     */
    protected function verifyImageDecoding(string $path, string $mimeType): bool
    {
        try {
            switch ($mimeType) {
                case 'image/jpeg':
                    $image = @imagecreatefromjpeg($path);
                    if ($image !== false) {
                        @imagedestroy($image);
                        return true;
                    }
                    break;

                case 'image/png':
                    $image = @imagecreatefrompng($path);
                    if ($image !== false) {
                        @imagedestroy($image);
                        return true;
                    }
                    break;

                case 'image/webp':
                    if (function_exists('imagecreatefromwebp')) {
                        $image = @imagecreatefromwebp($path);
                        if ($image !== false) {
                            @imagedestroy($image);
                            return true;
                        }
                    } else {
                        // If WebP support is not available, at least verify it's a valid RIFF file
                        $handle = @fopen($path, 'rb');
                        if ($handle !== false) {
                            $header = @fread($handle, 12);
                            @fclose($handle);
                            return str_starts_with($header, 'RIFF') && strpos($header, 'WEBP', 8) !== false;
                        }
                    }
                    break;
            }
        } catch (\Throwable $e) {
            // Any exception means the file cannot be decoded
            return false;
        }

        return false;
    }
}


