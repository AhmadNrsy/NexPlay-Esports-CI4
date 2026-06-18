<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;
use App\Models\AdminAccountModel;
use App\Models\AuthTokenModel;

class Auth extends BaseController
{
    use ResponseTrait;

    public function login()
    {
        // Try getting payload from JSON safely or fallback to form-data
        $json = $this->request->getJSON(true);
        $username = $json['username'] ?? $this->request->getVar('username');
        $password = $json['password'] ?? $this->request->getVar('password');

        // Added requested logging
        log_message('error', 'Login attempt - Username from request: ' . $username);
        log_message('error', 'Login attempt - Password from request (raw): ' . $password);

        if (empty($username) || empty($password)) {
            return $this->respond([
                'status' => false,
                'message' => 'Validation failed: username and password are required'
            ], 400);
        }

        $adminModel = new AdminAccountModel();
        $admin = $adminModel->where('username', $username)->first();

        log_message('error', 'Login attempt - Query Admin Result: ' . print_r($admin, true));

        $inputHash = hash('sha256', (string) $password);

        if ($admin) {
            log_message('error', 'Login attempt - Hash password input: ' . $inputHash);
            log_message('error', 'Login attempt - Hash password database: ' . $admin['password']);
        } else {
            log_message('error', 'Login attempt - Admin not found in database.');
        }

        if (!$admin || $admin['password'] !== $inputHash) {
            return $this->respond([
                'status' => false,
                'message' => 'Invalid username or password'
            ], 401);
        }

        $tokenModel = new AuthTokenModel();

        // Generate token (random hex 64 characters)
        $token = bin2hex(random_bytes(32));
        $expiredAt = date('Y-m-d H:i:s', strtotime('+24 hours'));

        $tokenData = [
            'admin_id' => $admin['admin_id'],
            'token' => $token,
            'expired_at' => $expiredAt,
            'created_at' => date('Y-m-d H:i:s')
        ];

        $tokenModel->insert($tokenData);

        return $this->respond([
            'status' => true,
            'message' => 'Login successful',
            'data' => [
                'token' => $token,
                'expired_at' => $expiredAt
            ]
        ], 200);
    }

    public function logout()
    {
        $header = $this->request->getHeaderLine('Authorization');
        $token = null;

        if (!empty($header) && preg_match('/Bearer\s+(.*)$/i', $header, $matches)) {
            $token = $matches[1];
        }

        if (!$token) {
            return $this->respond([
                'status' => false,
                'message' => 'Token not provided'
            ], 400);
        }

        $tokenModel = new AuthTokenModel();
        $existingToken = $tokenModel->where('token', $token)->first();

        if ($existingToken) {
            // Invalidate token by deleting it
            $tokenModel->delete($existingToken['token_id']);

            return $this->respond([
                'status' => true,
                'message' => 'Logout successful'
            ], 200);
        }

        return $this->respond([
            'status' => false,
            'message' => 'Invalid token'
        ], 400);
    }
}
