<?php

namespace Database\Seeders;

use App\Models\Advertisement;
use Illuminate\Database\Seeder;

class AdvertisementSeeder extends Seeder
{
    public function run(): void
    {
        Advertisement::firstOrCreate(
            ['position' => 'banner'],
            [
                'title' => 'Your Ad Here',
                'description' => 'Promote your product to active learners.',
                'button_text' => 'Learn',
                'link_url' => null,
                'is_active' => true,
            ],
        );

        Advertisement::firstOrCreate(
            ['position' => 'medium'],
            [
                'title' => 'Your Ad Here',
                'description' => 'Reach thousands of learners. Connect your Google or Apple ad account to display your ads here.',
                'button_text' => 'Learn More',
                'link_url' => null,
                'is_active' => true,
            ],
        );
    }
}
