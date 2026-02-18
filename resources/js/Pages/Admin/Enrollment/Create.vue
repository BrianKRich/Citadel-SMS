<template>
    <div>
        <h1>Create Enrollment</h1>
    </div>
</template>

<script setup>
defineProps({
    students: Array,
    terms: Array,
    classes: Array,
    selectedTermId: [Number, String, null],
});
</script>
