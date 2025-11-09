# SP-Shop API Documentation

This document describes the REST API endpoints available in the SP-Shop application.

## Authentication

### Register
- **POST** `/api/auth/register`
- Register a new user
- **Parameters:**
  - `name` (string, required)
  - `email` (string, required)
  - `password` (string, required, min:8)
  - `password_confirmation` (string, required)

### Login
- **POST** `/api/auth/login`
- Authenticate a user
- **Parameters:**
  - `email` (string, required)
  - `password` (string, required)

### Send Password Reset Link
- **POST** `/api/auth/password/email`
- Send a password reset link to the user's email
- **Parameters:**
  - `email` (string, required)

### Reset Password
- **POST** `/api/auth/password/reset`
- Reset user's password
- **Parameters:**
  - `token` (string, required)
  - `email` (string, required)
  - `password` (string, required, min:8)
  - `password_confirmation` (string, required)

### Logout
- **POST** `/api/logout`
- Logout the authenticated user
- **Authentication:** Required (Bearer Token)

## Public Routes

### Categories
- **GET** `/api/categories`
- Get a list of all categories with pagination
- **Query Parameters:**
  - `name` (string, optional) - Filter by category name

- **GET** `/api/categories/{category}`
- Get details of a specific category

### Products
- **GET** `/api/products`
- Get a list of all products with pagination
- **Query Parameters:**
  - `name` (string, optional) - Filter by product name
  - `category_id` (integer, optional) - Filter by category
  - `min_price` (numeric, optional) - Filter by minimum price
  - `max_price` (numeric, optional) - Filter by maximum price

- **GET** `/api/products/{product}`
- Get details of a specific product

## Protected Routes

All protected routes require authentication with a Bearer Token.

### Users
- **GET** `/api/users`
- Get a list of all users (Admin only)
- **Query Parameters:**
  - `page` (integer, optional) - Page number for pagination

- **POST** `/api/users`
- Create a new user (Admin only)
- **Parameters:**
  - `name` (string, required)
  - `email` (string, required)
  - `password` (string, required, min:8)
  - `password_confirmation` (string, required)
  - `role` (string, required, in:admin,customer)

- **GET** `/api/users/{user}`
- Get details of a specific user
- Users can only view their own profile, admins can view any user

- **PUT/PATCH** `/api/users/{user}`
- Update a user's information
- Users can only update their own profile, admins can update any user
- **Parameters:**
  - `name` (string, optional)
  - `email` (string, optional)
  - `password` (string, optional, min:8)
  - `password_confirmation` (string, optional)
  - `role` (string, optional, in:admin,customer) - Admin only

- **DELETE** `/api/users/{user}`
- Delete a user (Admin only)

### Categories
- **POST** `/api/categories`
- Create a new category (Admin only)
- **Parameters:**
  - `name` (string, required)
  - `description` (string, optional)
  - `image` (file, optional) - Category image

- **PUT/PATCH** `/api/categories/{category}`
- Update a category (Admin only)
- **Parameters:**
  - `name` (string, optional)
  - `description` (string, optional)
  - `image` (file, optional) - Category image

- **DELETE** `/api/categories/{category}`
- Delete a category (Admin only)

### Products
- **POST** `/api/products`
- Create a new product (Admin only)
- **Parameters:**
  - `name` (string, required)
  - `description` (string, required)
  - `price` (numeric, required)
  - `discount_price` (numeric, optional)
  - `category_id` (integer, required)
  - `image` (file, optional) - Product image

- **PUT/PATCH** `/api/products/{product}`
- Update a product (Admin only)
- **Parameters:**
  - `name` (string, optional)
  - `description` (string, optional)
  - `price` (numeric, optional)
  - `discount_price` (numeric, optional)
  - `category_id` (integer, optional)
  - `image` (file, optional) - Product image

- **DELETE** `/api/products/{product}`
- Delete a product (Admin only)

### Orders
- **GET** `/api/orders`
- Get a list of orders
- Customers see only their own orders, admins can see all orders
- **Query Parameters:**
  - `status` (string, optional) - Filter by order status
  - `user_id` (integer, optional) - Filter by user (Admin only)

- **POST** `/api/orders`
- Create a new order from cart items
- Automatically calculates total from cart items

- **GET** `/api/orders/{order}`
- Get details of a specific order
- Users can only view their own orders, admins can view any order

- **PUT/PATCH** `/api/orders/{order}`
- Update an order (Admin only)
- **Parameters:**
  - `status` (string, optional) - Order status

- **DELETE** `/api/orders/{order}`
- Delete an order
- Users can only delete their own orders, admins can delete any order

### Cart
- **GET** `/api/carts`
- Get all items in the authenticated user's cart

- **POST** `/api/carts`
- Add a product to the cart
- **Parameters:**
  - `product_id` (integer, required)
  - `quantity` (integer, required, min:1)

- **GET** `/api/carts/{cart}`
- Get details of a specific cart item

- **PUT/PATCH** `/api/carts/{cart}`
- Update a cart item
- **Parameters:**
  - `quantity` (integer, required, min:1)

- **DELETE** `/api/carts/{cart}`
- Remove an item from the cart
