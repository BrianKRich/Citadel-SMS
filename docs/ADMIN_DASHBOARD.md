# Admin Dashboard

## Overview

The Admin Dashboard is a centralized control panel for managing the Student Management System application.

## Access

**URL:** http://localhost:8000/admin

**Navigation:**
- Landing page: Click "Admin" link (top right, when logged in)
- Authenticated pages: Click "Admin" in main navigation

**Routes:**
```php
GET /admin  → Admin Dashboard
```

## Features

### Statistics Dashboard
Real-time statistics displayed in cards:
- **Total Users**: All registered users
- **Total Students**: All student records
- **Total Courses**: Course catalog count
- **Total Employees**: Staff count
- **Total Assessments**: All assessments across classes (Phase 3A)
- **Grades This Week**: Grades entered in the last 7 days (Phase 3A)
- **Average GPA**: Average GPA across all graded enrollments (Phase 3A)

### Quick Actions
Direct access to admin features:

1. **Theme Settings** (`/admin/theme`) - Customize application colors
2. **Student Management** (`/admin/students`) - Student records, enrollment, profiles
3. **User Management** (`/admin/users`) - View and manage users
4. **Course Management** (`/admin/courses`) - Course catalog
5. **Employee Management** (`/admin/employees`) - Staff directory
6. **Class Management** (`/admin/classes`) - Class sections and schedules
7. **Grade Management** (`/admin/grades`) - Grade book, assessments, grading scales (Phase 3A)

## Components Used

### Admin-Specific Components

**`StatCard.vue`** - Statistics display
```vue
<StatCard
    title="Total Users"
    :value="100"
    icon="👥"
    trend="up"
    trend-value="+12%"
/>
```

**Props:**
- `title` (String, required) - Card title
- `value` (String|Number, required) - Main value to display
- `icon` (String) - Emoji or HTML icon
- `trend` (String) - 'up', 'down', or ''
- `trendValue` (String) - Trend text (e.g., "+12%")

**`AdminActionCard.vue`** - Action/navigation cards
```vue
<AdminActionCard
    title="Theme Settings"
    description="Customize colors"
    icon="🎨"
    :href="route('admin.theme')"
    color="purple"
/>
```

**Props:**
- `title` (String, required) - Card title
- `description` (String, required) - Card description
- `icon` (String) - Emoji icon (default: ⚙️)
- `href` (String, required) - Link destination
- `color` (String) - Color theme: 'indigo', 'blue', 'green', 'purple', 'pink'

### Shared Components
- `PageHeader` - Page title and description
- `AuthenticatedLayout` - Page layout wrapper

## Architecture

```
Admin Dashboard
├── Statistics Section
│   └── 7x StatCard components (4 core + 3 grading stats)
├── Quick Actions Section
│   └── 7x AdminActionCard components (including Grade Management)
└── Recent Activity Section
    └── Placeholder (to be implemented)
```

## Adding New Admin Features

### 1. Create the Feature Page
```bash
# Example: Create users management
resources/js/Pages/Admin/Users.vue
```

### 2. Create Controller & Route
```php
// app/Http/Controllers/UserManagementController.php
public function index()
{
    return Inertia::render('Admin/Users', [
        'users' => User::all(),
    ]);
}

// routes/web.php
Route::get('/admin/users', [UserManagementController::class, 'index'])
    ->name('admin.users');
```

### 3. Add to Dashboard
Update `resources/js/Pages/Admin/Dashboard.vue`:
```vue
<AdminActionCard
    title="User Management"
    description="View and manage users"
    icon="👤"
    :href="route('admin.users')"
    color="green"
/>
```

## Extending Statistics

Add new stats in `AdminController::dashboard()`:

```php
public function dashboard()
{
    $stats = [
        'total_users' => User::count(),
        'total_messages' => Message::count(), // New stat
        // ... more stats
    ];

    return Inertia::render('Admin/Dashboard', [
        'stats' => $stats,
    ]);
}
```

Then add a new StatCard in the dashboard page.

## Security Considerations

### Current Setup
- All admin routes require authentication (`auth` middleware)
- All users can access admin features

### Recommended Enhancements

**1. Add Admin Role Check:**
```php
// In routes/web.php
Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/admin', [AdminController::class, 'dashboard']);
    // ... other admin routes
});

// Create middleware:
php artisan make:middleware AdminMiddleware
```

**2. Add Permission System:**
```bash
composer require spatie/laravel-permission

# Then add roles/permissions
```

**3. Audit Logging:**
Track admin actions for security:
```bash
composer require spatie/laravel-activitylog
```

## Future Enhancements

- [x] User management interface
- [x] Student management
- [x] Employee management
- [x] Course management
- [x] Class management
- [x] Grade management (Phase 3A)
- [ ] Report cards & transcripts (Phase 3B)
- [ ] Attendance tracking (Phase 4)
- [ ] Analytics dashboard (Phase 8)
- [ ] Activity logging
- [ ] Real-time notifications
- [ ] Role-based access control

## Files Created

```
app/Http/Controllers/AdminController.php
resources/js/Pages/Admin/Dashboard.vue
resources/js/Components/Admin/StatCard.vue
resources/js/Components/Admin/AdminActionCard.vue
```

## Routes

```php
GET  /admin              → Admin Dashboard
GET  /admin/theme        → Theme Settings
POST /admin/theme        → Update Theme
GET  /admin/grades       → Grade Overview (Phase 3A)
```
