<template>
    <div>
        <h1>Guardians</h1>
        <pre>{{ guardians }}</pre>
    </div>
</template>

<script setup>
defineProps({
    guardians: Object,
});
</script>
