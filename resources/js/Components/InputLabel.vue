<template>
    <label class="ff-label">
        <slot>{{ value }}</slot>
    </label>
</template>

<script>
export default {
    props: { value: String },
};
</script>

<style scoped>
.ff-label { display: block; font-size: 12.5px; font-weight: 500; color: #4B5A54; margin-bottom: 6px; }
</style>
