# 2k-shop

E-commerce application with product listing, cart, and order management features built with Laravel.

## Table of Contents

- [Stack Used](#stack-used)
- [Setup](#setup)
    - [Normal Laravel Setup](#normal-laravel-setup)
    - [Docker Setup](#docker-setup)
- [Configuration](#configuration)
- [Running the Application](#running-the-application)
- [Features](#features)
    - [Stock Management](#stock-management)
    - [Low Stock Notification](#low-stock-notification)
    - [Daily Sales Report](#daily-sales-report)
- [API Endpoints](#api-endpoints)
- [Running Unit Tests](#running-unit-tests)
- [Project Thoughts & Philosophy](#project-thoughts--philosophy)


## Stack Used

- Laravel 10.x
- PHP 8.1
- MySQL 8
- Vue 3 + Vite + Inertia
- Docker
- Nginx
- Redis

## Setup

### Normal Laravel Setup

1. **Clone the Repository:**
   ```bash
   git clone git@github.com:mavenleo/2k-shop.git
   cd 2k-shop
   ```
2. **Install Dependencies:**
    ```bash
    composer install
    yarn install
    ```
3. **Copy Environment File:**
    ```bash
    cp .env.example .env
    ```
4. **Generate Application Key:**
    ```bash
    php artisan key:generate
    ```
5. **Run Migrations & Seed:**
    ```bash
    php artisan migrate --seed
    ```
6. **Run Development Server:**
    ```bash
    yarn dev
    php artisan serve
    ```

### Docker Setup

1. **CD to application root:**
   ```bash
   cd 2k-shop
   ```
1. **Copy Environment File:**
   ```bash
   cp .env.example .env
   ```
1. **Build and Run Docker Containers:**
   ```bash
   docker-compose up -d --build
   ```
   
## Configuration

**Environment Configuration:**
- Update .env file with your database and other configuration settings.
- Set `ADMIN_EMAIL` in .env to configure the admin user email for notifications.
- Set `LOW_STOCK_THRESHOLD` in .env to configure the threshold for low stock alerts (default: 5).

## Running the Application

**Normal Laravel Setup:**
- Access the application at http://localhost:8000

**Docker Setup:**
- Access the application at http://localhost:8080

## Features

### Stock Management

The application includes comprehensive stock management features:

- **Stock Quantity Tracking**: Each product has a `stock_quantity` field that tracks available inventory.
- **Stock Validation**: When adding items to cart or updating quantities, the system validates that sufficient stock is available.
- **Out of Stock Handling**: Products with `stock_quantity = 0` cannot be added to cart.
- **Insufficient Stock Handling**: Users cannot add more items to cart than available in stock.
- **Automatic Stock Decrement**: When an order is created, product stock quantities are automatically decremented.

### Low Stock Notification

The system automatically monitors product stock levels and sends email notifications to the admin when products are running low.

**How it works:**
- When a product's stock quantity falls below the configured threshold (default: 5), a `LowStockNotificationJob` is dispatched.
- The job sends an email to the admin user (configured via `ADMIN_EMAIL` in .env) with product details and current stock level.
- The notification includes:
  - Product name and description
  - Current stock quantity
  - Product price

**Configuration:**
- Set `LOW_STOCK_THRESHOLD` in `.env` to configure the threshold (default: 5)
- Set `ADMIN_EMAIL` in `.env` to configure the admin user email

**Manual Trigger:**
You can manually check for low stock products and dispatch notifications by running:
```bash
php artisan tinker
>>> \App\Jobs\LowStockNotificationJob::dispatch($product);
```

### Daily Sales Report

The system automatically generates and emails a daily sales report to the admin user every evening.

**How it works:**
- A scheduled job (`DailySalesReportJob`) runs daily at the configured time (default: 8:00 PM).
- The job collects all completed orders from the previous day.
- An email report is sent to the admin user with:
  - Total revenue for the day
  - Total number of orders
  - Total items sold
  - Product breakdown (quantity and revenue per product)

**Configuration:**
- The job is scheduled in `app/Console/Kernel.php`
- Default schedule: Daily at 8:00 PM
- Set `ADMIN_EMAIL` in `.env` to configure the admin user email

**Manual Trigger:**
You can manually trigger the daily sales report by running:
```bash
php artisan tinker
>>> \App\Jobs\DailySalesReportJob::dispatch();
```

## API Endpoints

### Authentication

#### Register
- **Endpoint:** `POST /api/v1/auth/register`
- **Body:**
  ```json
  {
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123"
  }
  ```
- **Sample Response (201):**
  ```json
  {
    "message": "User registered successfully",
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com"
    },
    "token": "..."
  }
  ```

#### Login
- **Endpoint:** `POST /api/v1/auth/login`
- **Body:**
  ```json
  {
    "email": "john@example.com",
    "password": "password123"
  }
  ```
- **Sample Response (200):**
  ```json
  {
    "message": "Login successful",
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com"
    }
  }
  ```

#### Logout
- **Endpoint:** `POST /api/v1/auth/logout`
- **Authentication:** Required (session)
- **Sample Response (200):**
  ```json
  {
    "message": "Logout successful"
  }
  ```

#### Get Current User
- **Endpoint:** `GET /api/v1/auth/user`
- **Authentication:** Required (session)
- **Sample Response (200):**
  ```json
  {
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com"
    }
  }
  ```

---

### Products

#### List Products
- **Endpoint:** `GET /api/v1/products`
- **Authentication:** Required
- **Query Parameters:**
  - `page` (optional): Page number for pagination
- **Sample Response (200):**
  ```json
  {
    "data": [
      {
        "id": 1,
        "name": "Pioneer DJ Mixer",
        "price": "699.00",
        "description": "A professional DJ mixer.",
        "stock_quantity": 10,
        "is_in_cart": true
      }
    ],
    "links": {
      "first": "http://localhost:8000/api/v1/products?page=1",
      "last": "http://localhost:8000/api/v1/products?page=1",
      "prev": null,
      "next": null
    },
    "meta": {
      "current_page": 1,
      "last_page": 1,
      "per_page": 15,
      "total": 1
    }
  }
  ```

#### Get Single Product
- **Endpoint:** `GET /api/v1/products/{id}`
- **Authentication:** Optional
- **Sample Response (200):**
  ```json
  {
    "data": {
      "id": 1,
      "name": "Pioneer DJ Mixer",
      "price": "699.00",
      "description": "A professional DJ mixer.",
      "stock_quantity": 10
    },
    "is_in_cart": true
  }
  ```

---

### Cart

> All cart endpoints require authentication.

#### Get Cart
- **Endpoint:** `GET /api/v1/cart`
- **Query Parameters:**
  - `page` (optional): Page number for pagination
  - `perPage` (optional): Items per page (default: 15)
- **Sample Response (200):**
  ```json
  {
    "data": [
      {
        "id": 1,
        "user_id": 1,
        "product_id": 6,
        "quantity": 2,
        "product": {
          "id": 6,
          "name": "Product 1448",
          "description": "Product description",
          "price": "973.68",
          "stock_quantity": 10
        },
        "subtotal": 1947.36,
        "created_at": "2025-07-08T14:03:12.000000Z",
        "updated_at": "2025-07-08T14:03:12.000000Z"
      }
    ],
    "pagination": {
      "current_page": 1,
      "last_page": 1,
      "per_page": 15,
      "total": 1
    }
  }
  ```

#### Add Product to Cart
- **Endpoint:** `POST /api/v1/cart`
- **Body:**
  ```json
  {
    "product_id": 1,
    "quantity": 1
  }
  ```
- **Sample Response (201):**
  ```json
  {
    "message": "Product added to cart successfully",
    "data": {
      "id": 1,
      "user_id": 1,
      "product_id": 1,
      "quantity": 1
    }
  }
  ```
- **Error Responses:**
  - `400`: Product is out of stock or insufficient stock available
  - `422`: Validation error (product_id or quantity invalid)

#### Update Cart Item Quantity
- **Endpoint:** `PUT /api/v1/cart`
- **Body:**
  ```json
  {
    "product_id": 1,
    "quantity": 3
  }
  ```
- **Sample Response (200):**
  ```json
  {
    "message": "Cart item updated successfully",
    "data": {
      "id": 1,
      "user_id": 1,
      "product_id": 1,
      "quantity": 3
    }
  }
  ```
- **Error Responses:**
  - `400`: Product is out of stock or insufficient stock available
  - `422`: Validation error

#### Remove Product from Cart
- **Endpoint:** `DELETE /api/v1/cart`
- **Body:**
  ```json
  {
    "product_id": 1
  }
  ```
- **Sample Response (200):**
  ```json
  {
    "message": "Product removed from cart successfully"
  }
  ```

#### Toggle Product in Cart
- **Endpoint:** `POST /api/v1/cart/toggle`
- **Body:**
  ```json
  {
    "product_id": 1
  }
  ```
- **Sample Response (200):**
  ```json
  {
    "message": "Product added to cart successfully",
    "data": {
      "action": "added",
      "is_in_cart": true,
      "count": 1
    }
  }
  ```

#### Check if Product is in Cart
- **Endpoint:** `GET /api/v1/cart/check/{productId}`
- **Sample Response (200):**
  ```json
  {
    "is_in_cart": true
  }
  ```

#### Get Cart Count
- **Endpoint:** `GET /api/v1/cart/count`
- **Sample Response (200):**
  ```json
  {
    "data": {
      "count": 3
    }
  }
  ```

#### Clear Cart
- **Endpoint:** `DELETE /api/v1/cart/clear`
- **Sample Response (200):**
  ```json
  {
    "message": "Cart cleared successfully"
  }
  ```

---

### Orders

> All order endpoints require authentication.

#### Create Order
- **Endpoint:** `POST /api/v1/orders`
- **Description:** Creates an order from the user's current cart. Validates stock, creates order items, decrements product stock, and clears the cart.
- **Sample Response (201):**
  ```json
  {
    "message": "Order created successfully",
    "data": {
      "id": 1,
      "user_id": 1,
      "total_amount": "250.00",
      "status": "completed",
      "order_items": [
        {
          "id": 1,
          "order_id": 1,
          "product_id": 1,
          "quantity": 2,
          "price_at_purchase": "100.00",
          "product": {
            "id": 1,
            "name": "Product Name"
          }
        }
      ],
      "created_at": "2025-12-25T10:00:00.000000Z"
    }
  }
  ```
- **Error Responses:**
  - `400`: Cart is empty or insufficient stock available

#### Get User Orders
- **Endpoint:** `GET /api/v1/orders`
- **Query Parameters:**
  - `page` (optional): Page number for pagination
  - `perPage` (optional): Items per page (default: 15)
- **Sample Response (200):**
  ```json
  {
    "data": [
      {
        "id": 1,
        "user_id": 1,
        "total_amount": "250.00",
        "status": "completed",
        "order_items": [
          {
            "id": 1,
            "product_id": 1,
            "quantity": 2,
            "price_at_purchase": "100.00",
            "product": {
              "id": 1,
              "name": "Product Name"
            }
          }
        ],
        "created_at": "2025-12-25T10:00:00.000000Z"
      }
    ],
    "pagination": {
      "current_page": 1,
      "last_page": 1,
      "per_page": 15,
      "total": 1
    }
  }
  ```

## Running Unit Tests

```shell
php artisan key:generate --env=testing
php artisan test
```
