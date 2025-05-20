<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoomRequestDetail extends Model
{
    use HasFactory;

    protected $table = 'room_request_details';

    protected $fillable = [
        'room_request_id',
        'room_id',
    ];

    public function roomRequest()
    {
        return $this->belongsTo(RoomRequest::class);
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }
}
