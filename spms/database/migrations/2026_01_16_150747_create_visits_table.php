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
            $table->foreignId('vehicle_id')->constrained();
            $table->foreignId('driver_id')->constrained();
            $table->string('purpose'); // Delivery, Pickup, etc.
            $table->string('assigned_bay')->nullable();
            $table->string('status')->default('checked_in'); // checked_in, flagged, completed
            $table->timestamp('check_in_at')->useCurrent();
            $table->timestamp('check_out_at')->nullable();
            
            // Check-out Verification Fields (from your screenshot)
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
