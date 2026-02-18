<template>
    <div>
        <h1>Guardian Detail</h1>
        <pre>{{ guardian }}</pre>
    </div>
</template>

<script setup>
defineProps({
    guardian: Object,
});
</script>
