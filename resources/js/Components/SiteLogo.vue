<template>
    <img v-if="logo" :src="`/storage/${logo}`" :alt="siteName" class="site-logo-img" :style="sizeStyle" />
    <svg v-else viewBox="0 0 72 72" class="site-logo-fallback" :style="sizeStyle" role="img">
        <title>{{ siteName }}</title>
        <path d="M24 44c-6-2-9-8-7-14 2-5 7-8 11-6 1-6 6-10 12-10s11 4 12 10c4-2 9 1 11 6 2 6-1 12-7 14"
              :fill="markColor" />
        <rect x="23" y="42" width="26" height="14" rx="3" :fill="markColor" />
        <rect x="23" y="52" width="26" height="4" :fill="markShadeColor" />
    </svg>
</template>

<script>
export default {
    props: {
        size: { type: Number, default: 32 },
        dark: { type: Boolean, default: false }, // true = sur fond sombre (topbar admin foncée, etc.)
    },
    computed: {
        logo() {
            return this.$page.props.site?.logo ?? null;
        },
        siteName() {
            return this.$page.props.site?.name ?? 'FreshFeed';
        },
        sizeStyle() {
            return { width: `${this.size}px`, height: `${this.size}px` };
        },
        markColor() {
            return this.dark ? '#4ADE9B' : '#1D9E75';
        },
        markShadeColor() {
            return this.dark ? '#1D9E75' : '#0F6E56';
        },
    },
};
</script>

<style scoped>
.site-logo-img { object-fit: contain; display: block; }
.site-logo-fallback { display: block; }
</style>
