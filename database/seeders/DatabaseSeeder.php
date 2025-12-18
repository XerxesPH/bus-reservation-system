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
    private array $locations = [
        'PITX (Parañaque)',
        'Buendia (Pasay)',
        'Batangas City Grand Terminal',
        'Lipa City',
        'Calamba City',
        'San Pablo City',
        'Lucena Grand Terminal',
        'Sta. Cruz, Laguna',
        'Tagaytay City',
        'Nasugbu, Batangas',
    ];

    /**
     * Terminal coordinates for distance calculation
     */
    private array $coordinates = [
        'PITX (Parañaque)' => ['lat' => 14.5093, 'lng' => 120.9918],
        'Buendia (Pasay)' => ['lat' => 14.5543, 'lng' => 120.9972],
        'Batangas City Grand Terminal' => ['lat' => 13.7845, 'lng' => 121.0744],
        'Lipa City' => ['lat' => 13.9427, 'lng' => 121.1648],
        'Calamba City' => ['lat' => 14.1923, 'lng' => 121.1345],
        'San Pablo City' => ['lat' => 14.0688, 'lng' => 121.3236],
        'Lucena Grand Terminal' => ['lat' => 13.9575, 'lng' => 121.6022],
        'Sta. Cruz, Laguna' => ['lat' => 14.2758, 'lng' => 121.4132],
        'Tagaytay City' => ['lat' => 14.1153, 'lng' => 120.9619],
        'Nasugbu, Batangas' => ['lat' => 14.0722, 'lng' => 120.6322],
    ];

    /**
     * Daily departure timeslots
     */
    private array $timeslots = ['06:00:00', '12:00:00', '18:00:00'];

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

        // ==========================================
        // 5. CREATE SAMPLE BOOKINGS
        // ==========================================
        $this->command->info('🎫 Creating Sample Bookings...');
        $this->seedBookings($users);

        $this->command->info('✅ Seeding Complete!');
    }

    /**
     * Seed Terminals
     */
    private function seedTerminals(): array
    {
        $terminals = [];

        foreach ($this->locations as $location) {
            $coords = $this->coordinates[$location] ?? ['lat' => 14.0, 'lng' => 121.0];

            // Parse city and province from location name
            $parts = explode(',', $location);
            $city = trim($parts[0]);
            $province = isset($parts[1]) ? trim($parts[1]) : $this->getProvince($location);

            $terminals[$location] = Terminal::updateOrCreate(
                ['name' => $location],
                [
                    'city' => $city,
                    'province' => $province,
                    'type' => 'integrated_terminal',
                    'latitude' => $coords['lat'],
                    'longitude' => $coords['lng'],
                ]
            );
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
        $busNumber = 1001;

        // Create Regular Buses
        for ($i = 0; $i < 15; $i++) {
            $buses[] = Bus::updateOrCreate(
                ['code' => 'REG-' . $busNumber],
                [
                    'type' => 'regular',
                    'capacity' => 45,
                ]
            );
            $busNumber++;
        }

        // Create Deluxe Buses
        for ($i = 0; $i < 10; $i++) {
            $buses[] = Bus::updateOrCreate(
                ['code' => 'DLX-' . $busNumber],
                [
                    'type' => 'deluxe',
                    'capacity' => 30,
                ]
            );
            $busNumber++;
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
        $busIndex = 0;
        $totalBuses = count($buses);

        // ==========================================
        // MESH NETWORK: Every Origin to Every Destination
        // ==========================================
        foreach ($this->locations as $originName) {
            foreach ($this->locations as $destName) {
                // Skip if origin === destination (bus can't go to itself)
                if ($originName === $destName) {
                    continue;
                }

                $origin = $terminals[$originName];
                $dest = $terminals[$destName];

                // Calculate price based on distance
                $distance = $this->calculateDistance(
                    $this->coordinates[$originName]['lat'],
                    $this->coordinates[$originName]['lng'],
                    $this->coordinates[$destName]['lat'],
                    $this->coordinates[$destName]['lng']
                );

                // Price: ~₱3-5 per km, minimum ₱80
                $basePrice = max(80, round($distance * rand(3, 5)));

                // Travel time: ~1 hour per 40km, between 1-4 hours
                $travelHours = max(1, min(4, round($distance / 40, 1)));

                // Generate schedules for each day
                for ($day = 0; $day < $daysAhead; $day++) {
                    $date = Carbon::today()->addDays($day)->format('Y-m-d');

                    // 3 daily departures per route
                    foreach ($this->timeslots as $time) {
                        // Rotate through buses
                        $bus = $buses[$busIndex % $totalBuses];
                        $busIndex++;

                        // Deluxe buses get 25% price markup
                        $price = $bus->type === 'deluxe'
                            ? round($basePrice * 1.25)
                            : $basePrice;

                        Schedule::updateOrCreate(
                            [
                                'origin_id' => $origin->id,
                                'destination_id' => $dest->id,
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
        }

        // Calculate route count: n * (n-1) where n = number of locations
        $routeCount = count($this->locations) * (count($this->locations) - 1);
        $this->command->info("   Created {$routeCount} unique routes");
        $this->command->info("   Created {$scheduleCount} total schedules ({$daysAhead} days × 3 timeslots)");
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

        // Admin User
        $users['admin'] = User::updateOrCreate(
            ['email' => 'admin@southernlines.ph'],
            [
                'name' => 'System Administrator',
                'password' => bcrypt('password'),
                'role' => 'admin',
            ]
        );

        // Test Passenger
        $users['passenger'] = User::updateOrCreate(
            ['email' => 'passenger@example.com'],
            [
                'name' => 'Juan Dela Cruz',
                'password' => bcrypt('password'),
                'role' => 'user',
                'contact_number' => '09171234567',
            ]
        );

        // Additional test user
        $users['test'] = User::updateOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => bcrypt('password'),
                'role' => 'user',
            ]
        );

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
