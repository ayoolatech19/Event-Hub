<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $fillable = [
        'category_id', 'title', 'description', 'venue',
        'date', 'capacity', 'price', 'banner_image', 'status',
    ];

    protected $casts = [
        'date' => 'datetime',
        'price' => 'decimal:2',
    ];

    public function organizer()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }


    public function bookings()
{
    return $this->hasMany(Booking::class);
}

public function ticketsBooked(): int
{
    return (int) $this->bookings()->where('status', 'confirmed')->sum('quantity');
}
}