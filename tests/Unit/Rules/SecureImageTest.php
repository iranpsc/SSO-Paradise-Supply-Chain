<?php

namespace App\Rules {
    /**
     * Test doubles for SecureImage edge branches (resolved via namespace fallback).
     */
    function getimagesize($filename, ?array &$image_info = null): array|false
    {
        if (\Tests\Unit\Rules\SecureImageTest::$getImageSizeOverride !== null) {
            return (\Tests\Unit\Rules\SecureImageTest::$getImageSizeOverride)($filename);
        }

        return \getimagesize($filename, $image_info);
    }

    function exif_imagetype($filename): int|false
    {
        if (\Tests\Unit\Rules\SecureImageTest::$exifImageTypeOverride !== null) {
            return (\Tests\Unit\Rules\SecureImageTest::$exifImageTypeOverride)($filename);
        }

        return \exif_imagetype($filename);
    }

    function imagecreatefromjpeg($filename): \GdImage|false
    {
        if (\Tests\Unit\Rules\SecureImageTest::$imageCreateJpegThrows) {
            throw new \RuntimeException('forced jpeg decode failure');
        }

        if (\Tests\Unit\Rules\SecureImageTest::$imageCreateJpegReturnsFalse) {
            return false;
        }

        return \imagecreatefromjpeg($filename);
    }

    function fopen($filename, $mode, $use_include_path = false, $context = null)
    {
        if (\Tests\Unit\Rules\SecureImageTest::$fopenReturnsFalse) {
            return false;
        }

        return $context === null
            ? \fopen($filename, $mode, $use_include_path)
            : \fopen($filename, $mode, $use_include_path, $context);
    }

    function file_exists($filename): bool
    {
        if (\Tests\Unit\Rules\SecureImageTest::$forceFileMissing) {
            return false;
        }

        return \file_exists($filename);
    }

    function filesize($filename): int|false
    {
        if (\Tests\Unit\Rules\SecureImageTest::$forceFilesizeMismatch) {
            return 1;
        }

        return \filesize($filename);
    }

    function imagecreatefrompng($filename): \GdImage|false
    {
        if (\Tests\Unit\Rules\SecureImageTest::$imageCreatePngReturnsFalse) {
            return false;
        }

        return \imagecreatefrompng($filename);
    }

    function imagecreatefromwebp($filename): \GdImage|false
    {
        if (\Tests\Unit\Rules\SecureImageTest::$imageCreateWebpReturnsFalse) {
            return false;
        }

        return \imagecreatefromwebp($filename);
    }

    function function_exists($function): bool
    {
        if ($function === 'imagecreatefromwebp' && \Tests\Unit\Rules\SecureImageTest::$webpFunctionMissing) {
            return false;
        }

        if ($function === 'exif_imagetype' && \Tests\Unit\Rules\SecureImageTest::$exifFunctionMissing) {
            return false;
        }

        return \function_exists($function);
    }
}

namespace Tests\Unit\Rules {

use App\Rules\SecureImage;
use Illuminate\Http\UploadedFile;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SecureImageTest extends TestCase
{
    /** @var (callable(string): (array|false))|null */
    public static $getImageSizeOverride = null;

    /** @var (callable(string): (int|false))|null */
    public static $exifImageTypeOverride = null;

    public static bool $imageCreateJpegThrows = false;

    public static bool $imageCreateJpegReturnsFalse = false;

    public static bool $imageCreatePngReturnsFalse = false;

    public static bool $imageCreateWebpReturnsFalse = false;

    public static bool $webpFunctionMissing = false;

    public static bool $exifFunctionMissing = false;

    public static bool $fopenReturnsFalse = false;

    public static bool $forceFileMissing = false;

    public static bool $forceFilesizeMismatch = false;

    protected function tearDown(): void
    {
        self::$getImageSizeOverride = null;
        self::$exifImageTypeOverride = null;
        self::$imageCreateJpegThrows = false;
        self::$imageCreateJpegReturnsFalse = false;
        self::$imageCreatePngReturnsFalse = false;
        self::$imageCreateWebpReturnsFalse = false;
        self::$webpFunctionMissing = false;
        self::$exifFunctionMissing = false;
        self::$fopenReturnsFalse = false;
        self::$forceFileMissing = false;
        self::$forceFilesizeMismatch = false;

        parent::tearDown();
    }

    private function assertFails(SecureImage $rule, mixed $value, ?string $messageContains = null): void
    {
        $failed = false;
        $message = null;

        $rule->validate('avatar', $value, function (string $msg) use (&$failed, &$message) {
            $failed = true;
            $message = $msg;
        });

        $this->assertTrue($failed, 'Expected SecureImage validation to fail.');

        if ($messageContains !== null) {
            $this->assertStringContainsString($messageContains, (string) $message);
        }
    }

    private function assertPasses(SecureImage $rule, mixed $value): void
    {
        $failed = false;

        $rule->validate('avatar', $value, function () use (&$failed) {
            $failed = true;
        });

        $this->assertFalse($failed, 'Expected SecureImage validation to pass.');
    }

    private function realJpegUpload(string $filename = 'photo.jpg', int $width = 20, int $height = 20): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'jpeg_');
        $image = imagecreatetruecolor($width, $height);
        imagejpeg($image, $path, 90);
        imagedestroy($image);

        return new UploadedFile($path, $filename, 'image/jpeg', null, true);
    }

    private function realPngUpload(string $filename = 'photo.png', int $width = 20, int $height = 20): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'png_');
        $image = imagecreatetruecolor($width, $height);
        imagepng($image, $path);
        imagedestroy($image);

        return new UploadedFile($path, $filename, 'image/png', null, true);
    }

    private function realWebpUpload(string $filename = 'photo.webp', int $width = 20, int $height = 20): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'webp_');
        $image = imagecreatetruecolor($width, $height);
        imagewebp($image, $path);
        imagedestroy($image);

        return new UploadedFile($path, $filename, 'image/webp', null, true);
    }

    private function uploadFromBytes(string $filename, string $bytes, string $mime = 'application/octet-stream'): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'bin_');
        file_put_contents($path, $bytes);

        return new UploadedFile($path, $filename, $mime, null, true);
    }

    #[Test]
    public function it_accepts_a_valid_jpeg(): void
    {
        $this->assertPasses(new SecureImage, $this->realJpegUpload());
    }

    #[Test]
    public function it_rejects_non_uploaded_file_values(): void
    {
        $this->assertFails(new SecureImage, 'not-a-file', 'must be an uploaded file');
    }

    #[Test]
    public function it_rejects_invalid_uploads(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'bad_');
        file_put_contents($path, 'x');

        $file = new UploadedFile($path, 'photo.jpg', 'image/jpeg', UPLOAD_ERR_NO_FILE, true);

        $this->assertFails(new SecureImage, $file, 'not a valid upload');
    }

    #[Test]
    public function it_rejects_empty_uploads(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'empty_');
        file_put_contents($path, '');

        $file = new UploadedFile($path, 'photo.jpg', 'image/jpeg', null, true);

        $this->assertFails(new SecureImage, $file, 'not a valid upload');
    }

    #[Test]
    public function it_rejects_oversized_files(): void
    {
        $this->assertFails(
            new SecureImage(maxFileSize: 10),
            $this->realJpegUpload(),
            'file size exceeds'
        );
    }

    #[Test]
    public function it_rejects_files_without_image_magic_bytes(): void
    {
        $this->assertFails(
            new SecureImage,
            $this->uploadFromBytes('photo.jpg', 'not-an-image'),
            'does not appear to be a valid image'
        );
    }

    #[Test]
    public function it_rejects_magic_bytes_for_disallowed_mime_when_ctor_filters_types(): void
    {
        $rule = new SecureImage(allowedMimeTypes: ['image/png']);

        $this->assertFails($rule, $this->realJpegUpload('photo.jpg'), 'file type is not allowed');
    }

    #[Test]
    public function constructor_filters_imagetype_map_to_allowed_mimes(): void
    {
        $rule = new SecureImage(allowedMimeTypes: ['image/png']);

        $this->assertPasses($rule, $this->realPngUpload());
    }

    #[Test]
    public function it_rejects_when_getimagesize_fails(): void
    {
        // JPEG SOI marker only — magic passes, getimagesize fails.
        $this->assertFails(
            new SecureImage,
            $this->uploadFromBytes('photo.jpg', "\xFF\xD8\xFF\xD9"),
            'not a valid image or could not be read'
        );
    }

    #[Test]
    public function it_uses_getimagesize_mime_when_exif_type_is_unavailable(): void
    {
        self::$exifImageTypeOverride = fn () => false;

        $this->assertPasses(new SecureImage, $this->realJpegUpload());
    }

    #[Test]
    public function it_rejects_when_resolved_mime_is_not_allowed(): void
    {
        $rule = new SecureImage(allowedMimeTypes: ['image/jpeg']);

        self::$exifImageTypeOverride = fn () => IMAGETYPE_PNG;

        // Magic says jpeg (real file), but forced exif type is png and jpeg-only allow-list
        // clears png from imagetype map → falls through to getimagesize mime (jpeg) which passes.
        // Force getimagesize mime to a disallowed value instead:
        self::$getImageSizeOverride = fn () => [20, 20, IMAGETYPE_JPEG, 'mime' => 'image/gif'];
        self::$exifImageTypeOverride = fn () => false;

        $this->assertFails(
            $rule,
            $this->realJpegUpload(),
            'must be a valid image (jpeg, png, webp)'
        );
    }

    #[Test]
    public function it_rejects_when_mime_detection_methods_disagree(): void
    {
        self::$exifImageTypeOverride = fn () => IMAGETYPE_PNG;
        // Real file is JPEG (magic = jpeg). Exif forced to PNG → mismatch.
        $this->assertFails(
            new SecureImage,
            $this->realJpegUpload(),
            'file type verification failed'
        );
    }

    #[Test]
    public function it_rejects_zero_pixel_dimensions(): void
    {
        self::$getImageSizeOverride = fn () => [0, 0, IMAGETYPE_JPEG, 'mime' => 'image/jpeg'];

        $this->assertFails(new SecureImage, $this->realJpegUpload(), 'invalid dimensions');
    }

    #[Test]
    public function it_rejects_images_exceeding_max_pixel_count(): void
    {
        $this->assertFails(
            new SecureImage(maxPixelCount: 100),
            $this->realJpegUpload(width: 20, height: 20),
            'dimensions are too large'
        );
    }

    #[Test]
    public function it_rejects_images_exceeding_max_width(): void
    {
        $this->assertFails(
            new SecureImage(maxWidth: 10),
            $this->realJpegUpload(width: 20, height: 10),
            'width exceeds'
        );
    }

    #[Test]
    public function it_rejects_images_exceeding_max_height(): void
    {
        $this->assertFails(
            new SecureImage(maxHeight: 10),
            $this->realJpegUpload(width: 10, height: 20),
            'height exceeds'
        );
    }

    #[Test]
    public function it_rejects_images_below_min_width(): void
    {
        $this->assertFails(
            new SecureImage(minWidth: 50),
            $this->realJpegUpload(width: 20, height: 20),
            'width is below'
        );
    }

    #[Test]
    public function it_rejects_images_below_min_height(): void
    {
        $this->assertFails(
            new SecureImage(minHeight: 50),
            $this->realJpegUpload(width: 20, height: 20),
            'height is below'
        );
    }

    #[Test]
    public function it_rejects_when_finfo_reports_disallowed_mime(): void
    {
        $rule = new class (['image/jpeg']) extends SecureImage
        {
            protected function verifyMagicBytes(string $path): ?string
            {
                return 'image/jpeg';
            }
        };

        self::$exifImageTypeOverride = fn () => IMAGETYPE_JPEG;
        self::$getImageSizeOverride = fn () => [20, 20, IMAGETYPE_JPEG, 'mime' => 'image/jpeg'];

        // Real PNG contents → finfo returns image/png (not in jpeg-only allow-list).
        $this->assertFails($rule, $this->realPngUpload('photo.jpg'), 'file type is not allowed');
    }

    #[Test]
    public function it_rejects_when_finfo_mime_disagrees_with_magic_bytes(): void
    {
        $rule = new class extends SecureImage
        {
            protected function verifyMagicBytes(string $path): ?string
            {
                return 'image/jpeg';
            }
        };

        self::$exifImageTypeOverride = fn () => IMAGETYPE_JPEG;
        self::$getImageSizeOverride = fn () => [20, 20, IMAGETYPE_JPEG, 'mime' => 'image/jpeg'];

        $this->assertFails($rule, $this->realPngUpload('photo.jpg'), 'could not be verified');
    }

    #[Test]
    public function it_rejects_dangerous_framework_reported_mime(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'jpeg_');
        $image = imagecreatetruecolor(20, 20);
        imagejpeg($image, $path, 90);
        imagedestroy($image);

        $file = new class ($path, 'photo.jpg', 'image/jpeg', null, true) extends UploadedFile
        {
            public function getMimeType(): string
            {
                return 'text/plain';
            }
        };

        $this->assertFails(new SecureImage, $file, 'file type is not allowed');
    }

    #[Test]
    public function it_rejects_when_framework_mime_is_allowed_but_mismatches_contents(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'jpeg_');
        $image = imagecreatetruecolor(20, 20);
        imagejpeg($image, $path, 90);
        imagedestroy($image);

        $file = new class ($path, 'photo.jpg', 'image/jpeg', null, true) extends UploadedFile
        {
            public function getMimeType(): string
            {
                return 'image/png';
            }
        };

        $this->assertFails(new SecureImage, $file, 'does not match its contents');
    }

    #[Test]
    public function it_rejects_when_image_cannot_be_decoded(): void
    {
        self::$imageCreateJpegThrows = true;

        $this->assertFails(new SecureImage, $this->realJpegUpload(), 'could not be decoded');
    }

    #[Test]
    public function it_rejects_when_jpeg_decode_returns_false(): void
    {
        self::$imageCreateJpegReturnsFalse = true;

        $this->assertFails(new SecureImage, $this->realJpegUpload(), 'could not be decoded');
    }

    #[Test]
    public function it_rejects_when_file_disappears_after_validation(): void
    {
        $rule = new class extends SecureImage
        {
            protected function verifyImageDecoding(string $path, string $mimeType): bool
            {
                SecureImageTest::$forceFileMissing = true;

                return true;
            }
        };

        $this->assertFails($rule, $this->realJpegUpload(), 'file verification failed');
    }

    #[Test]
    public function it_rejects_when_filesize_changes_after_validation(): void
    {
        $rule = new class extends SecureImage
        {
            protected function verifyImageDecoding(string $path, string $mimeType): bool
            {
                SecureImageTest::$forceFilesizeMismatch = true;

                return true;
            }
        };

        $this->assertFails($rule, $this->realJpegUpload(), 'file verification failed');
    }

    #[Test]
    public function verify_magic_bytes_returns_null_when_fopen_fails(): void
    {
        self::$fopenReturnsFalse = true;

        $rule = new SecureImage;
        $method = new \ReflectionMethod(SecureImage::class, 'verifyMagicBytes');

        $path = tempnam(sys_get_temp_dir(), 'fopen_');
        file_put_contents($path, "\xFF\xD8\xFF\xE0");

        $this->assertNull($method->invoke($rule, $path));
    }

    #[Test]
    public function it_rejects_filenames_with_null_bytes(): void
    {
        $this->assertFails(
            new SecureImage,
            $this->realJpegUpload("pho\0to.jpg"),
            'invalid characters'
        );
    }

    #[Test]
    public function it_rejects_filenames_with_encoded_null_bytes(): void
    {
        $this->assertFails(
            new SecureImage,
            $this->realJpegUpload('pho%00to.jpg'),
            'invalid characters'
        );
    }

    #[Test]
    public function it_rejects_dangerous_double_extensions(): void
    {
        $this->assertFails(
            new SecureImage,
            $this->realJpegUpload('image.php.jpg'),
            'prohibited extension'
        );
    }

    #[Test]
    public function it_rejects_dangerous_final_extensions(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'jpeg_');
        $image = imagecreatetruecolor(20, 20);
        imagejpeg($image, $path, 90);
        imagedestroy($image);

        // Client original name ends with .php even though bytes are jpeg.
        $file = new UploadedFile($path, 'photo.php', 'image/jpeg', null, true);

        $this->assertFails(new SecureImage, $file, 'prohibited extension');
    }

    #[Test]
    public function it_rejects_disallowed_extensions(): void
    {
        $this->assertFails(
            new SecureImage,
            $this->realJpegUpload('photo.gif'),
            'must be a valid image file'
        );
    }

    #[Test]
    public function it_rejects_suspicious_filename_patterns(): void
    {
        $this->assertFails(
            new SecureImage,
            $this->realJpegUpload('shell.jpg'),
            'filename is not allowed'
        );
    }

    #[Test]
    public function verify_magic_bytes_returns_null_for_missing_file(): void
    {
        $rule = new SecureImage;
        $method = new \ReflectionMethod(SecureImage::class, 'verifyMagicBytes');

        $this->assertNull($method->invoke($rule, '/tmp/does-not-exist-'.uniqid('', true)));
    }

    #[Test]
    public function verify_magic_bytes_returns_null_for_unreadable_or_short_header(): void
    {
        $rule = new SecureImage;
        $method = new \ReflectionMethod(SecureImage::class, 'verifyMagicBytes');

        $path = tempnam(sys_get_temp_dir(), 'short_');
        file_put_contents($path, 'ab');

        $this->assertNull($method->invoke($rule, $path));
    }

    #[Test]
    public function verify_magic_bytes_accepts_webp_riff_header(): void
    {
        $rule = new SecureImage;
        $method = new \ReflectionMethod(SecureImage::class, 'verifyMagicBytes');

        $path = tempnam(sys_get_temp_dir(), 'webp_');
        // RIFF....WEBP
        file_put_contents($path, 'RIFF'.$this->bytes(4)."WEBP");

        $this->assertSame('image/webp', $method->invoke($rule, $path));
    }

    #[Test]
    public function verify_magic_bytes_rejects_riff_without_webp(): void
    {
        $rule = new SecureImage;
        $method = new \ReflectionMethod(SecureImage::class, 'verifyMagicBytes');

        $path = tempnam(sys_get_temp_dir(), 'riff_');
        file_put_contents($path, 'RIFF'.$this->bytes(4)."XXXX");

        $this->assertNull($method->invoke($rule, $path));
    }

    #[Test]
    public function verify_image_decoding_handles_png_and_webp(): void
    {
        $rule = new SecureImage;
        $method = new \ReflectionMethod(SecureImage::class, 'verifyImageDecoding');

        $png = $this->realPngUpload();
        $this->assertTrue($method->invoke($rule, $png->getPathname(), 'image/png'));

        $webp = $this->realWebpUpload();
        $this->assertTrue($method->invoke($rule, $webp->getPathname(), 'image/webp'));
    }

    #[Test]
    public function verify_image_decoding_returns_false_for_failed_png_decode(): void
    {
        self::$imageCreatePngReturnsFalse = true;

        $rule = new SecureImage;
        $method = new \ReflectionMethod(SecureImage::class, 'verifyImageDecoding');

        $this->assertFalse($method->invoke($rule, $this->realPngUpload()->getPathname(), 'image/png'));
    }

    #[Test]
    public function verify_image_decoding_returns_false_for_failed_webp_decode(): void
    {
        self::$imageCreateWebpReturnsFalse = true;

        $rule = new SecureImage;
        $method = new \ReflectionMethod(SecureImage::class, 'verifyImageDecoding');

        $this->assertFalse($method->invoke($rule, $this->realWebpUpload()->getPathname(), 'image/webp'));
    }

    #[Test]
    public function verify_image_decoding_falls_back_when_webp_function_missing(): void
    {
        self::$webpFunctionMissing = true;

        $rule = new SecureImage;
        $method = new \ReflectionMethod(SecureImage::class, 'verifyImageDecoding');

        $path = tempnam(sys_get_temp_dir(), 'webp_');
        file_put_contents($path, 'RIFF'.$this->bytes(4)."WEBP");

        $this->assertTrue($method->invoke($rule, $path, 'image/webp'));
    }

    #[Test]
    public function verify_image_decoding_returns_false_for_unknown_mime(): void
    {
        $rule = new SecureImage;
        $method = new \ReflectionMethod(SecureImage::class, 'verifyImageDecoding');

        $this->assertFalse($method->invoke($rule, $this->realJpegUpload()->getPathname(), 'image/gif'));
    }

    #[Test]
    public function it_skips_exif_block_when_exif_extension_missing(): void
    {
        self::$exifFunctionMissing = true;

        $this->assertPasses(new SecureImage, $this->realJpegUpload());
    }

    private function bytes(int $length): string
    {
        return str_repeat("\0", $length);
    }
}

}
