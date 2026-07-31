<template>
    <Head title="Mon espace" />

    <div class="dash-shell">
        <header class="dash-topbar">
            <Link href="/" class="brand-logo">FreshFeed</Link>
            <div class="dash-topbar-icons">
                <Link v-if="canAccessAdmin" :href="route('admin.dashboard')" class="btn-primary">
                    <i class="ti ti-layout-dashboard"></i> Zone admin
                </Link>
                <div class="avatar" :style="{ background: userColor.bg, color: userColor.text }">
                    {{ userInitials }}
                </div>
                <Link :href="route('logout')" method="post" as="button" class="icon-btn" title="Déconnexion">
                    <i class="ti ti-logout"></i>
                </Link>
            </div>
        </header>

        <main class="dash-content">
            <h1 class="dash-title">Bonjour {{ $page.props.auth.user.name }} 👋</h1>
            <p class="dash-subtitle">Voici ton espace FreshFeed.</p>

            <div class="dash-grid">
                <Link href="/feed" class="dash-card">
                    <div class="dash-card-icon" style="background:#E1F5EE;color:#0F6E56"><i class="ti ti-tools-kitchen-2"></i></div>
                    <div class="dash-card-title">Fil d'actualité</div>
                    <div class="dash-card-text">Découvre les dernières recettes publiées.</div>
                </Link>

                <Link href="/explore" class="dash-card">
                    <div class="dash-card-icon" style="background:#EEEDFE;color:#534AB7"><i class="ti ti-compass"></i></div>
                    <div class="dash-card-title">Explorer</div>
                    <div class="dash-card-text">Parcours les recettes par catégorie.</div>
                </Link>

                <Link v-if="canManagePosts" :href="route('admin.posts.index')" class="dash-card">
                    <div class="dash-card-icon" style="background:#FAEEDA;color:#854F0B"><i class="ti ti-pencil"></i></div>
                    <div class="dash-card-title">Mes recettes</div>
                    <div class="dash-card-text">Gère tes recettes publiées et brouillons.</div>
                </Link>

                <Link href="/profile" class="dash-card">
                    <div class="dash-card-icon" style="background:#FAECE7;color:#993C1D"><i class="ti ti-user"></i></div>
                    <div class="dash-card-title">Mon profil</div>
                    <div class="dash-card-text">Modifie tes informations et ton mot de passe.</div>
                </Link>
            </div>
        </main>
    </div>
</template>

<script>
import { Head, Link } from '@inertiajs/vue3';
import { avatarColor, initials } from '@/Components/Admin/avatarPalette.js';

export default {
    components: { Head, Link },
    computed: {
        userInitials() {
            return initials(this.$page.props.auth.user.name);
        },
        userColor() {
            return avatarColor(this.$page.props.auth.user.id);
        },
        canAccessAdmin() {
            return this.$page.props.auth.permissions?.includes('view-admin') ?? false;
        },
        canManagePosts() {
            return this.$page.props.auth.permissions?.some((p) => ['manage-posts', 'manage-own-posts'].includes(p)) ?? false;
        },
    },
};
</script>

<style scoped>
.dash-shell { min-height: 100vh; background: #F7F8F6; }

.dash-topbar {
    background: #fff; border-bottom: 0.5px solid #E7E9E7;
    display: flex; align-items: center; justify-content: space-between;
    padding: 0 28px; height: 60px;
}
.brand-logo { font-size: 16px; font-weight: 500; color: #1D9E75; letter-spacing: -0.3px; }
.dash-topbar-icons { display: flex; align-items: center; gap: 12px; }
.btn-primary {
    background: #1D9E75; color: #fff; border: none; border-radius: 20px;
    padding: 8px 16px; font-size: 13px; font-weight: 500; text-decoration: none;
    display: inline-flex; align-items: center; gap: 6px;
}
.avatar {
    width: 32px; height: 32px; border-radius: 50%; font-size: 12px; font-weight: 500;
    display: flex; align-items: center; justify-content: center;
}
.icon-btn {
    width: 32px; height: 32px; border-radius: 50%; border: none; background: transparent;
    display: flex; align-items: center; justify-content: center; color: #6B7B74; font-size: 16px; cursor: pointer;
}
.icon-btn:hover { background: #F0F1F0; }

.dash-content { max-width: 900px; margin: 0 auto; padding: 36px 28px; }
.dash-title { font-size: 22px; font-weight: 500; color: #10241D; margin-bottom: 4px; }
.dash-subtitle { font-size: 13.5px; color: #6B7B74; margin-bottom: 28px; }

.dash-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 14px; }
.dash-card {
    background: #fff; border: 0.5px solid #E7E9E7; border-radius: 16px; padding: 20px;
    text-decoration: none; display: flex; flex-direction: column; gap: 10px;
}
.dash-card-icon {
    width: 40px; height: 40px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 18px;
}
.dash-card-title { font-size: 14.5px; font-weight: 500; color: #10241D; }
.dash-card-text { font-size: 12.5px; color: #8FA098; line-height: 1.4; }
</style>
