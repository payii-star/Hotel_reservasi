<?php

namespace Database\Seeders;

use App\Models\HotelInfo;
use App\Models\Room;
use App\Models\RoomFacility;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Admin default
        User::create([
            'name' => 'Admin Hotel',
            'email' => 'admin@hotel.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        // Info hotel
        HotelInfo::create([
            'hotel_name' => 'Grand Nusantara Hotel',
            'description' => 'Hotel bintang 4 dengan pemandangan kota, cocok untuk liburan maupun bisnis.',
            'address' => 'Jl. Merdeka No. 10, Jakarta',
            'phone' => '021-1234567',
            'email' => 'info@grandnusantara.com',
        ]);

        // Contoh kamar
        $rooms = [
            ['name' => 'Standard Room', 'type' => 'Standard', 'price' => 350000, 'capacity' => 2, 'total_unit' => 10, 'facilities' => ['AC', 'WiFi', 'TV']],
            ['name' => 'Deluxe Room', 'type' => 'Deluxe', 'price' => 550000, 'capacity' => 2, 'total_unit' => 6, 'facilities' => ['AC', 'WiFi', 'TV', 'Sarapan', 'Mini Bar']],
            ['name' => 'Suite Room', 'type' => 'Suite', 'price' => 950000, 'capacity' => 4, 'total_unit' => 3, 'facilities' => ['AC', 'WiFi', 'TV', 'Sarapan', 'Mini Bar', 'Kolam Renang Pribadi']],
        ];

        foreach ($rooms as $roomData) {
            $facilities = $roomData['facilities'];
            unset($roomData['facilities']);

            $room = Room::create([
                ...$roomData,
                'description' => "Kamar {$roomData['name']} dengan fasilitas lengkap dan nyaman.",
            ]);

            foreach ($facilities as $facility) {
                RoomFacility::create(['room_id' => $room->id, 'name' => $facility]);
            }
        }
    }
}
