<?php

namespace App\Models;

use Src\Main\Database\Eloquent\Factories\HasFactory;
use Src\Main\Database\Eloquent\Model;

class User extends Model
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
