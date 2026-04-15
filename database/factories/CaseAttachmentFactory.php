<?php

namespace Database\Factories;

use App\Models\CaseAttachment;
use App\Models\LegalCase;
use Database\Factories\Concerns\CreatesPlaceholderPdf;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CaseAttachment>
 */
class CaseAttachmentFactory extends Factory
{
    use CreatesPlaceholderPdf;

    protected $model = CaseAttachment::class;

    public function definition(): array
    {
        return [
            'legal_case_id' => LegalCase::factory(),
            'file_path' => 'seed/case-attachments/' . Str::uuid() . '.pdf',
            'original_name' => 'attachment-' . fake()->unique()->numerify('####') . '.pdf',
            'mime_type' => 'application/pdf',
            'file_size' => 0,
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (CaseAttachment $attachment): void {
            $size = self::storePlaceholderPdf(
                $attachment->file_path,
                'Case attachment ' . $attachment->id
            );

            $attachment->forceFill([
                'file_size' => $size,
            ])->saveQuietly();
        });
    }
}
