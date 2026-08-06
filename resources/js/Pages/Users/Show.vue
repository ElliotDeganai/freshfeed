<template>
    <AppLayout>
        <Head :title="profileUser.name" />

        <div class="profile-header">
            <UserAvatar :user="profileUser" :size="88" />
            <h1 class="profile-name">{{ profileUser.name }}</h1>
            <div v-if="profileUser.bio_safe" class="profile-bio" v-html="profileUser.bio_safe"></div>
            <p v-else class="profile-bio profile-bio--empty">Cette personne n'a pas encore renseigné de description.</p>
        </div>

        <div class="profile-recipes">
            <h2 class="section-title"><i class="ti ti-tools-kitchen-2"></i> Recettes publiées ({{ recipes.total }})</h2>

            <div class="explore-grid">
                <Link v-for="post in items" :key="post.id" :href="route('posts.show', post.id)" class="explore-card">
                    <div class="explore-card-image">
                        <img v-if="post.image_path" :src="`/storage/${post.image_path}`" alt="" />
                        <div v-else class="explore-card-fallback" :style="{ background: avatarColor(post.id).bg, color: avatarColor(post.id).text }">
                            <i class="ti ti-tools-kitchen-2"></i>
                        </div>
                        <span v-if="post.ratings_count" class="rating-pill rating-pill--overlay"><i class="ti ti-star"></i> {{ Number(post.ratings_avg_rating).toFixed(1) }}</span>
                    </div>
                    <div class="explore-card-title">{{ post.title }}</div>
                </Link>

                <div v-if="items.length === 0" class="empty-state">
                    <i class="ti ti-tools-kitchen-2"></i>
                    <p>Aucune recette publiée pour l'instant.</p>
                </div>
            </div>

            <!-- Sentinelle observée pour déclencher le chargement de la page suivante -->
            <div ref="sentinel" class="scroll-sentinel">
                <div v-if="loading" class="loading-spinner"><i class="ti ti-loader-2"></i> Chargement...</div>
                <div v-else-if="!hasMore && items.length > 0" class="end-of-feed">Tu as tout vu 👋</div>
            </div>
        </div>
    </AppLayout>
</template>

<script>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import UserAvatar from '@/Components/UserAvatar.vue';
import { avatarColor } from '@/Components/Admin/avatarPalette.js';

export default {
    layout: null,
    components: { AppLayout, Head, Link, UserAvatar },
    props: {
        profileUser: Object,
        recipes: Object,
    },
    data() {
        return {
            items: [...this.recipes.data],
            currentPage: this.recipes.current_page,
            hasMore: this.recipes.next_page_url !== null,
            loading: false,
            observer: null,
        };
    },
    mounted() {
        this.observer = new IntersectionObserver((entries) => {
            if (entries[0].isIntersecting) this.loadMore();
        }, { rootMargin: '400px' });
        this.observer.observe(this.$refs.sentinel);
    },
    beforeUnmount() {
        this.observer?.disconnect();
    },
    methods: {
        avatarColor,
        loadMore() {
            if (this.loading || !this.hasMore) return;
            this.loading = true;

            fetch(`${route('users.show', this.profileUser.id)}?page=${this.currentPage + 1}`, {
                headers: { Accept: 'application/json' },
            })
                .then((r) => r.json())
                .then((fresh) => {
                    this.items.push(...fresh.data);
                    this.currentPage = fresh.current_page;
                    this.hasMore = fresh.next_page_url !== null;
                })
                .finally(() => { this.loading = false; });
        },
    },
};
</script>

<style scoped>
.profile-header {
    display: flex; flex-direction: column; align-items: center; text-align: center;
    max-width: 480px; margin: 0 auto 32px; padding: 8px 16px;
}
.profile-name { font-size: 20px; font-weight: 500; color: #10241D; margin: 14px 0 8px; }
.profile-bio { font-size: 13.5px; color: #4B5A54; line-height: 1.6; }
.profile-bio--empty { color: #8FA098; font-style: italic; }
.profile-bio :deep(h3) { font-size: 15px; font-weight: 500; color: #10241D; margin: 10px 0 4px; }
.profile-bio :deep(ul), .profile-bio :deep(ol) { text-align: left; padding-left: 20px; margin: 6px 0; }

.profile-recipes { max-width: 820px; margin: 0 auto; }
.section-title { display: flex; align-items: center; gap: 8px; font-size: 15px; font-weight: 500; color: #10241D; margin-bottom: 16px; }
.section-title i { color: #1D9E75; font-size: 17px; }

.explore-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)); gap: 12px; }
.explore-card { text-decoration: none; display: block; }
.explore-card-image { aspect-ratio: 1; border-radius: 14px; overflow: hidden; margin-bottom: 8px; background: #F0F1F0; position: relative; }
.explore-card-image img { width: 100%; height: 100%; object-fit: cover; display: block; }
.explore-card-fallback { width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; font-size: 24px; }
.explore-card-title { font-size: 12.5px; font-weight: 500; color: #10241D; line-height: 1.35; }
.rating-pill {
    display: inline-flex; align-items: center; gap: 4px; font-size: 10.5px; font-weight: 600;
    color: #854F0B; background: #FAEEDA; padding: 3px 9px; border-radius: 999px;
}
.rating-pill--overlay { position: absolute; top: 8px; right: 8px; background: rgba(255,255,255,.92); }
.rating-pill i { color: #E3B23C; }

.empty-state { grid-column: 1 / -1; text-align: center; color: #8FA098; padding: 40px 20px; display: flex; flex-direction: column; align-items: center; gap: 8px; }
.empty-state i { font-size: 26px; }

.scroll-sentinel { display: flex; justify-content: center; padding: 24px 0; }
.loading-spinner { display: flex; align-items: center; gap: 8px; font-size: 13px; color: #8FA098; }
.loading-spinner i { font-size: 16px; animation: spin 0.8s linear infinite; }
@keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
.end-of-feed { font-size: 13px; color: #8FA098; }
</style>
