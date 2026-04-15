<?php

namespace Database\Factories;

use App\Models\CaseHearing;
use App\Models\LegalCase;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CaseHearing>
 */
class CaseHearingFactory extends Factory
{
    protected $model = CaseHearing::class;

    public function definition(): array
    {
        $hearingDate = fake()->dateTimeBetween('-6 months', '+1 month');
        $hasNextSession = fake()->boolean(60);
        $nextHearingAt = $hasNextSession
            ? (clone $hearingDate)->modify('+' . fake()->numberBetween(10, 45) . ' days')
            : null;

        return [
            'legal_case_id' => LegalCase::factory(),
            'hearing_date' => $hearingDate->format('Y-m-d'),
            'roll_number' => 'RL-' . fake()->numerify('###'),
            'court_decision' => fake()->randomElement([
                'تأجيل الدعوى للاطلاع وتقديم المذكرات.',
                'حجز الدعوى للحكم مع التصريح بالمذكرات.',
                'تأجيل الجلسة لإعلان الخصوم واستكمال المستندات.',
                'ندب خبير مع إلزام الطرفين بتقديم المستندات.',
                'سماع المرافعة وتأجيل الجلسة للنطق بالقرار.',
            ]),
            'next_hearing_at' => $nextHearingAt?->format('Y-m-d'),
            'notes' => fake()->randomElement([
                'تم إثبات حضور الوكيل القانوني.',
                'المستندات المقدمة تحت الفحص.',
                'يلزم تجهيز مذكرة دفاع إضافية.',
                'جرى التنبيه على الخصوم بموعد الجلسة القادمة.',
                'لا توجد ملاحظات إضافية.',
            ]),
        ];
    }
}
