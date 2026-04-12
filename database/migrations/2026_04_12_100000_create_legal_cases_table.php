<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('legal_cases', function (Blueprint $table) {
            $table->id();
            $table->string('case_type', 20);
            $table->string('primary_party_name');
            $table->string('opponent_party_name');
            $table->string('case_number');
            $table->date('case_filed_at')->nullable();
            $table->date('first_session_at');
            $table->text('subject')->nullable();
            $table->string('primary_court_name')->nullable();
            $table->string('circuit_number')->nullable();
            $table->date('report_date')->nullable();
            $table->string('detention_order_number')->nullable();
            $table->date('judgment_issued_at')->nullable();
            $table->boolean('has_appeal')->default(false);
            $table->string('appeal_appellant_name')->nullable();
            $table->string('appeal_respondent_name')->nullable();
            $table->string('appeal_number')->nullable();
            $table->string('appeal_court_name')->nullable();
            $table->string('appeal_circuit_number')->nullable();
            $table->string('appeal_session_period', 20)->nullable();
            $table->date('appeal_first_session_at')->nullable();
            $table->timestamps();

            $table->index(['case_type', 'created_at']);
            $table->index('case_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('legal_cases');
    }
};
