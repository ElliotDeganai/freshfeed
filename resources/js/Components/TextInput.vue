<template>
    <input
        :value="modelValue"
        @input="$emit('update:modelValue', $event.target.value)"
        ref="input"
        class="ff-input"
    />
</template>

<script>
export default {
    props: { modelValue: String },
    emits: ['update:modelValue'],
    mounted() {
        if (this.$refs.input.hasAttribute('autofocus')) {
            this.$refs.input.focus();
        }
    },
    methods: {
        focus() {
            this.$refs.input.focus();
        },
    },
};
</script>

<style scoped>
.ff-input {
    width: 100%; border: 0.5px solid #D9DDD9; border-radius: 10px;
    padding: 10px 13px; font-size: 13.5px; background: #fff; font-family: inherit;
    color: #10241D;
}
.ff-input:focus {
    outline: none; border-color: #1D9E75; box-shadow: 0 0 0 3px rgba(29, 158, 117, 0.12);
}
</style>
