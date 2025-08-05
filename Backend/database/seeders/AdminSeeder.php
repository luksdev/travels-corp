<?php

declare(strict_types = 1);

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (! User::where('email', 'admin@travelscorp.com')->exists()) {
            User::create([
                'name'     => 'Admin',
                'email'    => 'admin@travelscorp.com',
                'password' => Hash::make('password'),
                'role'     => UserRole::ADMIN,
            ]);

            $this->command->info('Admin user created: admin@travelscorp.com / password');
        } else {
            $this->command->info('Admin user already exists');
        }
    }
}
