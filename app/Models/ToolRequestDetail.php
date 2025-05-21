<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ToolRequestDetail extends Model
{
    use HasFactory;

    protected $table = 'tool_request_details';
    protected $fillable = [
        'tool_request_id',
        'tool_id',
        'quantity',
        'return_image',
        'status',
        'return_date',
    ];

    public function toolRequest()
    {
        return $this->belongsTo(ToolRequest::class);
    }

    public function tool()
    {
        return $this->belongsTo(Tool::class);
    }
}
