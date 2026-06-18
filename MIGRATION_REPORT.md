# CodeIgniter 4 Migration Report: NexPlay Esports API

This report outlines the migration strategy for moving the NexPlay Esports REST API from native PHP to CodeIgniter 4 (CI4).

## 1. Database Tables

The database `db_nexplay_esports` consists of the following tables, which will be maintained in the CI4 migration:

1.  **`admin_accounts`**: Stores admin credentials for API access.
    - `admin_id` (PK), `username`, `password` (SHA-256), `created_at`
2.  **`auth_tokens`**: Stores active authentication tokens.
    - `token_id` (PK), `admin_id` (FK), `token`, `expired_at`, `created_at`
3.  **`users`**: Stores customer/player data.
    - `user_id` (PK), `username`, `email`, `tier_member` (Enum: Bronze, Silver, Gold, Radiant)
4.  **`gaming_rooms`**: Stores available rooms.
    - `room_id` (PK), `nama_room`, `tipe_room` (Enum: Regular, VIP, VVIP, Streaming), `harga_per_jam`, `status_room` (Enum: Available, Maintenance)
5.  **`pc_setups`**: Stores PC specifications mapped to rooms.
    - `pc_id` (PK), `room_id` (FK), `spek_cpu`, `spek_gpu`, `monitor`
6.  **`bookings`**: Stores room reservations.
    - `booking_id` (PK), `user_id` (FK), `room_id` (FK), `waktu_mulai`, `durasi_jam`, `total_harga`, `status_booking` (Enum: Pending, Active, Completed, Cancelled)
7.  **`payments`**: Stores payment data.
    - `payment_id` (PK), `booking_id` (FK), `metode_bayar` (Enum: QRIS, Gopay, OVO, Cash), `status_bayar` (Enum: Unpaid, Paid)

## 2. All Endpoints

| Resource     | Current Native Endpoint         | Proposed CI4 Endpoint        | Method   | Description           |
| :----------- | :------------------------------ | :--------------------------- | :------- | :-------------------- |
| **Auth**     | `api/auth/login.php`            | `/api/auth/login`            | POST     | Admin login           |
| **Auth**     | `api/auth/logout.php`           | `/api/auth/logout`           | POST     | Admin logout          |
| **Users**    | `api/users/create.php`          | `/api/users`                 | POST     | Create user           |
| **Users**    | `api/users/read.php`            | `/api/users`                 | GET      | List users            |
| **Users**    | `api/users/update.php`          | `/api/users/{id}`            | PUT/POST | Update user           |
| **Users**    | `api/users/delete.php`          | `/api/users/{id}`            | DELETE   | Delete user           |
| **Rooms**    | `api/gaming_rooms/create.php`   | `/api/gaming-rooms`          | POST     | Create room           |
| **Rooms**    | `api/gaming_rooms/read.php`     | `/api/gaming-rooms`          | GET      | List rooms            |
| **Rooms**    | `api/gaming_rooms/update.php`   | `/api/gaming-rooms/{id}`     | PUT/POST | Update room           |
| **Rooms**    | `api/gaming_rooms/delete.php`   | `/api/gaming-rooms/{id}`     | DELETE   | Delete room           |
| **Setups**   | `api/pc_setups/create.php`      | `/api/pc-setups`             | POST     | Create setup          |
| **Setups**   | `api/pc_setups/read.php`        | `/api/pc-setups`             | GET      | List setups           |
| **Setups**   | `api/pc_setups/update.php`      | `/api/pc-setups/{id}`        | PUT/POST | Update setup          |
| **Setups**   | `api/pc_setups/delete.php`      | `/api/pc-setups/{id}`        | DELETE   | Delete setup          |
| **Bookings** | `api/bookings/create.php`       | `/api/bookings`              | POST     | Create booking        |
| **Bookings** | `api/bookings/read.php`         | `/api/bookings`              | GET      | List bookings         |
| **Bookings** | `api/bookings/update.php`       | `/api/bookings/{id}`         | PUT/POST | Update booking        |
| **Bookings** | `api/bookings/delete.php`       | `/api/bookings/{id}`         | DELETE   | Delete booking        |
| **Payments** | `api/payments/create.php`       | `/api/payments`              | POST     | Create payment        |
| **Payments** | `api/payments/read.php`         | `/api/payments`              | GET      | List payments         |
| **Payments** | `api/payments/update.php`       | `/api/payments/{id}`         | PUT/POST | Update payment        |
| **Payments** | `api/payments/delete.php`       | `/api/payments/{id}`         | DELETE   | Delete payment        |
| **Stats**    | `api/stats/active_bookings.php` | `/api/stats/active-bookings` | GET      | Count active bookings |
| **Stats**    | `api/stats/available_rooms.php` | `/api/stats/available-rooms` | GET      | Count available rooms |
| **Stats**    | `api/stats/revenue.php`         | `/api/stats/revenue`         | GET      | Total revenue         |
| **Stats**    | `api/stats/total_bookings.php`  | `/api/stats/total-bookings`  | GET      | Total bookings        |

## 3. Authentication Flow

**Current Native Implementation:**

- A manual check is done at the beginning of each endpoint by calling `validate_token($conn)` from `middleware/auth.php`.
- The token is retrieved from the `Authorization: Bearer <token>` header.
- A DB query checks `auth_tokens` (joined with `admin_accounts`) to see if the token exists and hasn't expired (`expired_at > NOW()`).
- On successful login, a new token is generated (random hex 64 characters) and stored with a 12-hour expiration time. Old tokens for the admin are optionally deleted.

**CI4 Migration Strategy:**

- Move login and logout logic into an `AuthController`.
- Convert `validate_token()` logic into a **CodeIgniter 4 Filter** (e.g., `AuthFilter`).
- The `AuthFilter` will intercept all protected routes, validate the token from the request header against the database, and return a `401 Unauthorized` ResponseTrait if invalid.

## 4. Request Body

_Data typically passed as `form-data` or `x-www-form-urlencoded` in the current PHP native app. CI4 allows fetching via `$this->request->getVar()`._

- **Auth (Login)**: `username`, `password`
- **Users (Create/Update)**: `username`, `email`, `tier_member` (Update might require `id` depending on routing)
- **Gaming Rooms (Create/Update)**: `nama_room`, `tipe_room`, `harga_per_jam`, `status_room`
- **PC Setups (Create/Update)**: `room_id`, `spek_cpu`, `spek_gpu`, `monitor`
- **Bookings (Create/Update)**: `user_id`, `room_id`, `waktu_mulai`, `durasi_jam`, `total_harga`, `status_booking`
- **Payments (Create/Update)**: `booking_id`, `metode_bayar`, `status_bayar`
- _(Read and Delete endpoints rely on the URI segment ID or no body for full lists)_

## 5. Response JSON Format

The CI4 API should standardize the response format utilizing CodeIgniter's `ResponseTrait` (`$this->respond()`, `$this->fail()`).

**Success Response:**

```json
{
    "status": true,
    "message": "Action successful description",
    "data": { ... } // Array or Object, omitted if empty
}
```

**Error Response:**

```json
{
  "status": false,
  "message": "Error description or validation failures"
}
```

_(Note: Native PHP returns `"status": false` instead of `error` HTTP standard formats, but to maintain contract compatibility with clients, keeping the `"status"` boolean wrapper is recommended)._

## 6. Suggested Controllers

To fully embrace CI4 structure, use `ResourceController` or implement `ResponseTrait` in standard controllers inside the `app/Controllers/Api/` namespace:

1.  `app/Controllers/Api/Auth.php` (Custom logic for login/logout)
2.  `app/Controllers/Api/Users.php` (Resource Controller)
3.  `app/Controllers/Api/GamingRooms.php` (Resource Controller)
4.  `app/Controllers/Api/PcSetups.php` (Resource Controller)
5.  `app/Controllers/Api/Bookings.php` (Resource Controller)
6.  `app/Controllers/Api/Payments.php` (Resource Controller)
7.  `app/Controllers/Api/Stats.php` (Custom controller for aggregated queries)

## 7. Suggested Models

Create these CI4 Models matching the DB schema. They will handle `allowedFields`, `primaryKey`, and `returnType`:

1.  `AdminAccountModel` (Table: `admin_accounts`, PK: `admin_id`)
2.  `AuthTokenModel` (Table: `auth_tokens`, PK: `token_id`)
3.  `UserModel` (Table: `users`, PK: `user_id`)
4.  `GamingRoomModel` (Table: `gaming_rooms`, PK: `room_id`)
5.  `PcSetupModel` (Table: `pc_setups`, PK: `pc_id`)
6.  `BookingModel` (Table: `bookings`, PK: `booking_id`)
7.  `PaymentModel` (Table: `payments`, PK: `payment_id`)

## 8. Suggested Routes

Configure `app/Config/Routes.php` using groups and the `filter`:

```php
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
```

## 9. Middleware Requirements

Create `app/Filters/AuthFilter.php` implementing `FilterInterface`.

**Behavior:**

1. Check for `Authorization` header.
2. Extract the token (`Bearer <token>`).
3. Query `AuthTokenModel` where `token = $token` and `expired_at > date('Y-m-d H:i:s')`.
4. If invalid, block the request and return JSON `401 Unauthorized`.
5. Register this filter in `app/Config/Filters.php` under the `$aliases` array (e.g., `'authFilter' => \App\Filters\AuthFilter::class`).
