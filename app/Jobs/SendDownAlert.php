<?php

namespace App\Jobs;

use App\Mail\WebsiteDownAlert;
use App\Models\Website;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendDownAlert implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $website;
    public $tries = 3;
    public $backoff = [60, 300, 600]; // Retry after 1, 5, 10 minutes

    public function __construct(Website $website)
    {
        $this->website = $website;
    }

    public function handle(): void
    {
        $client = $this->website->client;
        
        if (!$client->is_active) {
            return;
        }

        Mail::to($client->email)
            ->send(new WebsiteDownAlert($this->website));
        
        // Update last notified time (you might want to add this field to clients table)
        // $client->update(['last_notified_at' => now()]);
    }

    public function failed(\Throwable $exception): void
    {
        \Log::error('Failed to send down alert', [
            'website_id' => $this->website->id,
            'error' => $exception->getMessage(),
        ]);
    }
}