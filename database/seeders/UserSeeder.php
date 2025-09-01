<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Carlos González',
            'password' => Hash::make('123'),
            'email' => 'carlos85g@gmail.com',
            'email_verified_at' => now()->toDateTimeString(),
            'role' => 'staff',
        ]);
    }
}
