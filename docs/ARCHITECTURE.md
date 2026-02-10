# Citadel SMS - System Architecture Overview

**Version:** 1.0
**Last Updated:** February 2026
**Organization:** Georgia Youth Challenge Academy

## Table of Contents

- [Overview](#overview)
- [Technology Stack](#technology-stack)
- [System Architecture](#system-architecture)
- [Project Structure](#project-structure)
- [Architecture Patterns](#architecture-patterns)
- [Security](#security)
- [Performance](#performance)
- [Documentation Index](#documentation-index)

---

## Overview

Citadel SMS is a comprehensive Student Management System built for the Georgia Youth Challenge Academy. The system manages student records, courses, grades, attendance, and academic operations with a focus on scalability, security, and user experience.

### Key Features

- **Student Management** - Student records, enrollment, demographics
- **Course Management** - Course catalog, curriculum tracking
- **Teacher Management** - Teacher profiles, assignments, schedules
- **Guardian Portal** - Parent/guardian access to student information
- **Grade Tracking** - Assessment management, grade calculations
- **Attendance System** - Daily attendance, reporting, alerts
- **Academic Calendar** - Terms, semesters, events management
- **Theme Customization** - Dynamic theming with organization branding

---

## Technology Stack

### Backend

| Technology | Version | Purpose |
|------------|---------|---------|
| **PHP** | 8.2+ | Server-side language |
| **Laravel** | 12.x | Backend framework |
| **PostgreSQL** | 14+ | Primary database |
| **Inertia.js** | 1.x | Server-side routing |

### Frontend

| Technology | Version | Purpose |
|------------|---------|---------|
| **Vue.js** | 3.x | Frontend framework |
| **Tailwind CSS** | 3.x | CSS framework |
| **Vite** | 5.x | Build tool |
| **Heroicons** | 2.x | Icon library |

### Development Tools

| Tool | Purpose |
|------|---------|
| **Composer** | PHP dependency management |
| **NPM** | JavaScript dependency management |
| **Laravel Pint** | Code formatting |
| **PHPUnit** | Backend testing |
| **Vitest** | Frontend testing |

---

## System Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                        CLIENT LAYER                          │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐      │
│  │   Browser    │  │   Mobile     │  │   Tablet     │      │
│  │  (Desktop)   │  │   Browser    │  │   Browser    │      │
│  └──────────────┘  └──────────────┘  └──────────────┘      │
└─────────────────────────────────────────────────────────────┘
                            ↓ HTTPS
┌─────────────────────────────────────────────────────────────┐
│                    PRESENTATION LAYER                        │
│  ┌──────────────────────────────────────────────────────┐   │
│  │              Vue.js 3 SPA (Inertia.js)               │   │
│  │  ┌──────────┐  ┌──────────┐  ┌──────────┐          │   │
│  │  │  Pages   │  │Components│  │Composables│          │   │
│  │  └──────────┘  └──────────┘  └──────────┘          │   │
│  └──────────────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────────┘
                            ↓ XHR/Fetch
┌─────────────────────────────────────────────────────────────┐
│                     APPLICATION LAYER                        │
│  ┌──────────────────────────────────────────────────────┐   │
│  │                  Laravel 12 Backend                  │   │
│  │  ┌──────────┐  ┌──────────┐  ┌──────────┐          │   │
│  │  │Controllers│  │ Services │  │Middleware│          │   │
│  │  └──────────┘  └──────────┘  └──────────┘          │   │
│  │  ┌──────────┐  ┌──────────┐  ┌──────────┐          │   │
│  │  │  Models  │  │Validators│  │  Events  │          │   │
│  │  └──────────┘  └──────────┘  └──────────┘          │   │
│  └──────────────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────────┘
                            ↓ Eloquent ORM
┌─────────────────────────────────────────────────────────────┐
│                       DATA LAYER                             │
│  ┌──────────────────────────────────────────────────────┐   │
│  │              PostgreSQL Database                     │   │
│  │  ┌──────────┐  ┌──────────┐  ┌──────────┐          │   │
│  │  │ Students │  │ Courses  │  │ Teachers │          │   │
│  │  └──────────┘  └──────────┘  └──────────┘          │   │
│  │  ┌──────────┐  ┌──────────┐  ┌──────────┐          │   │
│  │  │  Grades  │  │Attendance│  │ Settings │          │   │
│  │  └──────────┘  └──────────┘  └──────────┘          │   │
│  └──────────────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────┐
│                      STORAGE LAYER                           │
│  ┌──────────────────────────────────────────────────────┐   │
│  │  File Storage (Student Photos, Documents, Reports)  │   │
│  │  Location: storage/app/public/                      │   │
│  └──────────────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────────┘
```

---

## Project Structure

```
citadel-sms/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/              # Admin-specific controllers
│   │   │   │   ├── StudentController.php
│   │   │   │   ├── CourseController.php
│   │   │   │   ├── TeacherController.php
│   │   │   │   ├── GuardianController.php
│   │   │   │   └── AcademicYearController.php
│   │   │   ├── AdminController.php
│   │   │   ├── ThemeController.php
│   │   │   └── UserManagementController.php
│   │   ├── Middleware/             # Custom middleware
│   │   └── Requests/               # Form request validation
│   ├── Models/
│   │   ├── Student.php             # Student model with relationships
│   │   ├── Guardian.php            # Guardian model
│   │   ├── Course.php              # Course model
│   │   ├── Teacher.php             # Teacher model
│   │   ├── AcademicYear.php        # Academic year model
│   │   ├── Term.php                # Term/semester model
│   │   ├── Setting.php             # System settings
│   │   └── User.php                # User authentication
│   └── Providers/                  # Service providers
├── database/
│   ├── migrations/                 # Database schema migrations
│   │   ├── *_create_students_table.php
│   │   ├── *_create_guardians_table.php
│   │   ├── *_create_courses_table.php
│   │   ├── *_create_teachers_table.php
│   │   └── ...
│   └── seeders/                    # Database seeders
│       ├── AcademicYearSeeder.php
│       ├── CourseSeeder.php
│       └── StudentSeeder.php
├── resources/
│   ├── js/
│   │   ├── Components/             # Reusable Vue components
│   │   │   ├── Admin/              # Admin-specific components
│   │   │   ├── Form/               # Form components
│   │   │   ├── UI/                 # UI components
│   │   │   ├── PrimaryButton.vue
│   │   │   ├── SecondaryButton.vue
│   │   │   ├── TextInput.vue
│   │   │   └── ...
│   │   ├── composables/            # Vue composables
│   │   │   └── useTheme.js         # Theme management
│   │   ├── Layouts/                # Page layouts
│   │   │   ├── AuthenticatedLayout.vue
│   │   │   └── GuestLayout.vue
│   │   ├── Pages/                  # Page components (Inertia)
│   │   │   ├── Admin/              # Admin pages
│   │   │   │   ├── Dashboard.vue
│   │   │   │   ├── Students/
│   │   │   │   ├── Courses/
│   │   │   │   ├── Teachers/
│   │   │   │   └── ...
│   │   │   ├── Auth/               # Authentication pages
│   │   │   └── Welcome.vue         # Landing page
│   │   └── app.js                  # Vue app entry point
│   └── views/
│       └── app.blade.php           # Main HTML template
├── routes/
│   ├── web.php                     # Web routes
│   └── api.php                     # API routes
├── public/
│   ├── images/                     # Public images
│   │   └── logos/                  # Organization logos
│   └── build/                      # Compiled assets
├── storage/
│   ├── app/
│   │   └── public/                 # Public file storage
│   │       ├── students/photos/    # Student photos
│   │       └── teachers/photos/    # Teacher photos
│   └── logs/                       # Application logs
├── tests/                          # Automated tests
├── docs/                           # Documentation
│   ├── FRONTEND.md
│   ├── BACKEND.md
│   ├── API.md
│   └── DATABASE.md
├── .env                            # Environment configuration
├── composer.json                   # PHP dependencies
├── package.json                    # JavaScript dependencies
└── tailwind.config.js             # Tailwind CSS configuration
```

---

## Architecture Patterns

### MVC Pattern (Backend)

Citadel SMS follows the Model-View-Controller pattern on the backend:

- **Models** - Eloquent ORM models representing database tables
- **Views** - Inertia.js responses (Vue components)
- **Controllers** - Handle HTTP requests, business logic coordination

### Component-Based Architecture (Frontend)

The frontend uses a component-based architecture:

- **Pages** - Route-level components
- **Layouts** - Wrapping layouts for pages
- **Components** - Reusable UI elements
- **Composables** - Shared reactive logic

### Repository Pattern (Future Enhancement)

For complex business logic, repository pattern can be implemented:

```php
app/Repositories/
├── StudentRepository.php
├── CourseRepository.php
└── Contracts/
    └── StudentRepositoryInterface.php
```

### Service Layer Pattern

Service classes encapsulate complex business logic:

```php
app/Services/
├── EnrollmentService.php
├── GradeCalculationService.php
└── AttendanceService.php
```

---

## Security

### Authentication & Authorization

- **Laravel Sanctum** - API token authentication
- **Session-based auth** - Web application authentication
- **Role-based access control (RBAC)** - User roles: admin, teacher, student, parent

### Data Protection

- **Mass assignment protection** - Fillable properties on models
- **CSRF protection** - Automatic CSRF token validation
- **SQL injection prevention** - Eloquent ORM parameterized queries
- **XSS prevention** - Automatic output escaping in Blade/Vue

### Password Security

- **Bcrypt hashing** - Password encryption
- **Password validation** - Laravel's password rules
- **Password reset** - Secure token-based reset

### File Upload Security

- **File type validation** - Image and document validation
- **File size limits** - Max 2MB for photos
- **Secure storage** - Files stored outside web root

---

## Performance

### Database Optimization

- **Indexes** - Foreign keys and frequently queried columns indexed
- **Eager loading** - N+1 query prevention with `with()`
- **Query optimization** - Efficient queries with scopes
- **Pagination** - All lists paginated (10 items per page)

### Frontend Optimization

- **Code splitting** - Vite automatic code splitting
- **Lazy loading** - Route-based lazy loading
- **Asset optimization** - CSS/JS minification and compression
- **Image optimization** - Responsive images

### Caching Strategy

- **Route caching** - `php artisan route:cache`
- **Config caching** - `php artisan config:cache`
- **View caching** - `php artisan view:cache`
- **Query caching** - Cache frequently accessed data

---

## Documentation Index

Detailed architecture documentation is available in the following documents:

1. **[Frontend Architecture](docs/FRONTEND.md)** - Vue.js components, pages, layouts, composables
2. **[Backend Architecture](docs/BACKEND.md)** - Laravel controllers, models, services, middleware
3. **[API Documentation](docs/API.md)** - API endpoints, request/response formats
4. **[Database Schema](docs/DATABASE.md)** - Tables, relationships, migrations, indexes

---

## Development Workflow

### Local Development

```bash
# Install dependencies
composer install
npm install

# Environment setup
cp .env.example .env
php artisan key:generate

# Database setup
php artisan migrate --seed

# Start development servers
php artisan serve          # Backend (port 8000)
npm run dev               # Frontend (Vite)
```

### Production Deployment

```bash
# Optimize for production
composer install --optimize-autoloader --no-dev
npm run build
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run migrations
php artisan migrate --force

# Set permissions
chmod -R 755 storage bootstrap/cache
```

---

## Scalability Considerations

### Horizontal Scaling

- **Load balancing** - Multiple application servers
- **Session storage** - Redis/database for shared sessions
- **File storage** - S3/cloud storage for uploads

### Vertical Scaling

- **Database optimization** - Indexes, query optimization
- **Caching layer** - Redis for application cache
- **Queue workers** - Background job processing

### Future Enhancements

- **API versioning** - RESTful API for mobile apps
- **Microservices** - Service separation for large scale
- **CDN integration** - Static asset delivery
- **Real-time features** - WebSockets for live updates

---

## Maintenance & Monitoring

### Logging

- **Application logs** - `storage/logs/laravel.log`
- **Error tracking** - Exception logging and reporting
- **Audit trails** - User action logging (future)

### Monitoring

- **Performance monitoring** - Laravel Telescope (development)
- **Database monitoring** - Query performance tracking
- **Server monitoring** - Resource usage tracking

### Backup Strategy

- **Database backups** - Daily automated backups
- **File backups** - Regular storage backups
- **Configuration backups** - Version control for configs

---

## Support & Contact

For technical questions or issues:

- **Repository:** [GitHub Repository URL]
- **Documentation:** `/docs` directory
- **Issue Tracker:** [GitHub Issues URL]

---

**© 2026 Georgia Youth Challenge Academy. All rights reserved.**
