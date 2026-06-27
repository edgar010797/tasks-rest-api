<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Priority extends Model
{
    use HasFactory;

    protected $table = 'priorities';

    protected $fillable = ['name', 'slug', 'color', 'level'];
    //
}
