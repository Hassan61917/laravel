<?php

namespace Database\Factories;

use Src\Main\Database\Eloquent\Factories\Factory;
use Src\Main\Utils\Str;

class UserFactory extends Factory
{
    public function definition(): array
    {
        $num = rand(1, 100);
        return [
            'name' => "Conner Ernser",
            'email' => "ciara{$num}@example.com",
            'email_verified_at' => now(),
            'password' => "$2y$12$3deveYurIHSBS3lKRFh1.u4ZYqNXoUrAb2kQeh3oOUUvlPr9/02AG",
            'remember_token' => Str::random(10),
        ];
    }
}
