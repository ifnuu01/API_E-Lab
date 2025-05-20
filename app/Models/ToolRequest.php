<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ToolRequest extends Model
{
    use HasFactory;
    protected $table = 'tool_requests';
    protected $fillable = [
        'nim',
        'name',
        'email',
        'phone',
        'address',
        'borrow_date',
        'return_date',
        'expiration_date',
        'purpose',
        'status',
        'ticket_code',
        'image'
    ];
    public function toolRequestDetails()
    {
        return $this->hasMany(ToolRequestDetail::class);
    }
}
