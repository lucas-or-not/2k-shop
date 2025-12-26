<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminEmail = config('mail.admin_email', 'admin@2kshop.com');
        
        User::firstOrCreate(
            ['email' => $adminEmail],
            [
                'name' => 'Admin User',
                'email' => $adminEmail,
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
    }
}
