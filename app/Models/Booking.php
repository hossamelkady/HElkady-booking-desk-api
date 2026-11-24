<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    protected $fillable = ['customer_name',
            'service',
            'starts_at',
            'ends_at',
            'status'];
}
