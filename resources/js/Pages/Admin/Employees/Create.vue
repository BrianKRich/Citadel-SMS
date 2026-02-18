<template>
    <div>
        <h1>Create Employee</h1>
        <pre>{{ departments }}</pre>
    </div>
</template>

<script setup>
defineProps({
    departments: Array,
});
</script>
