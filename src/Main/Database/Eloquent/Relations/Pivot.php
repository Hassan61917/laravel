<?php

namespace Src\Main\Database\Eloquent\Relations;

use Src\Main\Database\Eloquent\Model;
use Src\Main\Database\Eloquent\Relations\Traits\AsPivot;

class Pivot extends Model
{
    use AsPivot;
    protected bool $incrementing = false;
    protected array $guarded = [];
}
