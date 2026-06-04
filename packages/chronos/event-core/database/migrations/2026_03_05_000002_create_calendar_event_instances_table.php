<?php

declare(strict_types=1);

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
        Schema::create('calendar_event_instances', function (Blueprint $table) {
            $table->id();
            
            // Явно указываем таблицу calendar_events, так как она не соответствует 
            // дефолтному поиску Laravel по имени колонки
            $table->foreignUuid('event_id')
                ->constrained('calendar_events') 
                ->onDelete('cascade');
            
            $table->timestamp('original_start_at')->index();
            
            $table->string('title')->nullable();
            $table->timestamp('start_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            
            $table->boolean('is_cancelled')->default(false);
            $table->boolean('is_completed')->default(false);
            
            $table->timestamps();

            $table->unique(['event_id', 'original_start_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('calendar_event_instances');
    }
};