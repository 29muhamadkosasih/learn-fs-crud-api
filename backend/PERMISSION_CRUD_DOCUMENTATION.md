# Permission CRUD System

## Overview

Sistem CRUD untuk mengelola permissions dan assign permissions ke roles. Semua users dengan role `admin` dapat mengelola permissions dan role-permission mappings.

## Backend Implementation

### Database Tables

#### 1. **permissions** table

Menyimpan daftar lengkap permissions yang tersedia di sistem.

```sql
id | name (unique) | module | action | description | created_at | updated_at
```

**Contoh data:**

```
1 | books.view    | books   | view   | View all books
2 | books.create  | books   | create | Create new book
3 | books.edit    | books   | edit   | Edit existing book
4 | books.delete  | books   | delete | Delete book
```

#### 2. **role_permissions** table

Mapping antara roles dan permissions dengan status `allowed` (true/false).

```sql
id | role    | permission  | allowed | created_at | updated_at
```

**Contoh data:**

```
1 | admin   | books.view     | true  | ...
2 | admin   | books.create   | true  | ...
3 | manager | books.view     | true  | ...
4 | manager | books.create   | true  | ...
5 | manager | books.delete   | false | ...
```

### Models

#### 1. **Permission Model** (`app/Models/Permission.php`)

```php
class Permission extends Model {
    protected $fillable = ['name', 'module', 'action', 'description'];

    public function rolePermissions() {
        return $this->hasMany(RolePermission::class, 'permission', 'name');
    }
}
```

#### 2. **RolePermission Model** (`app/Models/RolePermission.php`)

```php
class RolePermission extends Model {
    protected $fillable = ['role', 'permission', 'allowed'];

    public function permissionData() {
        return $this->belongsTo(Permission::class, 'permission', 'name');
    }
}
```

### API Endpoints

All endpoints require authentication (`auth:sanctum`) and admin role (`role:admin`).

#### GET /api/permissions

Retrieve all permissions dengan optional filters.

**Query Parameters:**

-   `module` - Filter by module (optional)
-   `action` - Filter by action (optional)

**Response:**

```json
{
    "success": true,
    "message": "List semua permission",
    "data": [
        {
            "id": 1,
            "name": "books.view",
            "module": "books",
            "action": "view",
            "description": "View access to books",
            "created_at": "2026-04-30T...",
            "updated_at": "2026-04-30T..."
        }
    ],
    "meta": {
        "modules": ["books", "products", "courses", "reports", "users"],
        "actions": ["view", "create", "edit", "delete"],
        "total": 20
    }
}
```

#### POST /api/permissions

Create a new permission.

**Request Body:**

```json
{
    "module": "books",
    "action": "view",
    "description": "View access to books"
}
```

**Response:** (201 Created)

```json
{
    "success": true,
    "message": "Permission berhasil dibuat.",
    "data": {
        "id": 1,
        "name": "books.view",
        "module": "books",
        "action": "view",
        "description": "View access to books",
        "created_at": "2026-04-30T...",
        "updated_at": "2026-04-30T..."
    }
}
```

#### GET /api/permissions/{id}

Get a specific permission by ID.

**Response:**

```json
{
    "success": true,
    "message": "Detail permission",
    "data": { ... }
}
```

#### PUT /api/permissions/{id}

Update permission description.

**Request Body:**

```json
{
    "description": "Updated description"
}
```

**Response:**

```json
{
    "success": true,
    "message": "Permission berhasil diubah.",
    "data": { ... }
}
```

**Note:** Module dan action tidak dapat diubah setelah creation.

#### DELETE /api/permissions/{id}

Delete a permission (only if not assigned to any role).

**Response:**

```json
{
    "success": true,
    "message": "Permission berhasil dihapus."
}
```

### Controllers

#### PermissionController (`app/Http/Controllers/Api/PermissionController.php`)

Mengelola CRUD operations untuk permissions.

-   `index()` - List all permissions
-   `store()` - Create new permission
-   `show()` - Get single permission
-   `update()` - Update permission description
-   `destroy()` - Delete permission

## Frontend Implementation

### Module Structure

```
src/modules/permissions/
├── services/
│   └── permissionsApi.js
├── pages/
│   └── PermissionsIndexPage.vue
├── components/
│   └── PermissionForm.vue
└── utils/
    └── permissionsHelpers.js
```

### Pages

#### PermissionsIndexPage.vue

Halaman utama untuk mengelola permissions.

**Features:**

-   List semua permissions dengan pagination
-   Create permission via modal form
-   Edit permission description
-   Delete permission dengan confirmation
-   Filter by module atau action

### Components

#### PermissionForm.vue

Reusable form component untuk create/edit permissions.

**Props:**

-   `permission` - Permission object (null untuk create mode)
-   `modules` - Available modules
-   `actions` - Available actions

**Emits:**

-   `close` - Close form event
-   `create` - New permission created
-   `update` - Permission updated

### Helper Functions

#### permissionsHelpers.js

```javascript
getActionLabel(action); // Get label untuk action
getActionBadgeColor(action); // Get badge color
formatPermissionName(name); // Format "module.action" ke "Module - Action"
```

### Routes

```javascript
{
  path: 'permissions',
  name: 'permissions.index',
  component: PermissionsIndexPage,
  meta: { requiresAdmin: true }
}
```

Access: `/permissions` (admin only)

## Workflow: Create Permission & Assign to Role

### 1. Create Permission

1. Go to **Permissions** menu (admin-only)
2. Click **"Create Permission"** button
3. Fill the form:
    - **Module:** Select from available modules
    - **Action:** Select from available actions
    - **Description:** Optional description
4. Click **"Create"** button

**Result:** New permission entry created in `permissions` table

### 2. Assign to Role

1. Go to **Role Permissions** menu (admin-only)
2. Find the role you want to assign permission to
3. Search for the permission you just created
4. Toggle the **"Allowed"** checkbox to enable/disable permission
5. Changes are saved automatically

**Result:** New entry in `role_permissions` table with the mapping

## Seeding

### PermissionSeeder

Automatically creates all permission combinations from `config/permissions.php` and assigns them to roles based on defaults.

Run seeder:

```bash
php artisan db:seed --class=PermissionSeeder
```

Or with fresh database:

```bash
php artisan migrate:fresh --seed
```

## Configuration

### config/permissions.php

```php
return [
    'roles' => ['admin', 'manager', 'user'],
    'modules' => ['books', 'products', 'courses', 'reports', 'users'],
    'actions' => ['view', 'create', 'edit', 'delete'],
    'defaults' => [
        'admin' => [
            'books.view' => true,
            'books.create' => true,
            // ...
        ],
        'manager' => [
            'books.view' => true,
            'books.create' => true,
            'books.delete' => false,
            // ...
        ],
        'user' => [
            'books.view' => true,
            'books.create' => false,
            // ...
        ],
    ],
];
```

## Security Notes

1. **Permission Enforcement:** All permissions are enforced on the backend via middleware
2. **Frontend Hiding:** Frontend hides buttons based on user's role/permissions
3. **No Bypass:** Only admins can manage permissions
4. **Atomic Permissions:** Each action is independently controlled
5. **Role-Based Access:** Permissions are always tied to roles, not individual users

## Examples

### Example 1: Add new module with permissions

1. Create permissions for new module:

    - `inventory.view`
    - `inventory.create`
    - `inventory.edit`
    - `inventory.delete`

2. Assign to roles:
    - Admin: all true
    - Manager: view/create/edit true, delete false
    - User: only view true

### Example 2: Disable action for specific role

1. Go to Role Permissions
2. Find the role
3. Find the permission (e.g., `books.delete`)
4. Toggle off the "Allowed" checkbox
5. Done!

## Troubleshooting

**Q: Cannot delete permission?**
A: Permission might be assigned to a role. Remove the role-permission mapping first.

**Q: Permission changes not reflected?**
A: Clear Laravel cache: `php artisan cache:clear`

**Q: Cannot access Permission Management page?**
A: Only admins can access this page. Check user role.
