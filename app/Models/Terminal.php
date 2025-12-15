<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Terminal extends Model
{
    use HasFactory;

    // 1. Allow these columns to be filled
    protected $fillable = [
        'name', // e.g., Cubao Station
        'city',  // e.g., Manila
    ];
}
