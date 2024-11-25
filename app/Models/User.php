<?php

namespace App\Models;

use Src\Main\Auth\User as AppUser;
use Src\Main\Database\Eloquent\Factories\HasFactory;
use Src\Main\Notifications\INotifiable;
use Src\Main\Notifications\Traits\Notifiable;

class User extends AppUser implements INotifiable
{
    use HasFactory,
        Notifiable;
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
