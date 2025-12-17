<?php

namespace App\Console\Commands;

use App\Models\Schedule;
use App\Models\ScheduleTemplate;
use Carbon\Carbon;
use Illuminate\Console\Command;

class GenerateDailySchedules extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'schedule:generate-daily';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically generate bus schedules based on active Route Plans (Templates)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting automatic schedule generation...');

        // 1. Get all active templates
        $templates = ScheduleTemplate::where('is_active', true)->get();
        $generatedCount = 0;

        // 2. Define the range to generate (e.g., next 7 days)
        // This ensures that even if the command fails one night, we have a buffer.
        $daysToGenerate = 7;

        for ($i = 0; $i < $daysToGenerate; $i++) {
            $date = Carbon::today()->addDays($i);
            $dayName = $date->format('D'); // Mon, Tue, etc.

            foreach ($templates as $template) {
                // Check if template runs on this day
                if (! in_array($dayName, $template->active_days ?? [])) {
                    continue;
                }

                foreach ($template->departure_times ?? [] as $time) {
                    // Check if schedule already exists to avoid duplicates
                    $exists = Schedule::where('bus_id', $template->bus_id)
                        ->where('departure_date', $date->format('Y-m-d'))
                        ->where('departure_time', $time)
                        ->exists();

                    if (! $exists) {
                        Schedule::create([
                            'bus_id' => $template->bus_id,
                            'origin_id' => $template->origin_id,
                            'destination_id' => $template->destination_id,
                            'departure_date' => $date->format('Y-m-d'),
                            'departure_time' => $time,
                            'price' => $template->price,
                            'status' => 'scheduled'
                        ]);
                        $generatedCount++;
                    }
                }
            }
        }

        $this->info("Successfully generated $generatedCount trips for the next $daysToGenerate days.");
    }
}
