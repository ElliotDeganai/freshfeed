<template>
    <div class="split-shell">
        <!-- Panneau de marque — masqué sur mobile -->
        <div class="brand-panel">
            <div class="brand-blob brand-blob--a"></div>
            <div class="brand-blob brand-blob--b"></div>

            <Link href="/" class="brand-logo-row">
                <span class="brand-logo-mark"><SiteLogo :size="20" dark /></span>
                <span class="brand-logo-text">{{ $page.props.site.name }}</span>
            </Link>

            <div class="brand-copy">
                <h2 class="brand-title">Des recettes fraîches,<br />partagées par de vraies personnes.</h2>
                <p class="brand-subtitle">
                    Rejoins une communauté qui cuisine vraiment, sans filtre.
                </p>

                <ul class="brand-features">
                    <li><i class="ti ti-tools-kitchen-2"></i> Des recettes testées par de vraies cuisines</li>
                    <li><i class="ti ti-users"></i> Une communauté active, pas des influenceurs</li>
                    <li><i class="ti ti-bookmark"></i> Enregistre et organise tes recettes favorites</li>
                </ul>
            </div>

            <p class="brand-footnote">{{ $page.props.site.name }} — {{ new Date().getFullYear() }}</p>
        </div>

        <!-- Panneau formulaire -->
        <div class="form-panel">
            <div class="form-panel-inner">
                <Link href="/" class="mobile-brand-mark">
                    <SiteLogo :size="22" />
                </Link>

                <div class="form-card">
                    <slot />
                </div>

                <Link href="/" class="back-link"><i class="ti ti-arrow-left"></i> Retour à l'accueil</Link>

                <a href="https://ed-factory.com/" target="_blank" rel="noopener noreferrer" class="form-ed-credit">
                    Made by
                    <img src="/images/ed-logo.png" alt="ED" class="ed-credit-logo" />
                    Web Factory
                </a>
            </div>
        </div>
    </div>
</template>

<script>
import { Link } from '@inertiajs/vue3';
import SiteLogo from '@/Components/SiteLogo.vue';

export default {
    components: { Link, SiteLogo },
};
</script>

<style scoped>
.split-shell { min-height: 100vh; display: flex; }

.brand-panel {
    display: none;
    position: relative; overflow: hidden;
    width: 45%; padding: 44px 48px;
    background: linear-gradient(155deg, #1D9E75 0%, #178563 55%, #0F6E56 100%);
    flex-direction: column; justify-content: space-between;
}
.brand-blob { position: absolute; border-radius: 50%; background: rgba(255,255,255,0.08); }
.brand-blob--a { width: 340px; height: 340px; top: -110px; right: -90px; }
.brand-blob--b { width: 280px; height: 280px; bottom: -120px; left: -80px; }

.brand-logo-row { display: flex; align-items: center; gap: 10px; text-decoration: none; position: relative; z-index: 2; }
.brand-logo-mark {
    width: 34px; height: 34px; border-radius: 10px; background: rgba(255,255,255,0.16);
    color: #fff; font-weight: 600; font-size: 15px; backdrop-filter: blur(4px);
    display: flex; align-items: center; justify-content: center;
}
.brand-logo-text { font-size: 17px; font-weight: 500; color: #fff; letter-spacing: -0.2px; }

.brand-copy { position: relative; z-index: 2; }
.brand-title { font-size: 28px; font-weight: 500; color: #fff; line-height: 1.3; letter-spacing: -0.4px; margin-bottom: 14px; }
.brand-subtitle { font-size: 14px; color: rgba(255,255,255,0.8); line-height: 1.6; margin-bottom: 28px; max-width: 340px; }

.brand-features { list-style: none; display: flex; flex-direction: column; gap: 14px; }
.brand-features li {
    display: flex; align-items: center; gap: 10px; font-size: 13px; color: rgba(255,255,255,0.9);
}
.brand-features i { font-size: 18px; color: #fff; flex-shrink: 0; }

.brand-footnote {
    position: relative; z-index: 2; font-size: 12px; color: rgba(255,255,255,0.55);
    text-align: center; margin: 0;
}

.form-panel {
    flex: 1; display: flex; align-items: center; justify-content: center;
    background: #fff; padding: 32px 20px;
}
.form-panel-inner { width: 100%; max-width: 380px; }

.mobile-brand-mark { display: inline-flex; margin-bottom: 28px; }

.form-card { margin-bottom: 20px; }

.back-link {
    color: #8FA098; font-size: 13px; text-decoration: none;
    display: flex; align-items: center; gap: 5px;
}
.back-link:hover { color: #6B7B74; }

.form-ed-credit {
    display: flex; align-items: center; justify-content: center; gap: 5px; width: 100%;
    color: #ADB6AF; text-decoration: none; font-size: 11.5px; margin-top: 18px; transition: color .15s;
}
.form-ed-credit:hover { color: #6B7B74; }
.ed-credit-logo { height: 18px; width: 18px; object-fit: contain; }

@media (min-width: 900px) {
    .brand-panel { display: flex; }
}
</style>
