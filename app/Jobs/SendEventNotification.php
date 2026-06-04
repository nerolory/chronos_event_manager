<?php

namespace App\Jobs;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Exception;

class SendEventNotification implements ShouldQueue
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
            Mail::to($this->user->email)->send(
                new class ($this->eventData) extends Mailable {
                    public function __construct(public array $eventData)
                    {
                    }

                    public function envelope(): Envelope
                    {
                        return new Envelope(
                            subject: 'Напоминание о событии: ' . $this->eventData['title'],
                        );
                    }

                    public function content(): Content
                    {
                        return new Content(
                            view: 'emails.event-notification',
                            with: [
                                'title' => $this->eventData['title'],
                                'start' => $this->eventData['start'],
                                'end' => $this->eventData['end'],
                            ],
                        );
                    }
                }
            );
        } catch (Exception $e) {
            Log::error('Failed to send event notification', [
                'user_id' => $this->user->id,
                'event_data' => $this->eventData,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }
}
