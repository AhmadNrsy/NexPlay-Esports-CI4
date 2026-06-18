<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class CorsFilter implements FilterInterface
{
    /**
     * Handle CORS Preflight and set standard CORS headers.
     * 
     * @param RequestInterface $request
     * @param array|null       $arguments
     * @return mixed
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        // Handle Preflight OPTIONS request
        if (strtolower($request->getMethod()) === 'options') {
            $response = service('response');
            
            // Set required CORS headers for Preflight
            $this->setCorsHeaders($response);
            
            // Return empty 200 OK for OPTIONS preflight
            return $response->setStatusCode(200);
        }
    }

    /**
     * Add CORS headers to the response after the controller is executed.
     * 
     * @param RequestInterface  $request
     * @param ResponseInterface $response
     * @param array|null        $arguments
     * @return mixed
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return $this->setCorsHeaders($response);
    }

    /**
     * Helper to set standard CORS headers.
     * 
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    private function setCorsHeaders(ResponseInterface $response)
    {
        return $response
            ->setHeader('Access-Control-Allow-Origin', '*') // Sesuaikan dengan domain Vercel jika ingin lebih ketat
            ->setHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS, PUT, DELETE')
            ->setHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, X-API-KEY')
            ->setHeader('Access-Control-Max-Age', '86400'); // Cache preflight for 24 hours
    }
}
