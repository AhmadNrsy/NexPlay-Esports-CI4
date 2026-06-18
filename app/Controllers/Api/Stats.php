<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use App\Models\BookingModel;
use App\Models\GamingRoomModel;

class Stats extends ResourceController
{
    protected $format = 'json';

    public function activeBookings()
    {
        $model = new BookingModel();
        $count = $model->where('status_booking', 'Active')->countAllResults();

        return $this->respond([
            'status' => true,
            'message' => 'Active bookings retrieved',
            'data' => ['active_bookings' => $count]
        ], 200);
    }

    public function availableRooms()
    {
        $model = new GamingRoomModel();
        $count = $model->where('status_room', 'Available')->countAllResults();

        return $this->respond([
            'status' => true,
            'message' => 'Available rooms retrieved',
            'data' => ['available_rooms' => $count]
        ], 200);
    }

    public function revenue()
    {
        $model = new BookingModel();
        $builder = $model->builder();
        $builder->selectSum('total_harga');
        $builder->where('status_booking', 'Completed');
        $query = $builder->get()->getRowArray();

        $revenue = $query['total_harga'] ?? 0;

        return $this->respond([
            'status' => true,
            'message' => 'Revenue retrieved',
            'data' => ['revenue' => $revenue]
        ], 200);
    }

    public function totalBookings()
    {
        $model = new BookingModel();
        $count = $model->countAllResults();

        return $this->respond([
            'status' => true,
            'message' => 'Total bookings retrieved',
            'data' => ['total_bookings' => $count]
        ], 200);
    }
}
