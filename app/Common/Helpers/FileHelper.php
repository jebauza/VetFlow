<?php

namespace App\Common\Helpers;

use Carbon\CarbonImmutable;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class FileHelper
{
    const PATH = 'path';
    const MODIFIED = 'modified';
    const MODIFIED_TIMESTAMP = 'modified_timestamp';
    const SORT_ASC_VALUE = 'asc';
    const SORT_DESC_VALUE = 'desc';

    public static function saveFile(UploadedFile $file, string $folder = 'uploads', string $disk = 'public'): ?string
    {
        try {
            $path = $file->store($folder, $disk);
            // $filename = time() . '_' . $file->getClientOriginalName();
            // $path = $file->storeAs($path, $filename, $disk);

            return $path;
        } catch (\Exception $e) {
            // Optionally log the error
            return null;
        }
    }

    public static function getFilesInDirectoryByExtension(string $directory, string $extension, string $sort_modified = self::SORT_ASC_VALUE): Collection
    {
        $files = Storage::disk('local')->files($directory);
        $filterExtensionFiles = preg_grep("/.$extension/i", $files);

        $files = collect([]);
        foreach ($filterExtensionFiles as $filePath) {
            $fileTime = Storage::disk('local')->lastModified($filePath);

            $files->push([
                self::PATH => $filePath,
                self::MODIFIED => CarbonImmutable::createFromTimestamp($fileTime)->toDateTimeString(),
                self::MODIFIED_TIMESTAMP => $fileTime,
            ]);
        }

        if ($sort_modified == self::SORT_ASC_VALUE) {
            $files = $files->sortBy(self::MODIFIED_TIMESTAMP)->values();
        } else if ($sort_modified == self::SORT_DESC_VALUE) {
            $files = $files->sortByDesc(self::MODIFIED_TIMESTAMP)->values();
        }

        return $files;
    }

    public static function getPathMostRecentFileInDirectory(string $directory, string $extension): string
    {
        $files = Storage::disk('local')->files($directory);
        $filterXmlFiles = preg_grep("/.$extension/i", $files);

        $lastFileUpload = [];
        foreach ($filterXmlFiles as $filePath) {
            $fileTime = Storage::disk('local')->lastModified($filePath);
            $time = date('r', $fileTime);

            if (empty($lastFileUpload) || $lastFileUpload['timeModified'] < $fileTime) {
                $lastFileUpload['timeModified'] = $fileTime;
                $lastFileUpload['time'] = $time;
                $lastFileUpload['path'] = $filePath;
                $pathFile = $lastFileUpload['path'];
            }
        }

        return $pathFile;
    }

    public static function deleteFile(string $path, string $disk = 'public'): bool
    {
        if (Storage::disk($disk)->exists($path)) {
            return Storage::disk($disk)->delete($path);
        }

        return false;
    }
}
