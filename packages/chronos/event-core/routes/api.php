<?php

declare(strict_types=1);

/**
 * API маршруты пакета Chronos Event Core.
 * Обеспечивают взаимодействие автономного Vue-фронтенда с бизнес-логикой.
 */

use Chronos\EventCore\Http\Controllers\EventController;
use Illuminate\Support\Facades\Route;

Route::prefix('api/chronos')->middleware(['web', 'auth'])->group(function () {
    /**
     * Сетка событий: разворачивает RRULE в список вхождений для заданного периода.
     * Ожидает параметры: start (Y-m-d), end (Y-m-d)
     */
    Route::get('/events', [EventController::class, 'index'])->name('chronos.events.index');

    /**
     * CRUD событий
     */
    Route::post('/events', [EventController::class, 'store'])->name('chronos.events.store');
    Route::put('/events/{event}', [EventController::class, 'update'])->name('chronos.events.update');
    Route::delete('/events/{event}', [EventController::class, 'destroy'])->name('chronos.events.destroy');

    /**
     * Создание/обновление переопределения (override) конкретного вхождения в серии.
     */
    Route::post('/events/instances', [EventController::class, 'storeInstance'])->name('chronos.events.instances.store');
});
