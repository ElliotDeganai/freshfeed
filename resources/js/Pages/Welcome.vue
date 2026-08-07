<script>
import { Head, Link } from '@inertiajs/vue3';
import SiteLogo from '@/Components/SiteLogo.vue';
import SiteFooter from '@/Components/SiteFooter.vue';

const DEFAULT_CONTENT = {
    hero_title: 'Cuisine, partage, découvre.',
    hero_badge: 'Le réseau social des cuisiniers du quotidien',
    hero_image: null,
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
        content: { type: Object, default: () => DEFAULT_CONTENT },
        featuredPost: { type: Object, default: null },
        gridPosts: { type: Array, default: () => [] },
        stats: { type: Object, default: () => ({}) },
    },
    data() {
        return {
            features: FEATURES,
            heroImageBroken: false,
            mosaicHeights: [170, 130, 150, 190],
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
        hasShowcase() {
            return this.featuredPost || this.gridPosts.length > 0;
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
                        <Link :href="route('login')" class="btn-primary">Connexion</Link>
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
                <Link v-if="!$page.props.auth.user" :href="route('login')" class="btn-primary btn-lg">
                    Se connecter <i class="ti ti-arrow-right"></i>
                </Link>
            </div>
            <div class="intro-image-col">
                <img v-if="heroImageSrc && !heroImageBroken" :src="heroImageSrc" alt="" @error="heroImageBroken = true" />
                <div v-else class="intro-image-fallback">
                    <i class="ti ti-tools-kitchen-2"></i>
                </div>
            </div>
        </section>

        <!-- Aperçu -->
        <section v-if="hasShowcase" class="preview">
            <div class="preview-layout">
                <Link
                    v-if="featuredPost" :href="route('posts.show', featuredPost.id)"
                    class="preview-featured"
                >
                    <img v-if="featuredPost.image_path" :src="`/storage/${featuredPost.image_path}`" alt="" />
                    <div v-else class="preview-tile-fallback"><i class="ti ti-tools-kitchen-2"></i></div>
                    <span class="preview-tile-badge"><i class="ti ti-flame"></i> Du moment</span>
                    <span class="preview-tile-caption">{{ featuredPost.title }}</span>
                </Link>

                <div class="preview-mosaic">
                    <Link
                        v-for="(post, i) in gridPosts" :key="post.id" :href="route('posts.show', post.id)"
                        class="preview-tile" :style="{ height: mosaicHeights[i % mosaicHeights.length] + 'px' }"
                    >
                        <img v-if="post.image_path" :src="`/storage/${post.image_path}`" alt="" />
                        <div v-else class="preview-tile-fallback"><i class="ti ti-tools-kitchen-2"></i></div>
                    </Link>
                </div>
            </div>
            <p class="preview-teaser">
                Ce n'est qu'un échantillon —
                <Link :href="route('login')">connecte-toi</Link> pour découvrir tout le catalogue.
            </p>
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

        <!-- Statistiques -->
        <section v-if="stats.recipes_count" class="stats-banner">
            <div class="stat-item">
                <span class="stat-value">{{ stats.recipes_count }}</span>
                <span class="stat-label">recette{{ stats.recipes_count > 1 ? 's' : '' }}</span>
            </div>
            <div class="stat-divider"></div>
            <div class="stat-item">
                <span class="stat-value">{{ stats.members_count }}</span>
                <span class="stat-label">membre{{ stats.members_count > 1 ? 's' : '' }} actif{{ stats.members_count > 1 ? 's' : '' }}</span>
            </div>
            <template v-if="stats.avg_rating">
                <div class="stat-divider"></div>
                <div class="stat-item">
                    <span class="stat-value">{{ stats.avg_rating }}★</span>
                    <span class="stat-label">note moyenne</span>
                </div>
            </template>
        </section>

        <!-- CTA final -->
        <section class="final-cta">
            <h2 class="final-cta-title">Prêt à partager ta première recette ?</h2>
            <p class="final-cta-text">{{ siteName }} fonctionne sur invitation pour le moment.</p>
            <Link v-if="!$page.props.auth.user" :href="route('login')" class="btn-primary btn-lg">
                Se connecter
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
    background-image: url('/images/pattern-leaves.svg');
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
.preview-layout { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }

.preview-featured {
    position: relative; border-radius: 16px; overflow: hidden; background: #EDEFEC;
    display: flex; align-items: center; justify-content: center; text-decoration: none; min-height: 360px;
}
.preview-featured img { width: 100%; height: 100%; object-fit: cover; display: block; position: absolute; inset: 0; }

.preview-mosaic { column-count: 2; column-gap: 14px; }
.preview-tile {
    display: block; break-inside: avoid; margin-bottom: 14px; border-radius: 14px; overflow: hidden;
    background: #EDEFEC; text-decoration: none;
}
.preview-tile img { width: 100%; height: 100%; object-fit: cover; display: block; }
.preview-tile-fallback {
    width: 100%; height: 100%; display: flex; align-items: center; justify-content: center;
    font-size: 24px; color: rgba(16, 36, 29, 0.28); background: #EDEFEC;
}
.preview-tile-badge {
    position: absolute; top: 12px; left: 12px; background: #fff; color: #146C4E;
    font-size: 11px; font-weight: 600; padding: 4px 11px; border-radius: 999px;
    display: inline-flex; align-items: center; gap: 4px; z-index: 2;
}
.preview-tile-badge i { color: #E3B23C; font-size: 13px; }
.preview-tile-caption {
    position: absolute; left: 12px; right: 12px; bottom: 12px; background: rgba(255,255,255,.92);
    color: #10241D; font-size: 14px; font-weight: 500; padding: 9px 12px; border-radius: 10px; z-index: 2;
}

.preview-teaser { text-align: center; font-size: 12.5px; color: #8FA098; margin-top: 16px; }
.preview-teaser a { color: #1D9E75; font-weight: 500; text-decoration: none; }
.preview-teaser a:hover { text-decoration: underline; }

/* Statistiques */
.stats-banner {
    background: linear-gradient(155deg, #1D9E75 0%, #178563 55%, #0F6E56 100%);
    display: flex; justify-content: center; align-items: center; gap: 48px;
    padding: 32px 24px;
}
.stat-item { text-align: center; }
.stat-value { display: block; font-size: 26px; font-weight: 600; color: #fff; }
.stat-label { display: block; font-size: 11.5px; color: rgba(255,255,255,.8); margin-top: 2px; }
.stat-divider { width: 1px; height: 40px; background: rgba(255,255,255,.25); }

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
    .preview-layout { grid-template-columns: 1fr; }
    .preview-featured { min-height: 220px; }
    .stats-banner { gap: 24px; padding: 26px 16px; }
    .features { padding: 44px 16px; }
    .features-grid { grid-template-columns: 1fr; gap: 28px; }
}
</style>
