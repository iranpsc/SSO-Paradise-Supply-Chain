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
     * Maximum total pixels (width * height) allowed.
     * Prevents image bombs (e.g., decompression bombs).
     */
    protected int $maxPixelCount;

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
     * @param int|null $maxWidth Optional cap on width
     * @param int|null $maxHeight Optional cap on height
     * @param int|null $minWidth Optional minimum width
     * @param int|null $minHeight Optional minimum height
     */
    public function __construct(
        ?array $allowedMimeTypes = null,
        int $maxPixelCount = 25_000_000,
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

        // 1) Verify real image type from magic bytes
        $mimeFromExif = null;
        if (function_exists('exif_imagetype')) {
            $imageType = @exif_imagetype($path);
            if ($imageType !== false && isset($this->imagetypeToMime[$imageType])) {
                $mimeFromExif = $this->imagetypeToMime[$imageType];
            }
        }

        // 2) Verify dimensions and pixel count
        $imageSize = @getimagesize($path);
        if ($imageSize === false || !isset($imageSize[0], $imageSize[1])) {
            $fail('The :attribute is not a valid image.');
            return;
        }
        $width = (int) $imageSize[0];
        $height = (int) $imageSize[1];
        $totalPixels = $width * $height;

        // Fallback MIME from getimagesize if EXIF not available
        if ($mimeFromExif === null && isset($imageSize['mime']) && is_string($imageSize['mime'])) {
            $mimeFromExif = $imageSize['mime'];
        }
        if ($mimeFromExif === null || !isset($this->allowedMimeTypes[$mimeFromExif])) {
            $fail('The :attribute must be a valid image (jpeg, png, webp).');
            return;
        }

        if ($totalPixels <= 0) {
            $fail('The :attribute is not a valid image.');
            return;
        }
        if ($totalPixels > $this->maxPixelCount) {
            $fail('The :attribute image is too large.');
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

        // 3) Cross-check MIME using finfo and the framework-reported MIME
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeFromFinfo = $finfo->file($path) ?: '';
        $mimeFromFramework = (string) $value->getMimeType();

        if (!isset($this->allowedMimeTypes[$mimeFromExif])) {
            $fail('The :attribute image type is not allowed.');
            return;
        }
        if ($mimeFromFinfo !== $mimeFromExif) {
            $fail('The :attribute file type could not be verified.');
            return;
        }
        if ($mimeFromFramework !== '' && $mimeFromFramework !== $mimeFromExif) {
            // Some environments may report generic types; only fail if clearly mismatched.
            $fail('The :attribute file type does not match its contents.');
            return;
        }
    }
}


