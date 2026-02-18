<template>
    <div>
        <h1>Student Schedule</h1>
        <pre>{{ student }}</pre>
    </div>
</template>

<script setup>
defineProps({
    student: Object,
    enrollments: Array,
});
</script>
