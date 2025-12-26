<?php

namespace App\Jobs;

use App\Mail\DailySalesReport;
use App\Models\User;
use App\Repositories\OrderRepository;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class DailySalesReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     */
    public function handle(OrderRepository $orderRepository): void
    {
        $yesterday = Carbon::yesterday();
        $salesSummary = $orderRepository->getDailySalesSummary($yesterday);

        $adminEmail = config('mail.admin_email', 'admin@2kshop.com');
        $admin = User::where('email', $adminEmail)->first();

        if ($admin) {
            Mail::to($admin->email)->queue(new DailySalesReport($salesSummary));
        }
    }
}
