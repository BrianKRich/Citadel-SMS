# Student Management System

A modern Student Management System built with Laravel, Vue.js, and PostgreSQL.

## Overview

Student Management System is a full-stack web application designed for managing student information, academic records, and educational administration with a focus on user management, theme customization, and extensibility. Built with modern technologies and a component-based architecture.

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
  - Responsive design (desktop and mobile)

- **Student Management** ✨ New
  - Complete student records with demographics
  - Student ID auto-generation (STU-YYYY-###)
  - Status tracking (active, inactive, graduated, withdrawn, suspended)
  - Guardian relationships and contact information
  - Photo upload support
  - Search and filtering capabilities

- **Teacher Management** ✨ New
  - Teacher profiles with qualifications
  - Teacher ID auto-generation (TCH-YYYY-###)
  - Department and specialization tracking
  - Status management (active, inactive, on_leave)
  - Assignment tracking

- **Course Catalog** ✨ New
  - Course offerings with unique codes
  - Department and level organization
  - Credit tracking
  - Active/inactive status

- **Class Scheduling** ✨ New
  - Class sections with schedules
  - Teacher assignment
  - Room allocation
  - Capacity management (max students)
  - Schedule conflict detection
  - Status tracking (open, closed, in_progress, completed)

- **Enrollment Management** ✨ New
  - Student class enrollment
  - Enrollment status tracking
  - Capacity enforcement
  - Schedule conflict prevention
  - Drop/withdraw functionality
  - Student schedule viewing

- **Academic Year & Term Management** ✨ New
  - Academic year definition
  - Term/semester organization
  - Current year/term designation
  - Date range management

### 🚧 Planned Features (Phase 3+)

- Grade and assessment tracking (Phase 3)
  - Assessment types and weights
  - Grade entry and calculation
  - Report cards and transcripts
- Attendance management (Phase 4)
  - Daily attendance tracking
  - Absence alerts
  - Attendance reports
- Parent/Guardian portal (Phase 5)
  - View student grades and attendance
  - Communication features
- Academic calendar (Phase 6)
  - Events and holidays
  - Exam schedules
- Document management (Phase 7)
  - File uploads and storage
  - Student documents
- Academic reporting and analytics (Phase 8)
  - Performance dashboards
  - Data exports
  - Custom reports

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
cd sms
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
DB_DATABASE=sms
DB_USERNAME=postgres
DB_PASSWORD=your_password
```

### 6. Create Database

```bash
# Using PostgreSQL command line
createdb sms

# Or via psql
psql -U postgres
CREATE DATABASE sms;
```

### 7. Run Migrations

```bash
php artisan migrate
```

### 8. Seed Database (Optional)

```bash
# Seed all data (recommended for development)
php artisan db:seed

# Or seed specific tables:
php artisan db:seed --class=ThemeSeeder
php artisan db:seed --class=AcademicYearSeeder
php artisan db:seed --class=CourseSeeder
php artisan db:seed --class=TeacherSeeder
php artisan db:seed --class=StudentSeeder
php artisan db:seed --class=ClassSeeder
php artisan db:seed --class=EnrollmentSeeder

# Make first user an admin
php artisan tinker
User::first()->update(['role' => 'admin']);
```

**Sample Data Included:**
- Academic year 2025-2026 with Fall/Spring terms
- 10 courses across different departments
- 5 teachers with assignments
- 20 students with guardians
- 12 class sections with schedules
- Sample enrollments

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
sms/
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
- **Login:** http://localhost:8000/login
- **Register:** http://localhost:8000/register

### Admin Features

- **User Management:** http://localhost:8000/admin/users
- **Theme Settings:** http://localhost:8000/admin/theme
- **Student Management:** http://localhost:8000/admin/students
- **Teacher Management:** http://localhost:8000/admin/teachers
- **Course Catalog:** http://localhost:8000/admin/courses
- **Class Management:** http://localhost:8000/admin/classes
- **Enrollment:** http://localhost:8000/admin/enrollment
- **Academic Years:** http://localhost:8000/admin/academic-years

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

Comprehensive documentation is available in the `docs/` directory:

### Architecture & Design
- [Architecture Overview](docs/ARCHITECTURE.md) - System architecture and design patterns
- [Database Schema](docs/DATABASE.md) - Complete database documentation
- [Backend Documentation](docs/BACKEND.md) - Controllers, models, and business logic
- [Frontend Documentation](docs/FRONTEND.md) - Vue components and pages
- [API Documentation](docs/API.md) - API endpoints and usage

### Features & Guides
- [Component Guide](docs/COMPONENTS.md) - Vue component library
- [User Management](docs/USER_MANAGEMENT.md) - User and role management
- [Dark Mode](docs/DARK_MODE.md) - Dark mode implementation
- [Security](docs/SECURITY.md) - Security features and best practices

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

**Version:** 2.0.0 (Phase 0-2 Complete)
**Last Updated:** February 9, 2026
**Status:** Active Development

**Completed Phases:**
- ✅ Phase 0: Theme System Integration
- ✅ Phase 1: Student & Course Foundation (Backend + Frontend)
- ✅ Phase 2: Class Scheduling & Enrollment (Backend + Frontend)

**Next Phase:** Phase 3 - Grading & Assessments
