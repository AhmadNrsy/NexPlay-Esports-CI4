<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;

class GamingRooms extends ResourceController
{
    protected $modelName = 'App\Models\GamingRoomModel';
    protected $format = 'json';

    public function index()
    {
        return $this->respond([
            'status' => true,
            'message' => 'Gaming rooms retrieved successfully',
            'data' => $this->model->findAll()
        ], 200);
    }

    public function show($id = null)
    {
        $data = $this->model->find($id);
        if ($data) {
            return $this->respond([
                'status' => true,
                'message' => 'Gaming room retrieved successfully',
                'data' => $data
            ], 200);
        }
        return $this->respond([
            'status' => false,
            'message' => 'Gaming room not found'
        ], 404);
    }

    public function create()
    {
        $data = $this->request->getJSON(true) ?? $this->request->getPost();

        if ($this->model->insert($data)) {
            return $this->respond([
                'status' => true,
                'message' => 'Gaming room created successfully',
                'data' => ['id' => $this->model->getInsertID()]
            ], 201);
        }

        return $this->respond([
            'status' => false,
            'message' => 'Failed to create gaming room',
            'errors' => $this->model->errors()
        ], 400);
    }

    public function update($id = null)
    {
        $data = $this->request->getJSON(true) ?? $this->request->getRawInput();

        if ($this->model->update($id, $data)) {
            return $this->respond([
                'status' => true,
                'message' => 'Gaming room updated successfully'
            ], 200);
        }

        return $this->respond([
            'status' => false,
            'message' => 'Failed to update gaming room',
            'errors' => $this->model->errors()
        ], 400);
    }

    public function delete($id = null)
    {
        if ($this->model->delete($id)) {
            return $this->respond([
                'status' => true,
                'message' => 'Gaming room deleted successfully'
            ], 200);
        }

        return $this->respond([
            'status' => false,
            'message' => 'Failed to delete gaming room'
        ], 400);
    }
}
