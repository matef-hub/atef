<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('case_hearings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('legal_case_id')->constrained('legal_cases')->cascadeOnDelete();
            $table->date('hearing_date');
            $table->string('roll_number')->nullable();
            $table->text('court_decision');
            $table->date('next_hearing_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('hearing_date');
            $table->index('next_hearing_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('case_hearings');
    }
};
