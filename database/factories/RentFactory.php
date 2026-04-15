<?php

namespace Database\Factories;

use App\Models\Rent;
use Database\Factories\Concerns\CreatesPlaceholderPdf;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Rent>
 */
class RentFactory extends Factory
{
    use CreatesPlaceholderPdf;

    protected $model = Rent::class;

    public function definition(): array
    {
        $signDate = fake()->dateTimeBetween('-24 months', '+2 months');
        $durationMonths = fake()->randomElement([6, 9, 12, 18, 24]);
        $endDate = (clone $signDate)->modify('+' . $durationMonths . ' months');
        $tenantNames = [
            'أحمد محمد علي',
            'خالد عبد الرحمن',
            'محمود السيد',
            'ياسر سمير',
            'سارة إبراهيم',
            'منة الله حسن',
            'ريم مصطفى',
            'شيماء عادل',
        ];
        $ownerNames = [
            'الأستاذ محمد عاطف',
            'شركة العقار المميز',
            'مؤسسة الرؤية العقارية',
            'مجموعة الأمان للاستثمار',
            'شركة النيل للتطوير',
            'مكتب الأفق العقاري',
        ];

        return [
            'tenant_name' => fake()->randomElement($tenantNames),
            'renter_name' => fake()->randomElement($ownerNames),
            'home_data' => fake()->randomElement([
                'شقة إدارية - مدينة نصر',
                'وحدة سكنية - التجمع الخامس',
                'مكتب تجاري - وسط البلد',
                'مقر فرعي - المعادي',
                'وحدة إدارية - الشيخ زايد',
                'محل تجاري - مصر الجديدة',
            ]) . ' / ' . fake()->numberBetween(1, 30),
            'date_sign' => $signDate->format('Y-m-d'),
            'date_duration' => $durationMonths . ' شهر',
            'date_end' => $endDate->format('Y-m-d'),
            'insurance_mon' => fake()->numberBetween(5000, 25000),
            'Monthly_rent' => fake()->numberBetween(3000, 18000),
            'add_notes' => fake()->randomElement([
                'يشمل بند صيانة سنوي.',
                'التجديد يتم بموافقة كتابية.',
                'الاستلام تم بمحضر رسمي.',
                'يوجد ملحق خاص بالاستخدام الإداري.',
                'يلزم إخطار قبل الإخلاء بمدة كافية.',
            ]),
            'con_pdf' => 'seed/rents/' . Str::uuid() . '.pdf',
            'con_word' => null,
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (Rent $rent): void {
            if (!$rent->con_pdf) {
                return;
            }

            self::storePlaceholderPdf(
                $rent->con_pdf,
                'Rent agreement ' . $rent->id
            );
        });
    }
}
