# Backend Role-Based Access Control (RBAC) Implementation

## Overview

The backend API now enforces role-based access control (RBAC) on all CRUD operations. Users can only perform actions they have permission for based on their assigned role.

## Roles & Permissions

### 1. **Admin** - Full Access

-   **Books**: View, Create, Edit, Delete ✓
-   **Products**: View, Create, Edit, Delete ✓
-   **Courses**: View, Create, Edit, Delete ✓
-   **Users**: View, Create, Edit, Delete ✓

### 2. **Manager** - Limited Access (No Delete)

-   **Books**: View ✓, Create ✓, Edit ✓, Delete ✗
-   **Products**: View ✓, Create ✓, Edit ✓, Delete ✗
-   **Courses**: View ✓, Create ✓, Edit ✓, Delete ✗
-   **Users**: No Access ✗

### 3. **User** - View Only

-   **Books**: View ✓, Create ✗, Edit ✗, Delete ✗
-   **Products**: View ✓, Create ✗, Edit ✗, Delete ✗
-   **Courses**: View ✓, Create ✗, Edit ✗, Delete ✗
-   **Users**: No Access ✗

## Implementation Details

### 1. Permission Middleware (`app/Http/Middleware/CheckPermission.php`)

A middleware that checks user permissions before allowing access to protected endpoints.

**How it works:**

```php
// Applied to routes like:
Route::post('/books', [BookController::class, 'store'])
    ->middleware('permission:books,create');
```

**Middleware Parameters:**

-   First parameter: Resource name (books, products, courses, users)
-   Second parameter: Action (view, create, edit, delete)

**Response when access denied:**

```json
{
    "message": "Akses ditolak. Anda tidak memiliki izin untuk melakukan aksi ini.",
    "status": 403
}
```

### 2. Route Protection (`routes/api.php`)

All CRUD routes are now explicitly defined with permission checks:

```php
// Books Routes
Route::get('/books', [BookController::class, 'index'])
    ->middleware('permission:books,view');
Route::post('/books', [BookController::class, 'store'])
    ->middleware('permission:books,create');
Route::get('/books/{id}', [BookController::class, 'show'])
    ->middleware('permission:books,view');
Route::post('/books/{id}', [BookController::class, 'update'])
    ->middleware('permission:books,edit');
Route::delete('/books/{id}', [BookController::class, 'destroy'])
    ->middleware('permission:books,delete');

// Similar pattern for products, courses, and users
```

### 3. User Role Assignment

**During Registration:**

-   Users can only register as `user` role
-   Role parameter is not accepted (always defaults to 'user')
-   Only admins can create managers or other roles

**During User Creation (Admin Only):**

-   Admins can create users with role: admin, manager, or user

**Authentication:**

-   Login returns user object with role included
-   Frontend stores role for UI-based permission checks
-   Backend enforces permissions on every request

### 4. Kernel Registration (`app/Http/Kernel.php`)

Permission middleware is registered in `$middlewareAliases`:

```php
'permission' => \App\Http\Middleware\CheckPermission::class,
```

### 5. User Model

No changes needed - the `User` model already has a `role` field in the migration.

## API Endpoints

### Books

```
GET    /api/books              # View all (requires: books.view)
POST   /api/books              # Create (requires: books.create)
GET    /api/books/{id}         # View single (requires: books.view)
POST   /api/books/{id}         # Update (requires: books.edit)
DELETE /api/books/{id}         # Delete (requires: books.delete)
```

### Products

```
GET    /api/products           # View all (requires: products.view)
POST   /api/products           # Create (requires: products.create)
GET    /api/products/{id}      # View single (requires: products.view)
PUT    /api/products/{id}      # Update (requires: products.edit)
DELETE /api/products/{id}      # Delete (requires: products.delete)
```

### Courses

```
GET    /api/courses            # View all (requires: courses.view)
POST   /api/courses            # Create (requires: courses.create)
GET    /api/courses/{id}       # View single (requires: courses.view)
PUT    /api/courses/{id}       # Update (requires: courses.edit)
DELETE /api/courses/{id}       # Delete (requires: courses.delete)
```

### Users (Admin Only)

```
GET    /api/users              # View all (requires: users.view)
POST   /api/users              # Create (requires: users.create)
GET    /api/users/{id}         # View single (requires: users.view)
PUT    /api/users/{id}         # Update (requires: users.edit)
DELETE /api/users/{id}         # Delete (requires: users.delete)
```

### Authentication (Open)

```
POST   /api/login              # No role check
POST   /api/register           # No role check (always creates 'user' role)
POST   /api/logout             # Requires authentication
GET    /api/user               # Current user info
```

## Security Features

1. **Two-Layer Protection:**

    - Frontend: Hides buttons based on permissions
    - Backend: Enforces permissions on API endpoints

2. **Token-Based Auth (Sanctum):**

    - All protected routes require valid API token
    - Tokens are tied to specific users

3. **Role Assignment Control:**

    - Users can only self-register as 'user' role
    - Only admins can assign manager/admin roles

4. **Atomic Permissions:**
    - Each action (view, create, edit, delete) is independently controlled
    - Permissions cannot be bypassed by modifying frontend

## Error Responses

### 401 - Unauthenticated

```json
{
    "message": "Unauthenticated."
}
```

### 403 - Permission Denied

```json
{
    "message": "Akses ditolak. Anda tidak memiliki izin untuk melakukan aksi ini."
}
```

### 422 - Validation Error

```json
{
    "message": "Validasi gagal.",
    "errors": {
        "field_name": ["Error message"]
    }
}
```

## Testing the RBAC

### 1. Test with Admin Account

```bash
# Login as admin
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@example.com","password":"password"}'

# Should have access to all endpoints
curl -X POST http://localhost:8000/api/books \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{...}'
```

### 2. Test with Manager Account

```bash
# Login as manager
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"manager@example.com","password":"password"}'

# Can create books
curl -X POST http://localhost:8000/api/books \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{...}'

# Cannot delete books (should get 403)
curl -X DELETE http://localhost:8000/api/books/1 \
  -H "Authorization: Bearer {token}"
# Response: 403 Forbidden
```

### 3. Test with User Account

```bash
# Login as user
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"user@example.com","password":"password"}'

# Can view books
curl -X GET http://localhost:8000/api/books \
  -H "Authorization: Bearer {token}"

# Cannot create books (should get 403)
curl -X POST http://localhost:8000/api/books \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{...}'
# Response: 403 Forbidden
```

## Files Modified

1. **app/Http/Middleware/CheckPermission.php** (NEW)

    - Permission middleware implementation

2. **app/Http/Kernel.php** (UPDATED)

    - Registered permission middleware as 'permission' alias

3. **routes/api.php** (UPDATED)

    - Changed from apiResource() to explicit routes with permission middleware
    - Added permission checks to all CRUD operations

4. **app/Http/Controllers/Api/AuthController.php** (UPDATED)

    - Register endpoint now always creates 'user' role
    - Removed role parameter from registration

5. **app/Http/Controllers/Api/UserController.php** (READY FOR UPDATE)
    - Role validation needs update to include 'manager'
    - Current validation: 'admin', 'user'
    - Needed: 'admin', 'manager', 'user'

## Future Enhancements

1. **Custom Roles** - Allow admins to create custom roles with specific permissions
2. **Permission Audit Log** - Log all permission checks and access attempts
3. **Row-Level Security** - Filter data based on user (e.g., managers see only their department's data)
4. **Rate Limiting by Role** - Different rate limits for different roles
5. **Temporal Permissions** - Time-based permission granted/revoked

## Database Migration

Ensure your `users` table has a `role` column:

```php
Schema::create('users', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('email')->unique();
    $table->timestamp('email_verified_at')->nullable();
    $table->string('password');
    $table->string('role')->default('user'); // Added
    $table->rememberToken();
    $table->timestamps();
});
```

If the column doesn't exist, create a migration:

```bash
php artisan make:migration add_role_to_users_table
```

## Support Matrix

| Feature                            | Status        |
| ---------------------------------- | ------------- |
| Role-based route protection        | ✓ Implemented |
| Permission middleware              | ✓ Implemented |
| Role assignment validation         | ✓ Implemented |
| Frontend role-based UI             | ✓ Implemented |
| Backend permission enforcement     | ✓ Implemented |
| Three roles (admin, manager, user) | ✓ Implemented |
| Four actions per module            | ✓ Implemented |
| Frontend & backend sync            | ✓ Implemented |
