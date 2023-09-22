<?php

namespace App\Console;

use App\Models\Roster;
use App\Models\RosterProduct;
use Carbon\Carbon;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // $schedule->command('inspire')->everySecond();


        // disable yesterday roster and push todo to today roster
        $schedule->call(function () {
            $rosters = Roster::where('status', '1')->get();
            $lastRoster = Roster::where('status', '1')->orderBy('id', 'desc')->first();
            $pendingProducts = RosterProduct::where('status', 0)->where('roster_id', $lastRoster->id)->get();

            foreach ($rosters as $roster) {
                $roster->status = 0;
                $roster->save();
            };

            $newRoster = Roster::create([
                'status' => 1
            ]);

            foreach ($pendingProducts as $rosterProduct) {
                RosterProduct::create([
                    'product_id' => $rosterProduct->product_id,
                    'roster_id' => $newRoster->id,
                    'status' => 0,
                    'user_id' => $rosterProduct->user_id,
                    'quantity' => $rosterProduct->quantity,
                    'created_at' => $rosterProduct->created_at,
                    'updated_at' => $rosterProduct->updated_at,
                ]);
            }
        })->timezone('Asia/Tehran')->dailyAt('06:00');

        // delete roster before 6 month
        $schedule->call(function () {
            $rosters = Roster::where('created_at', '<', Carbon::now()->subMonths(6))->get();
            foreach ($rosters as $roster) {
                $roster->delete();
            };
        })->timezone('Asia/Tehran')->monthly();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
