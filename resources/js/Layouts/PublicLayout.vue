<template>
    <div class="public-shell">
        <header class="public-topbar">
            <div class="public-topbar-inner">
                <Link href="/" class="brand-logo"><SiteLogo :size="26" />{{ $page.props.site.name }}</Link>
                <Link :href="route('login')" class="btn-primary">Connexion</Link>
            </div>
        </header>

        <main class="public-content">
            <div class="sample-banner">
                <i class="ti ti-eye"></i> Ce n'est qu'un échantillon —
                <Link :href="route('login')">connecte-toi</Link> pour découvrir tout le catalogue.
            </div>
            <slot />
        </main>

        <SiteFooter />
    </div>
</template>

<script>
import { Link } from '@inertiajs/vue3';
import SiteLogo from '@/Components/SiteLogo.vue';
import SiteFooter from '@/Components/SiteFooter.vue';

export default {
    components: { Link, SiteLogo, SiteFooter },
};
</script>

<style scoped>
.public-shell {
    min-height: 100vh;
    background-color: #fff;
    background-image: url('/images/pattern-leaves.svg');
    background-repeat: repeat;
}

.public-topbar { border-bottom: 0.5px solid #EEEFEC; position: sticky; top: 0; background: #fff; z-index: 10; }
.public-topbar-inner {
    display: flex; align-items: center; justify-content: space-between; gap: 20px;
    max-width: 1100px; margin: 0 auto; padding: 14px 24px;
}
.brand-logo { font-size: 18px; font-weight: 600; color: #1D9E75; letter-spacing: -0.3px; text-decoration: none; display: flex; align-items: center; gap: 8px; }

.btn-primary {
    background: #1D9E75; color: #fff; border: none; border-radius: 20px;
    padding: 9px 18px; font-size: 13.5px; font-weight: 500; text-decoration: none;
}
.btn-primary:hover { background: #178563; }

.public-content { max-width: 1100px; margin: 0 auto; padding: 20px 16px 40px; }

.sample-banner {
    display: flex; align-items: center; gap: 6px; justify-content: center;
    background: #F3FAF6; border: 0.5px solid #C7E8DA; color: #146C4E;
    font-size: 12.5px; padding: 10px 16px; border-radius: 12px; margin-bottom: 20px;
}
.sample-banner i { font-size: 15px; }
.sample-banner a { color: #1D9E75; font-weight: 600; text-decoration: none; }
.sample-banner a:hover { text-decoration: underline; }

@media (max-width: 640px) {
    .public-topbar-inner { padding: 12px 16px; }
}
</style>
