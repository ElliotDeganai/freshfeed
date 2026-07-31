<template>
    <div class="app-shell">
        <header class="app-topbar">
            <Link href="/feed" class="brand"><SiteLogo :size="24" /><span class="brand-logo">FreshFeed</span></Link>

            <nav class="app-nav">
                <Link href="/feed" class="app-nav-link" :class="{ on: isActive('/feed') }">
                    <i class="ti ti-home"></i> Fil d'actualité
                </Link>
                <Link href="/explore" class="app-nav-link" :class="{ on: isActive('/explore') }">
                    <i class="ti ti-compass"></i> Explorer
                </Link>
                <Link :href="route('my-recipes.index')" class="app-nav-link" :class="{ on: isActive('/my-recipes') }">
                    <i class="ti ti-pencil"></i> Mes recettes
                </Link>
                <Link href="/profile" class="app-nav-link" :class="{ on: isActive('/profile') }">
                    <i class="ti ti-user"></i> Mon profil
                </Link>
            </nav>

            <div class="app-topbar-icons">
                <Link :href="route('my-recipes.create')" class="btn-add-recipe">
                    <i class="ti ti-plus"></i> Ajouter une recette
                </Link>

                <div class="account-menu" ref="accountMenu">
                    <button class="avatar-btn" @click="menuOpen = !menuOpen">
                        <UserAvatar :user="$page.props.auth.user" :size="32" />
                    </button>

                    <transition name="menu-fade">
                        <div v-if="menuOpen" class="account-dropdown">
                            <div class="account-dropdown-name">{{ $page.props.auth.user.name }}</div>
                            <Link v-if="canAccessAdmin" :href="route('admin.dashboard')" class="account-dropdown-link">
                                <i class="ti ti-shield-cog"></i> Zone admin
                            </Link>
                            <Link href="/profile" class="account-dropdown-link">
                                <i class="ti ti-user"></i> Mon profil
                            </Link>
                            <Link :href="route('logout')" method="post" as="button" class="account-dropdown-link account-dropdown-link--danger">
                                <i class="ti ti-logout"></i> Déconnexion
                            </Link>
                        </div>
                    </transition>
                </div>
            </div>
        </header>

        <main class="app-content">
            <slot />
        </main>

        <!-- barre de navigation basse — mobile uniquement -->
        <nav class="app-tabbar">
            <Link href="/feed" class="tab" :class="{ on: isActive('/feed') }">
                <i class="ti ti-home"></i><span>Fil</span>
            </Link>
            <Link href="/explore" class="tab" :class="{ on: isActive('/explore') }">
                <i class="ti ti-compass"></i><span>Explorer</span>
            </Link>
            <Link :href="route('my-recipes.create')" class="tab tab--fab">
                <i class="ti ti-plus"></i>
            </Link>
            <Link :href="route('my-recipes.index')" class="tab" :class="{ on: isActive('/my-recipes') }">
                <i class="ti ti-pencil"></i><span>Recettes</span>
            </Link>
            <Link href="/profile" class="tab" :class="{ on: isActive('/profile') }">
                <i class="ti ti-user"></i><span>Profil</span>
            </Link>
        </nav>
    </div>
</template>

<script>
import { Link } from '@inertiajs/vue3';
import SiteLogo from '@/Components/SiteLogo.vue';
import UserAvatar from '@/Components/UserAvatar.vue';

export default {
    components: { Link, SiteLogo, UserAvatar },
    data() {
        return {
            menuOpen: false,
        };
    },
    computed: {
        canAccessAdmin() {
            return this.$page.props.auth.permissions?.includes('view-admin') ?? false;
        },
    },
    mounted() {
        document.addEventListener('click', this.onClickOutside);
    },
    beforeUnmount() {
        document.removeEventListener('click', this.onClickOutside);
    },
    methods: {
        isActive(path) {
            return this.$page.url.startsWith(path);
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
.app-shell { min-height: 100vh; background: #F7F8F6; padding-bottom: 76px; }

.app-topbar {
    background: #fff; border-bottom: 0.5px solid #E7E9E7; position: sticky; top: 0; z-index: 10;
    display: flex; align-items: center; gap: 24px; padding: 0 24px; height: 60px;
}
.brand { text-decoration: none; display: flex; align-items: center; gap: 8px; }
.brand-logo { font-size: 16px; font-weight: 500; color: #1D9E75; letter-spacing: -0.3px; }

.app-nav { display: none; gap: 4px; flex: 1; justify-content: center; }
.app-nav-link {
    display: flex; align-items: center; gap: 6px; padding: 7px 14px; border-radius: 20px;
    font-size: 13px; color: #6B7B74; text-decoration: none;
}
.app-nav-link i { font-size: 16px; }
.app-nav-link:hover { background: #F0F1F0; }
.app-nav-link.on { background: #1D9E75; color: #fff; font-weight: 500; }

.app-topbar-icons { display: flex; align-items: center; gap: 12px; margin-left: auto; }
.btn-add-recipe {
    display: none; align-items: center; gap: 6px; background: #1D9E75; color: #fff;
    border-radius: 20px; padding: 8px 16px; font-size: 13px; font-weight: 500; text-decoration: none;
}
.btn-add-recipe:hover { background: #178563; }
.btn-add-recipe i { font-size: 15px; }

.account-menu { position: relative; }
.avatar-btn {
    padding: 0; border: none; background: transparent; cursor: pointer; display: flex; border-radius: 50%;
}
.account-dropdown {
    position: absolute; top: 42px; right: 0; background: #fff; border: 0.5px solid #E7E9E7;
    border-radius: 14px; padding: 6px; width: 200px; box-shadow: 0 10px 26px rgba(16,36,29,.12);
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

.app-content { max-width: 900px; margin: 0 auto; padding: 20px 16px 40px; }

.app-tabbar {
    position: fixed; bottom: 0; left: 0; right: 0; z-index: 10;
    background: #fff; border-top: 0.5px solid #E7E9E7;
    display: flex; align-items: center; justify-content: space-around;
    padding: 8px 8px calc(env(safe-area-inset-bottom, 0px) + 8px);
}
.tab {
    display: flex; flex-direction: column; align-items: center; gap: 3px;
    color: #8FA098; text-decoration: none; font-size: 10px;
}
.tab i { font-size: 22px; }
.tab.on { color: #1D9E75; font-weight: 500; }
.tab--fab {
    width: 46px; height: 46px; border-radius: 50%; background: #1D9E75; color: #fff;
    display: flex; align-items: center; justify-content: center; margin-top: -18px;
    box-shadow: 0 4px 10px rgba(29, 158, 117, 0.3);
}
.tab--fab i { font-size: 22px; }

@media (min-width: 900px) {
    .app-nav { display: flex; }
    .app-tabbar { display: none; }
    .app-shell { padding-bottom: 0; }
    .btn-add-recipe { display: inline-flex; }
}
</style>
