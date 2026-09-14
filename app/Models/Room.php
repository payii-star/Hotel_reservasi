<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'type', 'description', 'capacity',
        'price', 'total_unit', 'main_image', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'price' => 'decimal:2',
    ];

    public function facilities()
    {
        return $this->hasMany(RoomFacility::class);
    }

    public function images()
    {
        return $this->hasMany(RoomImage::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * Cek berapa unit kamar tipe ini yang masih available
     * di rentang tanggal check_in - check_out tertentu.
     *
     * @param string $checkIn
     * @param string $checkOut
     * @param int|null $excludeBookingId  ID booking yang dikecualikan dari hitungan
     *                                    (dipakai saat reschedule, biar booking yang lagi
     *                                    diubah gak dianggap "nyangkut" ngeblok slotnya sendiri)
     */
    public function availableUnits(string $checkIn, string $checkOut, ?int $excludeBookingId = null): int
    {
        $query = $this->bookings()
            ->whereIn('status', ['pending', 'confirmed', 'checked_in'])
            ->where(function ($q) use ($checkIn, $checkOut) {
                $q->whereBetween('check_in', [$checkIn, $checkOut])
                  ->orWhereBetween('check_out', [$checkIn, $checkOut])
                  ->orWhere(function ($q2) use ($checkIn, $checkOut) {
                      $q2->where('check_in', '<=', $checkIn)
                         ->where('check_out', '>=', $checkOut);
                  });
            });

        if ($excludeBookingId) {
            $query->where('id', '!=', $excludeBookingId);
        }

        $bookedCount = $query->count();

        return max(0, $this->total_unit - $bookedCount);
    }
}