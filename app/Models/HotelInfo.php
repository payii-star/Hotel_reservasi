<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HotelInfo extends Model
{
    protected $table = 'hotel_info';

    protected $fillable = [
        'hotel_name',
        'welcome_text',
        'description',
        'address',
        'phone',
        'email',
        'logo',
        'main_photo',
        'facilities_text',
        'booking_text',
    ];
}