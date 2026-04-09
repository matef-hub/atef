<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->string('docu_name', 250)->nullable();
            $table->string('doc_numer', 250)->nullable();
            $table->string('docu_issu_from', 250)->nullable();
            $table->date('docu_iss_date')->nullable();
            $table->date('docu_expiry_date')->nullable();
            $table->string('docu_pdf', 250)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
