<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Event extends Model
{
      use HasFactory;  
      
    protected $fillable = [
        'category_id', 'title', 'description', 'venue',
        'date', 'capacity', 'price', 'banner_image', 'status',
    ];

    protected $casts = [
        'date' => 'datetime',
        'price' => 'integer',
        'tickets_sold' => 'integer',
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
public function ticketsRemaining(): int
{
    return $this->capacity - $this->tickets_sold;
}
public function tickets()
{
    return $this->hasManyThrough(Ticket::class, Booking::class);
}
}