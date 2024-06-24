<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Laravel\Passport\Token as PassportToken;

class Token extends PassportToken
{
    use HasUuids;
}
