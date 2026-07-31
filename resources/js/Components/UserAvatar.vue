<template>
    <img v-if="user?.avatar_path" :src="`/storage/${user.avatar_path}`" :alt="user.name" class="user-avatar-img" :style="sizeStyle" />
    <div v-else class="user-avatar-fallback" :style="{ ...sizeStyle, background: color.bg, color: color.text, fontSize: fontSize }">
        {{ initials(user?.name) }}
    </div>
</template>

<script>
import { avatarColor, initials } from '@/Components/Admin/avatarPalette.js';

export default {
    props: {
        user: { type: Object, default: null }, // { id, name, avatar_path }
        size: { type: Number, default: 32 },
    },
    computed: {
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
.user-avatar-img { border-radius: 50%; object-fit: cover; display: block; }
.user-avatar-fallback { border-radius: 50%; font-weight: 500; display: flex; align-items: center; justify-content: center; }
</style>
