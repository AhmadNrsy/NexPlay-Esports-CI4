<?php

namespace App\Models;

use CodeIgniter\Model;

class AuthTokenModel extends Model
{
    protected $table            = 'auth_tokens';
    protected $primaryKey       = 'token_id';
    protected $returnType       = 'array';
    protected $allowedFields    = ['admin_id', 'token', 'expired_at', 'created_at'];
}
