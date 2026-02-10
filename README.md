# Citadel SMS

A modern Student Management System built with Laravel, Vue.js, and PostgreSQL.

## Overview

Citadel SMS is a full-stack web application designed for managing student information, academic records, and educational administration with a focus on user management, theme customization, and extensibility. Built with modern technologies and a component-based architecture.

## Features

### ✅ Current Features

- **User Management**
  - Create, read, update, and delete users
  - Role-based permissions (Admin/User)
  - Secure password management with bcrypt encryption
  - Pagination and sorting

- **Admin Dashboard**
  - Real-time user statistics
  - Quick access to all admin features
  - Clean, intuitive interface

- **Theme Customization**
  - Live color customization
  - Real-time preview
  - Persistent theme settings
  - 5 customizable color schemes
  - Dark mode support with system preference detection

- **Authentication System**
  - User registration and login
  - Password reset functionality
  - Email verification ready
  - Session management

- **Component-Based Architecture**
  - Reusable Vue components
  - Organized component library
  - Consistent UI/UX patterns

### 🚧 Planned Features

- Student records management
- Course and class management
- Teacher/Instructor management
- Grade and assessment tracking
- Attendance management
- Enrollment and registration
- Academic reporting and analytics
- Parent/Guardian portal
- Academic calendar
- Document management

## Tech Stack

### Backend
- **Framework:** Laravel 12
- **Database:** PostgreSQL
- **Authentication:** Laravel Breeze
- **API:** Inertia.js

### Frontend
- **Framework:** Vue 3 (Composition API)
- **Styling:** Tailwind CSS
- **Build Tool:** Vite
- **State Management:** Inertia.js

### Development Tools
- PHP 8.3
- Node.js 24.13.0
- Composer 2.7.1
- npm 11.8.0

## Requirements

- PHP >= 8.3
- Composer >= 2.7
- Node.js >= 18.x
- PostgreSQL >= 13
- npm >= 8.x

## Installation

### 1. Clone the Repository

```bash
git clone <repository-url>
cd citadel-sms
```

### 2. Install PHP Dependencies

```bash
composer install
```

### 3. Install Node Dependencies

```bash
npm install --legacy-peer-deps
```

### 4. Environment Setup

```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

### 5. Configure Database

Edit `.env` file with your PostgreSQL credentials:

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=citadel_sms
DB_USERNAME=postgres
DB_PASSWORD=your_password
```

### 6. Create Database

```bash
# Using PostgreSQL command line
createdb citadel_sms

# Or via psql
psql -U postgres
CREATE DATABASE citadel_sms;
```

### 7. Run Migrations

```bash
php artisan migrate
```

### 8. Seed Database (Optional)

```bash
# Seed theme settings
php artisan db:seed --class=ThemeSeeder

# Make first user an admin
php artisan tinker
User::first()->update(['role' => 'admin']);
```

## Running the Application

### Development Mode

Start both servers in separate terminals:

**Terminal 1 - Laravel Backend:**
```bash
php artisan serve
```

**Terminal 2 - Vite Frontend:**
```bash
npm run dev
```

Access the application at: **http://localhost:8000**

### Production Build

```bash
# Build frontend assets
npm run build

# Configure web server to point to /public directory
```

## Project Structure

```
citadel-sms/
├── app/
│   ├── Http/Controllers/     # Backend controllers
│   ├── Models/                # Database models
│   └── Providers/             # Service providers
├── database/
│   ├── migrations/            # Database migrations
│   └── seeders/               # Database seeders
├── resources/
│   ├── js/
│   │   ├── Components/        # Vue components
│   │   │   ├── Admin/         # Admin-specific components
│   │   │   ├── Form/          # Form components
│   │   │   ├── UI/            # UI components
│   │   │   ├── Theme/         # Theme components
│   │   │   └── Users/         # User components
│   │   ├── Layouts/           # Page layouts
│   │   ├── Pages/             # Vue pages
│   │   │   ├── Admin/         # Admin pages
│   │   │   └── Auth/          # Authentication pages
│   │   └── composables/       # Vue composables
│   └── css/                   # Stylesheets
├── routes/
│   ├── web.php                # Web routes
│   └── auth.php               # Authentication routes
├── public/                    # Public assets
└── storage/                   # File storage
```

## Key URLs

- **Homepage:** http://localhost:8000
- **Admin Dashboard:** http://localhost:8000/admin
- **User Management:** http://localhost:8000/admin/users
- **Theme Settings:** http://localhost:8000/admin/theme
- **Login:** http://localhost:8000/login
- **Register:** http://localhost:8000/register

## Default Credentials

After registration, promote your user to admin:

```bash
php artisan tinker
User::where('email', 'your@email.com')->first()->update(['role' => 'admin']);
```

## Available Commands

### Development

```bash
# Start Laravel server
php artisan serve

# Start Vite dev server
npm run dev

# Run migrations
php artisan migrate

# Rollback migrations
php artisan migrate:rollback

# Seed database
php artisan db:seed

# Clear cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### Production

```bash
# Build frontend assets
npm run build

# Optimize autoloader
composer install --optimize-autoloader --no-dev

# Cache configuration
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## Component Library

### Admin Components
- `StatCard` - Statistics display cards
- `AdminActionCard` - Feature navigation cards

### Form Components
- `ColorPicker` - Color input with hex preview
- `UserForm` - Reusable user create/edit form

### UI Components
- `Card` - Content container
- `PageHeader` - Page title sections
- `Alert` - Success/error messages

### Theme Components
- `ThemePreview` - Live theme preview

See `COMPONENTS.md` for detailed component documentation.

## Documentation

- [Component Guide](COMPONENTS.md) - Vue component architecture
- [Admin Dashboard](ADMIN_DASHBOARD.md) - Admin dashboard features
- [User Management](USER_MANAGEMENT.md) - User management system
- [Security](SECURITY.md) - Security features and best practices
- [Dark Mode](DARK_MODE.md) - Dark mode implementation and usage

## User Roles

### Administrator
- Full access to all features
- User management capabilities
- Theme customization
- System configuration

### User
- Standard user access
- Limited permissions
- Profile management

## Security Features

- ✅ Bcrypt password hashing
- ✅ CSRF protection
- ✅ SQL injection protection (Eloquent ORM)
- ✅ XSS protection (Vue escaping)
- ✅ Rate limiting on authentication
- ✅ Secure session management
- ✅ Password reset functionality

## Testing

```bash
# Run all tests
php artisan test

# Run specific test
php artisan test --filter UserTest
```

## Troubleshooting

### Database Connection Issues

```bash
# Check PostgreSQL is running
sudo service postgresql status

# Verify database exists
psql -U postgres -l

# Test connection
php artisan tinker
DB::connection()->getPdo();
```

### Permission Errors

```bash
# Fix storage permissions
sudo chmod -R 775 storage bootstrap/cache
sudo chown -R $USER:www-data storage bootstrap/cache
```

### Asset Build Failures

```bash
# Clear npm cache
npm cache clean --force

# Reinstall dependencies
rm -rf node_modules package-lock.json
npm install --legacy-peer-deps
```

## Development Workflow

1. **Feature Development**
   ```bash
   # Create feature branch
   git checkout -b feature/feature-name

   # Make changes and commit
   git add .
   git commit -m "Add feature description"
   ```

2. **Database Changes**
   ```bash
   # Create migration
   php artisan make:migration create_table_name

   # Run migration
   php artisan migrate
   ```

3. **Adding Components**
   - Place in appropriate `resources/js/Components/` subdirectory
   - Follow naming conventions (PascalCase)
   - Document props and usage

## Contributing

1. Follow the existing code structure
2. Use component-based architecture for Vue features
3. Write descriptive commit messages
4. Test changes before committing
5. Update documentation as needed

## Performance Tips

### Production Optimization

```bash
# Optimize Composer autoloader
composer install --optimize-autoloader --no-dev

# Cache routes and config
php artisan config:cache
php artisan route:cache

# Build optimized assets
npm run build
```

## License

© 2026 Brian K. Rich. All rights reserved.

## Support

For issues or questions, please contact the development team.

---

**Version:** 1.0.0
**Last Updated:** February 2026
**Status:** Active Development
