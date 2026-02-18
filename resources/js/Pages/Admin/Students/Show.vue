<template>
    <div>
        <h1>Student Detail</h1>
        <pre>{{ student }}</pre>
    </div>
</template>

<script setup>
defineProps({
    student: Object,
});
</script>
