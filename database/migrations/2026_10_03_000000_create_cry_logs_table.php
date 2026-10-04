<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cry_logs', function (Blueprint $table) {
            $table->id();
            $table->string('device_id', 50)->index();          // contoh: BABYCRY-001
            $table->boolean('is_crying')->default(true);        // bayi terdeteksi menangis?
            $table->string('cry_type', 30)->default('unknown'); // hungry, tired, ...
            $table->decimal('confidence', 5, 2)->nullable();    // 0 - 100 (%)
            $table->decimal('sound_level', 6, 2)->nullable();   // dB
            $table->decimal('temperature', 5, 2)->nullable();   // °C
            $table->decimal('humidity', 5, 2)->nullable();      // %
            $table->unsignedInteger('duration_seconds')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('recorded_at')->useCurrent()->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cry_logs');
    }
};
