<?php

namespace App\Models;

use Vcx\Enum\Enum;
use Illuminate\Database\Eloquent\Model; 

class Exapmle extends Model
{
    use Enum;

    // declare the enum columns you want to manage
    protected $enum = ['status', 'type'];
}
