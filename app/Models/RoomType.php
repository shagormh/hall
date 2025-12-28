<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoomType extends Model
{
    protected $fillable = [
        'name',
        'hall_id',
        'is_active'
    ];

    public function hall()
    {
        return $this->belongsTo(Hall::class);
    }
}
