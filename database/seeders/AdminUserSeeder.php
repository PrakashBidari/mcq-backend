<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // Create Admin
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@mcqapp.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'is_verified' => true,
            'email_verified_at' => now(),
            'otp' => null, // Already verified, no OTP needed
            'otp_expires_at' => null,
            'reset_otp' => null,
            'reset_otp_expires_at' => null,
        ]);

        // Create Teacher
        User::create([
            'name' => 'Teacher User',
            'email' => 'teacher@mcqapp.com',
            'password' => Hash::make('password'),
            'role' => 'teacher',
            'is_verified' => true,
            'email_verified_at' => now(),
            'otp' => null, // Already verified, no OTP needed
            'otp_expires_at' => null,
            'reset_otp' => null,
            'reset_otp_expires_at' => null,
        ]);

        // Create a Regular User (for testing mobile app)
        User::create([
            'name' => 'Test User',
            'email' => 'user@mcqapp.com',
            'password' => Hash::make('password'),
            'role' => 'user',
            'is_verified' => true,
            'email_verified_at' => now(),
            'otp' => null, // Already verified, no OTP needed
            'otp_expires_at' => null,
            'reset_otp' => null,
            'reset_otp_expires_at' => null,
        ]);

        $this->command->info('✅ Admin, Teacher, and User accounts created!');
        $this->command->info('📧 Admin: admin@mcqapp.com / password');
        $this->command->info('📧 Teacher: teacher@mcqapp.com / password');
        $this->command->info('📧 User: user@mcqapp.com / password');
    }
}
