<template>
    <div>
        <h1>Edit Course</h1>
        <pre>{{ course }}</pre>
    </div>
</template>

<script setup>
defineProps({
    course: Object,
});
</script>
