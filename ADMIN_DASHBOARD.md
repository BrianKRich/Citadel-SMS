# Admin Dashboard

## Overview

The Admin Dashboard is a centralized control panel for managing the Citadel SMS application.

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

### 📊 Statistics Dashboard
Real-time user statistics displayed in cards:
- **Total Users**: All registered users
- **New Today**: Users registered today
- **This Week**: Users registered in the last 7 days
- **This Month**: Users registered in the last 30 days

### 🎯 Quick Actions
Direct access to admin features:

1. **🎨 Theme Settings** (`/admin/theme`)
   - Customize application colors
   - Live preview
   - Save theme globally

2. **📚 Student Management** (Coming soon)
   - Student records
   - Enrollment management
   - Student profiles

3. **👤 User Management** (Working)
   - View all users
   - Edit user details
   - Manage permissions

4. **📖 Course Management** (Coming soon)
   - Manage courses
   - Class schedules
   - Academic calendar

5. **📊 Analytics** (Coming soon)
   - Reports and insights
   - Usage statistics
   - Export data

6. **⚙️ Settings** (Coming soon)
   - Application configuration
   - API settings
   - Notifications

### 📈 Recent Activity
(Coming soon) - Activity feed showing:
- User registrations
- Student enrollments
- System events

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
│   └── 4x StatCard components
├── Quick Actions Section
│   └── 6x AdminActionCard components
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

- [ ] User management interface
- [ ] SMS sending interface
- [ ] Contact management
- [ ] Analytics dashboard
- [ ] Activity logging
- [ ] Export functionality
- [ ] Real-time notifications
- [ ] Dark mode support
- [ ] Mobile-responsive improvements
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
```
