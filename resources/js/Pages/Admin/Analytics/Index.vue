<template>
    <AdminLayout>
        <template #title>Statistiques</template>

        <div class="period-tabs">
            <Link
                v-for="opt in periodOptions" :key="opt.value"
                :href="route('admin.analytics.index', { days: opt.value })"
                class="period-tab" :class="{ on: days === opt.value }"
                preserve-scroll
            >
                {{ opt.label }}
            </Link>
        </div>

        <div class="charts-grid">
            <AnalyticsChart
                title="Visiteurs par jour" icon="ti-eye" color="#1D9E75"
                :series="series.visitors" @point-click="openDetail('visitors', $event)"
            />
            <AnalyticsChart
                title="Recettes créées par jour" icon="ti-tools-kitchen-2" color="#534AB7"
                :series="series.recipes" @point-click="openDetail('recipes', $event)"
            />
            <AnalyticsChart
                title="Connexions par jour" icon="ti-login" color="#E3B23C"
                :series="series.logins" @point-click="openDetail('logins', $event)"
            />
        </div>

        <p class="analytics-note">
            Les visiteurs sont comptés par session (un même visiteur ne compte qu'une fois par
            jour), hors zone admin. Les connexions comptent chaque connexion, pas les utilisateurs
            uniques — quelqu'un qui se connecte deux fois dans la journée compte deux fois.
        </p>

        <!-- Popup de détail au clic sur un point -->
        <transition name="modal-fade">
            <div v-if="detail.open" class="modal-backdrop" @click.self="closeDetail">
                <div class="modal-panel">
                    <div class="modal-header">
                        <h3>{{ detailTitle }} — {{ detailDateLabel }}</h3>
                        <button class="icon-btn" @click="closeDetail"><i class="ti ti-x"></i></button>
                    </div>

                    <div v-if="detail.loading" class="modal-loading"><i class="ti ti-loader-2"></i> Chargement...</div>

                    <template v-else>
                        <!-- Visiteurs : anonymes, pas de liste possible -->
                        <div v-if="detail.metric === 'visitors'" class="modal-visitors">
                            <div class="modal-visitors-count">{{ detail.data.count }}</div>
                            <p>visiteur{{ detail.data.count > 1 ? 's' : '' }} ce jour-là — le suivi est anonyme (par
                                session), aucun détail individuel n'est disponible.</p>
                        </div>

                        <!-- Recettes / connexions : liste nominative -->
                        <div v-else class="modal-list">
                            <div v-if="detail.data.items.length === 0" class="modal-empty">Aucun élément ce jour-là.</div>
                            <div v-for="(item, i) in detail.data.items" :key="i" class="modal-list-row">
                                <template v-if="detail.metric === 'recipes'">
                                    <span class="modal-list-time">{{ item.time }}</span>
                                    <span class="modal-list-main">{{ item.title }}</span>
                                    <span class="modal-list-sub">{{ item.user_name }}</span>
                                    <span class="tag-pill" :class="item.status === 'published' ? 'tag-pill--green' : ''">{{ item.status === 'published' ? 'Publiée' : 'Brouillon' }}</span>
                                </template>
                                <template v-else>
                                    <span class="modal-list-time">{{ item.time }}</span>
                                    <span class="modal-list-main">{{ item.user_name }}</span>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </transition>
    </AdminLayout>
</template>

<script>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Link } from '@inertiajs/vue3';
import AnalyticsChart from '@/Components/Admin/AnalyticsChart.vue';

export default {
    layout: null,
    components: { AdminLayout, Link, AnalyticsChart },
    props: {
        days: Number,
        series: Object,
        totals: Object,
    },
    data() {
        return {
            periodOptions: [
                { value: 7, label: '7 jours' },
                { value: 30, label: '30 jours' },
                { value: 90, label: '90 jours' },
            ],
            detail: {
                open: false,
                loading: false,
                metric: null,
                date: null,
                data: null,
            },
        };
    },
    computed: {
        detailTitle() {
            return { visitors: 'Visiteurs', recipes: 'Recettes créées', logins: 'Connexions' }[this.detail.metric] || '';
        },
        detailDateLabel() {
            if (!this.detail.date) return '';
            return new Date(this.detail.date).toLocaleDateString('fr-CH', { weekday: 'long', day: 'numeric', month: 'long' });
        },
    },
    methods: {
        openDetail(metric, point) {
            this.detail = { open: true, loading: true, metric, date: point.date, data: null };

            fetch(`${route('admin.analytics.details')}?metric=${metric}&date=${point.date}`, {
                headers: { Accept: 'application/json' },
            })
                .then((r) => r.json())
                .then((data) => { this.detail.data = data; })
                .finally(() => { this.detail.loading = false; });
        },
        closeDetail() {
            this.detail.open = false;
        },
    },
};
</script>

<style scoped>
.period-tabs { display: flex; gap: 6px; margin-bottom: 20px; }
.period-tab {
    padding: 7px 16px; border-radius: 20px; border: 0.5px solid #E7E9E7; background: #fff;
    color: #6B7B74; font-size: 13px; text-decoration: none;
}
.period-tab.on { background: #1D9E75; border-color: #1D9E75; color: #fff; font-weight: 500; }

.charts-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; }
@media (max-width: 1000px) { .charts-grid { grid-template-columns: 1fr; } }

.analytics-note { font-size: 12px; color: #8FA098; margin-top: 20px; line-height: 1.6; max-width: 720px; }

.modal-backdrop {
    position: fixed; inset: 0; background: rgba(16,36,29,.4); z-index: 50;
    display: flex; align-items: center; justify-content: center; padding: 20px;
}
.modal-panel { background: #fff; border-radius: 16px; padding: 22px; width: 100%; max-width: 440px; max-height: 80vh; overflow-y: auto; }
.modal-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px; }
.modal-header h3 { font-size: 14.5px; font-weight: 500; color: #10241D; text-transform: capitalize; }
.icon-btn { width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; background: none; border: none; color: #6B7B74; cursor: pointer; }
.icon-btn:hover { background: #F0F1F0; }

.modal-loading { display: flex; align-items: center; gap: 8px; font-size: 13px; color: #8FA098; padding: 20px 0; }
.modal-loading i { animation: spin 0.8s linear infinite; }
@keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }

.modal-visitors { text-align: center; padding: 10px 0; }
.modal-visitors-count { font-size: 34px; font-weight: 600; color: #1D9E75; }
.modal-visitors p { font-size: 12.5px; color: #8FA098; margin-top: 8px; line-height: 1.6; }

.modal-list { display: flex; flex-direction: column; gap: 2px; }
.modal-list-row {
    display: flex; align-items: center; gap: 10px; padding: 9px 4px; border-bottom: 0.5px solid #F0F1F0; font-size: 13px;
}
.modal-list-row:last-child { border-bottom: none; }
.modal-list-time { font-size: 11px; color: #8FA098; width: 38px; flex-shrink: 0; }
.modal-list-main { color: #10241D; font-weight: 500; flex: 1; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.modal-list-sub { font-size: 11.5px; color: #8FA098; }
.modal-empty { text-align: center; color: #8FA098; font-size: 13px; padding: 20px 0; }

.tag-pill { font-size: 10px; background: #F0F1F0; color: #6B7B74; padding: 3px 9px; border-radius: 999px; flex-shrink: 0; }
.tag-pill--green { background: #E7F5EF; color: #146C4E; }

.modal-fade-enter-active, .modal-fade-leave-active { transition: opacity .15s; }
.modal-fade-enter-from, .modal-fade-leave-to { opacity: 0; }
</style>
