<?php

namespace App\Models;

use CodeIgniter\Model;

class PaymentModel extends Model
{
    protected $table            = 'payments';
    protected $primaryKey       = 'payment_id';
    protected $returnType       = 'array';
    protected $allowedFields    = ['booking_id', 'metode_bayar', 'status_bayar'];
}

