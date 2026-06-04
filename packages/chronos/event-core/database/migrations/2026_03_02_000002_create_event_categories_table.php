<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_categories', function (Blueprint $table) {
            $table->id();
            
            // Связь с пользователем (каждый может создать свои категории)
            // nullable, если мы захотим сделать общие системные категории
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->onDelete('cascade');

            $table->string('title');
            $table->string('slug');
            
            // Тот самый дефолтный цвет категории
            $table->string('default_color', 7)->default('#38BDF8');

            $table->timestamps();

            // Чтобы у одного юзера не было категорий с одинаковыми именами
            $table->unique(['user_id', 'slug']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_categories');
    }
};
