<?php

declare(strict_types=1);

/*
 * This file is part of the nelexa/zip package.
 * (c) Ne-Lexa <http://github.com/Ne-Lexa/php-zip>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpZip\Util;

use PhpZip\Util\Iterator\IgnoreFilesFilterIterator;
use PhpZip\Util\Iterator\IgnoreFilesRecursiveFilterIterator;

/**
 * Files util.
 *
 * @internal
 */
final class FilesUtil
{
    /**
     * Is empty directory.
     *
     * @param string $dir Directory
     */
    public static function isEmptyDir(string $dir): bool
    {
        if (!is_readable($dir)) {
            return false;
        }

        return \count(scandir($dir)) === 2;
    }

    /**
     * Remove recursive directory.
     *
     * @param string $dir directory path
     */
    public static function removeDir(string $dir): void
    {
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        /** @var \SplFileInfo $fileInfo */
        foreach ($files as $fileInfo) {
            $function = ($fileInfo->isDir() ? 'rmdir' : 'unlink');
            $function($fileInfo->getPathname());
        }
        @rmdir($dir);
    }

    /**
     * Convert glob pattern to regex pattern.
     */
    public static function convertGlobToRegEx(string $globPattern): string
    {
        // Remove beginning and ending * globs because they're useless
        $globPattern = trim($globPattern, '*');
        $escaping = false;
        $inCurrent = 0;
        $chars = str_split($globPattern);
        $regexPattern = '';

        foreach ($chars as $currentChar) {
            switch ($currentChar) {
                case '*':
                    $regexPattern .= ($escaping ? '\\*' : '.*');
                    $escaping = false;
                    break;

                case '?':
                    $regexPattern .= ($escaping ? '\\?' : '.');
                    $escaping = false;
                    break;

                case '.':
                case '(':
                case ')':
                case '+':
                case '|':
                case '^':
                case '$':
                case '@':
                case '%':
                    $regexPattern .= '\\' . $currentChar;
                    $escaping = false;
                    break;

                case '\\':
                    if ($escaping) {
                        $regexPattern .= '\\\\';
                        $escaping = false;
                    } else {
                        $escaping = true;
                    }
                    break;

                case '{':
                    if ($escaping) {
                        $regexPattern .= '\\{';
                    } else {
                        $regexPattern = '(';
                        $inCurrent++;
                    }
                    $escaping = false;
                    break;

                case '}':
                    if ($inCurrent > 0 && !$escaping) {
                        $regexPattern .= ')';
                        $inCurrent--;
                    } elseif ($escaping) {
                        $regexPattern = '\\}';
                    } else {
                        $regexPattern = '}';
                    }
                    $escaping = false;
                    break;

                case ',':
                    if ($inCurrent > 0 && !$escaping) {
                        $regexPattern .= '|';
                    } elseif ($escaping) {
                        $regexPattern .= '\\,';
                    } else {
                        $regexPattern = ',';
                    }
                    break;
                default:
                    $escaping = false;
                    $regexPattern .= $currentChar;
            }
        }

        return $regexPattern;
    }

    /**
     * Search files.
     *
     * @return array Searched file list
     */
    public static function fileSearchWithIgnore(string $inputDir, bool $recursive = true, array $ignoreFiles = []): array
    {
        if ($recursive) {
            $directoryIterator = new \RecursiveDirectoryIterator($inputDir);

            if (!empty($ignoreFiles)) {
                $directoryIterator = new IgnoreFilesRecursiveFilterIterator($directoryIterator, $ignoreFiles);
            }
            $iterator = new \RecursiveIteratorIterator($directoryIterator);
        } else {
            $directoryIterator = new \DirectoryIterator($inputDir);

            if (!empty($ignoreFiles)) {
                $directoryIterator = new IgnoreFilesFilterIterator($directoryIterator, $ignoreFiles);
            }
            $iterator = new \IteratorIterator($directoryIterator);
        }

        $fileList = [];

        foreach ($iterator as $file) {
            if ($file instanceof \SplFileInfo) {
                $fileList[] = $file->getPathname();
            }
        }

        return $fileList;
    }

    /**
     * Search files from glob pattern.
     *
     * @return array Searched file list
     */
    public static function globFileSearch(string $globPattern, int $flags = 0, bool $recursive = true): array
    {
        $files = glob($globPattern, $flags);

        if (!$recursive) {
            return $files;
        }

        foreach (glob(\dirname($globPattern) . \DIRECTORY_SEPARATOR . '*', \GLOB_ONLYDIR | \GLOB_NOSORT) as $dir) {
            // Unpacking the argument via ... is supported starting from php 5.6 only
            /** @noinspection SlowArrayOperationsInLoopInspection */
            $files = array_merge($files, self::globFileSearch($dir . \DIRECTORY_SEPARATOR . basename($globPattern), $flags, $recursive));
        }

        return $files;
    }

    /**
     * Search files from regex pattern.
     *
     * @return array Searched file list
     */
    public static function regexFileSearch(string $folder, string $pattern, bool $recursive = true): array
    {
        if ($recursive) {
            $directoryIterator = new \RecursiveDirectoryIterator($folder);
            $iterator = new \RecursiveIteratorIterator($directoryIterator);
        } else {
            $directoryIterator = new \DirectoryIterator($folder);
            $iterator = new \IteratorIterator($directoryIterator);
        }

        $regexIterator = new \RegexIterator($iterator, $pattern, \RegexIterator::MATCH);
        $fileList = [];

        foreach ($regexIterator as $file) {
            if ($file instanceof \SplFileInfo) {
                $fileList[] = $file->getPathname();
            }
        }

        return $fileList;
    }

    /**
     * Convert bytes to human size.
     *
     * @param int         $size Size bytes
     * @param string|null $unit Unit support 'GB', 'MB', 'KB'
     */
    public static function humanSize(int $size, ?string $unit = null): string
    {
        if (($unit === null && $size >= 1 << 30) || $unit === 'GB') {
            return number_format($size / (1 << 30), 2) . 'GB';
        }

        if (($unit === null && $size >= 1 << 20) || $unit === 'MB') {
            return number_format($size / (1 << 20), 2) . 'MB';
        }

        if (($unit === null && $size >= 1 << 10) || $unit === 'KB') {
            return number_format($size / (1 << 10), 2) . 'KB';
        }

        return number_format($size) . ' bytes';
    }

    /**
     * Normalizes zip path.
     *
     * @param string $path Zip path
     */
    public static function normalizeZipPath(string $path): string
    {
        return implode(
            \DIRECTORY_SEPARATOR,
            array_filter(
                explode('/', $path),
                static fn ($part) => $part !== '.' && $part !== '..'
            )
        );
    }

    /**
     * Returns whether the file path is an absolute path.
     *
     * @param string $file A file path
     *
     * @see source symfony filesystem component
     */
    public static function isAbsolutePath(string $file): bool
    {
        return strspn($file, '/\\', 0, 1)
            || (
                \strlen($file) > 3 && ctype_alpha($file[0])
                && $file[1] === ':'
                && strspn($file, '/\\', 2, 1)
            )
            || parse_url($file, \PHP_URL_SCHEME) !== null;
    }

    public static function symlink(string $target, string $path, bool $allowSymlink): bool
    {
        if (\DIRECTORY_SEPARATOR === '\\' || !$allowSymlink) {
            return file_put_contents($path, $target) !== false;
        }

        return symlink($target, $path);
    }

    public static function isBadCompressionFile(string $file): bool
    {
        $badCompressFileExt = [
            'dic',
            'dng',
            'f4v',
            'flipchart',
            'h264',
            'lrf',
            'mobi',
            'mts',
            'nef',
            'pspimage',
        ];

        $ext = strtolower(pathinfo($file, \PATHINFO_EXTENSION));

        if (\in_array($ext, $badCompressFileExt, true)) {
            return true;
        }

        $mimeType = self::getMimeTypeFromFile($file);

        return self::isBadCompressionMimeType($mimeType);
    }

    public static function isBadCompressionMimeType(string $mimeType): bool
    {
        static $badDeflateCompMimeTypes = [
            'application/epub+zip',
            'application/gzip',
            'application/vnd.debian.binary-package',
            'application/vnd.oasis.opendocument.graphics',
            'application/vnd.oasis.opendocument.presentation',
            'application/vnd.oasis.opendocument.text',
            'application/vnd.oasis.opendocument.text-master',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.rn-realmedia',
            'application/x-7z-compressed',
            'application/x-arj',
            'application/x-bzip2',
            'application/x-hwp',
            'application/x-lzip',
            'application/x-lzma',
            'application/x-ms-reader',
            'application/x-rar',
            'application/x-rpm',
            'application/x-stuffit',
            'application/x-tar',
            'application/x-xz',
            'application/zip',
            'application/zlib',
            'audio/flac',
            'audio/mpeg',
            'audio/ogg',
            'audio/vnd.dolby.dd-raw',
            'audio/webm',
            'audio/x-ape',
            'audio/x-hx-aac-adts',
            'audio/x-m4a',
            'audio/x-m4a',
            'audio/x-wav',
            'image/gif',
            'image/heic',
            'image/jp2',
            'image/jpeg',
            'image/png',
            'image/vnd.djvu',
            'image/webp',
            'image/x-canon-cr2',
            'video/ogg',
            'video/webm',
            'video/x-matroska',
            'video/x-ms-asf',
            'x-epoc/x-sisx-app',
        ];

        return \in_array($mimeType, $badDeflateCompMimeTypes, true);
    }

    /**
     * @noinspection PhpComposerExtensionStubsInspection
     */
    public static function getMimeTypeFromFile(string $file): string
    {
        if (\function_exists('mime_content_type')) {
            return mime_content_type($file);
        }

        return 'application/octet-stream';
    }

    /**
     * @noinspection PhpComposerExtensionStubsInspection
     */
    public static function getMimeTypeFromString(string $contents): string
    {
        $finfo = new \finfo(\FILEINFO_MIME);
        $mimeType = $finfo->buffer($contents);

        if ($mimeType === false) {
            $mimeType = 'application/octet-stream';
        }

        return explode(';', $mimeType)[0];
    }
}
