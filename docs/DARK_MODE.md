# Dark Mode

## Overview

The application now supports dark mode with a toggle button in the navigation bar. The theme preference is saved to localStorage and persists across sessions.

## Features

- **System Preference Detection**: Automatically detects the user's system dark mode preference on first visit
- **Persistent Storage**: User preference is saved to localStorage
- **Seamless Toggle**: Switch between light and dark mode with a single click
- **Responsive Icons**: Sun icon in dark mode, moon icon in light mode
- **Comprehensive Coverage**: All main components styled for both modes

## User Interface

### Toggle Button Location
- **Desktop**: Located in the top navigation bar, between the navigation links and user dropdown
- **Icon**:
  - Moon icon (🌙) when in light mode - click to enable dark mode
  - Sun icon (☀️) when in dark mode - click to disable dark mode

## Technical Implementation

### 1. Tailwind Configuration

Dark mode enabled using class-based strategy in `tailwind.config.js`:

```javascript
export default {
    darkMode: 'class',
    // ... rest of config
};
```

### 2. Dark Mode Composable

`resources/js/composables/useDarkMode.js` provides:

```javascript
const { isDark, toggle, setDark } = useDarkMode();
```

**Features:**
- Reactive dark mode state
- Automatic DOM class management (adds/removes `dark` class on `<html>` element)
- localStorage persistence
- System preference detection on first load

### 3. Components

**`DarkModeToggle.vue`**
- Located in `resources/js/Components/UI/DarkModeToggle.vue`
- Simple button with sun/moon SVG icons
- Uses composable for state management

## Dark Mode Styling

### Components Updated

All following components support dark mode:

**Layouts:**
- `AuthenticatedLayout.vue` - Main layout wrapper

**Admin Components:**
- `StatCard.vue` - Statistics cards
- `AdminActionCard.vue` - Action/navigation cards

**UI Components:**
- `PageHeader.vue` - Page title sections
- `DarkModeToggle.vue` - Toggle button

**Pages:**
- `Admin/Dashboard.vue` - Admin dashboard

### Color Scheme

**Light Mode:**
- Background: `bg-gray-100`
- Cards/Panels: `bg-white`
- Text Primary: `text-gray-900`
- Text Secondary: `text-gray-500`
- Borders: `border-gray-100`

**Dark Mode:**
- Background: `dark:bg-gray-900`
- Cards/Panels: `dark:bg-gray-800`
- Text Primary: `dark:text-gray-100`
- Text Secondary: `dark:text-gray-400`
- Borders: `dark:border-gray-700`

## Adding Dark Mode to New Components

When creating new components, add dark mode variants using Tailwind's `dark:` prefix:

### Example Pattern

```vue
<template>
    <!-- Card with dark mode -->
    <div class="bg-white shadow dark:bg-gray-800">
        <!-- Primary text -->
        <h2 class="text-gray-900 dark:text-gray-100">
            Title
        </h2>

        <!-- Secondary text -->
        <p class="text-gray-600 dark:text-gray-400">
            Description
        </p>

        <!-- Borders -->
        <div class="border-gray-200 dark:border-gray-700">
            Content
        </div>
    </div>
</template>
```

### Common Patterns

**Backgrounds:**
```html
bg-white dark:bg-gray-800
bg-gray-100 dark:bg-gray-900
bg-gray-50 dark:bg-gray-800
```

**Text:**
```html
text-gray-900 dark:text-gray-100
text-gray-600 dark:text-gray-400
text-gray-500 dark:text-gray-400
```

**Borders:**
```html
border-gray-200 dark:border-gray-700
border-gray-300 dark:border-gray-600
```

**Hover States:**
```html
hover:bg-gray-100 dark:hover:bg-gray-700
hover:text-gray-700 dark:hover:text-gray-300
```

**Color Accents:**
```html
text-indigo-600 dark:text-indigo-400
text-green-600 dark:text-green-400
bg-indigo-100 dark:bg-indigo-900
```

## Browser Compatibility

Works in all modern browsers that support:
- CSS `prefers-color-scheme` media query
- localStorage API
- CSS custom properties

## Future Enhancements

Potential improvements:

- [ ] Smooth transition animations between modes
- [ ] Separate dark mode color theme customization
- [ ] Automatic time-based theme switching (light during day, dark at night)
- [ ] Different dark mode variants (true black for OLED, gray for LCD)
- [ ] Image/logo variants for dark mode
- [ ] Code syntax highlighting dark mode support

## Testing

### Manual Testing Checklist

- [ ] Toggle switches between light and dark modes
- [ ] Preference persists after page reload
- [ ] Works on desktop and mobile views
- [ ] All text is readable in both modes
- [ ] Icons and graphics are visible in both modes
- [ ] Hover states work correctly in both modes
- [ ] Buttons and links have proper contrast

### Testing in Browser

1. Open the application
2. Click the moon/sun icon in the navigation
3. Verify all components update colors
4. Refresh the page - preference should persist
5. Check browser DevTools localStorage for `darkMode` key

## Files Modified

```
tailwind.config.js
resources/js/composables/useDarkMode.js (new)
resources/js/Components/UI/DarkModeToggle.vue (new)
resources/js/Layouts/AuthenticatedLayout.vue
resources/js/Pages/Admin/Dashboard.vue
resources/js/Components/Admin/StatCard.vue
resources/js/Components/Admin/AdminActionCard.vue
resources/js/Components/UI/PageHeader.vue
```

## Troubleshooting

### Dark mode not toggling
- Check browser console for JavaScript errors
- Verify localStorage is enabled in browser
- Clear browser cache and reload

### Colors not changing
- Ensure Tailwind CSS is rebuilt: `npm run dev`
- Check that `darkMode: 'class'` is in tailwind.config.js
- Verify component has `dark:` variant classes

### Preference not persisting
- Check browser localStorage (DevTools → Application → Local Storage)
- Ensure cookies/storage are not blocked
- Try a different browser to rule out browser-specific issues

## Resources

- [Tailwind CSS Dark Mode](https://tailwindcss.com/docs/dark-mode)
- [Vue 3 Composables](https://vuejs.org/guide/reusability/composables.html)
- [MDN: prefers-color-scheme](https://developer.mozilla.org/en-US/docs/Web/CSS/@media/prefers-color-scheme)
