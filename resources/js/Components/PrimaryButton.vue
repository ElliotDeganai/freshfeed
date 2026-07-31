<template>
    <button :type="type" class="ff-btn-primary" :disabled="disabled">
        <slot />
    </button>
</template>

<script>
export default {
    props: {
        type: { type: String, default: 'submit' },
        disabled: { type: Boolean, default: false },
    },
};
</script>

<style scoped>
.ff-btn-primary {
    background: #1D9E75; color: #fff; border: none; border-radius: 20px;
    padding: 10px 20px; font-size: 13.5px; font-weight: 500; cursor: pointer;
    display: inline-flex; align-items: center; justify-content: center; gap: 6px;
    width: 100%;
}
.ff-btn-primary:hover { background: #178563; }
.ff-btn-primary:disabled { opacity: .6; cursor: default; }
</style>
