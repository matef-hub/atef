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
        Schema::create('contracts', function (Blueprint $table) {
            $table->id();
            $table->string('contr_number', 250)->unique()->nullable();
            $table->string('proje_name', 250)->nullable();
            $table->string('suppli_name', 250)->nullable();
            $table->string('proje_data', 250)->nullable();
            $table->date('Esnad_date')->nullable();
            $table->date('Contar_date')->nullable();
            $table->string('sign', 250)->nullable();
            $table->string('Pdf_image', 250)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contracts');
    }
};
