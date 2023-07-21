<?php

namespace App\Jobs;

use App\Mail\AdminPlatformErrorsUpdate;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendAdminErrorEmailUpdate implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        private readonly string $description,
        private readonly string $error,
    ) {
        
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Mail::to(config('app.admin_email'))
        ->send(
            new AdminPlatformErrorsUpdate($this->description, $this->error)
        );
    }
}
