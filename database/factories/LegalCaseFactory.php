<?php

namespace Database\Factories;

use App\Models\LegalCase;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\LegalCase>
 */
class LegalCaseFactory extends Factory
{
    protected $model = LegalCase::class;

    public function definition(): array
    {
        $caseType = fake()->randomElement([LegalCase::TYPE_CIVIL, LegalCase::TYPE_CRIMINAL]);

        return $caseType === LegalCase::TYPE_CIVIL
            ? $this->civilCaseDefinition()
            : $this->criminalCaseDefinition();
    }

    protected function civilCaseDefinition(): array
    {
        $filedAt = fake()->dateTimeBetween('-20 months', '-3 months');
        $firstSessionAt = (clone $filedAt)->modify('+' . fake()->numberBetween(7, 45) . ' days');
        $judgmentIssuedAt = fake()->boolean(65)
            ? fake()->dateTimeBetween($firstSessionAt, 'now')
            : null;
        $hasAppeal = $judgmentIssuedAt ? fake()->boolean(40) : false;
        $appealSessionAt = $hasAppeal
            ? (clone $judgmentIssuedAt)->modify('+' . fake()->numberBetween(10, 60) . ' days')
            : null;
        $primaryParty = fake()->randomElement([
            'شركة الرؤية المتحدة',
            'مؤسسة الأمان التجارية',
            'أحمد فؤاد محمود',
            'ليلى محمد السيد',
            'شركة النيل للمقاولات',
            'مكتب الصفوة للاستشارات',
        ]);
        $opponentParty = fake()->randomElement([
            'شركة المدار الحديثة',
            'محمود عبد العزيز',
            'سارة سمير حسن',
            'مجموعة البناء المتخصص',
            'مؤسسة الإعمار الوطنية',
            'شركة التميز العقاري',
        ]);

        return [
            'case_type' => LegalCase::TYPE_CIVIL,
            'primary_party_name' => $primaryParty,
            'opponent_party_name' => $opponentParty,
            'case_number' => 'MD/' . fake()->year() . '/' . fake()->unique()->numberBetween(100, 999),
            'case_filed_at' => $filedAt->format('Y-m-d'),
            'first_session_at' => $firstSessionAt->format('Y-m-d'),
            'subject' => fake()->randomElement([
                'مطالبة بتنفيذ التزامات تعاقدية وتعويض عن التأخير.',
                'دعوى فسخ عقد مع طلب تسليم الموقع وإثبات الحالة.',
                'مطالبة مالية ناشئة عن عقد تنفيذ وملاحقه.',
                'نزاع مدني متعلق بإدارة مشروع وتسليم مستندات.',
                'دعوى إثبات حق والتعويض عن الإخلال بالتعاقد.',
            ]),
            'primary_court_name' => fake()->randomElement([
                'محكمة شمال القاهرة الابتدائية',
                'محكمة جنوب الجيزة',
                'محكمة القاهرة الاقتصادية',
                'محكمة استئناف القاهرة',
                'محكمة 6 أكتوبر الابتدائية',
            ]),
            'circuit_number' => (string) fake()->numberBetween(1, 25),
            'report_date' => null,
            'detention_order_number' => null,
            'judgment_issued_at' => $judgmentIssuedAt?->format('Y-m-d'),
            'has_appeal' => $hasAppeal,
            'appeal_appellant_name' => $hasAppeal ? $primaryParty : null,
            'appeal_respondent_name' => $hasAppeal ? $opponentParty : null,
            'appeal_number' => $hasAppeal ? 'EST/' . fake()->year() . '/' . fake()->unique()->numberBetween(100, 999) : null,
            'appeal_court_name' => $hasAppeal ? fake()->randomElement([
                'محكمة استئناف القاهرة',
                'محكمة استئناف الجيزة',
                'الدائرة المدنية بمحكمة الاستئناف',
            ]) : null,
            'appeal_circuit_number' => $hasAppeal ? (string) fake()->numberBetween(1, 15) : null,
            'appeal_session_period' => $hasAppeal
                ? fake()->randomElement(array_keys(LegalCase::appealSessionPeriodOptions()))
                : null,
            'appeal_first_session_at' => $appealSessionAt?->format('Y-m-d'),
        ];
    }

    protected function criminalCaseDefinition(): array
    {
        $reportDate = fake()->dateTimeBetween('-18 months', '-2 months');
        $firstSessionAt = (clone $reportDate)->modify('+' . fake()->numberBetween(5, 30) . ' days');

        return [
            'case_type' => LegalCase::TYPE_CRIMINAL,
            'primary_party_name' => fake()->randomElement([
                'النيابة العامة',
                'أحمد حسن عبد الله',
                'محمد علي إبراهيم',
                'شركة الأمان للتجارة',
                'فاطمة سمير محمود',
            ]),
            'opponent_party_name' => fake()->randomElement([
                'محمود حسن علي',
                'خالد إبراهيم يوسف',
                'شركة الرؤية الصناعية',
                'طارق عبد المنعم',
                'حسام محمد السيد',
            ]),
            'case_number' => 'GN/' . fake()->year() . '/' . fake()->unique()->numberBetween(1000, 9999),
            'case_filed_at' => null,
            'first_session_at' => $firstSessionAt->format('Y-m-d'),
            'subject' => null,
            'primary_court_name' => null,
            'circuit_number' => null,
            'report_date' => $reportDate->format('Y-m-d'),
            'detention_order_number' => 'HBS-' . fake()->numerify('###-##'),
            'judgment_issued_at' => null,
            'has_appeal' => false,
            'appeal_appellant_name' => null,
            'appeal_respondent_name' => null,
            'appeal_number' => null,
            'appeal_court_name' => null,
            'appeal_circuit_number' => null,
            'appeal_session_period' => null,
            'appeal_first_session_at' => null,
        ];
    }
}
