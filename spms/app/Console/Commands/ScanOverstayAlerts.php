<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Visit;
use App\Models\Alert;
use Carbon\Carbon;

class ScanOverstayAlerts extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'alerts:scan-overstay';

    /**
     * The console command description.
     */
    protected $description = 'Scan checked-in visits and create overstay alerts.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $hours = (int) env('ALERT_OVERSTAY_HOURS', 4);
        $threshold = Carbon::now()->subHours($hours);

        $visits = Visit::with('vehicle')
            ->where('status', 'checked_in')
            ->whereNull('checked_out_at')
            ->where('checked_in_at', '<=', $threshold)
            ->get();

        $created = 0;

        foreach ($visits as $visit) {
            $entityType = $visit->vehicle_id ? 'vehicle' : 'visit';
            $entityId = $visit->vehicle_id ?? $visit->id;
            $plate = optional($visit->vehicle)->plate_number;
            $message = $plate
                ? "Vehicle {$plate} has exceeded the expected stay duration"
                : "Visit {$visit->id} has exceeded the expected stay duration";

            $alert = Alert::createIfNotExists([
                'type' => 'overstay',
                'severity' => 'high',
                'message' => $message,
                'entity_type' => $entityType,
                'entity_id' => $entityId,
                'resolved' => false,
            ]);

            if ($alert->wasRecentlyCreated) {
                $created++;
            }
        }

        $this->info("Overstay scan complete. Created {$created} alerts.");

        return Command::SUCCESS;
    }
}
