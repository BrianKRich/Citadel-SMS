<template>
    <div>
        <h1>Edit Academic Year</h1>
        <pre>{{ academicYear }}</pre>
    </div>
</template>

<script setup>
defineProps({
    academicYear: Object,
});
</script>
