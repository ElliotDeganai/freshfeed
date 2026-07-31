<template>
    <input
        type="checkbox"
        :checked="checked"
        @change="$emit('update:checked', $event.target.checked)"
        class="ff-checkbox"
    />
</template>

<script>
export default {
    props: {
        checked: { type: [Array, Boolean], default: false },
    },
    emits: ['update:checked'],
};
</script>

<style scoped>
.ff-checkbox { width: 15px; height: 15px; accent-color: #1D9E75; }
</style>
