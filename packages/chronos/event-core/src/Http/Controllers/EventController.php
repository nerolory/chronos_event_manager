<?php

declare(strict_types=1);

namespace Chronos\EventCore\Http\Controllers;

use Chronos\EventCore\Http\Requests\StoreEventInstanceRequest;
use Chronos\EventCore\Http\Requests\StoreEventRequest;
use Chronos\EventCore\Http\Requests\UpdateEventRequest;
use Chronos\EventCore\Http\Resources\EventInstanceResource;
use Chronos\EventCore\Http\Resources\EventOccurrenceResource;
use Chronos\EventCore\Http\Resources\EventResource;
use Chronos\EventCore\Models\Event;
use Chronos\EventCore\Services\EventService;
use Chronos\EventCore\Services\EventInstanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Routing\Controller;
use Illuminate\Support\Carbon;

/**
 * Class EventController
 *
 * Контроллер для работы с событиями и их конкретными вхождениями (исключениями).
 */
class EventController extends Controller
{
    /**
     * @param EventService $eventService
     */
    public function __construct(
        protected EventService $eventService,
        protected EventInstanceService $instanceService
    ) {}

    /**
     * Получение сетки событий для пользователя.
     *
     * @param Request $request
     * @return AnonymousResourceCollection
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $request->validate([
            'start' => ['required', 'date'],
            'end'   => ['required', 'date', 'after:start'],
        ]);

        $occurrences = $this->eventService->getEventGridForUser(
            (int) auth()->id(),
            Carbon::parse($request->query('start')),
            Carbon::parse($request->query('end'))
        );

        return EventOccurrenceResource::collection($occurrences);
    }

    /**
     * Создание нового события.
     */
    public function store(StoreEventRequest $request): JsonResponse
    {
        $event = $this->eventService->createEvent(
            (int) auth()->id(),
            $request->validated()
        );

        return (new EventResource($event))->response()->setStatusCode(201);
    }

    /**
     * Обновление события (только владелец).
     */
    public function update(UpdateEventRequest $request, Event $event): EventResource
    {
        $updated = $this->eventService->updateEvent($event, $request->validated());

        return new EventResource($updated);
    }

    /**
     * Мягкое удаление события.
     */
    public function destroy(Event $event): JsonResponse
    {
        if ((int) $event->user_id !== (int) auth()->id()) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $this->eventService->deleteEvent($event);

        return response()->json(['message' => 'Event deleted'], 200);
    }

    /**
     * Создание или обновление исключения (override) для конкретного дня в серии.
     */
    public function storeInstance(StoreEventInstanceRequest $request): EventInstanceResource
    {
        $instance = $this->instanceService->upsertOverride($request->validated());

        return new EventInstanceResource($instance);
    }
}