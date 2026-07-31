<template>
    <button :type="type" class="ff-btn-secondary" :disabled="disabled">
        <slot />
    </button>
</template>

<script>
export default {
    props: {
        type: { type: String, default: 'button' },
        disabled: { type: Boolean, default: false },
    },
};
</script>

<style scoped>
.ff-btn-secondary {
    background: transparent; color: #6B7B74; border: 0.5px solid #D9DDD9; border-radius: 20px;
    padding: 10px 20px; font-size: 13.5px; cursor: pointer;
    display: inline-flex; align-items: center; justify-content: center; gap: 6px;
}
.ff-btn-secondary:hover { background: #F0F1F0; }
.ff-btn-secondary:disabled { opacity: .6; cursor: default; }
</style>
