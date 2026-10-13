<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    protected $fillable = ['event_id', 'quantity', 'total_price', 'status', 'reference', 'expires_at', 'paid_at','reminder_sent_at'];
    protected $casts = [
        'total_price' => 'integer',
        'reminder_sent_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }
    public function payments()
{
    return $this->hasMany(Payment::class);
}
public function tickets()
{
    return $this->hasMany(Ticket::class);
}
}