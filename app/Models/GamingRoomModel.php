<?php

namespace App\Models;

use CodeIgniter\Model;

class GamingRoomModel extends Model
{
    protected $table            = 'gaming_rooms';
    protected $primaryKey       = 'room_id';
    protected $returnType       = 'array';
    protected $allowedFields    = ['nama_room', 'tipe_room', 'harga_per_jam', 'status_room'];
}
