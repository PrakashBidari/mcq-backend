<?php

namespace Database\Seeders;

use App\Models\Banner;
use Illuminate\Database\Seeder;

class BannerSeeder extends Seeder
{
    public function run(): void
    {
        Banner::firstOrCreate(
            ['sort_order' => 1],
            [
                'title'      => "Master Your\nSkills Today",
                'subtitle'   => 'Learn from world-class instructors',
                'image_url'  => 'https://images.unsplash.com/photo-1522202176988-66273c2fd55f?w=800',
                'link_type'  => 'learning',
                'is_active'  => true,
            ],
        );

        Banner::firstOrCreate(
            ['sort_order' => 2],
            [
                'title'      => "Test Your\nKnowledge",
                'subtitle'   => 'Challenge yourself with smart quizzes',
                'image_url'  => 'https://images.unsplash.com/photo-1606326608606-aa0b62935f2b?w=800',
                'link_type'  => 'quiz',
                'is_active'  => true,
            ],
        );

        Banner::firstOrCreate(
            ['sort_order' => 3],
            [
                'title'      => "Achieve\nExcellence",
                'subtitle'   => 'Get certified and grow your career',
                'image_url'  => 'https://images.unsplash.com/photo-1434030216411-0b793f4b4173?w=800',
                'link_type'  => 'learning',
                'is_active'  => true,
            ],
        );
    }
}
