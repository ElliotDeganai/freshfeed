<template>
    <div class="admin-shell">
        <transition name="overlay-fade">
            <div v-if="sidebarOpen" class="sidebar-overlay" @click="sidebarOpen = false"></div>
        </transition>

        <aside class="admin-sidebar" :class="{ open: sidebarOpen }">
            <Link :href="route('admin.dashboard')" class="sidebar-brand">
                <SiteLogo :size="24" />
                <span class="sidebar-brand-name">{{ $page.props.site.name }}</span>
                <span class="sidebar-brand-tag">admin</span>
            </Link>

            <nav class="sidebar-nav">
                <Link :href="route('admin.dashboard')" class="sidebar-link" :class="{ on: isActive('admin.dashboard') }">
                    <i class="ti ti-layout-dashboard"></i> Tableau de bord
                </Link>
                <Link :href="route('admin.analytics.index')" class="sidebar-link" :class="{ on: isActive('admin.analytics.*') }">
                    <i class="ti ti-chart-line"></i> Analytics
                </Link>
                <Link v-if="can('manage-posts') || can('manage-own-posts')"
                    :href="route('admin.posts.index')" class="sidebar-link" :class="{ on: isActive('admin.posts.*') }">
                    <i class="ti ti-tools-kitchen-2"></i> Recettes
                </Link>
                <Link v-if="can('manage-categories')"
                    :href="route('admin.categories.index')" class="sidebar-link" :class="{ on: isActive('admin.categories.*') }">
                    <i class="ti ti-tags"></i> Catégories
                </Link>
                <Link v-if="can('manage-pages')"
                    :href="route('admin.pages.index')" class="sidebar-link" :class="{ on: isActive('admin.pages.*') }">
                    <i class="ti ti-file-stack"></i> Pages
                </Link>
                <Link v-if="can('manage-users')"
                    :href="route('admin.users.index')" class="sidebar-link" :class="{ on: isActive('admin.users.*') }">
                    <i class="ti ti-users"></i> Utilisateurs
                </Link>
                <Link v-if="can('manage-settings')"
                    :href="route('admin.homepage.index')" class="sidebar-link" :class="{ on: isActive('admin.homepage.*') }">
                    <i class="ti ti-home"></i> Accueil
                </Link>
                <Link v-if="can('manage-settings')"
                    :href="route('admin.settings.index')" class="sidebar-link" :class="{ on: isActive('admin.settings.*') }">
                    <i class="ti ti-settings"></i> Paramètres
                </Link>
            </nav>

            <Link :href="route('feed')" class="sidebar-link sidebar-link--muted">
                <i class="ti ti-arrow-back-up"></i> Retour au site
            </Link>
        </aside>

        <div class="admin-main">
            <header class="admin-topbar">
                <div class="admin-topbar-inner">
                    <button class="sidebar-toggle-btn" @click="sidebarOpen = !sidebarOpen">
                        <i class="ti ti-menu-2"></i>
                    </button>

                    <Link :href="route('admin.dashboard')" class="admin-brand-mobile">
                        <SiteLogo :size="24" />
                    </Link>

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
            sidebarOpen: false,
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
.admin-shell {
    min-height: 100vh; display: flex;
    background-color: #fff;
    background-image: url('/images/pattern-leaves.svg');
    background-repeat: repeat;
}

/* ---------- Sidebar ---------- */
.admin-sidebar {
    width: 232px; flex-shrink: 0; background: #fff; border-right: 0.5px solid #E7E9E7;
    display: flex; flex-direction: column; padding: 18px 14px;
    position: sticky; top: 0; height: 100vh; overflow-y: auto;
}
.sidebar-brand { display: flex; align-items: center; gap: 8px; padding: 6px 10px 20px; text-decoration: none; }
.sidebar-brand-name { font-size: 15px; font-weight: 500; color: #10241D; letter-spacing: -0.2px; }
.sidebar-brand-tag { font-size: 10px; color: #8FA098; text-transform: uppercase; letter-spacing: .04em; margin-left: -2px; }

.sidebar-nav { display: flex; flex-direction: column; gap: 2px; flex: 1; }
.sidebar-link {
    display: flex; align-items: center; gap: 10px; padding: 9px 12px; border-radius: 9px;
    font-size: 13.5px; color: #4B5A54; text-decoration: none;
}
.sidebar-link i { font-size: 17px; width: 18px; text-align: center; flex-shrink: 0; }
.sidebar-link:hover { background: #F0F1F0; }
.sidebar-link.on { background: #E7F5EF; color: #146C4E; font-weight: 500; }
.sidebar-link--muted { color: #8FA098; border-top: 0.5px solid #F0F1F0; margin-top: 10px; padding-top: 14px; }

.sidebar-overlay { display: none; }

/* ---------- Main column ---------- */
.admin-main { flex: 1; min-width: 0; display: flex; flex-direction: column; }

.admin-topbar { background: #fff; border-bottom: 0.5px solid #E7E9E7; position: sticky; top: 0; z-index: 10; }
.admin-topbar-inner { padding: 0 24px; height: 56px; display: flex; align-items: center; gap: 14px; }
.sidebar-toggle-btn {
    display: none; width: 34px; height: 34px; border-radius: 9px; border: none; background: transparent;
    color: #4B5A54; font-size: 18px; align-items: center; justify-content: center; cursor: pointer;
}
.sidebar-toggle-btn:hover { background: #F0F1F0; }
.admin-brand-mobile { display: none; }

.admin-topbar-icons { display: flex; align-items: center; gap: 10px; margin-left: auto; }
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

.admin-page-head { padding: 24px 24px 0; display: flex; align-items: center; gap: 12px; }
.admin-page-title { font-size: 20px; font-weight: 500; color: #10241D; }
.admin-role-chip {
    background: #E7F5EF; color: #146C4E; font-size: 11px; font-weight: 600;
    padding: 3px 10px; border-radius: 999px; text-transform: uppercase; letter-spacing: .03em;
}

.admin-flash {
    margin: 14px 24px 0; padding: 10px 16px;
    display: flex; align-items: center; gap: 8px; font-size: 13.5px; border-radius: 10px;
}
.admin-flash--success { background: #E7F5EF; color: #146C4E; }
.admin-flash--error { background: #FDECEC; color: #B3261E; }
.admin-flash-enter-active, .admin-flash-leave-active { transition: opacity .2s; }
.admin-flash-enter-from, .admin-flash-leave-to { opacity: 0; }

.admin-content { padding: 20px 24px 60px; flex: 1; }

/* ---------- Mobile : sidebar devient un tiroir ---------- */
@media (max-width: 900px) {
    .admin-sidebar {
        position: fixed; left: 0; top: 0; z-index: 30;
        transform: translateX(-100%); transition: transform .2s ease;
        box-shadow: 0 0 0 rgba(0,0,0,0);
    }
    .admin-sidebar.open { transform: translateX(0); box-shadow: 10px 0 30px rgba(16,36,29,.15); }
    .sidebar-overlay {
        display: block; position: fixed; inset: 0; background: rgba(16,36,29,.4); z-index: 25;
    }
    .overlay-fade-enter-active, .overlay-fade-leave-active { transition: opacity .2s; }
    .overlay-fade-enter-from, .overlay-fade-leave-to { opacity: 0; }

    .sidebar-toggle-btn { display: flex; }
    .admin-brand-mobile { display: flex; }

    .admin-page-head, .admin-content, .admin-flash { padding-left: 16px; padding-right: 16px; }
    .admin-topbar-inner { padding: 0 16px; }
}
</style>
