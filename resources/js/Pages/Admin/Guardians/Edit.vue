<template>
    <div>
        <h1>Edit Guardian</h1>
        <pre>{{ guardian }}</pre>
    </div>
</template>

<script setup>
defineProps({
    guardian: Object,
    students: Array,
});
</script>
