<template>
    <div>
        <h1>Edit Student</h1>
        <pre>{{ student }}</pre>
    </div>
</template>

<script setup>
defineProps({
    student: Object,
});
</script>
