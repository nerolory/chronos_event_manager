<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Создание таблицы событий.
     * Используем UUID для упрощения будущей синхронизации и безопасности API.
     */
    public function up(): void
    {
        Schema::create('calendar_events', function (Blueprint $table) {
            $table->uuid('id')->primary();
            
            // Связь с пользователем (через стандартный bigint Laravel)
            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('cascade');
                
            $table->foreignId('category_id')
                ->nullable()
                ->constrained('event_categories')
                ->onDelete('set null');

            $table->string('title');
            $table->text('description')->nullable();
            $table->string('color_accent', 7)->default('#38BDF8');
            $table->text('recurrence_rule')->nullable();
            $table->timestamp('starts_at');
            $table->timestamp('ends_at');
            $table->softDeletes();
            $table->timestamps();
            $table->index(['user_id', 'starts_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calendar_events');
    }
};