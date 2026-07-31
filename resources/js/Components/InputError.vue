<template>
    <p v-show="message" class="ff-error">{{ message }}</p>
</template>

<script>
export default {
    props: { message: String },
};
</script>

<style scoped>
.ff-error { font-size: 12px; color: #B3261E; margin-top: 5px; }
</style>
