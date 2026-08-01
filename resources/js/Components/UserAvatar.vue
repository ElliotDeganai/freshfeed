<template>
    <component :is="linkable && user?.id ? Link : 'div'" :href="linkable && user?.id ? route('users.show', user.id) : undefined" class="user-avatar-wrap">
        <img v-if="user?.avatar_path" :src="`/storage/${user.avatar_path}`" :alt="user.name" class="user-avatar-img" :style="sizeStyle" />
        <div v-else class="user-avatar-fallback" :style="{ ...sizeStyle, background: color.bg, color: color.text, fontSize: fontSize }">
            {{ initials(user?.name) }}
        </div>
    </component>
</template>

<script>
import { Link } from '@inertiajs/vue3';
import { avatarColor, initials } from '@/Components/Admin/avatarPalette.js';

export default {
    components: { Link },
    props: {
        user: { type: Object, default: null }, // { id, name, avatar_path }
        size: { type: Number, default: 32 },
        linkable: { type: Boolean, default: false }, // true = clique vers /u/{id}
    },
    computed: {
        Link() {
            return Link;
        },
        color() {
            return avatarColor(this.user?.id);
        },
        sizeStyle() {
            return { width: `${this.size}px`, height: `${this.size}px` };
        },
        fontSize() {
            return `${Math.max(10, Math.round(this.size * 0.36))}px`;
        },
    },
    methods: { initials },
};
</script>

<style scoped>
.user-avatar-wrap { display: inline-flex; text-decoration: none; }
.user-avatar-img { border-radius: 50%; object-fit: cover; display: block; }
.user-avatar-fallback { border-radius: 50%; font-weight: 500; display: flex; align-items: center; justify-content: center; }
</style>
