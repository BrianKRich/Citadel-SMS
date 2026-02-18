<template>
    <div>
        <h1>Create Guardian</h1>
        <pre>{{ students }}</pre>
    </div>
</template>

<script setup>
defineProps({
    students: Array,
});
</script>
