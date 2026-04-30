# How to Add a New Module with Auto-Generated Route Middleware

This guide shows how to add a new module (e.g., `emails`) to the CRUD API with minimal code changes, leveraging the auto-generation helpers.

## Step 1: Define Module in Config

Edit `config/permissions.php` and add your module to the `modules` array:

```php
'modules' => [
    'books',
    'products',
    'courses',
    'reports',
    'users',
    'emails',  // ← ADD HERE
],
```

## Step 2: Define Default Permissions for All Roles

In the same file, add default permissions for your module in each role's defaults:

```php
'defaults' => [
    'admin' => [
        // ... existing permissions ...
        'emails.view' => true,
        'emails.create' => true,
        'emails.edit' => true,
        'emails.delete' => true,
    ],
    'manager' => [
        // ... existing permissions ...
        'emails.view' => true,
        'emails.create' => true,
        'emails.edit' => true,
        'emails.delete' => false,  // managers can't delete emails
    ],
    'user' => [
        // ... existing permissions ...
        'emails.view' => true,
        'emails.create' => false,
        'emails.edit' => false,
        'emails.delete' => false,
    ],
],
```

## Step 3: Update Frontend Permission Catalog

Edit `frontend/src/services/permissionCatalog.js` and:

1. Add module to `MODULES` array:

```javascript
const MODULES = ["books", "products", "courses", "reports", "users", "emails"];
```

2. Add default permissions for your module in `DEFAULT_ROLE_PERMISSIONS`:

```javascript
DEFAULT_ROLE_PERMISSIONS: {
    admin: {
        // ... existing ...
        'emails.view': true,
        'emails.create': true,
        'emails.edit': true,
        'emails.delete': true,
    },
    manager: {
        // ... existing ...
        'emails.view': true,
        'emails.create': true,
        'emails.edit': true,
        'emails.delete': false,
    },
    user: {
        // ... existing ...
        'emails.view': true,
        'emails.create': false,
        'emails.edit': false,
        'emails.delete': false,
    },
},
```

## Step 4: View Auto-Generated Routes

To see what routes and middleware you need, run:

```bash
php artisan list:route-permissions --module=emails
```

This outputs:

```
=== Route Permission Middleware Reference ===

emails
  Action HTTP      Middleware
view     GET       permission:emails.view
create   POST      permission:emails.create
edit     PUT/PATCH permission:emails.edit
delete   DELETE    permission:emails.delete
```

Or see snippets for all routes:

```bash
php artisan list:route-permissions --format=snippets --module=emails
```

## Step 5: Create Controller

Create `app/Http/Controllers/Api/EmailController.php`:

```php
<?php

namespace App\Http\Controllers\Api;

use App\Models\Email;
use Illuminate\Http\Request;

class EmailController extends Controller
{
    public function index()
    {
        return response()->json([
            'data' => Email::all(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'to' => 'required|email',
            'subject' => 'required|string',
            'body' => 'required|string',
        ]);

        $email = Email::create($validated);

        return response()->json(['data' => $email], 201);
    }

    public function update(Request $request, Email $email)
    {
        $validated = $request->validate([
            'to' => 'email',
            'subject' => 'string',
            'body' => 'string',
        ]);

        $email->update($validated);

        return response()->json(['data' => $email]);
    }

    public function destroy(Email $email)
    {
        $email->delete();

        return response()->json(['message' => 'Email deleted']);
    }
}
```

## Step 6: Add Routes

Edit `routes/api.php` and add routes with permission middleware:

```php
// Emails routes
Route::middleware('permission:emails.view')->group(function () {
    Route::get('/emails', [EmailController::class, 'index']);
    Route::get('/emails/{id}', [EmailController::class, 'show']);
});

Route::post('/emails', [EmailController::class, 'store'])
    ->middleware('permission:emails.create');

Route::put('/emails/{id}', [EmailController::class, 'update'])
    ->middleware('permission:emails.edit');

Route::delete('/emails/{id}', [EmailController::class, 'destroy'])
    ->middleware('permission:emails.delete');
```

## Step 7: Create Migration (if needed)

If you need a new table:

```bash
php artisan make:migration create_emails_table
```

Then add your fields in the migration file and run:

```bash
php artisan migrate
```

## Step 8: Create Frontend Service (if UI needed)

Create `frontend/src/modules/emails/services/emailsApi.js`:

```javascript
import { api } from "@/services/api";

export const emailsApi = {
    getEmails() {
        return api.get("/emails");
    },

    getEmail(id) {
        return api.get(`/emails/${id}`);
    },

    createEmail(data) {
        return api.post("/emails", data);
    },

    updateEmail(id, data) {
        return api.put(`/emails/${id}`, data);
    },

    deleteEmail(id) {
        return api.delete(`/emails/${id}`);
    },
};
```

## Step 9: Create Frontend Page (if UI needed)

Create `frontend/src/modules/emails/pages/EmailsIndexPage.vue`:

```vue
<template>
    <div class="emails-page">
        <h1>Emails</h1>
        <!-- Your UI here -->
    </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import { emailsApi } from "../services/emailsApi";
import { showToast } from "@/services/toast";

const emails = ref([]);

const loadEmails = async () => {
    try {
        const response = await emailsApi.getEmails();
        emails.value = response.data.data;
    } catch (error) {
        showToast("Failed to load emails", "error");
    }
};

onMounted(() => {
    loadEmails();
});
</script>
```

## Step 10: Add Route and Menu (if UI needed)

Edit `frontend/src/router/index.js` and add:

```javascript
import EmailsIndexPage from '@/modules/emails/pages/EmailsIndexPage.vue';

// In routes:
{
    path: '/emails',
    component: EmailsIndexPage,
    meta: { requiredModule: 'emails', requiresAuth: true },
},
```

Edit `frontend/src/layouts/AdminLayout.vue` and add to `allMenus`:

```javascript
{
    name: 'Emails',
    path: '/emails',
    icon: 'fas fa-envelope',
    module: 'emails',
},
```

## Helper Commands

### View All Routes with Permissions

```bash
php artisan list:route-permissions
```

### View Routes for Specific Module

```bash
php artisan list:route-permissions --module=emails
```

### Get Copy-Paste Snippets

```bash
php artisan list:route-permissions --format=snippets
```

### Get JSON for Programmatic Use

```bash
php artisan list:route-permissions --format=json
```

## PermissionCatalog Helper Methods

Use these in your code:

```php
use App\Support\PermissionCatalog;

// Get all modules
$modules = PermissionCatalog::modules();  // ['books', 'products', ...]

// Get all actions
$actions = PermissionCatalog::actions();  // ['view', 'create', 'edit', 'delete']

// Get all permissions
$perms = PermissionCatalog::permissions();  // ['books.view', 'books.create', ...]

// Get middleware for an action
$mid = PermissionCatalog::middlewareFor('emails', 'create');
// Returns: 'permission:emails.create'

// Get controller name
$controller = PermissionCatalog::controllerNameFor('emails');
// Returns: 'EmailsController'

// Get pluralized route path
$path = PermissionCatalog::pluralizeModule('email');
// Returns: '/emails'

// Get all route permissions with HTTP methods
$routes = PermissionCatalog::routePermissions();
// Returns: ['emails' => ['view' => [...], 'create' => [...], ...]]
```

## Summary: Files to Edit When Adding a Module

1. ✏️ `backend/config/permissions.php` - Add module + defaults
2. ✏️ `frontend/src/services/permissionCatalog.js` - Add module + defaults
3. 🆕 `backend/app/Http/Controllers/Api/EmailController.php` - Create controller
4. ✏️ `backend/routes/api.php` - Add routes with middleware
5. 🆕 `backend/database/migrations/YYYY_MM_DD_HHMMSS_create_emails_table.php` - If new table needed
6. 🆕 `frontend/src/modules/emails/services/emailsApi.js` - Create API service (if UI)
7. 🆕 `frontend/src/modules/emails/pages/EmailsIndexPage.vue` - Create page (if UI)
8. ✏️ `frontend/src/router/index.js` - Add route (if UI)
9. ✏️ `frontend/src/layouts/AdminLayout.vue` - Add menu item (if UI)

**That's it!** The permission middleware, role-based defaults, and permission overrides are all handled automatically.
