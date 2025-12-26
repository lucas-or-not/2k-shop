<?php

namespace App\Jobs;

use App\Mail\LowStockNotification;
use App\Models\Product;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class LowStockNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Product $product
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $adminEmail = config('mail.admin_email', 'admin@2kshop.com');
        $admin = User::where('email', $adminEmail)->first();

        if ($admin) {
            Mail::to($admin->email)->queue(new LowStockNotification($this->product));
        }
    }
}
