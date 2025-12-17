<?php

namespace Database\Seeders;

use App\Models\Bus;
use App\Models\Schedule;
use App\Models\Terminal;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Clear tables to prevent duplicates when reseeding
        // DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        // Terminal::truncate();
        // Bus::truncate();
        // Schedule::truncate();
        // DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // ==========================================
        // 1. CREATE TERMINALS (CALABARZON FOCUSED)
        // ==========================================
        $terminalsData = [
            // --- METRO MANILA HUBS (Gateways) ---
            'PITX' => [
                'city' => 'Parañaque',
                'name' => 'PITX (Parañaque Integrated Terminal)',
                'province' => 'Metro Manila',
                'type' => 'integrated_terminal',
                'latitude' => 14.5093,
                'longitude' => 120.9918
            ],
            'Buendia' => [
                'city' => 'Pasay',
                'name' => 'DLTB Buendia Terminal',
                'province' => 'Metro Manila',
                'type' => 'private_terminal',
                'latitude' => 14.5543,
                'longitude' => 120.9972
            ],

            // --- LAGUNA ---
            'SanPablo' => [
                'city' => 'San Pablo City',
                'name' => 'San Pablo City Central Terminal',
                'province' => 'Laguna',
                'type' => 'integrated_terminal',
                'latitude' => 14.0688,
                'longitude' => 121.3236
            ],
            'Turbina' => [
                'city' => 'Calamba City',
                'name' => 'Turbina Bus Terminal',
                'province' => 'Laguna',
                'type' => 'integrated_terminal',
                'latitude' => 14.1923,
                'longitude' => 121.1345
            ],
            'SantaRosa' => [
                'city' => 'Santa Rosa City',
                'name' => 'SRIT (Balibago Complex)',
                'province' => 'Laguna',
                'type' => 'integrated_terminal',
                'latitude' => 14.2936,
                'longitude' => 121.1037
            ],
            'SantaCruz' => [
                'city' => 'Santa Cruz',
                'name' => 'Santa Cruz / Pagsanjan Terminal',
                'province' => 'Laguna',
                'type' => 'integrated_terminal',
                'latitude' => 14.2758,
                'longitude' => 121.4132
            ],

            // --- BATANGAS ---
            'BatangasCity' => [
                'city' => 'Batangas City',
                'name' => 'Batangas City Grand Terminal',
                'province' => 'Batangas',
                'type' => 'integrated_terminal',
                'latitude' => 13.7845,
                'longitude' => 121.0744
            ],
            'BatangasPier' => [
                'city' => 'Batangas City',
                'name' => 'Batangas Port (Pier)',
                'province' => 'Batangas',
                'type' => 'integrated_terminal',
                'latitude' => 13.7601,
                'longitude' => 121.0478
            ],
            'Lipa' => [
                'city' => 'Lipa City',
                'name' => 'SM City Lipa Terminal',
                'province' => 'Batangas',
                'type' => 'mall_terminal',
                'latitude' => 13.9427,
                'longitude' => 121.1648
            ],
            'Nasugbu' => [
                'city' => 'Nasugbu',
                'name' => 'Nasugbu BSC Terminal',
                'province' => 'Batangas',
                'type' => 'private_terminal',
                'latitude' => 14.0722,
                'longitude' => 120.6322
            ],

            // --- CAVITE ---
            'Tagaytay' => [
                'city' => 'Tagaytay City',
                'name' => 'Tagaytay Olivarez Plaza',
                'province' => 'Cavite',
                'type' => 'integrated_terminal',
                'latitude' => 14.1153,
                'longitude' => 120.9619
            ],
            'Dasma' => [
                'city' => 'Dasmariñas',
                'name' => 'Robinsons Pala-Pala',
                'province' => 'Cavite',
                'type' => 'mall_terminal',
                'latitude' => 14.3015,
                'longitude' => 120.9631
            ],

            // --- QUEZON ---
            'Lucena' => [
                'city' => 'Lucena City',
                'name' => 'Lucena Grand Central',
                'province' => 'Quezon',
                'type' => 'integrated_terminal',
                'latitude' => 13.9575,
                'longitude' => 121.6022
            ],
            'Mauban' => [
                'city' => 'Mauban',
                'name' => 'Mauban JAC Liner',
                'province' => 'Quezon',
                'type' => 'private_terminal',
                'latitude' => 14.1884,
                'longitude' => 121.7297
            ],
        ];

        // Store terminal objects
        $t = [];
        foreach ($terminalsData as $key => $data) {
            // Check if terminal exists to avoid duplicate errors if reseeding
            $t[$key] = Terminal::firstOrCreate(
                ['name' => $data['name']], // Check by name
                $data // Create with data if not found
            );
        }

        // ==========================================
        // 2. DEFINE ROUTES (CALABARZON NETWORK)
        // ==========================================
        // Format: [Origin, Destination, Duration(Hrs), Price, RegularCount, DeluxeCount]

        $routes = [
            // --- 1. THE MANILA COMMUTE (North-South Backbone) ---
            ['PITX',         'BatangasCity', 2.5, 280,  3, 2],
            ['PITX',         'Lucena',       4,   420,  3, 2],
            ['Buendia',      'SantaRosa',    1,   120,  4, 0], // Commuter route
            ['Buendia',      'SantaCruz',    3,   250,  2, 1],
            ['PITX',         'Nasugbu',      3.5, 350,  2, 2], // Beach route
            ['PITX',         'Tagaytay',     2,   180,  3, 1], // Tourist route

            // --- 2. LAGUNA HUB (San Pablo & Turbina) ---
            ['SanPablo',     'PITX',         2.5, 230,  3, 1],
            ['SanPablo',     'Lucena',       1.5, 120,  3, 0], // Short hop
            ['Turbina',      'BatangasPier', 1.5, 150,  2, 2], // Ferry connector
            ['Turbina',      'Lipa',         0.8, 80,   4, 0], // Short hop

            // --- 3. BATANGAS ROUTES ---
            ['BatangasCity', 'Lipa',         0.8, 90,   4, 0],
            ['BatangasPier', 'PITX',         2.5, 290,  2, 2], // Direct from Pier
            ['Lipa',         'SanPablo',     1,   100,  3, 0],

            // --- 4. CAVITE CONNECTORS ---
            ['Dasma',        'Tagaytay',     0.8, 60,   5, 0], // Jeep/Bus route
            ['Dasma',        'PITX',         1,   80,   5, 0],
            ['Tagaytay',     'Nasugbu',      1.5, 120,  2, 0],

            // --- 5. QUEZON PROVINCE ---
            ['Lucena',       'Mauban',       1.5, 100,  2, 0], // Gateway to Cagbalete
            ['Lucena',       'Buendia',      4,   420,  2, 2],
        ];

        // ==========================================
        // 3. GENERATE FLEET & SCHEDULES
        // ==========================================
        $daysToCheck = 7; // Generate for 1 week
        $busCounter = 8000; // Start plate numbers at 8000

        foreach ($routes as $routeCfg) {
            [$originKey, $destKey, $duration, $price, $regCount, $dlxCount] = $routeCfg;

            // Safety check: ensure keys exist
            if (!isset($t[$originKey]) || !isset($t[$destKey])) continue;

            $origin = $t[$originKey];
            $dest = $t[$destKey];

            // 3a. Create Fleet
            $fleet = [];

            // Create Regular Buses (Capacity 45 for provincial)
            for ($i = 0; $i < $regCount; $i++) {
                $fleet[] = Bus::firstOrCreate(['code' => 'REG-' . $busCounter++], [
                    'type' => 'regular',
                    'capacity' => 40,
                ]);
            }
            // Create Deluxe Buses (Capacity 29 for leg room)
            for ($i = 0; $i < $dlxCount; $i++) {
                $fleet[] = Bus::firstOrCreate(['code' => 'DLX-' . $busCounter++], [
                    'type' => 'deluxe',
                    'capacity' => 20,
                ]);
            }

            // 3b. Smart Rotation Schedule
            for ($day = 0; $day < $daysToCheck; $day++) {
                $date = Carbon::today()->addDays($day)->format('Y-m-d');
                $isEvenDay = ($day % 2 == 0);

                foreach ($fleet as $index => $bus) {
                    // Stagger departures (start at 4 AM for early commuters)
                    $baseTime = 4 + ($index * 1.5); // 1.5 hour gaps

                    if ($baseTime >= 20) $baseTime = 4; // Reset if too late

                    $depTime = sprintf('%02d:00:00', floor($baseTime));
                    $minutes = ($baseTime - floor($baseTime)) * 60;
                    $depTime = sprintf('%02d:%02d:00', floor($baseTime), $minutes);

                    if ($duration < 6) {
                        // --- SAME DAY RETURN (Most CALABARZON trips are short) ---
                        // Leg 1: Origin -> Dest
                        $this->createTrip($bus, $origin, $dest, $date, $depTime, $price);

                        // Leg 2: Dest -> Origin (Turnaround)
                        $returnHour = $baseTime + $duration + 1; // +1 hr prep time
                        if ($returnHour < 22) {
                            $returnTime = sprintf('%02d:%02d:00', floor($returnHour), ($returnHour - floor($returnHour)) * 60);
                            $this->createTrip($bus, $dest, $origin, $date, $returnTime, $price);
                        }
                    } else {
                        // --- LONG HAUL (Next Day Return) ---
                        // Swap start points based on day to simulate bus rotation
                        $startAtOrigin = ($isEvenDay && $index % 2 == 0) || (! $isEvenDay && $index % 2 != 0);

                        if ($startAtOrigin) {
                            $this->createTrip($bus, $origin, $dest, $date, $depTime, $price);
                        } else {
                            $this->createTrip($bus, $dest, $origin, $date, $depTime, $price);
                        }
                    }
                }
            }
        }

        // ==========================================
        // 4. CREATE TEST USER
        // ==========================================
        User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Student Admin',
                'password' => bcrypt('password'),
                'role' => 'admin' // Assuming you have a role column
            ]
        );

        User::firstOrCreate(
            ['email' => 'passenger@example.com'],
            [
                'name' => 'Juan Dela Cruz',
                'password' => bcrypt('password'),
                'role' => 'user'
            ]
        );
    }

    private function createTrip($bus, $origin, $dest, $date, $time, $price)
    {
        // Adjust price for Deluxe (+20%)
        if ($bus->type === 'deluxe') {
            $price = $price * 1.25; // 25% markup for deluxe
        }

        Schedule::firstOrCreate([
            'bus_id' => $bus->id,
            'departure_date' => $date,
            'departure_time' => $time,
        ], [
            'origin_id' => $origin->id,
            'destination_id' => $dest->id,
            'price' => round($price, 2), // Round to 2 decimals
            'status' => 'scheduled' // Assuming you have a status column
        ]);
    }
}
