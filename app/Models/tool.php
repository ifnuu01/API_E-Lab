<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tool extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'tools';

    protected $fillable = [
        'id',
        'name',
        'description',
        'quantity',
        'available_quantity',
        'status',
        'image'
    ];
}
