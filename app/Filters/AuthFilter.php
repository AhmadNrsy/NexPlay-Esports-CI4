<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\AuthTokenModel;
use Config\Services;

class AuthFilter implements FilterInterface
{
    /**
     * Do whatever processing this filter needs to do.
     * By default it should not return anything during
     * normal execution. However, when an abnormal state
     * is found, it should return an instance of
     * CodeIgniter\HTTP\Response. If it does, script
     * execution will end and that Response will be
     * sent back to the client, allowing for error pages,
     * redirects, etc.
     *
     * @param RequestInterface $request
     * @param array|null       $arguments
     *
     * @return mixed
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        $header = $request->getHeaderLine('Authorization');
        $token = null;

        if (!empty($header) && preg_match('/Bearer\s+(.*)$/i', $header, $matches)) {
            $token = $matches[1];
        }

        if (!$token) {
            return Services::response()
                ->setJSON([
                    'status' => false,
                    'message' => 'Unauthorized - Token not provided'
                ])
                ->setStatusCode(401);
        }

        $tokenModel = new AuthTokenModel();
        $tokenData = $tokenModel->where('token', $token)->first();

        if (!$tokenData) {
            return Services::response()
                ->setJSON([
                    'status' => false,
                    'message' => 'Unauthorized - Invalid token'
                ])
                ->setStatusCode(401);
        }

        if (strtotime($tokenData['expired_at']) < time()) {
            return Services::response()
                ->setJSON([
                    'status' => false,
                    'message' => 'Unauthorized - Token expired'
                ])
                ->setStatusCode(401);
        }

        // Token is valid and not expired, allow request to proceed
    }

    /**
     * Allows After filters to inspect and modify the response
     * object as needed. This method does not allow any way
     * to stop execution of other after filters, short of
     * throwing an Exception or Error.
     *
     * @param RequestInterface  $request
     * @param ResponseInterface $response
     * @param array|null        $arguments
     *
     * @return mixed
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // No action needed after the request
    }
}
