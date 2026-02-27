<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $admin = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'role' => 'admin',
        ]);

        $vendor = User::factory()->create([
            'name' => 'Vendor User',
            'email' => 'vendor@example.com',
            'role' => 'vendor',
        ]);

        $customer = User::factory()->create([
            'name' => 'Customer User',
            'email' => 'user@example.com',
            'role' => 'user',
        ]);

        Event::query()->insert([
            [
                'vendor_id' => $vendor->id,
                'title' => 'Grand Wedding Full Design',
                'event_type' => 'wedding',
                'description' => 'Complete floral transformation for ceremony and reception.',
                'location' => 'Plaza Hotel, New York',
                'starts_at' => Carbon::now()->addDays(20),
                'ends_at' => Carbon::now()->addDays(20)->addHours(10),
                'price' => 4500,
                'capacity' => 8,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'vendor_id' => $vendor->id,
                'title' => 'Traditional Monk Ceremony Set',
                'event_type' => 'monk_ceremony',
                'description' => 'Spiritual floral and decor setup for monk ceremony.',
                'location' => 'Wat Phnom Hall',
                'starts_at' => Carbon::now()->addDays(32),
                'ends_at' => Carbon::now()->addDays(32)->addHours(4),
                'price' => 1800,
                'capacity' => 10,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'vendor_id' => $vendor->id,
                'title' => 'Corporate Gala Subscription',
                'event_type' => 'company_party',
                'description' => 'Monthly corporate arrangements tailored to your brand.',
                'location' => 'Midtown Conference Center',
                'starts_at' => Carbon::now()->addDays(45),
                'ends_at' => Carbon::now()->addDays(45)->addHours(5),
                'price' => 1250,
                'capacity' => 20,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'vendor_id' => $vendor->id,
                'title' => 'Elegant Engagement Package',
                'event_type' => 'engagement',
                'description' => 'Romantic arrangements with premium seasonal blooms.',
                'location' => 'Rooftop Garden District',
                'starts_at' => Carbon::now()->addDays(15),
                'ends_at' => Carbon::now()->addDays(15)->addHours(6),
                'price' => 2200,
                'capacity' => 12,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'vendor_id' => $vendor->id,
                'title' => 'House Blessing Floral Program',
                'event_type' => 'house_blessing',
                'description' => 'Traditional blessing florals and ceremonial table design.',
                'location' => 'Riverfront Residence Area',
                'starts_at' => Carbon::now()->addDays(27),
                'ends_at' => Carbon::now()->addDays(27)->addHours(5),
                'price' => 950,
                'capacity' => 15,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
