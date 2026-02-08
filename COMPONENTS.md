# Vue Component Architecture Guide

This document outlines the component organization and best practices for the Citadel SMS project.

## 📁 Component Directory Structure

```
resources/js/Components/
├── Form/              # Form-related components
│   └── ColorPicker.vue
├── UI/                # General UI components
│   ├── Card.vue
│   └── PageHeader.vue
├── Theme/             # Theme-specific components
│   └── ThemePreview.vue
└── [Base Components]  # Laravel Breeze components
    ├── TextInput.vue
    ├── Checkbox.vue
    ├── InputLabel.vue
    ├── InputError.vue
    ├── PrimaryButton.vue
    ├── SecondaryButton.vue
    ├── DangerButton.vue
    ├── Modal.vue
    ├── Dropdown.vue
    ├── DropdownLink.vue
    ├── NavLink.vue
    └── ResponsiveNavLink.vue
```

## 🎯 Component Categories

### 1. **Form Components** (`/Form`)
Reusable form inputs and controls.

**Examples:**
- `ColorPicker.vue` - Color input with hex preview
- Future: `DatePicker.vue`, `PhoneInput.vue`, `RichTextEditor.vue`

**Usage:**
```vue
<ColorPicker
    v-model="form.color"
    label="Primary Color"
    :error="form.errors.color"
/>
```

### 2. **UI Components** (`/UI`)
General-purpose interface components.

**Examples:**
- `Card.vue` - Container with shadow and padding
- `PageHeader.vue` - Consistent page titles
- Future: `Badge.vue`, `Alert.vue`, `Tooltip.vue`, `Table.vue`

**Usage:**
```vue
<Card>
    <PageHeader
        title="Settings"
        description="Manage your preferences"
    />
    <!-- Content -->
</Card>
```

### 3. **Feature Components** (`/Theme`, `/Students`, etc.)
Domain-specific components for features.

**Examples:**
- `Theme/ThemePreview.vue` - Live theme preview
- Future: `Students/StudentList.vue`, `Students/StudentProfile.vue`

### 4. **Base Components** (Root `/Components`)
Foundational components from Laravel Breeze.

## 🏗️ Component Best Practices

### 1. **Single Responsibility**
Each component should do one thing well.

✅ **Good:**
```vue
<!-- ColorPicker.vue - handles color input -->
<!-- ThemePreview.vue - displays theme preview -->
```

❌ **Bad:**
```vue
<!-- ThemeEverything.vue - handles input AND preview AND saving -->
```

### 2. **Props and Events**
Use props for data down, events for data up.

```vue
<script setup>
const props = defineProps({
    modelValue: String,
    label: String,
    error: String,
});

const emit = defineEmits(['update:modelValue']);
</script>
```

### 3. **Composable v-model**
Use computed properties for two-way binding.

```vue
<script setup>
import { computed } from 'vue';

const props = defineProps({ modelValue: String });
const emit = defineEmits(['update:modelValue']);

const value = computed({
    get: () => props.modelValue,
    set: (val) => emit('update:modelValue', val),
});
</script>

<template>
    <input v-model="value" />
</template>
```

### 4. **Slots for Flexibility**
Use slots for customizable content areas.

```vue
<!-- Card.vue -->
<template>
    <div class="card">
        <slot /> <!-- Default content -->
        <slot name="footer" /> <!-- Named slot -->
    </div>
</template>

<!-- Usage -->
<Card>
    <p>Main content</p>
    <template #footer>
        <button>Action</button>
    </template>
</Card>
```

### 5. **TypeScript-style Props**
Define props with types and defaults.

```vue
<script setup>
defineProps({
    title: {
        type: String,
        required: true,
    },
    count: {
        type: Number,
        default: 0,
    },
    items: {
        type: Array,
        default: () => [],
    },
});
</script>
```

### 6. **Scoped Styles** (if needed)
Use scoped styles to prevent CSS leakage.

```vue
<style scoped>
.my-component {
    color: blue;
}
</style>
```

## 📋 Component Creation Checklist

When creating a new component:

- [ ] Place in appropriate directory (`/Form`, `/UI`, feature folder)
- [ ] Use descriptive PascalCase name
- [ ] Define clear props with types
- [ ] Emit events for parent communication
- [ ] Add default values where appropriate
- [ ] Consider if slots are needed
- [ ] Keep template clean and readable
- [ ] Extract complex logic to composables
- [ ] Test with different prop combinations

## 🚀 Future Component Ideas

### Student Feature Components:
- `Students/StudentList.vue` - Display list of students
- `Students/StudentCard.vue` - Individual student profile card
- `Students/StudentProfile.vue` - Detailed student profile
- `Students/EnrollmentForm.vue` - Student enrollment
- `Students/AttendanceTracker.vue` - Attendance management

### General UI Components:
- `UI/Badge.vue` - Status badges
- `UI/Alert.vue` - Alert messages
- `UI/Tooltip.vue` - Hover tooltips
- `UI/Table.vue` - Data tables
- `UI/Pagination.vue` - Page navigation
- `UI/Tabs.vue` - Tab interface
- `UI/EmptyState.vue` - No data placeholder

### Form Components:
- `Form/DatePicker.vue` - Date selection
- `Form/PhoneInput.vue` - Phone number input
- `Form/SearchInput.vue` - Search with icon
- `Form/Select.vue` - Dropdown select
- `Form/Toggle.vue` - Switch toggle

## 💡 Tips

1. **Keep components small** - If a component file is over 200 lines, consider splitting it
2. **Reuse existing components** - Check `/Components` before creating new ones
3. **Consistent naming** - Use PascalCase for component names
4. **Document props** - Add comments for complex prop requirements
5. **Think reusability** - If you use something twice, make it a component

## 📚 Resources

- [Vue 3 Documentation](https://vuejs.org/guide/components/registration.html)
- [Inertia.js Documentation](https://inertiajs.com/)
- [Tailwind CSS](https://tailwindcss.com/)
