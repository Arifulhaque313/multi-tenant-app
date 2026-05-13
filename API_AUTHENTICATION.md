# Sanctum API Authentication Guide

This guide documents the API authentication system for your multi-tenant Laravel application using Laravel Sanctum.

## Overview

Laravel Sanctum provides a lightweight authentication system for SPAs (Single Page Applications) and mobile applications. This implementation includes:

- **User Registration** - Create new user accounts
- **User Login** - Authenticate and receive API tokens
- **User Logout** - Revoke current tokens
- **Token Refresh** - Generate a new token while revoking the old one
- **Current User** - Fetch authenticated user data

## Base URL

```
http://localhost:8000/api
```

---

## Public Endpoints

### 1. Register (POST)

**Endpoint:** `/auth/register`

**Description:** Create a new user account.

**Request Headers:**
```
Content-Type: application/json
Accept: application/json
```

**Request Body:**
```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password123",
  "password_confirmation": "password123"
}
```

**Success Response (201 Created):**
```json
{
  "success": true,
  "message": "User registered successfully",
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "email_verified_at": null,
    "profile_photo_path": null,
    "created_at": "2026-05-14T12:34:56.000000Z",
    "updated_at": "2026-05-14T12:34:56.000000Z"
  },
  "token": "1|abcd1234efgh5678ijkl9012mnop3456"
}
```

**Error Response (422 Unprocessable Entity):**
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "email": ["The email has already been taken."],
    "password": ["The password confirmation does not match."]
  }
}
```

**Validation Rules:**
- `name`: Required, string, max 255 characters
- `email`: Required, string, email format, unique in users table
- `password`: Required, string, min 8 characters, must match password_confirmation

---

### 2. Login (POST)

**Endpoint:** `/auth/login`

**Description:** Authenticate user and receive an API token.

**Request Headers:**
```
Content-Type: application/json
Accept: application/json
```

**Request Body:**
```json
{
  "email": "john@example.com",
  "password": "password123"
}
```

**Success Response (200 OK):**
```json
{
  "success": true,
  "message": "Login successful",
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "email_verified_at": null,
    "profile_photo_path": null,
    "created_at": "2026-05-14T12:34:56.000000Z",
    "updated_at": "2026-05-14T12:34:56.000000Z"
  },
  "token": "2|wxyz5678abcd1234efgh9012ijkl5678"
}
```

**Error Response (401 Unauthorized):**
```json
{
  "success": false,
  "message": "Invalid credentials"
}
```

**Validation Rules:**
- `email`: Required, string, email format
- `password`: Required, string

---

## Protected Endpoints

All endpoints below require authentication using the Sanctum token.

### 3. Get Current User (GET)

**Endpoint:** `/auth/user`

**Description:** Fetch the authenticated user's data.

**Request Headers:**
```
Authorization: Bearer {token}
Accept: application/json
```

**Success Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "email_verified_at": null,
    "profile_photo_path": null,
    "created_at": "2026-05-14T12:34:56.000000Z",
    "updated_at": "2026-05-14T12:34:56.000000Z"
  }
}
```

**Error Response (401 Unauthorized):**
```json
{
  "message": "Unauthenticated."
}
```

---

### 4. Refresh Token (POST)

**Endpoint:** `/auth/refresh-token`

**Description:** Generate a new token and revoke the current one.

**Request Headers:**
```
Authorization: Bearer {token}
Accept: application/json
```

**Request Body:**
```json
{}
```

**Success Response (200 OK):**
```json
{
  "success": true,
  "message": "Token refreshed successfully",
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "email_verified_at": null,
    "profile_photo_path": null,
    "created_at": "2026-05-14T12:34:56.000000Z",
    "updated_at": "2026-05-14T12:34:56.000000Z"
  },
  "token": "3|new1234token5678efgh9012ijkl5678"
}
```

---

### 5. Logout (POST)

**Endpoint:** `/auth/logout`

**Description:** Revoke all user tokens and logout.

**Request Headers:**
```
Authorization: Bearer {token}
Accept: application/json
```

**Request Body:**
```json
{}
```

**Success Response (200 OK):**
```json
{
  "success": true,
  "message": "Logged out successfully"
}
```

**Error Response (401 Unauthorized):**
```json
{
  "message": "Unauthenticated."
}
```

---

## How to Use Tokens

### In JavaScript/Fetch API:

```javascript
// Registration
const registerResponse = await fetch('http://localhost:8000/api/auth/register', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
  body: JSON.stringify({
    name: 'John Doe',
    email: 'john@example.com',
    password: 'password123',
    password_confirmation: 'password123'
  })
});

const { token } = await registerResponse.json();

// Using token for authenticated requests
const userResponse = await fetch('http://localhost:8000/api/auth/user', {
  method: 'GET',
  headers: {
    'Authorization': `Bearer ${token}`,
    'Accept': 'application/json',
  }
});

const user = await userResponse.json();
console.log(user);
```

### In Axios:

```javascript
import axios from 'axios';

const api = axios.create({
  baseURL: 'http://localhost:8000/api'
});

// Login
const { data } = await api.post('/auth/login', {
  email: 'john@example.com',
  password: 'password123'
});

const token = data.token;

// Set token for all requests
api.defaults.headers.common['Authorization'] = `Bearer ${token}`;

// Get user
const user = await api.get('/auth/user');
console.log(user.data);

// Logout
await api.post('/auth/logout');
```

### In cURL:

```bash
# Register
curl -X POST http://localhost:8000/api/auth/register \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123"
  }'

# Login
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "email": "john@example.com",
    "password": "password123"
  }'

# Get current user (replace TOKEN with actual token)
curl -X GET http://localhost:8000/api/auth/user \
  -H "Authorization: Bearer TOKEN" \
  -H "Accept: application/json"

# Logout
curl -X POST http://localhost:8000/api/auth/logout \
  -H "Authorization: Bearer TOKEN" \
  -H "Accept: application/json"
```

---

## Multi-Tenant Support

### Each Tenant Has Its Own:
- Users table
- Personal access tokens table
- Isolated authentication context

### Token Isolation:
- Tokens created on `tenant1.example.com` work **ONLY** on `tenant1.example.com`
- Tokens are automatically scoped to their tenant's database
- No cross-tenant token reuse is possible

### How It Works:
```
Request to tenant1.example.com/api/auth/login
  ↓
Tenancy middleware identifies tenant
  ↓
Switches to tenant1 database
  ↓
User authenticated in tenant1 context
  ↓
Token stored in tenant1.personal_access_tokens
  ↓
Token only works for requests to tenant1.example.com
```

---

## Error Handling

### Common Error Responses

**401 Unauthorized - Missing Token:**
```json
{
  "message": "Unauthenticated."
}
```

**401 Unauthorized - Invalid Token:**
```json
{
  "message": "Unauthenticated."
}
```

**422 Unprocessable Entity - Validation Error:**
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "field_name": ["Error message"]
  }
}
```

**500 Internal Server Error:**
```json
{
  "success": false,
  "message": "Operation failed",
  "error": "Error details"
}
```

---

## Security Best Practices

1. **Always use HTTPS** - Don't send tokens over unencrypted connections
2. **Store tokens securely** - Use httpOnly cookies or secure storage
3. **Token expiration** - Configure token expiration in `config/sanctum.php`
4. **Token prefix** - Set `SANCTUM_TOKEN_PREFIX` in `.env` for security scanning
5. **Revoke old tokens** - Use refresh-token to get new tokens and revoke old ones
6. **Rate limiting** - Consider adding rate limiting to auth endpoints
7. **CORS** - Configure CORS properly in `config/cors.php`

---

## Configuration

### Environment Variables

Add these to your `.env` file:

```env
# Sanctum configuration
SANCTUM_STATEFUL_DOMAINS=localhost:3000,127.0.0.1:3000,localhost:8000
SANCTUM_TOKEN_PREFIX='' # Can be set for security scanning

# Authentication guard
AUTH_GUARD=api
AUTH_MODEL=App\Models\User
```

### Config Files

- **`config/sanctum.php`** - Sanctum token configuration
- **`config/auth.php`** - Authentication guard definitions (updated)
- **`config/tenancy.php`** - Multi-tenant configuration

---

## Testing the API

### Using cURL

```bash
# 1. Register
TOKEN=$(curl -s -X POST http://localhost:8000/api/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Test User",
    "email": "test@example.com",
    "password": "password123",
    "password_confirmation": "password123"
  }' | jq -r '.token')

# 2. Get user
curl -X GET http://localhost:8000/api/auth/user \
  -H "Authorization: Bearer $TOKEN"

# 3. Logout
curl -X POST http://localhost:8000/api/auth/logout \
  -H "Authorization: Bearer $TOKEN"
```

### Using Postman

1. Create a new Collection: "Multi-Tenant App API"
2. Add requests for each endpoint
3. Set environment variable: `{{token}}` from login/register response
4. Use `{{token}}` in Authorization header for protected endpoints

### Using Laravel Pest/PHPUnit

Tests are located in `tests/Feature/Api/`. Run:

```bash
php artisan test tests/Feature/Api/SanctumAuthenticationTest.php
```

---

## Next Steps (Optional Enhancements)

1. **Email Verification** - Require email verification before account activation
2. **Password Reset** - Add forgot password functionality
3. **Rate Limiting** - Prevent brute force attacks
4. **Two-Factor Authentication** - Already configured! (Jetstream includes 2FA)
5. **Roles & Permissions** - Add authorization layer
6. **API Resource Classes** - Transform responses
7. **API Testing** - Write comprehensive tests

---

## Support

For issues or questions, check:
- [Laravel Sanctum Documentation](https://laravel.com/docs/sanctum)
- [Multi-Tenancy for Laravel Documentation](https://tenancyforlaravel.com)
- Project README.md
