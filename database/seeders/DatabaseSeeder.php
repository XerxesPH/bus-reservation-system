<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\Bus;
use App\Models\Schedule;
use App\Models\Terminal;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Calabarzon Terminal Locations for Mesh Network
     */
    private array $terminalData = [
        [
            'name' => 'PITX',
            'city' => 'Parañaque',
            'province' => 'Metro Manila',
            'type' => 'integrated_terminal',
            'lat' => 14.5093,
            'lng' => 120.9918,
        ],
        [
            'name' => 'Buendia Terminal',
            'city' => 'Pasay',
            'province' => 'Metro Manila',
            'type' => 'private_terminal',
            'lat' => 14.5543,
            'lng' => 120.9972,
        ],
        [
            'name' => 'Batangas City Grand Terminal',
            'city' => 'Batangas City',
            'province' => 'Batangas',
            'type' => 'integrated_terminal',
            'lat' => 13.7845,
            'lng' => 121.0744,
        ],
        [
            'name' => 'SM Lipa Bus Terminal',
            'city' => 'Lipa City',
            'province' => 'Batangas',
            'type' => 'private_terminal',
            'lat' => 13.9427,
            'lng' => 121.1648,
        ],
        [
            'name' => 'Calamba Terminal',
            'city' => 'Calamba City',
            'province' => 'Laguna',
            'type' => 'integrated_terminal',
            'lat' => 14.1923,
            'lng' => 121.1345,
        ],
        [
            'name' => 'San Pablo City Terminal',
            'city' => 'San Pablo City',
            'province' => 'Laguna',
            'type' => 'integrated_terminal',
            'lat' => 14.0688,
            'lng' => 121.3236,
        ],
        [
            'name' => 'Sta. Cruz Terminal',
            'city' => 'Santa Cruz',
            'province' => 'Laguna',
            'type' => 'integrated_terminal',
            'lat' => 14.2758,
            'lng' => 121.4132,
        ],
        [
            'name' => 'Lucena Grand Terminal',
            'city' => 'Lucena City',
            'province' => 'Quezon',
            'type' => 'integrated_terminal',
            'lat' => 13.9575,
            'lng' => 121.6022,
        ],
        [
            'name' => 'Tagaytay City Terminal',
            'city' => 'Tagaytay City',
            'province' => 'Cavite',
            'type' => 'integrated_terminal',
            'lat' => 14.1153,
            'lng' => 120.9619,
        ],
        [
            'name' => 'Nasugbu Terminal',
            'city' => 'Nasugbu',
            'province' => 'Batangas',
            'type' => 'integrated_terminal',
            'lat' => 14.0722,
            'lng' => 120.6322,
        ],
        [
            'name' => 'Dasmariñas Terminal',
            'city' => 'Dasmariñas',
            'province' => 'Cavite',
            'type' => 'integrated_terminal',
            'lat' => 14.3290,
            'lng' => 120.9367,
        ],
        [
            'name' => 'Antipolo Terminal',
            'city' => 'Antipolo City',
            'province' => 'Rizal',
            'type' => 'integrated_terminal',
            'lat' => 14.5869,
            'lng' => 121.1752,
        ],
    ];

    private array $timeslots = ['06:00:00', '10:00:00', '14:00:00', '18:00:00'];

    /**
     * Terminal coordinates for distance calculation
     */
    private array $coordinates = [];

    public function run(): void
    {
        $this->command->info('🚌 Southern Lines Bus Reservation System - Database Seeder');
        $this->command->info('=========================================================');

        // ==========================================
        // 1. CREATE TERMINALS (CALABARZON NETWORK)
        // ==========================================
        $this->command->info('📍 Creating Terminals...');
        $terminals = $this->seedTerminals();

        // ==========================================
        // 2. CREATE BUS FLEET
        // ==========================================
        $this->command->info('🚌 Creating Bus Fleet...');
        $buses = $this->seedBuses();

        // ==========================================
        // 3. GENERATE MESH NETWORK SCHEDULES
        // ==========================================
        $this->command->info('📅 Generating Mesh Network Schedules (All Routes)...');
        $this->seedMeshSchedules($terminals, $buses);

        // ==========================================
        // 4. CREATE USERS
        // ==========================================
        $this->command->info('👤 Creating Users...');
        $users = $this->seedUsers();

        $this->command->info('✅ Seeding Complete!');
    }

    /**
     * Seed Terminals
     */
    private function seedTerminals(): array
    {
        $terminals = [];

        $this->coordinates = [];

        foreach ($this->terminalData as $terminal) {
            $name = $terminal['name'];

            $terminals[$name] = Terminal::updateOrCreate(
                ['name' => $name],
                [
                    'city' => $terminal['city'],
                    'province' => $terminal['province'],
                    'type' => $terminal['type'],
                    'latitude' => $terminal['lat'],
                    'longitude' => $terminal['lng'],
                ]
            );

            $this->coordinates[$name] = ['lat' => $terminal['lat'], 'lng' => $terminal['lng']];
        }

        $this->command->info("   Created " . count($terminals) . " terminals");
        return $terminals;
    }

    /**
     * Get province from location name
     */
    private function getProvince(string $location): string
    {
        $provinces = [
            'PITX' => 'Metro Manila',
            'Buendia' => 'Metro Manila',
            'Batangas' => 'Batangas',
            'Lipa' => 'Batangas',
            'Calamba' => 'Laguna',
            'San Pablo' => 'Laguna',
            'Lucena' => 'Quezon',
            'Sta. Cruz' => 'Laguna',
            'Tagaytay' => 'Cavite',
            'Nasugbu' => 'Batangas',
        ];

        foreach ($provinces as $key => $province) {
            if (str_contains($location, $key)) {
                return $province;
            }
        }
        return 'Calabarzon';
    }

    /**
     * Seed Bus Fleet
     */
    private function seedBuses(): array
    {
        $buses = [];

        $drivers = [
            'Ramon Santos',
            'Jun dela Cruz',
            'Mark Reyes',
            'Anthony Garcia',
            'Paolo Mendoza',
            'Arman Bautista',
            'Joel Navarro',
            'Michael Flores',
            'Leo Castillo',
            'Dennis Aquino',
            'Carlo Villanueva',
            'Benjie Ramos',
        ];

        $terminalCount = count($this->terminalData);
        $routeCount = $terminalCount * ($terminalCount - 1);

        // IMPORTANT: prevent "ghost bus" schedules by ensuring we have enough buses
        // so that each route in a given (date, time) slot can have a unique bus.
        $desiredBusCount = max($routeCount, 132);

        $letters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $driverIndex = 0;

        for ($i = 0; $i < $desiredBusCount; $i++) {
            // 25% deluxe, 75% regular
            $type = ($i % 4 === 0) ? 'deluxe' : 'regular';
            $capacity = $type === 'deluxe' ? 20 : 40;

            // Deterministic PH-style plate (unique)
            $a = $letters[intdiv($i, 26) % 26];
            $b = $letters[$i % 26];
            $plate = 'N' . $a . $b . '-' . str_pad((string) (1000 + $i), 4, '0', STR_PAD_LEFT);

            $buses[] = Bus::updateOrCreate(
                ['code' => $plate],
                [
                    'type' => $type,
                    'capacity' => $capacity,
                    'driver_name' => $drivers[$driverIndex % count($drivers)],
                    'driver_image' => null,
                ]
            );

            $driverIndex++;
        }

        $this->command->info("   Created " . count($buses) . " buses");
        return $buses;
    }

    /**
     * Seed Mesh Network Schedules
     * Creates routes between ALL terminal pairs (fully connected network)
     */
    private function seedMeshSchedules(array $terminals, array $buses): void
    {
        $daysAhead = 14; // Generate schedules for 2 weeks
        $scheduleCount = 0;
        $totalBuses = count($buses);

        // ==========================================
        // MESH NETWORK: Every Origin to Every Destination
        // ==========================================
        $terminalNames = array_keys($terminals);

        $routes = [];
        foreach ($terminalNames as $originName) {
            foreach ($terminalNames as $destName) {
                if ($originName === $destName) {
                    continue;
                }

                $distance = $this->calculateDistance(
                    $this->coordinates[$originName]['lat'],
                    $this->coordinates[$originName]['lng'],
                    $this->coordinates[$destName]['lat'],
                    $this->coordinates[$destName]['lng']
                );

                $basePrice = max(80, round($distance * rand(3, 5)));

                $routes[] = [
                    'origin' => $terminals[$originName],
                    'dest' => $terminals[$destName],
                    'base_price' => $basePrice,
                ];
            }
        }

        if ($totalBuses < count($routes)) {
            $this->command->warn('   Not enough buses for unique-per-timeslot assignment; buses will be reused.');
        }

        for ($day = 0; $day < $daysAhead; $day++) {
            $date = Carbon::today()->addDays($day)->format('Y-m-d');

            foreach ($this->timeslots as $time) {
                // For each (date,time), each route gets a unique bus (no ghost bus)
                $busOrder = $buses;
                shuffle($busOrder);

                foreach ($routes as $idx => $route) {
                    $bus = $busOrder[$idx % $totalBuses];

                    $price = $bus->type === 'deluxe'
                        ? round($route['base_price'] * 1.25)
                        : $route['base_price'];

                    Schedule::updateOrCreate(
                        [
                            'origin_id' => $route['origin']->id,
                            'destination_id' => $route['dest']->id,
                            'departure_date' => $date,
                            'departure_time' => $time,
                        ],
                        [
                            'bus_id' => $bus->id,
                            'price' => $price,
                            'status' => 'scheduled',
                        ]
                    );
                    $scheduleCount++;
                }
            }
        }

        // Calculate route count: n * (n-1) where n = number of locations
        $routeCount = count($terminalNames) * (count($terminalNames) - 1);
        $this->command->info("   Created {$routeCount} unique routes");
        $timeslotCount = count($this->timeslots);
        $this->command->info("   Created {$scheduleCount} total schedules ({$daysAhead} days × {$timeslotCount} timeslots)");
    }

    /**
     * Calculate distance between two coordinates (Haversine formula)
     */
    private function calculateDistance(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371; // km

        $latDelta = deg2rad($lat2 - $lat1);
        $lngDelta = deg2rad($lng2 - $lng1);

        $a = sin($latDelta / 2) * sin($latDelta / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($lngDelta / 2) * sin($lngDelta / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    /**
     * Seed Users
     */
    private function seedUsers(): array
    {
        $users = [];

        $adminAccounts = [
            ['email' => 'admin@southernlines.ph', 'name' => 'System Administrator'],
            ['email' => 'admin2@southernlines.ph', 'name' => 'Operations Admin'],
        ];

        foreach ($adminAccounts as $i => $admin) {
            $u = User::updateOrCreate(
                ['email' => $admin['email']],
                [
                    'name' => $admin['name'],
                    'password' => bcrypt('password'),
                    'contact_number' => '09' . rand(10, 99) . rand(100, 999) . rand(1000, 9999),
                    'age' => rand(25, 55),
                    'gender' => rand(0, 1) ? 'Male' : 'Female',
                ]
            );
            $u->role = 'admin';
            $u->save();
            $users['admin_' . ($i + 1)] = $u;
        }

        for ($i = 1; $i <= 10; $i++) {
            $email = "user{$i}@example.com";

            $u = User::updateOrCreate(
                ['email' => $email],
                [
                    'name' => "Test Passenger {$i}",
                    'password' => bcrypt('password'),
                    'contact_number' => '09' . rand(10, 99) . rand(100, 999) . rand(1000, 9999),
                    'age' => rand(18, 60),
                    'gender' => ['Male', 'Female', 'Other'][rand(0, 2)],
                ]
            );
            $u->role = 'user';
            $u->save();
            $users['user_' . $i] = $u;
        }

        $this->command->info("   Created " . count($users) . " users");
        return $users;
    }

    /**
     * Seed Sample Bookings
     */
    private function seedBookings(array $users): void
    {
        // Get some random schedules for sample bookings
        $schedules = Schedule::where('departure_date', '>=', Carbon::today())
            ->take(5)
            ->get();

        if ($schedules->isEmpty()) {
            $this->command->warn("   No schedules available for sample bookings");
            return;
        }

        $bookingCount = 0;
        $passenger = $users['passenger'];

        foreach ($schedules as $index => $schedule) {
            $seatNumbers = [$index + 1, $index + 2]; // 2 seats per booking

            Booking::updateOrCreate(
                [
                    'user_id' => $passenger->id,
                    'schedule_id' => $schedule->id,
                ],
                [
                    'booking_reference' => Booking::generateReference(),
                    'bus_number' => $schedule->bus->code ?? 'N/A',
                    'seat_numbers' => $seatNumbers,
                    'adults' => 2,
                    'children' => 0,
                    'total_price' => $schedule->price * 2,
                    'status' => 'confirmed',
                    'guest_name' => $passenger->name,
                    'guest_email' => $passenger->email,
                    'guest_phone' => $passenger->contact_number ?? '09171234567',
                    'payment_method' => 'GCash',
                    'payment_status' => 'paid',
                    'trip_type' => 'oneway',
                ]
            );
            $bookingCount++;
        }

        $this->command->info("   Created {$bookingCount} sample bookings");
    }
}
