<template>
    <div>
        <h1>Enrollments</h1>
        <pre>{{ enrollments }}</pre>
    </div>
</template>

<script setup>
defineProps({
    enrollments: Object,
    students: Array,
    terms: Array,
    filters: Object,
});
</script>
