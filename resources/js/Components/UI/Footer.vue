<script setup>
const currentYear = new Date().getFullYear();
</script>

<template>
    <footer class="border-t border-gray-200 bg-white py-6 dark:border-gray-700 dark:bg-gray-800">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <p class="text-center text-sm text-gray-600 dark:text-gray-400">
                &copy; {{ currentYear }} Brian K. Rich. All rights reserved.
            </p>
        </div>
    </footer>
</template>
