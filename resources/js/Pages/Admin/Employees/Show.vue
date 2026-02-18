<template>
    <div>
        <h1>Employee Detail</h1>
        <pre>{{ employee }}</pre>
    </div>
</template>

<script setup>
defineProps({
    employee: Object,
});
</script>
