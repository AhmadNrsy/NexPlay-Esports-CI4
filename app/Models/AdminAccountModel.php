<?php

namespace App\Models;

use CodeIgniter\Model;

class AdminAccountModel extends Model
{
    protected $table            = 'admin_accounts';
    protected $primaryKey       = 'admin_id';
    protected $returnType       = 'array';
    protected $allowedFields    = ['username', 'password', 'created_at'];
}
