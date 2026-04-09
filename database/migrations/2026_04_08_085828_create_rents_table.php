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
        Schema::create('rents', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_name', 250)->nullable();
            $table->string('renter_name', 250)->nullable();
            $table->string('home_data', 250)->nullable();
            $table->date('date_sign')->nullable();
            $table->string('date_duration', 250)->nullable();
            $table->date('date_end')->nullable();
            $table->string('insurance_mon', 250)->nullable();
            $table->string('Monthly_rent', 250)->nullable();
            $table->string('add_notes', 250)->nullable();
            $table->string('con_pdf', 250)->nullable();
            $table->string('con_word', 250)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rents');
    }
};
