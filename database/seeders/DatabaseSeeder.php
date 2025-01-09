<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolesTableSeeder::class,
            NetworkChannelsSeeder::class,
        ]);

        // Create a default admin user
        $adminRole = Role::where('name', 'admin')->first();

        $adminUser = User::create([
            'name' => 'Admin User',
            'email' => 'nomannomi306@gmail.com',
            'password' => bcrypt('password'), // Use a secure password
        ]);

        // Assign the admin role to the user
        $adminUser->roles()->attach($adminRole);
    }
}
