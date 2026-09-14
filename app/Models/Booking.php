<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_code', 'user_id', 'room_id', 'guest_name', 'guest_email',
        'guest_phone', 'check_in', 'check_out', 'total_guest',
        'total_price', 'notes', 'status',
        'midtrans_order_id', 'snap_token', 'payment_type', 'paid_at',
        'rescheduled_at',
    ];

    protected $casts = [
        'check_in' => 'date',
        'check_out' => 'date',
        'total_price' => 'decimal:2',
        'paid_at' => 'datetime',
        'rescheduled_at' => 'datetime',
    ];

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function generateCode(): string
    {
        return 'BK-' . now()->format('Ymd') . '-' . str_pad((self::count() + 1), 4, '0', STR_PAD_LEFT);
    }
}