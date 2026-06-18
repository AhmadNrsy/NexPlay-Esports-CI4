<?php

namespace App\Models;

use CodeIgniter\Model;

class BookingModel extends Model
{
    protected $table            = 'bookings';
    protected $primaryKey       = 'booking_id';
    protected $returnType       = 'array';
    protected $allowedFields    = ['user_id', 'room_id', 'waktu_mulai', 'durasi_jam', 'total_harga', 'status_booking'];
}
