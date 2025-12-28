<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Room extends Model
{
    protected $fillable = [
        'hall_id',
        'room_type_id',
        'room_number',
        'capacity'
    ];

    public function seats(): HasMany
    {
        return $this->hasMany(Seat::class);
    }

    public function hall()
    {
        return $this->belongsTo(Hall::class);
    }

    public function roomType()
    {
        return $this->belongsTo(RoomType::class);
    }
}
