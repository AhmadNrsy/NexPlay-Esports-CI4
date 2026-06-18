<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');

$routes->group('api', ['namespace' => 'App\Controllers\Api'], function ($routes) {
    // Public routes
    $routes->post('auth/login', 'Auth::login');

    // Protected routes (requires Token)
    $routes->group('', ['filter' => 'authFilter'], function ($routes) {
        $routes->post('auth/logout', 'Auth::logout');

        // Resourceful routes (automatically maps GET, POST, PUT, DELETE)
        $routes->resource('users', ['controller' => 'Users']);
        $routes->resource('gaming-rooms', ['controller' => 'GamingRooms']);
        $routes->resource('pc-setups', ['controller' => 'PcSetups']);
        $routes->resource('bookings', ['controller' => 'Bookings']);
        $routes->resource('payments', ['controller' => 'Payments']);

        // Custom stats routes
        $routes->get('stats/active-bookings', 'Stats::activeBookings');
        $routes->get('stats/available-rooms', 'Stats::availableRooms');
        $routes->get('stats/revenue', 'Stats::revenue');
        $routes->get('stats/total-bookings', 'Stats::totalBookings');
    });
});
