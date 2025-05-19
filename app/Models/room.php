<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Room extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'rooms';
    protected $primaryKey = 'code_room';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'code_room',
        'name',
        'description',
        'capacity',
        'status',
        'image'
    ];
}
