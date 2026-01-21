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
        Schema::create('visits', function (Blueprint $table) {
            $table->id();
            $table->enum('visit_type',['visitors', 'vehicles'] )->nullable();
            $table->foreignId('visitor_id')->nullable()->constrained();
            $table->foreignId('vehicle_id')->nullable()->constrained();
            $table->foreignId('driver_id')->nullable()->constrained();

            //visitor specific
            $table->string('person_to_visit')->nullable();
            $table->string('department')->nullable();
            $table->string('additional_notes')->nullable();

            //vehicle info
            $table->string('assigned_bay')->nullable();

            //common
            $table->string('purpose');
            $table->string('status')->default('checked_in'); // checked_in, flagged, completed
            $table->boolean('has_discrepancies')->default(false);
            $table->timestamp('checked_in_at')->useCurrent();
            $table->timestamp('checked_out_at')->nullable();
            
            // Check-out Verification
            $table->boolean('goods_verified')->default(false);
            $table->boolean('weight_checked')->default(false);
            $table->boolean('photo_documented')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visits');
    }
};
