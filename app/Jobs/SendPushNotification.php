<?php

namespace App\Jobs;

use App\Models\User;
use App\Models\UserConsent;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendPushNotification implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        public User $user,
        public array $eventData
    ) {
    }

    public function handle(): void
    {
        try {
            // Проверяем согласие пользователя на push-уведомления
            $hasPushConsent = UserConsent::where('user_id', $this->user->id)
                ->where('consent_type_id', function ($query) {
                    $query->select('id')
                        ->from('consent_types')
                        ->where('slug', 'push_notifications');
                })
                ->where('granted', true)
                ->exists();

            if (! $hasPushConsent) {
                Log::info("User {$this->user->id} has not consented to push notifications");

                return;
            }

            // Здесь должна быть интеграция с сервисом Web-Push (например, Firebase Cloud Messaging)
            // Для примера просто логируем
            Log::info("Push notification sent to user {$this->user->id} for event: {$this->eventData['title']}");

            // Пример интеграции с Firebase:
            // $firebase = app('firebase.messaging');
            // $message = CloudMessage::withTarget('token', $this->user->fcm_token)
            //     ->withNotification([
            //         'title' => 'Напоминание о событии',
            //         'body' => $this->eventData['title'],
            //     ]);
            // $firebase->send($message);
        } catch (Exception $e) {
            Log::error('Failed to send push notification', [
                'user_id' => $this->user->id,
                'event_data' => $this->eventData,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }
}
