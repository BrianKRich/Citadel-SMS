# User Management System

## Overview

Complete CRUD (Create, Read, Update, Delete) system for managing users with role-based permissions.

## Features

### ✅ Implemented

1. **User List** - View all users with pagination
2. **Create Users** - Add new users with roles
3. **Edit Users** - Update user information and roles
4. **Delete Users** - Remove users (with protection against self-deletion)
5. **Role Management** - Assign admin or user roles
6. **Password Management** - Set/update passwords securely

## Access

**URL:** http://localhost:8000/admin/users

**Navigation:**
- Admin Dashboard → "User Management" card
- Direct URL: `/admin/users`

## User Roles

### Available Roles:

**Administrator (`admin`)**
- Full access to all features
- Can manage other users
- Can access admin dashboard
- Can customize theme settings

**User (`user`)**
- Standard user access
- Limited permissions
- Cannot access admin features

## Features Breakdown

### 1. User List (`/admin/users`)

**Displays:**
- User name
- Email address
- Role (with color-coded badge)
- Join date
- Action buttons (Edit/Delete)

**Features:**
- Pagination (10 users per page)
- Responsive table design
- Search and filter (coming soon)
- Export functionality (coming soon)

### 2. Create User (`/admin/users/create`)

**Form Fields:**
- Name (required)
- Email (required, unique)
- Role (required: admin or user)
- Password (required, min 8 characters)
- Password Confirmation (required)

**Validation:**
- Email uniqueness check
- Password strength requirements
- All fields validated before submission

### 3. Edit User (`/admin/users/{id}/edit`)

**Editable Fields:**
- Name
- Email (with uniqueness check)
- Role
- Password (optional - leave blank to keep current)

**Features:**
- Pre-filled form with current data
- Optional password update
- Email uniqueness validation (excluding current user)

### 4. Delete User

**Protection:**
- Cannot delete your own account
- Confirmation dialog before deletion
- Soft delete (can be changed to hard delete)

## Database Schema

### Users Table

```sql
users
├── id (integer, primary key)
├── name (string)
├── email (string, unique)
├── role (string: 'admin' or 'user')
├── email_verified_at (timestamp, nullable)
├── password (string, hashed)
├── remember_token (string)
├── created_at (timestamp)
└── updated_at (timestamp)
```

## Routes

```php
GET     /admin/users              → List all users
GET     /admin/users/create       → Show create form
POST    /admin/users              → Store new user
GET     /admin/users/{id}/edit    → Show edit form
PUT     /admin/users/{id}         → Update user
DELETE  /admin/users/{id}         → Delete user
```

## Components Used

### Custom Components

**`UserForm.vue`** - Reusable form for create/edit
```vue
<UserForm
    :form="form"
    :is-editing="true"
    @submit="handleSubmit"
/>
```

**Props:**
- `form` (Object, required) - Inertia form object
- `isEditing` (Boolean) - Changes button text and password requirement

**`Alert.vue`** - Success/error messages
```vue
<Alert
    type="success"
    message="User created!"
    @dismiss="hideAlert"
/>
```

**Props:**
- `type` (String) - 'success', 'error', 'warning', 'info'
- `message` (String, required) - Message to display
- `dismissible` (Boolean) - Show dismiss button

### Shared Components
- `Card` - Container wrapper
- `PageHeader` - Page titles
- `TextInput` - Form inputs
- `PrimaryButton` - Action buttons
- `DangerButton` - Delete actions

## Security Features

### Password Security
- Bcrypt hashing (automatically applied)
- Minimum 8 characters
- Confirmation required
- Never displayed in plain text

### Validation
- Server-side validation on all inputs
- Email uniqueness checks
- Role validation (must be 'admin' or 'user')
- CSRF protection on all forms

### Protection
- Cannot delete own account
- Confirmation dialogs for destructive actions
- SQL injection protection (Eloquent ORM)
- XSS protection (Vue escaping)

## Usage Examples

### Creating a User via Code

```php
use App\Models\User;
use Illuminate\Support\Facades\Hash;

User::create([
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'password' => Hash::make('password123'),
    'role' => 'user',
]);
```

### Checking User Role

```php
// In controller or middleware
if (auth()->user()->isAdmin()) {
    // Admin-only code
}

// Or
if (auth()->user()->hasRole('admin')) {
    // Admin-only code
}
```

### Promoting User to Admin

```bash
# Via Tinker
php artisan tinker
User::where('email', 'user@example.com')->first()->update(['role' => 'admin']);

# Or via SQL
UPDATE users SET role = 'admin' WHERE email = 'user@example.com';
```

## Future Enhancements

- [ ] Bulk user actions (delete, role change)
- [ ] User search and filtering
- [ ] Export users to CSV/Excel
- [ ] User activity logging
- [ ] Password reset from admin panel
- [ ] Email verification management
- [ ] User status (active/inactive/suspended)
- [ ] More granular permissions system
- [ ] Profile pictures/avatars
- [ ] Last login tracking

## Files Created

```
Backend:
✓ database/migrations/2026_02_08_233902_add_role_to_users_table.php
✓ app/Http/Controllers/UserManagementController.php
✓ app/Models/User.php (updated with role methods)

Frontend:
✓ resources/js/Pages/Admin/Users/Index.vue
✓ resources/js/Pages/Admin/Users/Create.vue
✓ resources/js/Pages/Admin/Users/Edit.vue
✓ resources/js/Components/Users/UserForm.vue
✓ resources/js/Components/UI/Alert.vue

Routes:
✓ routes/web.php (user management routes added)
```

## Component Architecture

```
Admin Users Pages
├── Index (List)
│   ├── Card
│   ├── PageHeader
│   ├── Alert (for flash messages)
│   └── Table (with pagination)
├── Create
│   ├── Card
│   ├── PageHeader
│   └── UserForm
└── Edit
    ├── Card
    ├── PageHeader
    └── UserForm

UserForm Component
├── TextInput (name, email)
├── Select (role)
├── TextInput (password fields)
├── InputLabel
├── InputError
└── PrimaryButton
```

## Tips

1. **Always have at least one admin** - Don't delete or demote all admin users
2. **Use strong passwords** - Enforce password complexity in production
3. **Regular audits** - Review user list periodically
4. **Role assignment** - Be careful when assigning admin role
5. **Backup before bulk changes** - Always backup database before major changes

## Troubleshooting

### Cannot access user management
- Check if you're logged in
- Verify your role is 'admin' in database
- Clear browser cache and reload

### Password validation fails
- Ensure password is at least 8 characters
- Password confirmation must match exactly
- Check for leading/trailing spaces

### Email already exists error
- Email must be unique across all users
- When editing, system allows keeping same email
- Case-sensitive check (john@ex.com ≠ John@ex.com)

### Cannot delete user
- Cannot delete your own account (safety feature)
- User might be referenced in other tables (add cascade delete if needed)

## API Endpoints (Future)

For future API integration:

```
GET    /api/users              → List users (paginated)
POST   /api/users              → Create user
GET    /api/users/{id}         → Get user details
PUT    /api/users/{id}         → Update user
DELETE /api/users/{id}         → Delete user
PATCH  /api/users/{id}/role    → Update user role
```
