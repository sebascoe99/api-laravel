<?php

namespace App\Console\Commands;

use App\Models\Promotion;
use Illuminate\Console\Command;
use Carbon\Carbon;

class CheckExpirationDate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'verify:expirationDate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check expiration date of the promotions';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {

        foreach (Promotion::all() as $promotion) {
            if (Carbon::now()->format('Y-m-d H:i:s') > $promotion->promotion_date_of_expiry) {
                $promotionUpdate = Promotion::findOrFail($promotion->id_promotion);
                $promotionUpdate->promotion_status = 0;
                $promotionUpdate->save();
            }
        }
    }
}
