<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoomRequest extends Model
{
    use HasFactory;

    protected $table = 'room_requests';

    protected $fillable = [
        'nim',
        'name',
        'email',
        'phone',
        'address',
        'borrow_date',
        'start_time',
        'end_time',
        'purpose',
        'status',
        'code_ticket'
    ];

    public function roomRequestDetails()
    {
        return $this->hasMany(RoomRequestDetail::class);
    }
}
