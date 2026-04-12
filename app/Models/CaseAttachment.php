<?php

namespace App\Models;

use App\Models\Concerns\HasStoredFiles;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CaseAttachment extends Model
{
    use HasFactory;
    use HasStoredFiles;

    protected $fillable = [
        'legal_case_id',
        'file_path',
        'original_name',
        'mime_type',
        'file_size',
    ];

    protected $appends = [
        'file_url',
        'file_extension',
    ];

    public function legalCase(): BelongsTo
    {
        return $this->belongsTo(LegalCase::class);
    }

    public function getFileUrlAttribute(): ?string
    {
        return $this->storedFileUrl('file_path');
    }

    public function getFileExtensionAttribute(): string
    {
        return strtolower(pathinfo((string) $this->original_name, PATHINFO_EXTENSION));
    }
}
