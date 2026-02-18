<template>
    <div>
        <h1>Edit Employee</h1>
        <pre>{{ employee }}</pre>
    </div>
</template>

<script setup>
defineProps({
    employee: Object,
    departments: Array,
});
</script>
