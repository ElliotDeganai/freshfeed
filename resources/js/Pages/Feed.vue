<template>
    <AppLayout>
        <Head title="Fil d'actualité" />

        <div class="feed-header">
            <h1 class="feed-title">Fil d'actualité</h1>
            <Link :href="route('my-recipes.create')" class="btn-add-recipe">
                <i class="ti ti-plus"></i> <span>Ajouter une recette</span>
            </Link>
        </div>

        <div class="feed-list">
            <article v-for="post in items" :key="post.id" class="post-card">
                <div class="post-card-header">
                    <UserAvatar :user="post.user" :size="36" linkable />
                    <div class="post-header-body">
                        <Link v-if="post.user" :href="route('users.show', post.user.id)" class="post-author">{{ post.user.name }}</Link>
                        <span v-else class="post-author">{{ $page.props.site.name }}</span>
                        <span class="post-meta">{{ timeAgo(post.published_at) }} · {{ post.categories.map(c => c.name).join(', ') || 'Recette' }}</span>
                    </div>
                    <span v-if="post.calories !== null" class="calorie-pill"><i class="ti ti-flame"></i> {{ post.calories }} kcal / 100{{ post.calories_unit || 'g' }}</span>
                    <span v-if="post.ratings_count" class="rating-pill"><i class="ti ti-star"></i> {{ Number(post.ratings_avg_rating).toFixed(1) }} <span class="rating-pill-count">({{ post.ratings_count }})</span></span>
                    <FavoriteButton :post-id="post.id" :favorited="!!post.is_favorited" />
                </div>

                <Link :href="route('posts.show', post.id)" class="post-image-link">
                    <div v-if="post.image_path" class="post-image">
                        <img :src="`/storage/${post.image_path}`" alt="" />
                    </div>
                </Link>

                <div class="post-body">
                    <Link :href="route('posts.show', post.id)" class="post-title">{{ post.title }}</Link>
                </div>
            </article>

            <div v-if="items.length === 0" class="empty-state">
                <i class="ti ti-tools-kitchen-2"></i>
                <p>Aucune recette publiée pour l'instant.</p>
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
import FavoriteButton from '@/Components/FavoriteButton.vue';
import UserAvatar from '@/Components/UserAvatar.vue';

export default {
    layout: null,
    components: { AppLayout, Head, Link, UserAvatar, FavoriteButton },
    props: {
        posts: Object,
    },
    data() {
        return {
            items: [...this.posts.data],
            currentPage: this.posts.current_page,
            hasMore: this.posts.next_page_url !== null,
            loading: false,
            observer: null,
        };
    },
    mounted() {
        this.observer = new IntersectionObserver((entries) => {
            if (entries[0].isIntersecting) this.loadMore();
        }, { rootMargin: '400px' }); // déclenche un peu avant d'atteindre le bas visuel
        this.observer.observe(this.$refs.sentinel);
    },
    beforeUnmount() {
        this.observer?.disconnect();
    },
    methods: {
        loadMore() {
            if (this.loading || !this.hasMore) return;
            this.loading = true;

            fetch(`${route('feed')}?page=${this.currentPage + 1}`, {
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
        timeAgo(dateStr) {
            if (!dateStr) return '';
            const diffMs = Date.now() - new Date(dateStr).getTime();
            const hours = Math.floor(diffMs / 3600000);
            if (hours < 1) return "à l'instant";
            if (hours < 24) return `il y a ${hours}h`;
            const days = Math.floor(hours / 24);
            return `il y a ${days}j`;
        },
    },
};
</script>

<style scoped>
.feed-header {
    display: flex; align-items: center; justify-content: space-between; gap: 12px;
    max-width: 720px; margin: 0 auto 18px;
}
.feed-title { font-size: 20px; font-weight: 500; color: #10241D; }
.btn-add-recipe {
    display: inline-flex; align-items: center; gap: 6px; background: #1D9E75; color: #fff;
    border-radius: 20px; padding: 8px 16px; font-size: 13px; font-weight: 500; text-decoration: none;
    flex-shrink: 0;
}
.btn-add-recipe:hover { background: #178563; }
.btn-add-recipe i { font-size: 15px; }

@media (max-width: 420px) {
    .btn-add-recipe span { display: none; }
    .btn-add-recipe { padding: 9px; }
}

.feed-list { display: flex; flex-direction: column; gap: 14px; max-width: 720px; margin: 0 auto; }

.post-card { background: #fff; border: 0.5px solid #E7E9E7; border-radius: 16px; overflow: hidden; }
.post-card-header { display: flex; align-items: center; gap: 10px; padding: 12px 14px; }
.post-avatar {
    width: 34px; height: 34px; border-radius: 50%; flex-shrink: 0; font-size: 12px; font-weight: 500;
    display: flex; align-items: center; justify-content: center;
}
.post-header-body { display: flex; flex-direction: column; gap: 1px; }
.post-author { font-size: 13px; font-weight: 500; color: #10241D; text-decoration: none; }
.post-author:hover { color: #1D9E75; }
.post-meta { font-size: 11.5px; color: #8FA098; }
.calorie-pill {
    display: inline-flex; align-items: center; gap: 4px; font-size: 11px; font-weight: 600;
    color: #993C1D; background: #FAECE7; padding: 4px 10px; border-radius: 999px; flex-shrink: 0;
}
.rating-pill {
    display: inline-flex; align-items: center; gap: 4px; font-size: 11px; font-weight: 600;
    color: #854F0B; background: #FAEEDA; padding: 4px 10px; border-radius: 999px; flex-shrink: 0;
}
.rating-pill i { color: #E3B23C; }
.rating-pill-count { font-weight: 400; opacity: .8; }

.post-image-link { display: block; }
.post-image { height: 220px; background: #F0F1F0; }
.post-image img { width: 100%; height: 100%; object-fit: cover; display: block; }

.post-body { padding: 12px 14px; }
.post-title { font-size: 14.5px; font-weight: 500; color: #10241D; text-decoration: none; display: inline-block; }
.post-title:hover { color: #1D9E75; }

.empty-state { text-align: center; color: #8FA098; padding: 60px 20px; display: flex; flex-direction: column; align-items: center; gap: 8px; }
.empty-state i { font-size: 30px; }

.scroll-sentinel { display: flex; justify-content: center; padding: 24px 0; }
.loading-spinner { display: flex; align-items: center; gap: 8px; font-size: 13px; color: #8FA098; }
.loading-spinner i { font-size: 16px; animation: spin 0.8s linear infinite; }
@keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
.end-of-feed { font-size: 13px; color: #8FA098; }
</style>
