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
        Schema::create('visitors', function (Blueprint $table) {
            $table->id();
            $table->string('full_name');
            $table->string('ID_number')->nullable()->unique();
            $table->string('phone_number');
            $table->string('company')->nullable();
            // $table->string('purpose_of_visit');
            // $table->string('person_to_visit');
            // $table->string('department')->nullable();
            // $table->string('additional_notes')->nullable();
            // $table->string('status')->default('checked_in');
            // $table->timestamp('check_in_time')->nullable();
            // $table->timestamp('checked_out_time')->nullable();
             $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visitors');
    }
};
