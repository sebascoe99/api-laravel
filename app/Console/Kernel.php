<?php

namespace App\Console;

use App\Models\Promotion;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Carbon\Carbon;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')->hourly();
        $schedule->call(function () {
            foreach (Promotion::all() as $promotion) {
                if (Carbon::now()->format('Y-m-d H:i:s') > $promotion->promotion_date_of_expiry) {
                    $promotionUpdate = Promotion::findOrFail($promotion->id_promotion);
                    $promotionUpdate->promotion_status = 0;
                    $promotionUpdate->save();
                }
            }
        })->everyFourHours();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
