<?php

namespace Database\Factories;

use App\Models\Contract;
use Database\Factories\Concerns\CreatesPlaceholderPdf;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Contract>
 */
class ContractFactory extends Factory
{
    use CreatesPlaceholderPdf;

    protected $model = Contract::class;

    public function definition(): array
    {
        $awardDate = fake()->dateTimeBetween('-18 months', '-2 weeks');
        $contractDate = (clone $awardDate)->modify('+' . fake()->numberBetween(2, 28) . ' days');
        $projectNames = [
            'تطوير مجمع إداري',
            'تحديث شبكة المرافق',
            'إنشاء مبنى الخدمات',
            'تأهيل مقر إداري',
            'توسعة مركز قانوني',
            'توريد أعمال التشطيب',
            'إعادة تأهيل الأرشيف',
            'تطوير بيئة العمل',
        ];
        $contractorNames = [
            'شركة الصفوة للمقاولات',
            'مؤسسة البناء الحديث',
            'شركة الأفق الهندسية',
            'مجموعة الريادة للمشروعات',
            'شركة النخبة للأعمال المدنية',
            'مقاولات النهضة المتحدة',
            'شركة المدار للتنفيذ',
            'مكتب الإعمار المتخصص',
        ];
        $signOptions = Contract::signOptions();
        $signCount = fake()->numberBetween(0, count($signOptions));

        return [
            'contr_number' => (string) fake()->unique()->numberBetween(10000, 99999),
            'proje_name' => fake()->randomElement($projectNames) . ' - ' . fake()->numberBetween(1, 30),
            'suppli_name' => fake()->randomElement($contractorNames),
            'proje_data' => fake()->randomElement([
                'المرحلة الأولى - نطاق إداري',
                'أعمال تنفيذ وتسليم ابتدائي',
                'ملحقات ومواصفات تعاقدية',
                'موقع المشروع داخل القاهرة',
                'بنود تنفيذ ومتابعة ميدانية',
            ]),
            'Esnad_date' => $awardDate->format('Y-m-d'),
            'Contar_date' => $contractDate->format('Y-m-d'),
            'sign' => fake()->randomElements($signOptions, $signCount),
            'Pdf_image' => 'seed/contracts/' . Str::uuid() . '.pdf',
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (Contract $contract): void {
            if (!$contract->Pdf_image) {
                return;
            }

            self::storePlaceholderPdf(
                $contract->Pdf_image,
                'Contract ' . $contract->contr_number
            );
        });
    }
}
