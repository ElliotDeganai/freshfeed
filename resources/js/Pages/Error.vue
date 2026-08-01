<template>
    <div class="error-shell">
        <Head :title="message.title" />
        <div class="error-card">
            <div class="error-icon" :style="{ background: theme.bg, color: theme.text }">
                <i :class="`ti ${message.icon}`"></i>
            </div>

            <div class="error-status">{{ status }}</div>
            <h1 class="error-title">{{ message.title }}</h1>
            <p class="error-text">{{ message.text }}</p>

            <div class="error-actions">
                <Link href="/" class="btn-primary">
                    <i class="ti ti-home"></i> Retour à l'accueil
                </Link>
                <button v-if="status >= 500" class="btn-secondary" @click="reload">
                    <i class="ti ti-refresh"></i> Réessayer
                </button>
            </div>
        </div>
    </div>
</template>

<script>
import { Link, Head } from '@inertiajs/vue3';

const MESSAGES = {
    401: {
        title: 'Non connecté',
        text: 'Tu dois être connecté pour accéder à cette page.',
        icon: 'ti-lock',
    },
    403: {
        title: 'Accès refusé',
        text: "Tu n'as pas la permission d'accéder à cette page.",
        icon: 'ti-shield-x',
    },
    404: {
        title: 'Page introuvable',
        text: "Cette page n'existe pas ou a été déplacée.",
        icon: 'ti-map-pin-off',
    },
    419: {
        title: 'Session expirée',
        text: 'Ta session a expiré. Merci de réessayer.',
        icon: 'ti-clock-x',
    },
    429: {
        title: 'Trop de requêtes',
        text: 'Tu as effectué trop de requêtes. Réessaie dans un instant.',
        icon: 'ti-hourglass',
    },
    500: {
        title: 'Erreur serveur',
        text: "Quelque chose s'est mal passé de notre côté. On y travaille.",
        icon: 'ti-alert-triangle',
    },
    503: {
        title: 'Maintenance en cours',
        text: "{site} est momentanément indisponible. Reviens dans quelques minutes.",
        icon: 'ti-tool',
    },
};

const THEMES = {
    401: { bg: '#FBEAF0', text: '#993556' },
    403: { bg: '#FAECE7', text: '#993C1D' },
    404: { bg: '#EEEDFE', text: '#534AB7' },
    419: { bg: '#E6F1FB', text: '#0C447C' },
    429: { bg: '#FAEEDA', text: '#854F0B' },
    500: { bg: '#FAECE7', text: '#993C1D' },
    503: { bg: '#FAEEDA', text: '#854F0B' },
};

export default {
    components: { Link, Head },
    props: {
        status: { type: Number, required: true },
    },
    computed: {
        message() {
            const base = MESSAGES[this.status] ?? {
                title: 'Erreur inattendue',
                text: "Une erreur inattendue s'est produite.",
                icon: 'ti-alert-circle',
            };
            return { ...base, text: base.text.replace('{site}', this.$page.props.site?.name ?? 'FreshFeed') };
        },
        theme() {
            return THEMES[this.status] ?? { bg: '#F0F1F0', text: '#6B7B74' };
        },
    },
    methods: {
        reload() {
            window.location.reload();
        },
    },
};
</script>

<style scoped>
.error-shell {
    min-height: 100vh; display: flex; align-items: center; justify-content: center;
    background: #F7F8F6; padding: 24px;
}
.error-card {
    background: #fff; border: 0.5px solid #E7E9E7; border-radius: 20px;
    padding: 44px 40px; max-width: 420px; width: 100%; text-align: center;
}
.error-icon {
    width: 64px; height: 64px; border-radius: 50%; margin: 0 auto 20px;
    display: flex; align-items: center; justify-content: center; font-size: 28px;
}
.error-status { font-size: 13px; font-weight: 600; color: #8FA098; letter-spacing: .04em; margin-bottom: 6px; }
.error-title { font-size: 20px; font-weight: 500; color: #10241D; margin-bottom: 10px; }
.error-text { font-size: 13.5px; color: #6B7B74; line-height: 1.5; margin-bottom: 26px; }
.error-actions { display: flex; gap: 10px; justify-content: center; flex-wrap: wrap; }
.btn-primary {
    background: #1D9E75; color: #fff; border: none; border-radius: 20px;
    padding: 10px 20px; font-size: 13.5px; font-weight: 500; text-decoration: none;
    display: inline-flex; align-items: center; gap: 6px;
}
.btn-secondary {
    background: transparent; color: #6B7B74; border: 0.5px solid #D9DDD9; border-radius: 20px;
    padding: 10px 20px; font-size: 13.5px; cursor: pointer;
    display: inline-flex; align-items: center; gap: 6px;
}
</style>
