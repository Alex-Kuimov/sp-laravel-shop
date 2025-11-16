# SP-Shop REST API Documentation

This document provides detailed information about the REST API endpoints for the SP-Shop e-commerce application. The API follows REST conventions and uses JSON for request/response formatting.

## Base URL

All URLs referenced in the documentation have the following base:

```
http://localhost:8000/api
```

## Authentication

The API uses token-based authentication. After successful login or registration, a token is returned which must be included in the Authorization header for all subsequent requests:

```
Authorization: Bearer <token>
```

## Public Endpoints

These endpoints do not require authentication.

### Authentication Routes

#### Register a New User

- **URL**: `/auth/register`
- **Method**: `POST`
- **Auth Required**: No
- **Permissions**: None

##### Request Body

| Field | Type | Description |
|-------|------|-------------|
| name | string | User's full name |
| email | string | User's email address (must be unique) |
| password | string | User's password (minimum 8 characters) |
| password_confirmation | string | Password confirmation |

##### Success Response

- **Code**: 200
- **Content**:
```json
{
  "token": "1|abcdefghijklmnopqrstuvwxyz"
}
```

#### Login User

- **URL**: `/auth/login`
- **Method**: `POST`
- **Auth Required**: No
- **Permissions**: None

##### Request Body

| Field | Type | Description |
|-------|------|-------------|
| email | string | User's email address |
| password | string | User's password |

##### Success Response

- **Code**: 200
- **Content**:
```json
{
  "token": "1|abcdefghijklmnopqrstuvwxyz"
}
```

#### Send Password Reset Link

- **URL**: `/auth/password/email`
- **Method**: `POST`
- **Auth Required**: No
- **Permissions**: None

##### Request Body

| Field | Type | Description |
|-------|------|-------------|
| email | string | User's email address |

##### Success Response

- **Code**: 200
- **Content**:
```json
{
  "message": "Password reset link sent successfully"
}
```

#### Reset Password

- **URL**: `/auth/password/reset`
- **Method**: `POST`
- **Auth Required**: No
- **Permissions**: None

##### Request Body

| Field | Type | Description |
|-------|------|-------------|
| token | string | Password reset token |
| email | string | User's email address |
| password | string | New password (minimum 8 characters) |
| password_confirmation | string | Password confirmation |

##### Success Response

- **Code**: 200
- **Content**:
```json
{
  "message": "Password reset successfully"
}
```

### Category Routes

#### Get All Categories

- **URL**: `/categories`
- **Method**: `GET`
- **Auth Required**: No
- **Permissions**: None

##### Query Parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| name | string | Filter categories by name (partial match) |
| page | integer | Page number for pagination |

##### Success Response

- **Code**: 200
- **Content**:
```json
{
  "data": [
    {
      "id": 1,
      "name": "Electronics",
      "description": "Electronic devices and accessories",
      "created_at": "2023-01-01T00:00:00.000000Z",
      "updated_at": "2023-01-01T00:00:00.000000Z",
      "media": []
    }
  ],
  "links": {
    "first": "http://localhost:8000/api/categories?page=1",
    "last": "http://localhost:8000/api/categories?page=1",
    "prev": null,
    "next": null
  },
  "meta": {
    "current_page": 1,
    "from": 1,
    "last_page": 1,
    "path": "http://localhost:8000/api/categories",
    "per_page": 10,
    "to": 1,
    "total": 1
  }
}
```

#### Get Specific Category

- **URL**: `/categories/{id}`
- **Method**: `GET`
- **Auth Required**: No
- **Permissions**: None

##### Success Response

- **Code**: 200
- **Content**:
```json
{
  "id": 1,
  "name": "Electronics",
  "description": "Electronic devices and accessories",
  "created_at": "2023-01-01T00:00:00.000000Z",
  "updated_at": "2023-01-01T00:00:00.000000Z",
  "media": []
}
```

### Product Routes

#### Get All Products

- **URL**: `/products`
- **Method**: `GET`
- **Auth Required**: No
- **Permissions**: None

##### Query Parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| name | string | Filter products by name (partial match) |
| category_id | integer | Filter products by category |
| min_price | number | Minimum price filter |
| max_price | number | Maximum price filter |
| page | integer | Page number for pagination |

##### Success Response

- **Code**: 200
- **Content**:
```json
{
  "data": [
    {
      "id": 1,
      "name": "Smartphone",
      "description": "Latest model smartphone",
      "price": 599.99,
      "discount_price": 499.99,
      "category_id": 1,
      "created_at": "2023-01-01T00:00:00.000000Z",
      "updated_at": "2023-01-01T00:00:00.000000Z",
      "category": {
        "id": 1,
        "name": "Electronics",
        "description": "Electronic devices and accessories",
        "created_at": "2023-01-01T00:00:00.000000Z",
        "updated_at": "2023-01-01T00:00:00.000000Z"
      },
      "media": []
    }
  ],
  "links": {
    "first": "http://localhost:8000/api/products?page=1",
    "last": "http://localhost:8000/api/products?page=1",
    "prev": null,
    "next": null
  },
  "meta": {
    "current_page": 1,
    "from": 1,
    "last_page": 1,
    "path": "http://localhost:8000/api/products",
    "per_page": 10,
    "to": 1,
    "total": 1
  }
}
```

#### Get Specific Product

- **URL**: `/products/{id}`
- **Method**: `GET`
- **Auth Required**: No
- **Permissions**: None

##### Success Response

- **Code**: 200
- **Content**:
```json
{
  "id": 1,
  "name": "Smartphone",
  "description": "Latest model smartphone",
  "price": 599.99,
  "discount_price": 499.99,
  "category_id": 1,
  "created_at": "2023-01-01T00:00:00.000000Z",
  "updated_at": "2023-01-01T00:00:00.000000Z",
  "category": {
    "id": 1,
    "name": "Electronics",
    "description": "Electronic devices and accessories",
    "created_at": "2023-01-01T00:00:00.000000Z",
    "updated_at": "2023-01-01T00:00:00.000000Z"
  },
  "media": []
}
```

## Protected Endpoints

These endpoints require authentication with a valid token.

### User Routes

#### Get All Users (Admin Only)

- **URL**: `/users`
- **Method**: `GET`
- **Auth Required**: Yes
- **Permissions**: Admin only

##### Success Response

- **Code**: 200
- **Content**:
```json
{
  "data": [
    {
      "id": 1,
      "name": "Admin User",
      "email": "admin@example.com",
      "role": "admin",
      "email_verified_at": null,
      "created_at": "2023-01-01T00:00:00.000000Z",
      "updated_at": "2023-01-01T00:00:00.000000Z"
    }
  ],
  "links": {
    "first": "http://localhost:8000/api/users?page=1",
    "last": "http://localhost:8000/api/users?page=1",
    "prev": null,
    "next": null
  },
  "meta": {
    "current_page": 1,
    "from": 1,
    "last_page": 1,
    "path": "http://localhost:8000/api/users",
    "per_page": 10,
    "to": 1,
    "total": 1
  }
}
```

#### Create User (Admin Only)

- **URL**: `/users`
- **Method**: `POST`
- **Auth Required**: Yes
- **Permissions**: Admin only

##### Request Body

| Field | Type | Description |
|-------|------|-------------|
| name | string | User's full name |
| email | string | User's email address (must be unique) |
| password | string | User's password (minimum 8 characters) |
| password_confirmation | string | Password confirmation |
| role | string | User role (admin or customer) |

##### Success Response

- **Code**: 201
- **Content**:
```json
{
  "id": 2,
  "name": "Customer User",
  "email": "customer@example.com",
  "role": "customer",
  "email_verified_at": null,
  "created_at": "2023-01-01T00:00:00.000000Z",
  "updated_at": "2023-01-01T00:00:00.000000Z"
}
```

#### Get Specific User

- **URL**: `/users/{id}`
- **Method**: `GET`
- **Auth Required**: Yes
- **Permissions**: User can view own profile, Admin can view any user

##### Success Response

- **Code**: 200
- **Content**:
```json
{
  "id": 1,
  "name": "Admin User",
  "email": "admin@example.com",
  "role": "admin",
  "email_verified_at": null,
  "created_at": "2023-01-01T00:00:00.000000Z",
  "updated_at": "2023-01-01T00:00:00.000000Z"
}
```

#### Update User

- **URL**: `/users/{id}`
- **Method**: `PUT/PATCH`
- **Auth Required**: Yes
- **Permissions**: User can update own profile, Admin can update any user

##### Request Body

| Field | Type | Description |
|-------|------|-------------|
| name | string | User's full name (optional) |
| email | string | User's email address (optional) |
| password | string | User's password (optional, minimum 8 characters) |
| password_confirmation | string | Password confirmation (required if password is provided) |

##### Success Response

- **Code**: 200
- **Content**:
```json
{
  "id": 1,
  "name": "Updated Admin User",
  "email": "admin@example.com",
  "role": "admin",
  "email_verified_at": null,
  "created_at": "2023-01-01T00:00:00.000000Z",
  "updated_at": "2023-01-01T00:00:00.000000Z"
}
```

#### Delete User (Admin Only)

- **URL**: `/users/{id}`
- **Method**: `DELETE`
- **Auth Required**: Yes
- **Permissions**: Admin only

##### Success Response

- **Code**: 200
- **Content**:
```json
{
  "message": "User deleted successfully"
}
```

#### Logout User

- **URL**: `/auth/logout`
- **Method**: `POST`
- **Auth Required**: Yes
- **Permissions**: Authenticated users

##### Success Response

- **Code**: 200
- **Content**:
```json
{
  "message": "Logged out successfully"
}
```

### Category Routes (Admin Only)

#### Create Category

- **URL**: `/categories`
- **Method**: `POST`
- **Auth Required**: Yes
- **Permissions**: Admin only

##### Request Body

| Field | Type | Description |
|-------|------|-------------|
| name | string | Category name |
| description | string | Category description |
| image | file | Category image (optional) |

##### Success Response

- **Code**: 201
- **Content**:
```json
{
  "id": 2,
  "name": "Clothing",
  "description": "Clothing and accessories",
  "created_at": "2023-01-01T00:00:00.000000Z",
  "updated_at": "2023-01-01T00:00:00.000000Z",
  "media": []
}
```

#### Update Category

- **URL**: `/categories/{id}`
- **Method**: `PUT/PATCH`
- **Auth Required**: Yes
- **Permissions**: Admin only

##### Request Body

| Field | Type | Description |
|-------|------|-------------|
| name | string | Category name (optional) |
| description | string | Category description (optional) |
| image | file | Category image (optional) |

##### Success Response

- **Code**: 200
- **Content**:
```json
{
  "id": 2,
  "name": "Updated Clothing",
  "description": "Clothing and accessories",
  "created_at": "2023-01-01T00:00:00.000000Z",
  "updated_at": "2023-01-01T00:00:00.000000Z",
  "media": []
}
```

#### Delete Category

- **URL**: `/categories/{id}`
- **Method**: `DELETE`
- **Auth Required**: Yes
- **Permissions**: Admin only

##### Success Response

- **Code**: 204
- **Content**: No content

### Product Routes (Admin Only)

#### Create Product

- **URL**: `/products`
- **Method**: `POST`
- **Auth Required**: Yes
- **Permissions**: Admin only

##### Request Body

| Field | Type | Description |
|-------|------|-------------|
| name | string | Product name |
| description | string | Product description |
| price | number | Product price |
| discount_price | number | Discounted price (optional) |
| category_id | integer | Category ID |
| image | file | Product image (optional) |

##### Success Response

- **Code**: 201
- **Content**:
```json
{
  "id": 2,
  "name": "T-Shirt",
  "description": "Cotton t-shirt",
  "price": 19.99,
  "discount_price": null,
  "category_id": 2,
  "created_at": "2023-01-01T00:00:00.000000Z",
  "updated_at": "2023-01-01T00:00:00.000000Z",
  "category": {
    "id": 2,
    "name": "Clothing",
    "description": "Clothing and accessories",
    "created_at": "2023-01-01T00:00:00.000000Z",
    "updated_at": "2023-01-01T00:00:00.000000Z"
  },
  "media": []
}
```

#### Update Product

- **URL**: `/products/{id}`
- **Method**: `PUT/PATCH`
- **Auth Required**: Yes
- **Permissions**: Admin only

##### Request Body

| Field | Type | Description |
|-------|------|-------------|
| name | string | Product name (optional) |
| description | string | Product description (optional) |
| price | number | Product price (optional) |
| discount_price | number | Discounted price (optional) |
| category_id | integer | Category ID (optional) |
| image | file | Product image (optional) |

##### Success Response

- **Code**: 200
- **Content**:
```json
{
  "id": 2,
  "name": "Updated T-Shirt",
  "description": "Cotton t-shirt",
  "price": 19.99,
  "discount_price": 15.99,
  "category_id": 2,
  "created_at": "2023-01-01T00:00:00.000000Z",
  "updated_at": "2023-01-01T00:00:00.000000Z",
  "category": {
    "id": 2,
    "name": "Clothing",
    "description": "Clothing and accessories",
    "created_at": "2023-01-01T00:00:00.000000Z",
    "updated_at": "2023-01-01T00:00:00.000000Z"
  },
  "media": []
}
```

#### Delete Product

- **URL**: `/products/{id}`
- **Method**: `DELETE`
- **Auth Required**: Yes
- **Permissions**: Admin only

##### Success Response

- **Code**: 204
- **Content**: No content

### Cart Routes

#### Get User's Cart

- **URL**: `/carts`
- **Method**: `GET`
- **Auth Required**: Yes
- **Permissions**: Authenticated users

##### Success Response

- **Code**: 200
- **Content**:
```json
[
  {
    "id": 1,
    "user_id": 1,
    "product_id": 1,
    "quantity": 2,
    "created_at": "2023-01-01T00:00:00.000000Z",
    "updated_at": "2023-01-01T00:00:00.000000Z",
    "product": {
      "id": 1,
      "name": "Smartphone",
      "description": "Latest model smartphone",
      "price": 599.99,
      "discount_price": 499.99,
      "category_id": 1,
      "created_at": "2023-01-01T00:00:00.000000Z",
      "updated_at": "2023-01-01T00:00:00.000000Z"
    }
  }
]
```

#### Add Item to Cart

- **URL**: `/carts`
- **Method**: `POST`
- **Auth Required**: Yes
- **Permissions**: Authenticated users

##### Request Body

| Field | Type | Description |
|-------|------|-------------|
| product_id | integer | Product ID |
| quantity | integer | Quantity (default: 1) |

##### Success Response

- **Code**: 201
- **Content**:
```json
{
  "id": 2,
  "user_id": 1,
  "product_id": 2,
  "quantity": 1,
  "created_at": "2023-01-01T00:00:00.000000Z",
  "updated_at": "2023-01-01T00:00:00.000000Z"
}
```

#### Get Specific Cart Item

- **URL**: `/carts/{id}`
- **Method**: `GET`
- **Auth Required**: Yes
- **Permissions**: User can view own cart items

##### Success Response

- **Code**: 200
- **Content**:
```json
{
  "id": 1,
  "user_id": 1,
  "product_id": 1,
  "quantity": 2,
  "created_at": "2023-01-01T00:00:00.000000Z",
  "updated_at": "2023-01-01T00:00:00.000000Z",
  "product": {
    "id": 1,
    "name": "Smartphone",
    "description": "Latest model smartphone",
    "price": 599.99,
    "discount_price": 499.99,
    "category_id": 1,
    "created_at": "2023-01-01T00:00:00.000000Z",
    "updated_at": "2023-01-01T00:00:00.000000Z"
  }
}
```

#### Update Cart Item

- **URL**: `/carts/{id}`
- **Method**: `PUT/PATCH`
- **Auth Required**: Yes
- **Permissions**: User can update own cart items

##### Request Body

| Field | Type | Description |
|-------|------|-------------|
| quantity | integer | New quantity |

##### Success Response

- **Code**: 200
- **Content**:
```json
{
  "id": 1,
  "user_id": 1,
  "product_id": 1,
  "quantity": 3,
  "created_at": "2023-01-01T00:00:00.000000Z",
  "updated_at": "2023-01-01T00:00:00.000000Z",
  "product": {
    "id": 1,
    "name": "Smartphone",
    "description": "Latest model smartphone",
    "price": 599.99,
    "discount_price": 499.99,
    "category_id": 1,
    "created_at": "2023-01-01T00:00:00.000000Z",
    "updated_at": "2023-01-01T00:00:00.000000Z"
  }
}
```

#### Remove Item from Cart

- **URL**: `/carts/{id}`
- **Method**: `DELETE`
- **Auth Required**: Yes
- **Permissions**: User can delete own cart items

##### Success Response

- **Code**: 204
- **Content**: No content

### Order Routes

#### Get User's Orders

- **URL**: `/orders`
- **Method**: `GET`
- **Auth Required**: Yes
- **Permissions**: Authenticated users

##### Query Parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| status | string | Filter orders by status |
| page | integer | Page number for pagination |

##### Success Response

- **Code**: 200
- **Content**:
```json
{
  "data": [
    {
      "id": 1,
      "user_id": 1,
      "status": "new",
      "total": 1099.98,
      "created_at": "2023-01-01T00:00:00.000000Z",
      "updated_at": "2023-01-01T00:00:00.000000Z",
      "user": {
        "id": 1,
        "name": "Admin User",
        "email": "admin@example.com",
        "role": "admin",
        "email_verified_at": null,
        "created_at": "2023-01-01T00:00:00.000000Z",
        "updated_at": "2023-01-01T00:00:00.000000Z"
      },
      "products": [
        {
          "id": 1,
          "name": "Smartphone",
          "description": "Latest model smartphone",
          "price": 599.99,
          "discount_price": 499.99,
          "category_id": 1,
          "created_at": "2023-01-01T00:00:00.000000Z",
          "updated_at": "2023-01-01T00:00:00.000000Z",
          "pivot": {
            "order_id": 1,
            "product_id": 1,
            "quantity": 2,
            "price": 499.99
          }
        }
      ],
      "payment": null
    }
  ],
  "links": {
    "first": "http://localhost:8000/api/orders?page=1",
    "last": "http://localhost:8000/api/orders?page=1",
    "prev": null,
    "next": null
  },
  "meta": {
    "current_page": 1,
    "from": 1,
    "last_page": 1,
    "path": "http://localhost:8000/api/orders",
    "per_page": 10,
    "to": 1,
    "total": 1
  }
}
```

#### Create Order

- **URL**: `/orders`
- **Method**: `POST`
- **Auth Required**: Yes
- **Permissions**: Authenticated users

##### Request Body

No request body required. The order will be created from the items in the user's cart.

##### Success Response

- **Code**: 201
- **Content**:
```json
{
  "id": 2,
  "user_id": 1,
  "status": "new",
  "total": 19.99,
  "created_at": "2023-01-01T00:00:00.000000Z",
  "updated_at": "2023-01-01T00:00:00.000000Z",
  "products": [
    {
      "id": 2,
      "name": "T-Shirt",
      "description": "Cotton t-shirt",
      "price": 19.99,
      "discount_price": null,
      "category_id": 2,
      "created_at": "2023-01-01T00:00:00.000000Z",
      "updated_at": "2023-01-01T00:00:00.000000Z",
      "pivot": {
        "order_id": 2,
        "product_id": 2,
        "quantity": 1,
        "price": 19.99
      }
    }
  ]
}
```

#### Get Specific Order

- **URL**: `/orders/{id}`
- **Method**: `GET`
- **Auth Required**: Yes
- **Permissions**: User can view own orders, Admin can view any order

##### Success Response

- **Code**: 200
- **Content**:
```json
{
  "id": 1,
  "user_id": 1,
  "status": "new",
  "total": 1099.98,
  "created_at": "2023-01-01T00:00:00.000000Z",
  "updated_at": "2023-01-01T00:00:00.000000Z",
  "user": {
    "id": 1,
    "name": "Admin User",
    "email": "admin@example.com",
    "role": "admin",
    "email_verified_at": null,
    "created_at": "2023-01-01T00:00:00.000000Z",
    "updated_at": "2023-01-01T00:00:00.000000Z"
  },
  "products": [
    {
      "id": 1,
      "name": "Smartphone",
      "description": "Latest model smartphone",
      "price": 599.99,
      "discount_price": 499.99,
      "category_id": 1,
      "created_at": "2023-01-01T00:00:00.000000Z",
      "updated_at": "2023-01-01T00:00:00.000000Z",
      "pivot": {
        "order_id": 1,
        "product_id": 1,
        "quantity": 2,
        "price": 499.99
      }
    }
  ],
  "payment": null,
  "history": []
}
```

#### Update Order (Admin Only)

- **URL**: `/orders/{id}`
- **Method**: `PUT/PATCH`
- **Auth Required**: Yes
- **Permissions**: Admin only

##### Request Body

| Field | Type | Description |
|-------|------|-------------|
| status | string | Order status (new, processing, shipped, delivered, cancelled) |

##### Success Response

- **Code**: 200
- **Content**:
```json
{
  "id": 1,
  "user_id": 1,
  "status": "processing",
  "total": 1099.98,
  "created_at": "2023-01-01T00:00:00.000000Z",
  "updated_at": "2023-01-01T00:00:00.000000Z",
  "user": {
    "id": 1,
    "name": "Admin User",
    "email": "admin@example.com",
    "role": "admin",
    "email_verified_at": null,
    "created_at": "2023-01-01T00:00:00.000000Z",
    "updated_at": "2023-01-01T00:00:00.000000Z"
  },
  "products": [
    {
      "id": 1,
      "name": "Smartphone",
      "description": "Latest model smartphone",
      "price": 599.99,
      "discount_price": 499.99,
      "category_id": 1,
      "created_at": "2023-01-01T00:00:00.000000Z",
      "updated_at": "2023-01-01T00:00:00.000000Z",
      "pivot": {
        "order_id": 1,
        "product_id": 1,
        "quantity": 2,
        "price": 499.99
      }
    }
  ],
  "payment": null,
  "history": []
}
```

#### Delete Order

- **URL**: `/orders/{id}`
- **Method**: `DELETE`
- **Auth Required**: Yes
- **Permissions**: User can delete own orders, Admin can delete any order

##### Success Response

- **Code**: 204
- **Content**: No content

## Error Responses

The API uses standard HTTP status codes to indicate the success or failure of requests:

| Status Code | Description |
|-------------|-------------|
| 200 | Success - GET, PUT, PATCH requests |
| 201 | Success - POST requests |
| 204 | Success - DELETE requests |
| 400 | Bad Request - Invalid request data |
| 401 | Unauthorized - Authentication required |
| 403 | Forbidden - Access denied |
| 404 | Not Found - Resource not found |
| 422 | Unprocessable Entity - Validation errors |
| 500 | Internal Server Error - Server error |

## Rate Limiting

The API implements rate limiting to prevent abuse. Users are limited to 60 requests per minute.

## Versioning

The API version is included in the URL path as `v1`:

```
http://localhost:8000/api/v1/
```

Currently, all endpoints are under version 1.