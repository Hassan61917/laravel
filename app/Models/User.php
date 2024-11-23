<?php

namespace App\Models;

use Src\Main\Auth\User as AppUser;
use Src\Main\Database\Eloquent\Factories\HasFactory;
class User extends AppUser
{
    use HasFactory;

    protected array $fillable = ["name", "email", "password"];

    protected array $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            "password" => "hashed",
            'email_verified_at' => 'datetime'
        ];
    }
}
