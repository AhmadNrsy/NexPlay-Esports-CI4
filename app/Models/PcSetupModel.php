<?php

namespace App\Models;

use CodeIgniter\Model;

class PcSetupModel extends Model
{
    protected $table            = 'pc_setups';
    protected $primaryKey       = 'pc_id';
    protected $returnType       = 'array';
    protected $allowedFields    = ['room_id', 'spek_cpu', 'spek_gpu', 'monitor'];
}
