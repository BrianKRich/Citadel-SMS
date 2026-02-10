# Citadel SMS - API Documentation

**Version:** 1.0
**Last Updated:** February 2026
**Architecture:** Inertia.js (Server-Side Routing)

---

## Table of Contents

- [Overview](#overview)
- [Inertia.js Architecture](#inertiajs-architecture)
- [API Endpoints](#api-endpoints)
- [Authentication](#authentication)
- [Error Handling](#error-handling)
- [Future API Considerations](#future-api-considerations)

---

## Overview

Citadel SMS uses **Inertia.js**, which means it does NOT have a traditional RESTful JSON API. Instead, Inertia provides a seamless SPA experience using server-side routing with Laravel.

### Key Concepts

**Inertia.js Approach:**
- Frontend makes requests to Laravel routes (not API endpoints)
- Laravel controllers return Inertia responses (not JSON)
- Inertia automatically handles rendering Vue components
- No need for separate frontend routing or API layer

**Traditional REST API:**
- Frontend makes requests to `/api/students`
- Backend returns JSON: `{ "data": [...] }`
- Frontend handles routing and rendering

**Inertia.js:**
- Frontend makes requests to `/admin/students`
- Backend returns Inertia response with component name and props
- Inertia renders Vue component on frontend

---

## Inertia.js Architecture

### How It Works

1. **User clicks link** in Vue component:
   ```vue
   <Link href="/admin/students">View Students</Link>
   ```

2. **Inertia makes XHR request** to Laravel route:
   ```
   GET /admin/students
   Headers: X-Inertia: true
   ```

3. **Laravel controller returns Inertia response**:
   ```php
   return Inertia::render('Admin/Students/Index', [
       'students' => $students,
   ]);
   ```

4. **Inertia response format** (JSON):
   ```json
   {
     "component": "Admin/Students/Index",
     "props": {
       "students": { "data": [...], "links": [...] }
     },
     "url": "/admin/students",
     "version": "abc123"
   }
   ```

5. **Frontend receives response** and renders Vue component:
   ```vue
   <script setup>
   defineProps({ students: Object });
   </script>
   ```

### Benefits

✅ **No API boilerplate** - No need to define separate API routes and controllers
✅ **Type safety** - Props are defined in Vue components
✅ **Server-side routing** - Laravel handles all routing logic
✅ **SEO-friendly** - Server-rendered on first load
✅ **SPA experience** - Client-side navigation after initial load
✅ **CSRF protection** - Automatic CSRF token handling
✅ **Middleware** - All Laravel middleware works seamlessly

---

## API Endpoints

Citadel SMS currently has **one** traditional API endpoint for theme data. All other functionality uses Inertia.js.

### Theme API

#### Get Theme Settings

**Endpoint:** `GET /api/theme`

**Description:** Retrieves current theme color settings.

**Authentication:** None (public endpoint)

**Request:**
```http
GET /api/theme HTTP/1.1
Host: localhost:8000
Accept: application/json
```

**Response:**
```json
{
  "primary_color": "#1B3A6B",
  "secondary_color": "#FFB81C",
  "accent_color": "#C8102E",
  "background_color": "#ffffff",
  "text_color": "#1f2937"
}
```

**Status Codes:**
- `200 OK` - Success

**Example Usage:**

```javascript
// Frontend (useTheme composable)
const loadTheme = async () => {
    try {
        const response = await axios.get('/api/theme');
        theme.value = response.data;
        applyTheme();
    } catch (error) {
        console.error('Failed to load theme:', error);
    }
};
```

**Controller:**
```php
// app/Http/Controllers/ThemeController.php
public function show()
{
    return response()->json([
        'primary_color' => Setting::get('theme_primary_color', '#1B3A6B'),
        'secondary_color' => Setting::get('theme_secondary_color', '#FFB81C'),
        'accent_color' => Setting::get('theme_accent_color', '#C8102E'),
        'background_color' => Setting::get('theme_background_color', '#ffffff'),
        'text_color' => Setting::get('theme_text_color', '#1f2937'),
    ]);
}
```

---

## Authentication

### Session-Based Authentication

Citadel SMS uses Laravel's built-in session-based authentication (Laravel Breeze).

**How It Works:**
1. User submits login form to `/login`
2. Laravel validates credentials and creates session
3. Session cookie is sent with all subsequent requests
4. Laravel middleware (`auth`) checks session validity

**Protected Routes:**
```php
Route::middleware(['auth'])->prefix('admin')->group(function () {
    Route::resource('students', StudentController::class);
    // ... other protected routes
});
```

**Inertia Authentication:**
- Authenticated user data is shared globally via `HandleInertiaRequests` middleware
- Available in all Vue components as `$page.props.auth.user`

**Example:**
```vue
<script setup>
import { usePage } from '@inertiajs/vue3';

const page = usePage();
const user = computed(() => page.props.auth.user);
</script>

<template>
    <div v-if="user">
        Welcome, {{ user.name }}!
    </div>
</template>
```

### CSRF Protection

- All POST, PUT, PATCH, DELETE requests require CSRF token
- Inertia automatically includes CSRF token in requests
- Token is embedded in page meta tag

**Verification:**
```html
<!-- resources/views/app.blade.php -->
<meta name="csrf-token" content="{{ csrf_token() }}">
```

---

## Error Handling

### Validation Errors

**Backend:**
```php
$validated = $request->validate([
    'first_name' => ['required', 'string', 'max:255'],
    'email' => ['required', 'email', 'unique:students,email'],
]);
```

**If validation fails:**
- Laravel returns 422 Unprocessable Entity
- Inertia intercepts and attaches errors to form
- No page reload, errors appear inline

**Frontend:**
```vue
<script setup>
import { useForm } from '@inertiajs/vue3';

const form = useForm({
    first_name: '',
    email: '',
});

const submit = () => {
    form.post('/admin/students');
};
</script>

<template>
    <form @submit.prevent="submit">
        <input v-model="form.first_name" />
        <span v-if="form.errors.first_name" class="error">
            {{ form.errors.first_name }}
        </span>

        <input v-model="form.email" />
        <span v-if="form.errors.email" class="error">
            {{ form.errors.email }}
        </span>

        <button type="submit" :disabled="form.processing">
            Submit
        </button>
    </form>
</template>
```

### HTTP Errors

**404 Not Found:**
- Laravel returns 404 response
- Inertia renders `Error.vue` page component

**403 Forbidden:**
- Laravel returns 403 response (e.g., unauthorized access)
- Inertia renders `Error.vue` page component

**500 Server Error:**
- Laravel returns 500 response
- Inertia renders `Error.vue` page component

**Error Page:**
```vue
<!-- resources/js/Pages/Error.vue -->
<script setup>
defineProps({
    status: Number,
});

const title = computed(() => {
    return {
        404: 'Page Not Found',
        403: 'Forbidden',
        500: 'Server Error',
    }[props.status] || 'Error';
});
</script>

<template>
    <div>
        <h1>{{ status }}</h1>
        <p>{{ title }}</p>
    </div>
</template>
```

---

## Inertia Form Handling

### Form Submissions

**Using `useForm` Composable:**

```vue
<script setup>
import { useForm } from '@inertiajs/vue3';

const form = useForm({
    first_name: 'John',
    last_name: 'Doe',
    email: 'john@example.com',
});

const submit = () => {
    form.post('/admin/students', {
        onSuccess: () => {
            // Handle success
            form.reset();
        },
        onError: () => {
            // Handle error (errors automatically attached to form)
        },
        onFinish: () => {
            // Always called
        },
    });
};
</script>

<template>
    <form @submit.prevent="submit">
        <input v-model="form.first_name" />
        <span v-if="form.errors.first_name">{{ form.errors.first_name }}</span>

        <button type="submit" :disabled="form.processing">
            Submit
        </button>
    </form>
</template>
```

**Form Properties:**
- `form.data` - Form data object
- `form.errors` - Validation errors
- `form.processing` - Boolean indicating if request is in progress
- `form.progress` - Upload progress (for file uploads)
- `form.wasSuccessful` - Boolean indicating successful submission
- `form.recentlySuccessful` - Boolean (true for 2 seconds after success)

**Form Methods:**
- `form.post(url, options)` - Submit as POST
- `form.put(url, options)` - Submit as PUT
- `form.patch(url, options)` - Submit as PATCH
- `form.delete(url, options)` - Submit as DELETE
- `form.reset()` - Reset form to initial values
- `form.clearErrors()` - Clear all validation errors

### File Uploads

```vue
<script setup>
import { useForm } from '@inertiajs/vue3';

const form = useForm({
    photo: null,
});

const handleFileChange = (event) => {
    form.photo = event.target.files[0];
};

const submit = () => {
    form.post('/admin/students', {
        onUploadProgress: (progress) => {
            console.log(`Upload: ${progress.percentage}%`);
        },
    });
};
</script>

<template>
    <form @submit.prevent="submit">
        <input type="file" @change="handleFileChange" />

        <div v-if="form.progress">
            Uploading: {{ form.progress.percentage }}%
        </div>

        <button type="submit" :disabled="form.processing">
            Upload
        </button>
    </form>
</template>
```

---

## Inertia Links

### Navigation

**Using `Link` Component:**

```vue
<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <!-- Regular link (SPA navigation) -->
    <Link href="/admin/students">View Students</Link>

    <!-- Link with data (POST request) -->
    <Link
        href="/admin/students"
        method="post"
        :data="{ first_name: 'John', last_name: 'Doe' }"
    >
        Create Student
    </Link>

    <!-- Link with confirmation -->
    <Link
        href="/admin/students/1"
        method="delete"
        as="button"
        @before="() => confirm('Are you sure?')"
    >
        Delete
    </Link>

    <!-- Preserve scroll position -->
    <Link href="/admin/students" preserve-scroll>
        View Students
    </Link>

    <!-- Preserve state (form inputs) -->
    <Link href="/admin/students" preserve-state>
        View Students
    </Link>
</template>
```

**Link Props:**
- `href` - URL to navigate to
- `method` - HTTP method (get, post, put, patch, delete)
- `data` - Data to send with request
- `as` - HTML element to render (a, button, etc.)
- `preserve-scroll` - Don't reset scroll position
- `preserve-state` - Preserve component state
- `only` - Only reload specific props
- `headers` - Additional HTTP headers

---

## Shared Data

### Global Data (All Pages)

Data shared globally via `HandleInertiaRequests` middleware:

```php
// app/Http/Middleware/HandleInertiaRequests.php
public function share(Request $request): array
{
    return [
        ...parent::share($request),
        'auth' => [
            'user' => $request->user(),
        ],
        'flash' => [
            'success' => fn () => $request->session()->get('success'),
            'error' => fn () => $request->session()->get('error'),
        ],
    ];
}
```

**Access in Vue:**
```vue
<script setup>
import { usePage } from '@inertiajs/vue3';

const page = usePage();
const user = computed(() => page.props.auth.user);
const flash = computed(() => page.props.flash);
</script>

<template>
    <div v-if="flash.success" class="alert alert-success">
        {{ flash.success }}
    </div>

    <div v-if="user">
        Logged in as {{ user.name }}
    </div>
</template>
```

---

## Future API Considerations

### RESTful API (Phase 9 - Future)

If a separate API is needed for mobile apps or third-party integrations:

**Planned Endpoints:**

#### Students

```
GET    /api/v1/students           - List all students
POST   /api/v1/students           - Create student
GET    /api/v1/students/{id}      - Get student details
PUT    /api/v1/students/{id}      - Update student
DELETE /api/v1/students/{id}      - Delete student
GET    /api/v1/students/{id}/guardians - Get student guardians
```

#### Courses

```
GET    /api/v1/courses            - List all courses
POST   /api/v1/courses            - Create course
GET    /api/v1/courses/{id}       - Get course details
PUT    /api/v1/courses/{id}       - Update course
DELETE /api/v1/courses/{id}       - Delete course
```

#### Enrollments

```
POST   /api/v1/enrollments        - Enroll student in class
DELETE /api/v1/enrollments/{id}   - Drop student from class
GET    /api/v1/students/{id}/enrollments - Get student enrollments
```

#### Grades

```
GET    /api/v1/students/{id}/grades - Get student grades
POST   /api/v1/grades             - Submit grade
PUT    /api/v1/grades/{id}        - Update grade
```

#### Attendance

```
POST   /api/v1/attendance         - Mark attendance
GET    /api/v1/students/{id}/attendance - Get student attendance
```

### API Authentication (Future)

**Laravel Sanctum** for API token authentication:

```php
// Generate token for user
$token = $user->createToken('mobile-app')->plainTextToken;

// Use token in requests
Authorization: Bearer {token}
```

**API Routes:**
```php
// routes/api.php
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('students', StudentApiController::class);
    Route::apiResource('courses', CourseApiController::class);
    Route::apiResource('enrollments', EnrollmentApiController::class);
});
```

### API Response Format (Future)

**Success Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "student_id": "STU-2026-001",
    "first_name": "John",
    "last_name": "Doe",
    "email": "john@example.com"
  },
  "message": "Student retrieved successfully"
}
```

**Error Response:**
```json
{
  "success": false,
  "error": {
    "code": "VALIDATION_ERROR",
    "message": "The given data was invalid.",
    "details": {
      "email": ["The email has already been taken."]
    }
  }
}
```

**Pagination Response:**
```json
{
  "success": true,
  "data": [...],
  "meta": {
    "current_page": 1,
    "per_page": 10,
    "total": 100,
    "last_page": 10
  },
  "links": {
    "first": "/api/v1/students?page=1",
    "last": "/api/v1/students?page=10",
    "prev": null,
    "next": "/api/v1/students?page=2"
  }
}
```

### API Rate Limiting (Future)

```php
// app/Http/Kernel.php
protected $middlewareGroups = [
    'api' => [
        'throttle:60,1', // 60 requests per minute
        // ...
    ],
];

// Custom rate limit for specific routes
Route::middleware('throttle:10,1')->group(function () {
    // 10 requests per minute
});
```

---

## Comparison: Inertia vs Traditional API

| Feature | Inertia.js (Current) | Traditional REST API |
|---------|----------------------|----------------------|
| **Routing** | Server-side (Laravel) | Client-side (Vue Router) |
| **Data Format** | Inertia JSON (component + props) | Pure JSON |
| **Authentication** | Session-based | Token-based (Sanctum/Passport) |
| **CSRF Protection** | Automatic | Manual token handling |
| **SEO** | ✅ Server-rendered | ❌ Requires SSR setup |
| **Type Safety** | ✅ Props defined in components | ❌ Manual type definitions |
| **API Documentation** | Not needed | Required |
| **Mobile Apps** | ❌ Not suitable | ✅ Ideal |
| **Third-Party Access** | ❌ Not suitable | ✅ Ideal |
| **Development Speed** | ✅ Faster | ❌ Slower (more boilerplate) |
| **Complexity** | ✅ Simpler | ❌ More complex |

---

## WebSockets (Future Enhancement)

### Real-Time Updates with Laravel Echo

For real-time features (live notifications, attendance updates, etc.):

**Setup:**
```bash
composer require pusher/pusher-php-server
npm install --save laravel-echo pusher-js
```

**Broadcasting Events:**
```php
// app/Events/GradePosted.php
class GradePosted implements ShouldBroadcast
{
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('student.' . $this->student->id),
        ];
    }
}

// Controller
event(new GradePosted($student, $grade));
```

**Frontend Listening:**
```javascript
// resources/js/app.js
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: import.meta.env.VITE_PUSHER_APP_KEY,
    cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
});

// Component
window.Echo.private(`student.${studentId}`)
    .listen('GradePosted', (e) => {
        console.log('New grade posted:', e.grade);
    });
```

---

## API Versioning (Future)

When building a traditional API:

**URL Versioning:**
```
/api/v1/students
/api/v2/students
```

**Route Setup:**
```php
// routes/api.php
Route::prefix('v1')->group(function () {
    Route::apiResource('students', StudentApiController::class);
});

Route::prefix('v2')->group(function () {
    Route::apiResource('students', StudentApiV2Controller::class);
});
```

---

## GraphQL (Alternative Future Approach)

Instead of REST API, consider GraphQL for flexible data fetching:

**Setup:**
```bash
composer require rebing/graphql-laravel
```

**Query Example:**
```graphql
query {
  student(id: 1) {
    id
    firstName
    lastName
    guardians {
      firstName
      lastName
    }
    enrollments {
      class {
        course {
          name
        }
      }
    }
  }
}
```

**Benefits:**
- Fetch exactly the data you need
- No over-fetching or under-fetching
- Strongly typed schema
- Single endpoint for all queries

---

## Summary

**Current State:**
- Inertia.js architecture (no traditional API)
- One API endpoint: `/api/theme` (GET theme settings)
- Session-based authentication
- CSRF protection automatic
- Server-side routing with Laravel
- SPA experience with Vue 3

**Future API:**
- RESTful API for mobile apps (Phase 9)
- Laravel Sanctum for token authentication
- API versioning (v1, v2, etc.)
- Rate limiting
- Comprehensive API documentation
- WebSockets for real-time updates
- Possibly GraphQL for flexible querying

---

**© 2026 Brian K. Rich. All rights reserved.**
