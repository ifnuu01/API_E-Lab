<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Room extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'rooms';

    protected $fillable = [
        'id',
        'code_room',
        'name',
        'description',
        'capacity',
        'status',
        'image'
    ];

    public function roomRequestDetails()
    {
        return $this->hasMany(RoomRequestDetail::class);
    }
}
