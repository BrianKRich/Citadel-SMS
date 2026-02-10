# Citadel SMS - Frontend Architecture

**Version:** 2.0 (Phase 0-2 Complete)
**Last Updated:** February 9, 2026
**Framework:** Vue 3 + Inertia.js

---

## Table of Contents

- [Overview](#overview)
- [Directory Structure](#directory-structure)
- [Technology Stack](#technology-stack)
- [Component Architecture](#component-architecture)
- [Pages](#pages)
- [Layouts](#layouts)
- [Composables](#composables)
- [Styling System](#styling-system)
- [State Management](#state-management)
- [Form Handling](#form-handling)
- [Routing](#routing)
- [Dark Mode](#dark-mode)
- [Responsive Design](#responsive-design)
- [Best Practices](#best-practices)

---

## Overview

Citadel SMS frontend is built with **Vue 3** using the **Composition API** and **Inertia.js** for seamless SPA functionality. The application emphasizes:

- **Component-based architecture** - Reusable, modular components
- **Tailwind CSS** - Utility-first styling with custom theming
- **Dark mode support** - System-wide dark mode toggle
- **Responsive design** - Mobile-first approach
- **Type safety** - Props validation and computed properties
- **Accessibility** - ARIA labels, keyboard navigation, semantic HTML

**Key Features:**
- Dynamic theming with custom CSS variables
- Inertia.js for server-side routing without page reloads
- Shared layouts for consistent UI structure
- Composables for reusable reactive logic
- Form helpers with validation display
- Loading states and progress indicators

---

## Directory Structure

```
resources/
├── js/
│   ├── Components/
│   │   ├── Admin/
│   │   │   ├── AdminActionCard.vue    # Admin dashboard action cards
│   │   │   ├── AdminLayout.vue         # Admin-specific layout (deprecated)
│   │   │   └── StatCard.vue            # Statistics display cards
│   │   ├── Alert.vue                   # Success/error alerts
│   │   ├── ApplicationLogo.vue         # App logo component
│   │   ├── Checkbox.vue                # Checkbox input
│   │   ├── DangerButton.vue            # Red danger button
│   │   ├── Dropdown.vue                # Dropdown menu
│   │   ├── DropdownLink.vue            # Dropdown menu item
│   │   ├── InputError.vue              # Validation error display
│   │   ├── InputLabel.vue              # Form input label
│   │   ├── Modal.vue                   # Modal dialog
│   │   ├── NavLink.vue                 # Navigation link
│   │   ├── PrimaryButton.vue           # Primary action button
│   │   ├── ResponsiveNavLink.vue       # Mobile navigation link
│   │   ├── SecondaryButton.vue         # Secondary action button
│   │   └── TextInput.vue               # Text input field
│   ├── composables/
│   │   └── useTheme.js                 # Theme management composable
│   ├── Layouts/
│   │   ├── AuthenticatedLayout.vue     # Main authenticated layout
│   │   └── GuestLayout.vue             # Guest/public layout
│   ├── Pages/
│   │   ├── Admin/
│   │   │   ├── Dashboard.vue           # Admin dashboard
│   │   │   ├── Settings.vue            # System settings
│   │   │   ├── Users/
│   │   │   │   ├── Index.vue           # User list
│   │   │   │   ├── Create.vue          # Create user
│   │   │   │   └── Edit.vue            # Edit user
│   │   │   ├── Students/               # Student pages (future)
│   │   │   ├── Courses/                # Course pages (future)
│   │   │   └── Teachers/               # Teacher pages (future)
│   │   ├── Auth/
│   │   │   ├── ConfirmPassword.vue
│   │   │   ├── ForgotPassword.vue
│   │   │   ├── Login.vue
│   │   │   ├── Register.vue
│   │   │   ├── ResetPassword.vue
│   │   │   └── VerifyEmail.vue
│   │   ├── Profile/
│   │   │   ├── Edit.vue                # User profile edit
│   │   │   └── Partials/
│   │   │       ├── DeleteUserForm.vue
│   │   │       ├── UpdatePasswordForm.vue
│   │   │       └── UpdateProfileInformationForm.vue
│   │   └── Welcome.vue                 # Landing page
│   └── app.js                          # Vue app entry point
├── views/
│   └── app.blade.php                   # Root HTML template
└── css/
    └── app.css                         # Global styles + Tailwind imports
```

---

## Technology Stack

### Core Technologies

| Technology | Version | Purpose |
|------------|---------|---------|
| **Vue.js** | 3.x | Progressive JavaScript framework |
| **Inertia.js** | 1.x | Server-side routing adapter |
| **Tailwind CSS** | 3.x | Utility-first CSS framework |
| **Vite** | 5.x | Build tool and dev server |
| **Heroicons** | 2.x | SVG icon library |

### Build Tools

```json
// package.json (relevant dependencies)
{
  "dependencies": {
    "@inertiajs/vue3": "^1.0.0",
    "@tailwindcss/forms": "^0.5.3",
    "axios": "^1.6.4",
    "vue": "^3.4.0"
  },
  "devDependencies": {
    "@vitejs/plugin-vue": "^5.0.0",
    "autoprefixer": "^10.4.12",
    "laravel-vite-plugin": "^1.0",
    "postcss": "^8.4.31",
    "tailwindcss": "^3.2.1",
    "vite": "^5.0"
  }
}
```

---

## Component Architecture

### Component Types

**1. UI Components** - Low-level reusable elements
- `PrimaryButton.vue`, `TextInput.vue`, `Checkbox.vue`
- No business logic, highly reusable
- Accept props, emit events

**2. Form Components** - Input fields with validation
- `TextInput.vue`, `InputLabel.vue`, `InputError.vue`
- Handle validation display
- Accessible with proper ARIA attributes

**3. Layout Components** - Page structure
- `AuthenticatedLayout.vue`, `GuestLayout.vue`
- Provide consistent navigation and structure
- Manage sidebar, header, footer

**4. Feature Components** - Business logic components
- `AdminActionCard.vue`, `StatCard.vue`
- Contain specific feature logic
- May fetch data or compute values

**5. Page Components** - Route-level components
- `Admin/Dashboard.vue`, `Admin/Users/Index.vue`
- Receive props from Inertia
- Use layouts for structure

---

### Button Components

#### PrimaryButton.vue

**Purpose:** Main call-to-action buttons with theme colors.

**File:** `resources/js/Components/PrimaryButton.vue`

```vue
<script setup>
defineProps({
    type: {
        type: String,
        default: 'submit',
    },
});
</script>

<template>
    <button
        :type="type"
        class="inline-flex items-center rounded-md border border-transparent bg-primary-600 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition duration-150 ease-in-out hover:bg-primary-700 focus:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 active:bg-primary-900 dark:bg-primary-700 dark:hover:bg-primary-600 dark:focus:bg-primary-600 dark:focus:ring-offset-gray-800 dark:active:bg-primary-600"
    >
        <slot />
    </button>
</template>
```

**Usage:**
```vue
<PrimaryButton @click="save">Save</PrimaryButton>
<PrimaryButton type="button" :disabled="processing">Submit</PrimaryButton>
```

**Features:**
- Uses theme primary color (`bg-primary-600`)
- Supports dark mode variants
- Accessible focus states
- Smooth transitions
- Slot for flexible content

#### SecondaryButton.vue

**Purpose:** Secondary actions with theme secondary color.

```vue
<template>
    <button
        :type="type"
        class="inline-flex items-center rounded-md border border-transparent bg-secondary-600 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition duration-150 ease-in-out hover:bg-secondary-700 focus:bg-secondary-700 focus:outline-none focus:ring-2 focus:ring-secondary-500 focus:ring-offset-2 active:bg-secondary-900 dark:bg-secondary-700 dark:hover:bg-secondary-600"
    >
        <slot />
    </button>
</template>
```

#### DangerButton.vue

**Purpose:** Destructive actions (delete, remove, etc.).

```vue
<template>
    <button
        :type="type"
        class="inline-flex items-center rounded-md border border-transparent bg-red-600 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition duration-150 ease-in-out hover:bg-red-700 focus:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 active:bg-red-900 dark:hover:bg-red-600 dark:focus:bg-red-600 dark:active:bg-red-600"
    >
        <slot />
    </button>
</template>
```

**Note:** Danger button uses fixed `bg-red-600` (semantic color, not themed).

---

### Form Components

#### TextInput.vue

**Purpose:** Text input field with proper styling and accessibility.

**File:** `resources/js/Components/TextInput.vue`

```vue
<script setup>
import { onMounted, ref } from 'vue';

defineProps({
    modelValue: String,
});

defineEmits(['update:modelValue']);

const input = ref(null);

defineExpose({ focus: () => input.value.focus() });

onMounted(() => {
    if (input.value.hasAttribute('autofocus')) {
        input.value.focus();
    }
});
</script>

<template>
    <input
        ref="input"
        class="rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:focus:border-primary-600 dark:focus:ring-primary-600"
        :value="modelValue"
        @input="$emit('update:modelValue', $event.target.value)"
    />
</template>
```

**Usage:**
```vue
<TextInput
    v-model="form.email"
    type="email"
    placeholder="Enter email"
    required
/>
```

**Features:**
- Two-way binding with `v-model`
- Exposes `focus()` method for programmatic focus
- Auto-focuses if `autofocus` attribute present
- Dark mode support
- Theme-aware focus ring (`focus:ring-primary-500`)

#### InputLabel.vue

**Purpose:** Form field labels with consistent styling.

```vue
<script setup>
defineProps({
    value: String,
});
</script>

<template>
    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
        <span v-if="value">{{ value }}</span>
        <span v-else><slot /></span>
    </label>
</template>
```

**Usage:**
```vue
<InputLabel for="email" value="Email Address" />
<InputLabel for="name">Full Name</InputLabel>
```

#### InputError.vue

**Purpose:** Display validation errors.

```vue
<script setup>
defineProps({
    message: String,
});
</script>

<template>
    <div v-show="message">
        <p class="text-sm text-red-600 dark:text-red-400">
            {{ message }}
        </p>
    </div>
</template>
```

**Usage:**
```vue
<InputError :message="form.errors.email" />
```

#### Checkbox.vue

**Purpose:** Checkbox input with proper styling.

```vue
<script setup>
import { computed } from 'vue';

const props = defineProps({
    checked: {
        type: [Array, Boolean],
        default: false,
    },
    value: {
        default: null,
    },
});

const emit = defineEmits(['update:checked']);

const proxyChecked = computed({
    get() {
        return props.checked;
    },
    set(val) {
        emit('update:checked', val);
    },
});
</script>

<template>
    <input
        v-model="proxyChecked"
        type="checkbox"
        :value="value"
        class="rounded border-gray-300 text-primary-600 shadow-sm focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-900 dark:focus:ring-primary-600 dark:focus:ring-offset-gray-800"
    />
</template>
```

**Usage:**
```vue
<Checkbox v-model:checked="form.remember" name="remember" />
```

---

### Navigation Components

#### NavLink.vue

**Purpose:** Desktop navigation links with active state.

**File:** `resources/js/Components/NavLink.vue`

```vue
<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    href: String,
    active: Boolean,
});

const classes = computed(() =>
    props.active
        ? 'inline-flex items-center border-b-2 border-primary-600 px-1 pt-1 text-sm font-medium leading-5 text-gray-900 transition duration-150 ease-in-out focus:border-primary-700 focus:outline-none dark:border-primary-400 dark:text-gray-100'
        : 'inline-flex items-center border-b-2 border-transparent px-1 pt-1 text-sm font-medium leading-5 text-gray-500 transition duration-150 ease-in-out hover:border-gray-300 hover:text-gray-700 focus:border-gray-300 focus:text-gray-700 focus:outline-none dark:text-gray-400 dark:hover:border-gray-700 dark:hover:text-gray-300 dark:focus:border-gray-700 dark:focus:text-gray-300',
);
</script>

<template>
    <Link :href="href" :class="classes">
        <slot />
    </Link>
</template>
```

**Usage:**
```vue
<NavLink :href="route('admin.dashboard')" :active="route().current('admin.dashboard')">
    Dashboard
</NavLink>
```

**Features:**
- Active state with theme primary color border
- Hover and focus states
- Dark mode support
- Uses Inertia `Link` component for SPA navigation

#### ResponsiveNavLink.vue

**Purpose:** Mobile navigation links.

```vue
<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    href: String,
    active: Boolean,
});

const classes = computed(() =>
    props.active
        ? 'block w-full border-l-4 border-primary-600 bg-primary-50 py-2 pl-3 pr-4 text-start text-base font-medium text-primary-700 transition duration-150 ease-in-out focus:border-primary-700 focus:bg-primary-100 focus:text-primary-800 focus:outline-none dark:border-primary-400 dark:bg-primary-900/50 dark:text-primary-300 dark:focus:border-primary-300 dark:focus:bg-primary-900 dark:focus:text-primary-200'
        : 'block w-full border-l-4 border-transparent py-2 pl-3 pr-4 text-start text-base font-medium text-gray-600 transition duration-150 ease-in-out hover:border-gray-300 hover:bg-gray-50 hover:text-gray-800 focus:border-gray-300 focus:bg-gray-50 focus:text-gray-800 focus:outline-none dark:text-gray-400 dark:hover:border-gray-600 dark:hover:bg-gray-700 dark:hover:text-gray-200 dark:focus:border-gray-600 dark:focus:bg-gray-700 dark:focus:text-gray-200',
);
</script>

<template>
    <Link :href="href" :class="classes">
        <slot />
    </Link>
</template>
```

---

### Admin Components

#### AdminActionCard.vue

**Purpose:** Action cards on admin dashboard for quick navigation.

**File:** `resources/js/Components/Admin/AdminActionCard.vue`

```vue
<script setup>
import { Link } from '@inertiajs/vue3';
import { ChevronRightIcon } from '@heroicons/vue/24/outline';

defineProps({
    title: String,
    description: String,
    href: String,
    icon: String,
    color: {
        type: String,
        default: 'primary', // primary, secondary, accent, green, blue, purple, etc.
    },
});
</script>

<template>
    <Link
        :href="href"
        class="block rounded-lg border border-gray-200 bg-white p-6 shadow-sm transition-all hover:shadow-md dark:border-gray-700 dark:bg-gray-800"
    >
        <div class="flex items-start justify-between">
            <div class="flex-1">
                <div class="mb-2 flex items-center">
                    <component
                        :is="icon"
                        :class="`h-6 w-6 text-${color}-600 dark:text-${color}-400`"
                    />
                    <h3 class="ml-3 text-lg font-semibold text-gray-900 dark:text-white">
                        {{ title }}
                    </h3>
                </div>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    {{ description }}
                </p>
            </div>
            <ChevronRightIcon class="h-5 w-5 text-gray-400" />
        </div>
    </Link>
</template>
```

**Usage:**
```vue
<script setup>
import { AcademicCapIcon } from '@heroicons/vue/24/outline';
</script>

<template>
    <AdminActionCard
        title="Manage Students"
        description="View and manage student records"
        href="/admin/students"
        :icon="AcademicCapIcon"
        color="primary"
    />
</template>
```

#### StatCard.vue

**Purpose:** Display statistics on dashboard.

```vue
<script setup>
defineProps({
    title: String,
    value: [String, Number],
    icon: String,
    trend: {
        type: String,
        default: null, // 'up', 'down', null
    },
    trendValue: String,
});
</script>

<template>
    <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">
                    {{ title }}
                </p>
                <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">
                    {{ value }}
                </p>
                <p v-if="trend" class="mt-2 flex items-center text-sm">
                    <span :class="trend === 'up' ? 'text-green-600' : 'text-red-600'">
                        {{ trendValue }}
                    </span>
                    <span class="ml-1 text-gray-600 dark:text-gray-400">vs last month</span>
                </p>
            </div>
            <component :is="icon" class="h-12 w-12 text-gray-400" />
        </div>
    </div>
</template>
```

**Usage:**
```vue
<script setup>
import { UsersIcon } from '@heroicons/vue/24/outline';
</script>

<template>
    <StatCard
        title="Total Students"
        :value="totalStudents"
        :icon="UsersIcon"
        trend="up"
        trendValue="+12%"
    />
</template>
```

---

## Pages

### Page Structure

Pages receive props from Laravel controllers via Inertia:

```vue
<script setup>
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

// Props from controller
defineProps({
    students: Object,
    filters: Object,
});
</script>

<template>
    <Head title="Students" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold">Students</h2>
        </template>

        <!-- Page content -->
        <div class="py-12">
            <!-- ... -->
        </div>
    </AuthenticatedLayout>
</template>
```

### Admin Dashboard

**File:** `resources/js/Pages/Admin/Dashboard.vue`

**Purpose:** Main admin dashboard with statistics and quick actions.

```vue
<script setup>
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import AdminActionCard from '@/Components/Admin/AdminActionCard.vue';
import StatCard from '@/Components/Admin/StatCard.vue';
import {
    UsersIcon,
    AcademicCapIcon,
    BookOpenIcon,
    CalendarIcon,
} from '@heroicons/vue/24/outline';

defineProps({
    totalUsers: Number,
    totalStudents: Number,
    totalCourses: Number,
    totalTeachers: Number,
});
</script>

<template>
    <Head title="Admin Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2>Admin Dashboard</h2>
        </template>

        <div class="py-12">
            <!-- Statistics -->
            <div class="mb-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                <StatCard
                    title="Total Users"
                    :value="totalUsers"
                    :icon="UsersIcon"
                />
                <StatCard
                    title="Total Students"
                    :value="totalStudents"
                    :icon="AcademicCapIcon"
                />
                <StatCard
                    title="Total Courses"
                    :value="totalCourses"
                    :icon="BookOpenIcon"
                />
                <StatCard
                    title="Total Teachers"
                    :value="totalTeachers"
                    :icon="CalendarIcon"
                />
            </div>

            <!-- Quick Actions -->
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                <AdminActionCard
                    title="Manage Students"
                    description="View and manage student records"
                    href="/admin/students"
                    :icon="AcademicCapIcon"
                    color="primary"
                />
                <!-- More action cards -->
            </div>
        </div>
    </AuthenticatedLayout>
</template>
```

---

### Admin Pages (Phase 1 & 2)

All admin pages follow a consistent pattern:
- Responsive design (desktop table + mobile cards)
- Dark mode support
- Pagination for large datasets
- Status badges with color coding
- Search and filter capabilities

#### Students Index

**File:** `resources/js/Pages/Admin/Students/Index.vue`

**Purpose:** List and manage all students with search and filtering.

**Features:**
- Paginated student list
- Status badges (active, inactive, graduated, withdrawn, suspended)
- Desktop: table view with sortable columns
- Mobile: card view with touch-friendly buttons
- Search by name, student ID, or email
- Filter by status

**Props:**
- `students` (Object) - Paginated student collection with metadata

#### Teachers Index

**File:** `resources/js/Pages/Admin/Teachers/Index.vue`

**Purpose:** List and manage all teachers.

**Features:**
- Teacher directory with department info
- Status badges (active, inactive, on_leave)
- Auto-generated teacher IDs display (TCH-YYYY-###)
- Search by name or department
- Filter by status

**Props:**
- `teachers` (Object) - Paginated teacher collection

#### Courses Index

**File:** `resources/js/Pages/Admin/Courses/Index.vue`

**Purpose:** Course catalog management.

**Features:**
- Course listings with codes and credits
- Department and level organization
- Active/inactive status
- Search by course name or code
- Filter by department

**Props:**
- `courses` (Object) - Paginated course collection

#### Classes Index (Phase 2)

**File:** `resources/js/Pages/Admin/Classes/Index.vue`

**Purpose:** Manage class sections with schedules and enrollment.

**Features:**
- Class sections with course and teacher info
- Term and room information
- Status badges (open, closed, in_progress, completed)
- Schedule display
- Enrollment count vs capacity
- Filter by term, course, teacher, status
- Search across multiple fields

**Props:**
- `classes` (Object) - Paginated class collection with relationships
- `terms` (Array) - Available terms for filtering
- `courses` (Array) - Available courses for filtering
- `teachers` (Array) - Available teachers for filtering
- `filters` (Object) - Current filter values

**Relationships loaded:**
- course (name, code)
- teacher (name)
- term (name, academic year)

#### Academic Years Index

**File:** `resources/js/Pages/Admin/AcademicYears/Index.vue`

**Purpose:** Manage academic years and their terms.

**Features:**
- Academic year cards (not table layout)
- Current year badge
- Nested term display with current term badge
- Term date ranges
- Action buttons for edit and set current

**Props:**
- `academic_years` (Object) - Paginated academic years with terms

**Layout:**
Each academic year is displayed as a card containing:
- Year name and date range
- "Current" badge if active
- List of terms with dates and current status
- Edit and "Set Current" action buttons

---

### Welcome Page

**File:** `resources/js/Pages/Welcome.vue`

**Purpose:** Landing page with login functionality.

```vue
<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { useTheme } from '@/composables/useTheme';
import TextInput from '@/Components/TextInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

// Initialize theme
useTheme();

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Head title="Welcome" />

    <!-- Two-column layout: Logos + Features | Login Form -->
    <div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100">
        <!-- Header with logos -->
        <!-- Login form -->
    </div>
</template>
```

---

## Layouts

### AuthenticatedLayout.vue

**Purpose:** Main layout for authenticated pages with navigation and sidebar.

**File:** `resources/js/Layouts/AuthenticatedLayout.vue`

**Features:**
- Top navigation bar
- Responsive mobile menu
- User dropdown menu
- Dark mode toggle
- Slot for page header
- Slot for page content

**Structure:**
```vue
<script setup>
import { ref } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import NavLink from '@/Components/NavLink.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';

const showingNavigationDropdown = ref(false);
const page = usePage();
</script>

<template>
    <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
        <!-- Navigation -->
        <nav class="border-b border-gray-100 bg-white dark:border-gray-700 dark:bg-gray-800">
            <!-- Desktop Navigation -->
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex h-16 justify-between">
                    <!-- Logo and links -->
                    <div class="flex">
                        <Link href="/" class="flex items-center">
                            <ApplicationLogo class="h-9 w-auto" />
                        </Link>

                        <!-- Navigation Links -->
                        <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                            <NavLink :href="route('admin.dashboard')" :active="route().current('admin.dashboard')">
                                Dashboard
                            </NavLink>
                            <NavLink :href="route('admin.users.index')" :active="route().current('admin.users.*')">
                                Users
                            </NavLink>
                            <!-- More nav links -->
                        </div>
                    </div>

                    <!-- User dropdown -->
                    <Dropdown align="right" width="48">
                        <template #trigger>
                            <button>{{ page.props.auth.user.name }}</button>
                        </template>

                        <template #content>
                            <DropdownLink :href="route('profile.edit')">Profile</DropdownLink>
                            <DropdownLink :href="route('logout')" method="post">Log Out</DropdownLink>
                        </template>
                    </Dropdown>
                </div>
            </div>

            <!-- Mobile Navigation -->
            <!-- ... -->
        </nav>

        <!-- Page Header -->
        <header v-if="$slots.header" class="bg-white shadow dark:bg-gray-800">
            <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                <slot name="header" />
            </div>
        </header>

        <!-- Page Content -->
        <main>
            <slot />
        </main>
    </div>
</template>
```

**Usage:**
```vue
<template>
    <AuthenticatedLayout>
        <template #header>
            <h2>Page Title</h2>
        </template>

        <div class="py-12">
            <!-- Page content -->
        </div>
    </AuthenticatedLayout>
</template>
```

### GuestLayout.vue

**Purpose:** Layout for unauthenticated pages (login, register, etc.).

**Features:**
- Centered content
- Logo display
- Minimal styling

---

## Composables

### useTheme

**Purpose:** Manage theme colors and apply CSS variables.

**File:** `resources/js/composables/useTheme.js`

```javascript
import { ref, onMounted } from 'vue';
import axios from 'axios';

export function useTheme() {
    const theme = ref({
        primary_color: '#1B3A6B',   // Navy Blue - GYCA
        secondary_color: '#FFB81C',  // Gold - GYCA
        accent_color: '#C8102E',     // Red - GYCA
        background_color: '#ffffff',
        text_color: '#1f2937',
    });

    const loadTheme = async () => {
        try {
            const response = await axios.get('/api/theme');
            theme.value = response.data;
            applyTheme();
        } catch (error) {
            console.error('Failed to load theme:', error);
            applyTheme(); // Apply default theme
        }
    };

    const hexToRgb = (hex) => {
        const result = /^#?([a-f\\d]{2})([a-f\\d]{2})([a-f\\d]{2})$/i.exec(hex);
        if (result) {
            return `${parseInt(result[1], 16)} ${parseInt(result[2], 16)} ${parseInt(result[3], 16)}`;
        }
        return '99 102 241'; // fallback
    };

    const applyTheme = () => {
        const root = document.documentElement;
        // Set RGB values for Tailwind opacity support
        root.style.setProperty('--color-primary-rgb', hexToRgb(theme.value.primary_color));
        root.style.setProperty('--color-secondary-rgb', hexToRgb(theme.value.secondary_color));
        root.style.setProperty('--color-accent-rgb', hexToRgb(theme.value.accent_color));
        // Keep hex values for backward compatibility
        root.style.setProperty('--color-primary', theme.value.primary_color);
        root.style.setProperty('--color-secondary', theme.value.secondary_color);
        root.style.setProperty('--color-accent', theme.value.accent_color);
        root.style.setProperty('--color-background', theme.value.background_color);
        root.style.setProperty('--color-text', theme.value.text_color);
    };

    onMounted(() => {
        loadTheme();
    });

    return {
        theme,
        loadTheme,
        applyTheme,
    };
}
```

**Usage:**
```vue
<script setup>
import { useTheme } from '@/composables/useTheme';

// Initialize theme on component mount
useTheme();
</script>
```

---

## Styling System

### Tailwind Configuration

**File:** `tailwind.config.js`

```javascript
import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                // Theme colors with RGB for opacity support
                primary: {
                    DEFAULT: 'rgb(var(--color-primary-rgb) / <alpha-value>)',
                    50: 'rgb(var(--color-primary-rgb) / 0.05)',
                    100: 'rgb(var(--color-primary-rgb) / 0.1)',
                    200: 'rgb(var(--color-primary-rgb) / 0.2)',
                    300: 'rgb(var(--color-primary-rgb) / 0.3)',
                    400: 'rgb(var(--color-primary-rgb) / 0.4)',
                    500: 'rgb(var(--color-primary-rgb) / 0.5)',
                    600: 'rgb(var(--color-primary-rgb) / 1)',
                    700: 'rgb(var(--color-primary-rgb) / 0.9)',
                    800: 'rgb(var(--color-primary-rgb) / 0.8)',
                    900: 'rgb(var(--color-primary-rgb) / 0.7)',
                },
                secondary: {
                    DEFAULT: 'rgb(var(--color-secondary-rgb) / <alpha-value>)',
                    50: 'rgb(var(--color-secondary-rgb) / 0.05)',
                    100: 'rgb(var(--color-secondary-rgb) / 0.1)',
                    600: 'rgb(var(--color-secondary-rgb) / 1)',
                    700: 'rgb(var(--color-secondary-rgb) / 0.9)',
                },
                accent: {
                    DEFAULT: 'rgb(var(--color-accent-rgb) / <alpha-value>)',
                    50: 'rgb(var(--color-accent-rgb) / 0.05)',
                    100: 'rgb(var(--color-accent-rgb) / 0.1)',
                    600: 'rgb(var(--color-accent-rgb) / 1)',
                    700: 'rgb(var(--color-accent-rgb) / 0.9)',
                },
            },
        },
    },

    plugins: [forms],
};
```

**Using Theme Colors:**
```vue
<!-- Primary color -->
<button class="bg-primary-600 hover:bg-primary-700 text-white">
    Save
</button>

<!-- With opacity -->
<div class="bg-primary-500/20 border border-primary-600">
    Alert
</div>

<!-- Dark mode variant -->
<button class="bg-primary-600 dark:bg-primary-500">
    Button
</button>
```

### Global Styles

**File:** `resources/css/app.css`

```css
@tailwind base;
@tailwind components;
@tailwind utilities;

/* Custom scrollbar (optional) */
@layer utilities {
    .scrollbar-thin {
        scrollbar-width: thin;
    }
}
```

---

## State Management

### Inertia Shared State

Data shared globally across all pages via `HandleInertiaRequests` middleware:

**Accessing Shared Data:**
```vue
<script setup>
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

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

### Local Component State

Use Vue's `ref` and `computed`:

```vue
<script setup>
import { ref, computed } from 'vue';

const count = ref(0);
const doubleCount = computed(() => count.value * 2);

const increment = () => {
    count.value++;
};
</script>

<template>
    <div>
        <p>Count: {{ count }}</p>
        <p>Double: {{ doubleCount }}</p>
        <button @click="increment">Increment</button>
    </div>
</template>
```

### Pinia (Future)

For complex state management, consider Pinia:

```bash
npm install pinia
```

```javascript
// stores/student.js
import { defineStore } from 'pinia';

export const useStudentStore = defineStore('student', {
    state: () => ({
        students: [],
        selectedStudent: null,
    }),
    actions: {
        async fetchStudents() {
            // ...
        },
    },
});
```

---

## Form Handling

### Using Inertia Forms

**Basic Form:**
```vue
<script setup>
import { useForm } from '@inertiajs/vue3';

const form = useForm({
    first_name: '',
    last_name: '',
    email: '',
});

const submit = () => {
    form.post('/admin/students');
};
</script>

<template>
    <form @submit.prevent="submit">
        <div>
            <InputLabel for="first_name" value="First Name" />
            <TextInput
                id="first_name"
                v-model="form.first_name"
                type="text"
                required
            />
            <InputError :message="form.errors.first_name" />
        </div>

        <PrimaryButton :disabled="form.processing">
            Save
        </PrimaryButton>
    </form>
</template>
```

**File Upload:**
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
        <input type="file" @change="handleFileChange" accept="image/*" />

        <div v-if="form.progress">
            Uploading: {{ form.progress.percentage }}%
        </div>

        <PrimaryButton :disabled="form.processing">
            Upload
        </PrimaryButton>
    </form>
</template>
```

---

## Routing

### Inertia Links

**Basic Navigation:**
```vue
<Link href="/admin/students">View Students</Link>
```

**With Method:**
```vue
<Link href="/admin/students/1" method="delete" as="button">
    Delete
</Link>
```

**Programmatic Navigation:**
```vue
<script setup>
import { router } from '@inertiajs/vue3';

const goToStudents = () => {
    router.visit('/admin/students');
};

const createStudent = () => {
    router.post('/admin/students', {
        first_name: 'John',
        last_name: 'Doe',
    });
};
</script>
```

---

## Dark Mode

Dark mode is implemented using Tailwind's `dark:` variant class.

**Toggle Dark Mode:**
```vue
<script setup>
import { ref, onMounted } from 'vue';

const darkMode = ref(false);

const toggleDarkMode = () => {
    darkMode.value = !darkMode.value;
    if (darkMode.value) {
        document.documentElement.classList.add('dark');
        localStorage.setItem('darkMode', 'enabled');
    } else {
        document.documentElement.classList.remove('dark');
        localStorage.setItem('darkMode', null);
    }
};

onMounted(() => {
    const savedMode = localStorage.getItem('darkMode');
    if (savedMode === 'enabled') {
        darkMode.value = true;
        document.documentElement.classList.add('dark');
    }
});
</script>

<template>
    <button @click="toggleDarkMode">
        {{ darkMode ? 'Light Mode' : 'Dark Mode' }}
    </button>
</template>
```

**Dark Mode Classes:**
```vue
<!-- Background -->
<div class="bg-white dark:bg-gray-800">

<!-- Text -->
<p class="text-gray-900 dark:text-white">

<!-- Border -->
<div class="border-gray-200 dark:border-gray-700">
```

---

## Responsive Design

### Mobile-First Approach

Tailwind uses mobile-first breakpoints:

```vue
<!-- Stack on mobile, grid on larger screens -->
<div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
    <!-- Cards -->
</div>

<!-- Hidden on mobile, visible on desktop -->
<div class="hidden sm:block">
    Desktop menu
</div>

<!-- Visible on mobile, hidden on desktop -->
<div class="block sm:hidden">
    Mobile menu
</div>
```

### Breakpoints

| Prefix | Min Width |
|--------|-----------|
| `sm:` | 640px |
| `md:` | 768px |
| `lg:` | 1024px |
| `xl:` | 1280px |
| `2xl:` | 1536px |

---

## Best Practices

### 1. Component Organization

✅ **DO:**
- One component per file
- Name components with PascalCase
- Use `<script setup>` syntax
- Define props with validation

❌ **DON'T:**
- Create overly large components
- Mix concerns (keep business logic separate)
- Forget to validate props

### 2. Performance

✅ **DO:**
- Use `v-show` for frequently toggled elements
- Use `v-if` for conditionally rendered elements
- Lazy load heavy components
- Use `computed` for derived state

❌ **DON'T:**
- Use inline functions in templates
- Perform expensive operations in templates
- Forget to cleanup event listeners

### 3. Accessibility

✅ **DO:**
- Use semantic HTML elements
- Add ARIA labels where needed
- Ensure keyboard navigation works
- Test with screen readers

### 4. Styling

✅ **DO:**
- Use theme colors (`bg-primary-600`)
- Add dark mode variants (`dark:bg-gray-800`)
- Use Tailwind utilities
- Keep styles consistent

❌ **DON'T:**
- Use hardcoded colors (except semantic colors like red for danger)
- Write custom CSS unless absolutely necessary
- Forget responsive variants

---

**© 2026 Brian K. Rich. All rights reserved.**
