<template>
    <div class="admin-shell">
        <header class="admin-topbar">
            <div class="admin-topbar-inner">
                <Link href="/" class="admin-brand">
                    <SiteLogo :size="26" />
                    <span class="admin-brand-logo">{{ $page.props.site.name }}</span>
                    <span class="admin-brand-tag">admin</span>
                </Link>

                <nav class="admin-pills">
                    <Link :href="route('admin.dashboard')" class="admin-pill" :class="{ on: isActive('admin.dashboard') }">
                        <i class="ti ti-layout-dashboard"></i> Tableau de bord
                    </Link>
                    <Link v-if="can('manage-posts') || can('manage-own-posts')"
                        :href="route('admin.posts.index')" class="admin-pill" :class="{ on: isActive('admin.posts.*') }">
                        <i class="ti ti-tools-kitchen-2"></i> Recettes
                    </Link>
                    <Link v-if="can('manage-categories')"
                        :href="route('admin.categories.index')" class="admin-pill" :class="{ on: isActive('admin.categories.*') }">
                        <i class="ti ti-tags"></i> Catégories
                    </Link>
                    <Link v-if="can('manage-pages')"
                        :href="route('admin.pages.index')" class="admin-pill" :class="{ on: isActive('admin.pages.*') }">
                        <i class="ti ti-file-stack"></i> Pages
                    </Link>
                    <Link v-if="can('manage-users')"
                        :href="route('admin.users.index')" class="admin-pill" :class="{ on: isActive('admin.users.*') }">
                        <i class="ti ti-users"></i> Utilisateurs
                    </Link>
                    <Link v-if="can('manage-settings')"
                        :href="route('admin.homepage.index')" class="admin-pill" :class="{ on: isActive('admin.homepage.*') }">
                        <i class="ti ti-home"></i> Accueil
                    </Link>

                    <Link v-if="can('manage-settings')"
                        :href="route('admin.settings.index')" class="admin-pill" :class="{ on: isActive('admin.settings.*') }">
                        <i class="ti ti-settings"></i> Paramètres
                    </Link>
                </nav>

                <div class="admin-topbar-icons">
                    <div class="account-menu" ref="accountMenu">
                        <button class="admin-user-avatar-btn" @click="menuOpen = !menuOpen">
                            <UserAvatar :user="$page.props.auth.user" :size="32" />
                        </button>

                        <transition name="menu-fade">
                            <div v-if="menuOpen" class="account-dropdown">
                                <div class="account-dropdown-name">{{ $page.props.auth.user.name }}</div>
                                <Link :href="route('feed')" class="account-dropdown-link">
                                    <i class="ti ti-arrow-back-up"></i> Retour au site
                                </Link>
                                <Link :href="route('logout')" method="post" as="button" class="account-dropdown-link account-dropdown-link--danger">
                                    <i class="ti ti-logout"></i> Déconnexion
                                </Link>
                            </div>
                        </transition>
                    </div>
                </div>
            </div>
        </header>

        <div class="admin-page-head">
            <h1 class="admin-page-title"><slot name="title">Admin</slot></h1>
            <div class="admin-role-chip">{{ primaryRole }}</div>
        </div>

        <transition name="admin-flash">
            <div v-if="$page.props.flash?.success" class="admin-flash admin-flash--success">
                <i class="ti ti-check"></i> {{ $page.props.flash.success }}
            </div>
        </transition>
        <transition name="admin-flash">
            <div v-if="$page.props.flash?.error" class="admin-flash admin-flash--error">
                <i class="ti ti-alert-circle"></i> {{ $page.props.flash.error }}
            </div>
        </transition>

        <main class="admin-content">
            <slot />
        </main>

        <SiteFooter />
    </div>
</template>

<script>
import { Link } from '@inertiajs/vue3';
import SiteLogo from '@/Components/SiteLogo.vue';
import UserAvatar from '@/Components/UserAvatar.vue';
import SiteFooter from '@/Components/SiteFooter.vue';

export default {
    components: { Link, SiteLogo, UserAvatar, SiteFooter },
    data() {
        return {
            menuOpen: false,
        };
    },
    computed: {
        primaryRole() {
            return this.$page.props.auth.user.roles?.[0] ?? '—';
        },
    },
    mounted() {
        document.addEventListener('click', this.onClickOutside);
    },
    beforeUnmount() {
        document.removeEventListener('click', this.onClickOutside);
    },
    methods: {
        can(permission) {
            return this.$page.props.auth.permissions?.includes(permission) ?? false;
        },
        isActive(pattern) {
            const current = route().current();
            if (!current) return false;
            if (pattern.endsWith('*')) {
                return current.startsWith(pattern.slice(0, -1));
            }
            return current === pattern;
        },
        onClickOutside(e) {
            if (this.menuOpen && this.$refs.accountMenu && !this.$refs.accountMenu.contains(e.target)) {
                this.menuOpen = false;
            }
        },
    },
};
</script>

<style scoped>
.admin-shell { min-height: 100vh; background: #F7F8F6; }

.admin-topbar { background: #fff; border-bottom: 0.5px solid #E7E9E7; position: sticky; top: 0; z-index: 10; }
.admin-topbar-inner {
    max-width: 1180px; margin: 0 auto; padding: 0 24px; height: 60px;
    display: flex; align-items: center; gap: 24px;
}
.admin-brand { display: flex; align-items: baseline; gap: 6px; text-decoration: none; }
.admin-brand-logo { font-size: 17px; font-weight: 500; color: #1D9E75; letter-spacing: -0.3px; }
.admin-brand-tag { font-size: 11px; color: #8FA098; text-transform: uppercase; letter-spacing: .04em; }

.admin-pills { display: flex; gap: 4px; flex: 1; overflow-x: auto; }
.admin-pill {
    display: flex; align-items: center; gap: 6px; white-space: nowrap;
    padding: 7px 14px; border-radius: 20px; font-size: 13px; color: #6B7B74;
    text-decoration: none; border: 0.5px solid transparent;
}
.admin-pill i { font-size: 15px; }
.admin-pill:hover { background: #F0F1F0; }
.admin-pill.on { background: #1D9E75; color: #fff; font-weight: 500; }

.admin-topbar-icons { display: flex; align-items: center; gap: 10px; }
.account-menu { position: relative; }
.admin-user-avatar-btn {
    padding: 0; border: none; background: transparent; cursor: pointer; display: flex; border-radius: 50%;
}
.account-dropdown {
    position: absolute; top: 42px; right: 0; background: #fff; border: 0.5px solid #E7E9E7;
    border-radius: 14px; padding: 6px; width: 200px; box-shadow: 0 10px 26px rgba(16,36,29,.12); z-index: 20;
}
.account-dropdown-name {
    font-size: 12.5px; font-weight: 500; color: #10241D; padding: 8px 12px 6px;
    border-bottom: 0.5px solid #F0F1F0; margin-bottom: 4px;
}
.account-dropdown-link {
    display: flex; align-items: center; gap: 9px; padding: 9px 12px; border-radius: 9px;
    font-size: 13px; color: #4B5A54; text-decoration: none; width: 100%; background: none; border: none;
    cursor: pointer; text-align: left; font-family: inherit;
}
.account-dropdown-link i { font-size: 16px; }
.account-dropdown-link:hover { background: #F0F1F0; }
.account-dropdown-link--danger:hover { background: #FDECEC; color: #B3261E; }
.menu-fade-enter-active, .menu-fade-leave-active { transition: opacity .12s, transform .12s; }
.menu-fade-enter-from, .menu-fade-leave-to { opacity: 0; transform: translateY(-4px); }

.admin-page-head {
    max-width: 1180px; margin: 0 auto; padding: 24px 24px 0;
    display: flex; align-items: center; gap: 12px;
}
.admin-page-title { font-size: 20px; font-weight: 500; color: #10241D; }
.admin-role-chip {
    background: #E7F5EF; color: #146C4E; font-size: 11px; font-weight: 600;
    padding: 3px 10px; border-radius: 999px; text-transform: uppercase; letter-spacing: .03em;
}

.admin-flash {
    max-width: 1180px; margin: 14px auto 0; padding: 10px 24px;
    display: flex; align-items: center; gap: 8px; font-size: 13.5px; border-radius: 10px;
}
.admin-flash--success { background: #E7F5EF; color: #146C4E; }
.admin-flash--error { background: #FDECEC; color: #B3261E; }
.admin-flash-enter-active, .admin-flash-leave-active { transition: opacity .2s; }
.admin-flash-enter-from, .admin-flash-leave-to { opacity: 0; }

.admin-content { max-width: 1180px; margin: 0 auto; padding: 20px 24px 60px; }

@media (max-width: 860px) {
    .admin-topbar-inner { padding: 0 16px; gap: 14px; }
    .admin-brand-tag { display: none; }
    .admin-pill { padding: 7px 11px; font-size: 12.5px; }
    .admin-pill span, .admin-pill { white-space: nowrap; }
    .admin-page-head, .admin-content, .admin-flash { padding-left: 16px; padding-right: 16px; }
}
@media (max-width: 560px) {
    .admin-pill i { font-size: 15px; }
}
</style>
