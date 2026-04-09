<?php

namespace App\Models;

use App\Models\Concerns\HasStoredFiles;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class Contract extends Model
{
    use HasFactory;
    use HasStoredFiles;

    public const SIGN_OPTIONS = [
        'توقيع المقاول',
        'توقيع المدير العام',
        'استلام العقد',
        'تسليم مخالصة',
    ];

    protected $fillable = [
        'contr_number',
        'proje_name',
        'suppli_name',
        'proje_data',
        'Esnad_date',
        'Contar_date',
        'sign',
        'Pdf_image',
    ];

    protected $casts = [
        'Esnad_date' => 'date',
        'Contar_date' => 'date',
    ];

    protected $appends = [
        'pdf_image_url',
    ];

    public function getPdfImageUrlAttribute(): ?string
    {
        return $this->storedFileUrl('Pdf_image');
    }

    public static function generateUniqueContractNumber(): string
    {
        do {
            $number = (string) random_int(10000, 99999);
        } while (!static::isContractNumberAvailable($number));

        return $number;
    }

    public static function resolveContractNumber(?string $candidate = null): string
    {
        if ($candidate && preg_match('/^\d{5}$/', $candidate) && static::isContractNumberAvailable($candidate)) {
            return $candidate;
        }

        return static::generateUniqueContractNumber();
    }

    public static function availableProjectNames(): Collection
    {
        return static::query()
            ->whereNotNull('proje_name')
            ->where('proje_name', '!=', '')
            ->orderBy('proje_name')
            ->distinct()
            ->pluck('proje_name');
    }

    public static function availableSupplierNames(): Collection
    {
        return static::query()
            ->whereNotNull('suppli_name')
            ->where('suppli_name', '!=', '')
            ->orderBy('suppli_name')
            ->distinct()
            ->pluck('suppli_name');
    }

    public static function signOptions(): array
    {
        return self::SIGN_OPTIONS;
    }

    protected function sign(): Attribute
    {
        return Attribute::make(
            get: fn(?string $value) => $this->normalizeSignValues($value),
            set: fn(mixed $value) => $this->serializeSignValues($value),
        );
    }

    protected static function isContractNumberAvailable(string $number): bool
    {
        return static::query()
            ->where('contr_number', $number)
            ->doesntExist();
    }

    protected function normalizeSignValues(?string $value): array
    {
        if (blank($value)) {
            return [];
        }

        $decoded = json_decode($value, true);

        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return array_values(array_filter($decoded, fn($item) => filled($item)));
        }

        return array_values(array_filter(
            array_map('trim', explode(',', $value)),
            fn($item) => $item !== ''
        ));
    }

    protected function serializeSignValues(mixed $value): ?string
    {
        if (blank($value)) {
            return null;
        }

        $values = is_array($value) ? $value : [$value];
        $values = array_values(array_filter(
            array_map(fn($item) => trim((string) $item), $values),
            fn($item) => $item !== ''
        ));

        if ($values === []) {
            return null;
        }

        return json_encode($values, JSON_UNESCAPED_UNICODE);
    }
}
