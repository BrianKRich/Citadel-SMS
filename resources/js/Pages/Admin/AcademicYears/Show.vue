<template>
    <div>
        <h1>Academic Year Detail</h1>
        <pre>{{ academicYear }}</pre>
    </div>
</template>

<script setup>
defineProps({
    academicYear: Object,
});
</script>
