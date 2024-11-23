<?php

namespace Src\Main\Auth;

use Src\Main\Auth\Authentication\IAuth;
use Src\Main\Auth\Authorization\IAuthorize;
use Src\Main\Auth\Traits\WithAuth;
use Src\Main\Auth\Traits\WithAuthorize;
use Src\Main\Database\Eloquent\Model;
class User extends Model implements IAuth, IAuthorize
{
    use WithAuth,
        WithAuthorize;
}
