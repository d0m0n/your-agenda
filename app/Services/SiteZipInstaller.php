<?php

namespace App\Services;

use App\Exceptions\InvalidSiteZipException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use ZipArchive;

class SiteZipInstaller
{
    private const ALLOWED_EXTENSIONS = [
        'html', 'htm', 'css', 'js', 'png', 'jpg', 'jpeg', 'gif', 'svg',
        'webp', 'ico', 'woff', 'woff2', 'ttf', 'json', 'txt', 'pdf', 'mp4',
    ];

    private const ALLOWED_SINGLE_FILE_EXTENSIONS = [
        'pdf', 'jpg', 'jpeg', 'png', 'gif', 'webp',
    ];

    private const MAX_TOTAL_BYTES = 200 * 1024 * 1024;

    private const MAX_FILE_COUNT = 1000;

    private const CHUNK_SIZE = 65536;

    private const INDEX_FILENAME = 'gian.htm';

    /**
     * Install the uploaded file into storage/app/public/sites/{uuid} and
     * return the path (relative to that directory) that should be opened.
     *
     * Zip archives are extracted and searched for gian.htm; a single PDF
     * or image file is stored as-is and opened directly, since there is
     * no archive structure to search within.
     */
    public function install(UploadedFile $file, string $uuid): string
    {
        if ($file->extension() === 'zip') {
            return $this->installZip($file, $uuid);
        }

        return $this->installSingleFile($file, $uuid);
    }

    private function installSingleFile(UploadedFile $file, string $uuid): string
    {
        $extension = $file->extension();

        if (! in_array($extension, self::ALLOWED_SINGLE_FILE_EXTENSIONS, true)) {
            throw new InvalidSiteZipException('対応していないファイル形式です。');
        }

        $destination = storage_path("app/public/sites/{$uuid}");
        $filename = 'document.'.$extension;

        File::ensureDirectoryExists($destination);
        $file->move($destination, $filename);

        $this->writeHtaccess(dirname($destination));

        return $filename;
    }

    private function installZip(UploadedFile $zipFile, string $uuid): string
    {
        $destination = storage_path("app/public/sites/{$uuid}");

        $zip = new ZipArchive;

        if ($zip->open($zipFile->getRealPath()) !== true) {
            throw new InvalidSiteZipException('Zipファイルを開けませんでした。');
        }

        try {
            $entries = $this->collectValidEntries($zip);

            File::ensureDirectoryExists($destination);

            $this->extractEntries($zip, $entries, $destination);
        } catch (InvalidSiteZipException $e) {
            File::deleteDirectory($destination);
            throw $e;
        } finally {
            $zip->close();
        }

        $indexPath = $this->locateIndexFile($destination);

        if ($indexPath === null) {
            File::deleteDirectory($destination);
            throw new InvalidSiteZipException('Zip内にgian.htmが見つかりませんでした。');
        }

        $this->writeHtaccess(dirname($destination));

        return $indexPath;
    }

    /**
     * Validate every entry and return the names that should be extracted.
     * Rejects the whole archive on any Zip Slip attempt or file-count overflow.
     *
     * @return list<string>
     */
    private function collectValidEntries(ZipArchive $zip): array
    {
        $valid = [];
        $fileCount = 0;

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $stat = $zip->statIndex($i);
            $name = $stat['name'];

            if (str_ends_with($name, '/')) {
                continue;
            }

            if (str_starts_with($name, '__MACOSX/') || str_contains($name, '/__MACOSX/')) {
                continue;
            }

            if (str_contains($name, '..') || str_starts_with($name, '/') || str_starts_with($name, '\\')) {
                throw new InvalidSiteZipException('不正なパスを含むZipファイルです。');
            }

            $extension = strtolower(pathinfo($name, PATHINFO_EXTENSION));

            if (! in_array($extension, self::ALLOWED_EXTENSIONS, true)) {
                continue;
            }

            $fileCount++;

            if ($fileCount > self::MAX_FILE_COUNT) {
                throw new InvalidSiteZipException(
                    'Zip内のファイル数が多すぎます(上限'.self::MAX_FILE_COUNT.'件)。'
                );
            }

            $valid[] = $name;
        }

        return $valid;
    }

    /**
     * Stream each entry to disk, enforcing the real (decompressed) byte
     * budget as data is written rather than trusting the archive's metadata.
     *
     * @param  list<string>  $entries
     */
    private function extractEntries(ZipArchive $zip, array $entries, string $destination): void
    {
        $totalBytes = 0;

        foreach ($entries as $entry) {
            $targetPath = $destination.'/'.$entry;
            File::ensureDirectoryExists(dirname($targetPath));

            $stream = $zip->getStream($entry);
            if ($stream === false) {
                continue;
            }

            $out = fopen($targetPath, 'wb');

            while (! feof($stream)) {
                $chunk = fread($stream, self::CHUNK_SIZE);
                if ($chunk === false || $chunk === '') {
                    break;
                }

                $totalBytes += strlen($chunk);

                if ($totalBytes > self::MAX_TOTAL_BYTES) {
                    fclose($stream);
                    fclose($out);
                    throw new InvalidSiteZipException('展開後のサイズが大きすぎます(上限200MB)。');
                }

                fwrite($out, $chunk);
            }

            fclose($stream);
            fclose($out);
        }
    }

    /**
     * Look for gian.htm at the archive root, or exactly one folder down.
     */
    private function locateIndexFile(string $destination): ?string
    {
        if (is_file($destination.'/'.self::INDEX_FILENAME)) {
            return self::INDEX_FILENAME;
        }

        foreach (glob($destination.'/*', GLOB_ONLYDIR) ?: [] as $dir) {
            if (basename($dir) === '__MACOSX') {
                continue;
            }

            if (is_file($dir.'/'.self::INDEX_FILENAME)) {
                return basename($dir).'/'.self::INDEX_FILENAME;
            }
        }

        return null;
    }

    /**
     * Production runs on shared Apache hosting with no root access, so PHP
     * execution and search indexing under sites/ are both blocked via .htaccess
     * rather than server config. One file at the sites/ root covers every
     * extracted site underneath it.
     *
     * AddDefaultCharset Off stops Apache from forcing charset=UTF-8 on every
     * response; uploaded sites are a mix of UTF-8 and legacy Shift_JIS (e.g.
     * Word-exported HTML), and each already declares its own <meta charset>.
     * Without this, Apache's header overrides that declaration and the
     * browser mojibakes anything that isn't UTF-8.
     */
    private function writeHtaccess(string $sitesRootDir): void
    {
        $path = $sitesRootDir.'/.htaccess';

        if (File::exists($path)) {
            return;
        }

        File::ensureDirectoryExists($sitesRootDir);
        File::put($path, <<<'HTACCESS'
            AddDefaultCharset Off
            RemoveHandler .php .phtml .phar
            RemoveType .php
            <FilesMatch "\.(php|phtml|phar|cgi|pl)$">
                Require all denied
            </FilesMatch>
            Header set X-Robots-Tag "noindex, nofollow"
            HTACCESS);
    }
}
