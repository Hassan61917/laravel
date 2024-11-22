<?php

namespace Database\Seeders;

use App\Models\User;
use Src\Main\Database\Seeder;
use Src\Main\Facade\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(array $parameters = []): void
    {
        User::create([
            'name' => 'Test User',
            'email' => 'test2@example.com',
            "password" => Hash::make('password')
        ]);
    }
}
