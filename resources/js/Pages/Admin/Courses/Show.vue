<template>
    <div>
        <h1>Course Detail</h1>
        <pre>{{ course }}</pre>
    </div>
</template>

<script setup>
defineProps({
    course: Object,
});
</script>
