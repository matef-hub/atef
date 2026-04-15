<?php

namespace Database\Factories;

use App\Models\Document;
use Database\Factories\Concerns\CreatesPlaceholderPdf;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Document>
 */
class DocumentFactory extends Factory
{
    use CreatesPlaceholderPdf;

    protected $model = Document::class;

    public function definition(): array
    {
        $issueDate = fake()->dateTimeBetween('-24 months', '-1 month');
        $expiryDate = (clone $issueDate)->modify('+' . fake()->numberBetween(6, 36) . ' months');
        $documentNames = [
            'رخصة تشغيل',
            'شهادة قيد',
            'تفويض إداري',
            'ترخيص مزاولة',
            'اعتماد مكتب',
            'موافقة تنظيمية',
            'محرر رسمي',
            'شهادة تسجيل',
        ];
        $issuers = [
            'محافظة القاهرة',
            'الهيئة العامة للاستثمار',
            'السجل التجاري',
            'الغرفة التجارية',
            'الوحدة المحلية',
            'الهيئة الهندسية',
            'نقابة المحامين',
            'وزارة العدل',
        ];

        return [
            'docu_name' => fake()->randomElement($documentNames) . ' رقم ' . fake()->numberBetween(1, 50),
            'doc_numer' => 'DOC-' . fake()->unique()->numerify('####-##'),
            'docu_issu_from' => fake()->randomElement($issuers),
            'docu_iss_date' => $issueDate->format('Y-m-d'),
            'docu_expiry_date' => $expiryDate->format('Y-m-d'),
            'docu_pdf' => 'seed/documents/' . Str::uuid() . '.pdf',
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (Document $document): void {
            if (!$document->docu_pdf) {
                return;
            }

            self::storePlaceholderPdf(
                $document->docu_pdf,
                'Document ' . $document->doc_numer
            );
        });
    }
}
