<?php

namespace App\Models\Concerns;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

trait HasStoredFiles
{
    protected string $storageDisk = 'public';

    public function storageDiskName(): string
    {
        return $this->storageDisk;
    }

    public function storedFileUrl(string $attribute): ?string
    {
        $path = $this->{$attribute};

        if (!$path) {
            return null;
        }

        return Storage::disk($this->storageDiskName())->url($path);
    }

    public function replaceStoredFile(?UploadedFile $file, string $attribute, string $directory): void
    {
        if (!$file) {
            return;
        }

        $this->deleteStoredFile($attribute);

        $this->{$attribute} = $file->store($directory, $this->storageDiskName());
    }

    public function deleteStoredFile(string $attribute): void
    {
        $path = $this->{$attribute};

        if (!$path) {
            return;
        }

        Storage::disk($this->storageDiskName())->delete($path);
    }
}
