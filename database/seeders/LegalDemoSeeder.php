<?php

namespace Database\Seeders;

use App\Models\CaseAttachment;
use App\Models\CaseHearing;
use App\Models\Contract;
use App\Models\Document;
use App\Models\LegalCase;
use App\Models\Rent;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class LegalDemoSeeder extends Seeder
{
    public function run(): void
    {
        User::factory(29)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
        ]);

        Contract::factory(30)->create();
        Document::factory(30)->create();
        Rent::factory(30)->create();

        $cases = LegalCase::factory(30)->create();
        $caseIds = $cases->modelKeys();

        CaseHearing::factory(30)
            ->state(new Sequence(
                fn (Sequence $sequence): array => [
                    'legal_case_id' => $caseIds[$sequence->index % count($caseIds)],
                ]
            ))
            ->create();

        CaseAttachment::factory(30)
            ->state(new Sequence(
                fn (Sequence $sequence): array => [
                    'legal_case_id' => $caseIds[$sequence->index % count($caseIds)],
                ]
            ))
            ->create();
    }
}
