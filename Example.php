<?php

namespace App\Models;

use Vcx\Enum\Enum;
use Illuminate\Database\Eloquent\Model; 

class Exapmle extends Model
{
    use Enum;

    protected $enum = ['culomn']; // An array of column names
}
