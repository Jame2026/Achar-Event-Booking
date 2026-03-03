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

        $vendor1 = User::factory()->create([
            'name' => 'Skyline Grand Atrium',
            'email' => 'vendor1@example.com',
            'role' => 'vendor',
        ]);

        $vendor2 = User::factory()->create([
            'name' => 'Artisan Palate',
            'email' => 'vendor2@example.com',
            'role' => 'vendor',
        ]);

        $vendor3 = User::factory()->create([
            'name' => 'Lumina Studios',
            'email' => 'vendor3@example.com',
            'role' => 'vendor',
        ]);

        $vendor4 = User::factory()->create([
            'name' => 'Elegant Events Co',
            'email' => 'vendor4@example.com',
            'role' => 'vendor',
        ]);

        $vendor5 = User::factory()->create([
            'name' => 'Prime Venues',
            'email' => 'vendor5@example.com',
            'role' => 'vendor',
        ]);

        $customer = User::factory()->create([
            'name' => 'Customer User',
            'email' => 'user@example.com',
            'role' => 'user',
        ]);

        Event::query()->insert([
            // Wedding Events
            [
                'vendor_id' => $vendor1->id,
                'title' => 'Grand Wedding Full Design',
                'event_type' => 'wedding',
                'description' => 'Complete floral transformation for ceremony and reception. Premium venue with stunning architecture.',
                'location' => 'Plaza Hotel, Downtown Manhattan',
                'starts_at' => Carbon::now()->addDays(20),
                'ends_at' => Carbon::now()->addDays(20)->addHours(10),
                'price' => 4500,
                'capacity' => 200,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'vendor_id' => $vendor5->id,
                'title' => 'Intimate Wedding Ceremony',
                'event_type' => 'wedding',
                'description' => 'Gorgeous garden wedding with elegant decorations and intimate setting.',
                'location' => 'Rosewood Manor Gardens, Connecticut',
                'starts_at' => Carbon::now()->addDays(35),
                'ends_at' => Carbon::now()->addDays(35)->addHours(8),
                'price' => 3200,
                'capacity' => 100,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'vendor_id' => $vendor1->id,
                'title' => 'Modern Minimalist Wedding',
                'event_type' => 'wedding',
                'description' => 'Contemporary wedding design with sleek aesthetics and white/gold color scheme.',
                'location' => 'Aria Gallery & Hall, Brooklyn',
                'starts_at' => Carbon::now()->addDays(42),
                'ends_at' => Carbon::now()->addDays(42)->addHours(10),
                'price' => 3800,
                'capacity' => 150,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Baby Shower Events
            [
                'vendor_id' => $vendor4->id,
                'title' => 'Luxury Baby Shower Bash',
                'event_type' => 'baby_shower',
                'description' => 'Elegant baby shower with premium decorations, catering, and entertainment.',
                'location' => 'Riverside Ballroom, New Jersey',
                'starts_at' => Carbon::now()->addDays(25),
                'ends_at' => Carbon::now()->addDays(25)->addHours(4),
                'price' => 1850,
                'capacity' => 60,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'vendor_id' => $vendor2->id,
                'title' => 'Botanical Baby Shower',
                'event_type' => 'baby_shower',
                'description' => 'Nature-inspired baby shower with garden themes and organic decor.',
                'location' => 'Botanical Gardens Pavilion, Queens',
                'starts_at' => Carbon::now()->addDays(38),
                'ends_at' => Carbon::now()->addDays(38)->addHours(3),
                'price' => 1200,
                'capacity' => 45,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // House Blessing Events
            [
                'vendor_id' => $vendor4->id,
                'title' => 'Traditional House Blessing',
                'event_type' => 'house_blessing',
                'description' => 'Traditional blessing ceremony with ceremonial setup and spiritual guidance.',
                'location' => 'Residential Areas, Manhattan',
                'starts_at' => Carbon::now()->addDays(27),
                'ends_at' => Carbon::now()->addDays(27)->addHours(5),
                'price' => 950,
                'capacity' => 50,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'vendor_id' => $vendor5->id,
                'title' => 'Modern Home Blessing Ceremony',
                'event_type' => 'house_blessing',
                'description' => 'Blended traditional and modern house blessing ceremony.',
                'location' => 'Residential Areas, Brooklyn',
                'starts_at' => Carbon::now()->addDays(50),
                'ends_at' => Carbon::now()->addDays(50)->addHours(4),
                'price' => 1100,
                'capacity' => 40,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Company Party Events
            [
                'vendor_id' => $vendor1->id,
                'title' => 'Corporate Gala Subscription',
                'event_type' => 'company_party',
                'description' => 'Premium corporate event with catering, entertainment, and networking setup.',
                'location' => 'Midtown Conference Center, Manhattan',
                'starts_at' => Carbon::now()->addDays(45),
                'ends_at' => Carbon::now()->addDays(45)->addHours(5),
                'price' => 2500,
                'capacity' => 150,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'vendor_id' => $vendor2->id,
                'title' => 'Casual Team Building Event',
                'event_type' => 'company_party',
                'description' => 'Fun and interactive team building event with games and catering.',
                'location' => 'Skyline Rooftop Lounge, Manhattan',
                'starts_at' => Carbon::now()->addDays(32),
                'ends_at' => Carbon::now()->addDays(32)->addHours(4),
                'price' => 1250,
                'capacity' => 80,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'vendor_id' => $vendor3->id,
                'title' => 'Executive Networking Mixer',
                'event_type' => 'company_party',
                'description' => 'Exclusive networking event for executives with premium catering and ambiance.',
                'location' => 'Five Star Hotel, Manhattan',
                'starts_at' => Carbon::now()->addDays(60),
                'ends_at' => Carbon::now()->addDays(60)->addHours(3),
                'price' => 3000,
                'capacity' => 120,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Engagement Events
            [
                'vendor_id' => $vendor5->id,
                'title' => 'Elegant Engagement Package',
                'event_type' => 'engagement',
                'description' => 'Romantic arrangements with premium seasonal blooms and elegant setup.',
                'location' => 'Rooftop Garden District, Manhattan',
                'starts_at' => Carbon::now()->addDays(15),
                'ends_at' => Carbon::now()->addDays(15)->addHours(6),
                'price' => 2200,
                'capacity' => 100,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'vendor_id' => $vendor4->id,
                'title' => 'Luxury Engagement Celebration',
                'event_type' => 'engagement',
                'description' => 'Luxurious engagement party with fine dining and entertainment.',
                'location' => 'Upscale Venue, Upper East Side',
                'starts_at' => Carbon::now()->addDays(48),
                'ends_at' => Carbon::now()->addDays(48)->addHours(7),
                'price' => 2800,
                'capacity' => 120,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Birthday Events
            [
                'vendor_id' => $vendor4->id,
                'title' => 'Kids Birthday Party',
                'event_type' => 'birthday',
                'description' => 'Fun-filled kids birthday party with games, decorations, and entertainment.',
                'location' => 'Community Center, Queens',
                'starts_at' => Carbon::now()->addDays(18),
                'ends_at' => Carbon::now()->addDays(18)->addHours(3),
                'price' => 850,
                'capacity' => 50,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'vendor_id' => $vendor2->id,
                'title' => 'Adult Milestone Birthday',
                'event_type' => 'birthday',
                'description' => 'Sophisticated milestone birthday celebration with gourmet catering.',
                'location' => 'Upscale Restaurant & Bar, Manhattan',
                'starts_at' => Carbon::now()->addDays(30),
                'ends_at' => Carbon::now()->addDays(30)->addHours(5),
                'price' => 1600,
                'capacity' => 80,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'vendor_id' => $vendor1->id,
                'title' => 'Glamorous Sweet 16',
                'event_type' => 'birthday',
                'description' => 'Elegant and glamorous Sweet 16 party with premium decorations and DJ.',
                'location' => 'Grand Ballroom, Brooklyn',
                'starts_at' => Carbon::now()->addDays(55),
                'ends_at' => Carbon::now()->addDays(55)->addHours(6),
                'price' => 2200,
                'capacity' => 100,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Anniversary Events
            [
                'vendor_id' => $vendor5->id,
                'title' => 'Golden Anniversary Celebration',
                'event_type' => 'anniversary',
                'description' => 'Romantic anniversary celebration with premium decor and fine dining.',
                'location' => 'Waterfront Restaurant, Manhattan',
                'starts_at' => Carbon::now()->addDays(40),
                'ends_at' => Carbon::now()->addDays(40)->addHours(5),
                'price' => 1950,
                'capacity' => 60,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'vendor_id' => $vendor4->id,
                'title' => 'Intimate Anniversary Dinner',
                'event_type' => 'anniversary',
                'description' => 'Cozy intimate dinner for two with romantic ambiance.',
                'location' => 'Fine Dining Restaurant, Manhattan',
                'starts_at' => Carbon::now()->addDays(22),
                'ends_at' => Carbon::now()->addDays(22)->addHours(4),
                'price' => 450,
                'capacity' => 20,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
