<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Admin KedaiKu',
            'email' => 'admin@kedaiku.com',
            'password' => bcrypt('password'),
            'role' => 'admin'
        ]);

        User::factory()->create([
            'name' => 'Customer',
            'email' => 'customer@kedaiku.com',
            'password' => bcrypt('password'),
            'role' => 'customer'
        ]);

        $this->call([
            CategorySeeder::class,
            ProductSeeder::class,
        ]);
    }
}
