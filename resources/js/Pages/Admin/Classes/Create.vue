<template>
    <div>
        <h1>Create Class</h1>
        <pre>{{ courses }}</pre>
    </div>
</template>

<script setup>
defineProps({
    courses: Array,
    employees: Array,
    academicYears: Array,
});
</script>
