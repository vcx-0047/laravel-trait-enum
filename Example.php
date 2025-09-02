<?php

namespace App\Models;

Use App\Models\Traits\Enum;
use Illuminate\Database\Eloquent\Model; 

class Exapmle extends Model
{
    use Enum;

    protected $enum = ['culomn']; // An array of column names
}
