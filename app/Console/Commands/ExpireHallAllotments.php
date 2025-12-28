<?php

namespace App\Console\Commands;

use App\Models\HallAllotment;
use Carbon\Carbon;
use Illuminate\Console\Command;

class ExpireHallAllotments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hall-allotments:expire';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically expire hall allotments that have reached their ending month';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $currentMonth = Carbon::now()->startOfMonth();
        $expiredCount = 0;

        $this->info('Checking for expired hall allotments...');

        // Find all allotments with cancel_requested status that have passed their ending month
        HallAllotment::where('status', 'cancel_requested')
            ->whereNotNull('ending_month')
            ->where('ending_month', '<=', $currentMonth)
            ->chunk(100, function($allotments) use (&$expiredCount) {
                foreach($allotments as $allotment) {
                    try {
                        // Skip if already blocked
                        if ($allotment->status === 'blocked') {
                            continue;
                        }

                        // Update allotment to cancelled
                        $allotment->update([
                            'status' => 'cancelled',
                            'cancelled_at' => now(),
                            'cancellation_reason' => 'Auto expired after ending month'
                        ]);

                        // Free the seat
                        if ($allotment->seat) {
                            $allotment->seat->update(['status' => 'empty']);
                        }

                        $expiredCount++;
                        $this->line("Expired allotment ID: {$allotment->id}");
                    } catch (\Exception $e) {
                        $this->error("Failed to expire allotment ID {$allotment->id}: " . $e->getMessage());
                    }
                }
            });

        $this->info("Expired {$expiredCount} hall allotment(s).");
        return 0;
    }
}
