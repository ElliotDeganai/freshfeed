<script>
import { Head, Link } from '@inertiajs/vue3';
import SiteLogo from '@/Components/SiteLogo.vue';
import SiteFooter from '@/Components/SiteFooter.vue';

// Repli utilisé tant qu'aucune image n'a été ajoutée depuis l'admin
// (Admin > Accueil), et couleurs/icônes de repli pour la galerie d'aperçu.
const PREVIEW_FALLBACKS = [
    { bg: '#E1F5EE', icon: 'ti-salad', h: 200 },
    { bg: '#FAEEDA', icon: 'ti-soup', h: 260 },
    { bg: '#FBEAF0', icon: 'ti-apple', h: 170 },
    { bg: '#EEEDFE', icon: 'ti-bread', h: 230 },
    { bg: '#FAECE7', icon: 'ti-cherry', h: 190 },
    { bg: '#E6F1FB', icon: 'ti-carrot', h: 250 },
];

const DEFAULT_CONTENT = {
    hero_title: 'Cuisine, partage, découvre.',
    hero_badge: 'Le réseau social des cuisiniers du quotidien',
    hero_image: null,
    preview_images: [null, null, null, null, null, null],
};

const FEATURES = [
    {
        color: { bg: '#E1F5EE', text: '#0F6E56' },
        title: 'Partage tes recettes',
        text: "Publie tes recettes avec photos, ingrédients et étapes. Choisis de les garder en brouillon ou de les publier.",
    },
    {
        color: { bg: '#EEEDFE', text: '#534AB7' },
        title: 'Découvre par catégorie',
        text: 'Rapide, healthy, comfort food, vegan... explore le fil par catégorie ou laisse-toi surprendre.',
    },
    {
        color: { bg: '#FAEEDA', text: '#854F0B' },
        title: 'Enregistre tes favoris',
        text: 'Garde de côté les recettes qui te plaisent pour les retrouver facilement plus tard.',
    },
    {
        color: { bg: '#FAECE7', text: '#993C1D' },
        title: 'Suis une communauté',
        text: 'Suis les personnes dont tu aimes la cuisine et vois leurs nouvelles recettes dans ton fil.',
    },
];

export default {
    components: { Head, Link, SiteLogo, SiteFooter },
    props: {
        canLogin: { type: Boolean, default: true },
        canRegister: { type: Boolean, default: true },
        content: { type: Object, default: () => DEFAULT_CONTENT },
    },
    data() {
        return {
            features: FEATURES,
            heroImageBroken: false,
        };
    },
    computed: {
        siteName() {
            return this.$page.props.site?.name ?? 'FreshFeed';
        },
        heroTitle() {
            return this.content.hero_title || DEFAULT_CONTENT.hero_title;
        },
        heroSubtitle() {
            return this.content.hero_subtitle
                || `${this.siteName} est l'endroit où de vraies personnes partagent ce qu'elles cuisinent vraiment — rapide, healthy ou gourmand.`;
        },
        heroBadge() {
            return this.content.hero_badge || DEFAULT_CONTENT.hero_badge;
        },
        heroImageSrc() {
            return this.content.hero_image ? `/storage/${this.content.hero_image}` : null;
        },
        items() {
            const paths = this.content.preview_images?.length ? this.content.preview_images : DEFAULT_CONTENT.preview_images;
            return paths.map((path, i) => ({
                src: path ? `/storage/${path}` : null,
                broken: !path,
                ...PREVIEW_FALLBACKS[i],
            }));
        },
    },
    methods: {
        onImgError(item) {
            item.broken = true;
        },
    },
};
</script>

<template>
    <Head title="Bienvenue" />

    <div class="welcome-shell">
        <header class="welcome-topbar">
            <div class="welcome-topbar-inner">
                <Link href="/" class="brand-logo"><SiteLogo :size="28" />{{ siteName }}</Link>

                <nav class="welcome-nav">
                    <Link v-if="$page.props.auth.user" :href="route('feed')" class="btn-primary">
                        Mon espace
                    </Link>
                    <template v-else>
                        <Link :href="route('login')" class="btn-ghost">Connexion</Link>
                        <Link v-if="canRegister" :href="route('register')" class="btn-primary">
                            S'inscrire
                        </Link>
                    </template>
                </nav>
            </div>
        </header>

        <!-- Hero -->
        <section class="welcome-intro">
            <div class="intro-text-col">
                <div v-if="heroBadge" class="hero-badge"><i class="ti ti-tools-kitchen-2"></i> {{ heroBadge }}</div>
                <h1 class="intro-title">{{ heroTitle }}</h1>
                <p class="intro-text">{{ heroSubtitle }}</p>
                <Link v-if="!$page.props.auth.user" :href="route('register')" class="btn-primary btn-lg">
                    Rejoindre {{ siteName }} <i class="ti ti-arrow-right"></i>
                </Link>
            </div>
            <div class="intro-image-col">
                <img v-if="heroImageSrc && !heroImageBroken" :src="heroImageSrc" alt="" @error="heroImageBroken = true" />
                <div v-else class="intro-image-fallback">
                    <i class="ti ti-tools-kitchen-2"></i>
                </div>
            </div>
        </section>

        <!-- Aperçu du fil -->
        <section class="preview">
            <div class="preview-grid">
                <div v-for="(item, i) in items" :key="i" class="preview-tile" :style="{ height: item.h + 'px' }">
                    <img v-if="!item.broken" :src="item.src" alt="" @error="onImgError(item)" />
                    <div v-else class="preview-tile-fallback" :style="{ background: item.bg }">
                        <i :class="`ti ${item.icon}`"></i>
                    </div>
                </div>
            </div>
            <Link href="/explore" class="preview-more">
                Voir toutes les recettes <i class="ti ti-arrow-right"></i>
            </Link>
        </section>

        <!-- Fonctionnalités -->
        <section class="features">
            <h2 class="features-title">Comment ça marche</h2>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon" :style="{ background: features[0].color.bg, color: features[0].color.text }">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M4 11c0-3.5 3-6 8-6s8 2.5 8 6" />
                            <path d="M3 11h18l-1.4 6.2a2 2 0 0 1-2 1.8H6.4a2 2 0 0 1-2-1.8L3 11Z" />
                            <path d="M9 5.5c.5-1 1.6-1.8 3-1.8s2.5.8 3 1.8" />
                        </svg>
                    </div>
                    <h3 class="feature-title">{{ features[0].title }}</h3>
                    <p class="feature-text">{{ features[0].text }}</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon" :style="{ background: features[1].color.bg, color: features[1].color.text }">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="9" />
                            <path d="M15.5 8.5 13 13l-4.5 2.5L11 11l4.5-2.5Z" stroke-linejoin="round" />
                        </svg>
                    </div>
                    <h3 class="feature-title">{{ features[1].title }}</h3>
                    <p class="feature-text">{{ features[1].text }}</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon" :style="{ background: features[2].color.bg, color: features[2].color.text }">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M7 4h10a1 1 0 0 1 1 1v15l-6-3.6L6 20V5a1 1 0 0 1 1-1Z" />
                        </svg>
                    </div>
                    <h3 class="feature-title">{{ features[2].title }}</h3>
                    <p class="feature-text">{{ features[2].text }}</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon" :style="{ background: features[3].color.bg, color: features[3].color.text }">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="9" cy="9" r="3.2" />
                            <path d="M3.5 19c.6-3 2.7-4.8 5.5-4.8s4.9 1.8 5.5 4.8" />
                            <circle cx="17" cy="8.5" r="2.6" />
                            <path d="M15.3 14.6c2.3.3 3.8 1.9 4.2 4.4" />
                        </svg>
                    </div>
                    <h3 class="feature-title">{{ features[3].title }}</h3>
                    <p class="feature-text">{{ features[3].text }}</p>
                </div>
            </div>
        </section>

        <!-- CTA final -->
        <section class="final-cta">
            <h2 class="final-cta-title">Prêt à partager ta première recette ?</h2>
            <p class="final-cta-text">Rejoins la communauté {{ siteName }}, c'est gratuit.</p>
            <Link v-if="!$page.props.auth.user" :href="route('register')" class="btn-primary btn-lg">
                Créer mon compte
            </Link>
            <Link v-else :href="route('feed')" class="btn-primary btn-lg">
                Accéder à mon espace
            </Link>
        </section>

        <SiteFooter />
    </div>
</template>

<style scoped>
.welcome-shell {
    min-height: 100vh;
    background-color: #fff;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='180' height='180'%3E%3Cg fill='none' stroke='%231D9E75' stroke-width='1.4' opacity='0.05'%3E%3Cpath d='M20 150c0-40 20-65 45-80'/%3E%3Cpath d='M30 110c8-4 14-2 18 4'/%3E%3Cpath d='M42 90c8-4 14-1 17 5'/%3E%3Cpath d='M54 70c7-4 13-1 16 5'/%3E%3Cpath d='M140 30c-25 5-40 22-44 48'/%3E%3Cpath d='M115 55c-3 8-1 14 5 17'/%3E%3Cpath d='M100 70c-3 8 0 14 6 17'/%3E%3Ccircle cx='150' cy='140' r='9'/%3E%3Cpath d='M150 131v-10M150 149v10M141 140h-10M159 140h10'/%3E%3C/g%3E%3C/svg%3E");
    background-repeat: repeat;
}

.welcome-topbar {
    border-bottom: 0.5px solid #EEEFEC;
    position: sticky; top: 0; background: #fff; z-index: 10;
}
.welcome-topbar-inner {
    display: flex; align-items: center; justify-content: space-between; gap: 20px;
    max-width: 1200px; margin: 0 auto; padding: 14px 24px;
}
.brand-logo { font-size: 20px; font-weight: 700; color: #1D9E75; letter-spacing: -0.4px; text-decoration: none; display: flex; align-items: center; gap: 8px; }

.welcome-nav { display: flex; gap: 8px; align-items: center; }
.btn-ghost {
    color: #10241D; font-size: 13.5px; font-weight: 500; text-decoration: none;
    padding: 9px 16px; border-radius: 20px;
}
.btn-ghost:hover { background: #F1F3F0; }
.btn-primary {
    background: #1D9E75; color: #fff; border: none; border-radius: 20px;
    padding: 9px 18px; font-size: 13.5px; font-weight: 500; text-decoration: none;
    display: inline-flex; align-items: center; gap: 6px;
}
.btn-primary:hover { background: #178563; }
.btn-lg { padding: 13px 26px; font-size: 14.5px; }

/* Hero */
.welcome-intro {
    display: flex; align-items: center; gap: 36px;
    padding: 44px 32px; max-width: 1100px; margin: 0 auto;
}
.intro-text-col { flex: 1; min-width: 0; }
.hero-badge {
    background: #E7F5EF; color: #146C4E; font-size: 12.5px; font-weight: 500;
    padding: 6px 14px; border-radius: 999px; display: inline-flex; align-items: center; gap: 6px;
    margin-bottom: 18px;
}
.hero-badge i { font-size: 14px; }
.intro-title { font-size: 30px; font-weight: 500; color: #10241D; letter-spacing: -0.6px; margin-bottom: 14px; line-height: 1.2; }
.intro-text { font-size: 14.5px; color: #6B7B74; line-height: 1.6; margin-bottom: 24px; max-width: 380px; }

.intro-image-col {
    flex: 1; height: 300px; border-radius: 20px; overflow: hidden; background: #EDEFEC;
}
.intro-image-col img { width: 100%; height: 100%; object-fit: cover; display: block; }
.intro-image-fallback {
    width: 100%; height: 100%; display: flex; align-items: center; justify-content: center;
    font-size: 40px; color: rgba(16, 36, 29, 0.22);
}

/* Aperçu */
.preview { padding: 0 24px 20px; max-width: 1100px; margin: 0 auto; }
.preview-grid { column-count: 3; column-gap: 12px; }
.preview-tile {
    break-inside: avoid; margin-bottom: 12px; border-radius: 14px; overflow: hidden; background: #EDEFEC;
}
.preview-tile img { width: 100%; height: 100%; object-fit: cover; display: block; }
.preview-tile-fallback {
    width: 100%; height: 100%; display: flex; align-items: center; justify-content: center;
    font-size: 24px; color: rgba(16, 36, 29, 0.28);
}
.preview-more {
    display: block; text-align: center; margin-top: 14px; font-size: 13px; font-weight: 500;
    color: #1D9E75; text-decoration: none;
}
.preview-more:hover { text-decoration: underline; }

/* Fonctionnalités */
.features { background: #FAFBFA; padding: 56px 24px; margin-top: 40px; }
.features-title { text-align: center; font-size: 24px; font-weight: 500; color: #10241D; letter-spacing: -0.4px; margin-bottom: 36px; }
.features-grid {
    display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px;
    max-width: 1100px; margin: 0 auto;
}
.feature-card { text-align: center; }
.feature-icon {
    width: 52px; height: 52px; border-radius: 16px; display: flex; align-items: center; justify-content: center;
    margin: 0 auto 16px;
}
.feature-icon svg { width: 24px; height: 24px; }
.feature-title { font-size: 15px; font-weight: 500; color: #10241D; margin-bottom: 8px; }
.feature-text { font-size: 13px; color: #6B7B74; line-height: 1.55; }

/* CTA final */
.final-cta { text-align: center; padding: 60px 24px; max-width: 480px; margin: 0 auto; }
.final-cta-title { font-size: 24px; font-weight: 500; color: #10241D; letter-spacing: -0.4px; margin-bottom: 8px; }
.final-cta-text { font-size: 13.5px; color: #6B7B74; margin-bottom: 24px; }



@media (max-width: 900px) {
    .features-grid { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 640px) {
    .welcome-topbar-inner { padding: 12px 16px; }
    .welcome-intro { flex-direction: column; padding: 32px 20px; gap: 24px; }
    .intro-text-col { text-align: center; }
    .intro-text { margin: 0 auto 24px; }
    .intro-image-col { width: 100%; height: 220px; }
    .intro-title { font-size: 26px; }
    .preview-grid { column-count: 2; column-gap: 10px; }
    .features { padding: 44px 16px; }
    .features-grid { grid-template-columns: 1fr; gap: 28px; }
}
</style>
