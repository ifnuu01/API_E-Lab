<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class room extends Model
{
    use HasFactory;

    protected $table = 'rooms';
    protected $primaryKey = 'code_room';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'code_room',
        'name',
        'notes',
        'capacity',
    ];
}
