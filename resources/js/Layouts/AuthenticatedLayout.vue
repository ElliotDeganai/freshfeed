<template>
    <div class="auth-shell">
        <header class="auth-topbar">
            <Link href="/feed" class="brand"><span class="brand-logo">FreshFeed</span></Link>
            <div class="auth-topbar-icons">
                <div class="avatar" :style="{ background: userColor.bg, color: userColor.text }">
                    {{ userInitials }}
                </div>
                <Link :href="route('logout')" method="post" as="button" class="icon-btn" title="Déconnexion">
                    <i class="ti ti-logout"></i>
                </Link>
            </div>
        </header>

        <div v-if="$slots.header" class="auth-page-head">
            <slot name="header" />
        </div>

        <main class="auth-content">
            <slot />
        </main>
    </div>
</template>

<script>
import { Link } from '@inertiajs/vue3';
import { avatarColor, initials } from '@/Components/Admin/avatarPalette.js';

export default {
    components: { Link },
    computed: {
        userInitials() {
            return initials(this.$page.props.auth.user.name);
        },
        userColor() {
            return avatarColor(this.$page.props.auth.user.id);
        },
    },
};
</script>

<style scoped>
.auth-shell { min-height: 100vh; background: #F7F8F6; }
.auth-topbar {
    background: #fff; border-bottom: 0.5px solid #E7E9E7;
    display: flex; align-items: center; justify-content: space-between;
    padding: 0 28px; height: 60px;
}
.brand { text-decoration: none; }
.brand-logo { font-size: 16px; font-weight: 500; color: #1D9E75; letter-spacing: -0.3px; }
.auth-topbar-icons { display: flex; align-items: center; gap: 12px; }
.avatar {
    width: 32px; height: 32px; border-radius: 50%; font-size: 12px; font-weight: 500;
    display: flex; align-items: center; justify-content: center;
}
.icon-btn {
    width: 32px; height: 32px; border-radius: 50%; border: none; background: transparent;
    display: flex; align-items: center; justify-content: center; color: #6B7B74; font-size: 16px; cursor: pointer;
}
.icon-btn:hover { background: #F0F1F0; }

.auth-page-head { max-width: 900px; margin: 0 auto; padding: 24px 28px 0; }
.auth-page-head :deep(h2) { font-size: 18px; font-weight: 500; color: #10241D; }

.auth-content { max-width: 900px; margin: 0 auto; padding: 20px 28px 60px; }
.auth-content :deep(.bg-white) { background: #fff !important; border: 0.5px solid #E7E9E7; border-radius: 16px !important; box-shadow: none !important; }
</style>
