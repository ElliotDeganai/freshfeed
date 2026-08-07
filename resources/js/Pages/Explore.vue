<template>
    <AppLayout>
        <Head title="Explorer" />

        <h1 class="explore-title">Explorer</h1>

        <div class="category-pills">
            <Link href="/explore" class="pill" :class="{ on: !activeCategory }">Toutes</Link>
            <Link v-for="cat in categories" :key="cat.id" :href="`/explore?category=${cat.id}`"
                class="pill" :class="{ on: activeCategory === cat.id }">
                {{ cat.name }} <span class="pill-count">{{ cat.posts_count }}</span>
            </Link>
        </div>

        <div class="explore-grid">
            <Link v-for="post in items" :key="post.id" :href="route('posts.show', post.id)" class="explore-card">
                <div class="explore-card-image">
                    <img v-if="post.image_path" :src="`/storage/${post.image_path}`" alt="" />
                    <div v-else class="explore-card-fallback" :style="{ background: avatarColor(post.id).bg, color: avatarColor(post.id).text }">
                        <i class="ti ti-tools-kitchen-2"></i>
                    </div>
                    <span v-if="post.ratings_count" class="rating-pill rating-pill--overlay"><i class="ti ti-star"></i> {{ Number(post.ratings_avg_rating).toFixed(1) }}</span>
                    <FavoriteButton :post-id="post.id" :favorited="!!post.is_favorited" overlay class="fav-btn--corner" />
                </div>
                <div class="explore-card-title">{{ post.title }}</div>
                <div class="explore-card-badges">
                    <span v-if="post.calories !== null" class="calorie-pill"><i class="ti ti-flame"></i> {{ post.calories }} kcal / 100{{ post.calories_unit || 'g' }}</span>
                </div>
            </Link>

            <div v-if="items.length === 0" class="empty-state">
                <i class="ti ti-compass"></i>
                <p>Aucune recette dans cette catégorie.</p>
            </div>
        </div>

        <!-- Sentinelle observée pour déclencher le chargement de la page suivante -->
        <div ref="sentinel" class="scroll-sentinel">
            <div v-if="loading" class="loading-spinner"><i class="ti ti-loader-2"></i> Chargement...</div>
            <div v-else-if="!hasMore && items.length > 0" class="end-of-feed">Tu as tout vu 👋</div>
        </div>
    </AppLayout>
</template>

<script>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import FavoriteButton from '@/Components/FavoriteButton.vue';
import { avatarColor } from '@/Components/Admin/avatarPalette.js';

export default {
    layout: null,
    components: { AppLayout, Head, Link, FavoriteButton },
    props: {
        categories: Array,
        posts: Object,
        activeCategory: { type: Number, default: null },
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

            const params = new URLSearchParams({ page: this.currentPage + 1 });
            if (this.activeCategory) params.set('category', this.activeCategory);

            fetch(`${route('explore')}?${params}`, {
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
.explore-title { font-size: 20px; font-weight: 500; color: #10241D; margin-bottom: 16px; }

.category-pills { display: flex; gap: 6px; flex-wrap: wrap; margin-bottom: 20px; }
.pill {
    padding: 7px 14px; border-radius: 20px; border: 0.5px solid #E7E9E7; background: #fff;
    color: #6B7B74; font-size: 13px; text-decoration: none; display: inline-flex; align-items: center; gap: 5px;
}
.pill.on { background: #1D9E75; border-color: #1D9E75; color: #fff; font-weight: 500; }
.pill-count { font-size: 11px; opacity: .75; }

.explore-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)); gap: 12px; }
.explore-card { text-decoration: none; display: block; }
.explore-card-image { aspect-ratio: 1; border-radius: 14px; overflow: hidden; margin-bottom: 8px; background: #F0F1F0; position: relative; }
.rating-pill--overlay { position: absolute; top: 8px; right: 8px; background: rgba(255,255,255,.92); }
.fav-btn--corner { position: absolute; top: 8px; left: 8px; }
.explore-card-image img { width: 100%; height: 100%; object-fit: cover; display: block; }
.explore-card-fallback { width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; font-size: 24px; }
.explore-card-title { font-size: 12.5px; font-weight: 500; color: #10241D; line-height: 1.35; }
.explore-card-badges { display: flex; gap: 5px; flex-wrap: wrap; margin-top: 5px; }
.calorie-pill {
    display: inline-flex; align-items: center; gap: 4px; font-size: 10.5px; font-weight: 600;
    color: #993C1D; background: #FAECE7; padding: 3px 9px; border-radius: 999px;
}
.rating-pill {
    display: inline-flex; align-items: center; gap: 4px; font-size: 10.5px; font-weight: 600;
    color: #854F0B; background: #FAEEDA; padding: 3px 9px; border-radius: 999px;
}
.rating-pill i { color: #E3B23C; }

.empty-state { grid-column: 1 / -1; text-align: center; color: #8FA098; padding: 60px 20px; display: flex; flex-direction: column; align-items: center; gap: 8px; }
.empty-state i { font-size: 30px; }

.scroll-sentinel { display: flex; justify-content: center; padding: 24px 0; }
.loading-spinner { display: flex; align-items: center; gap: 8px; font-size: 13px; color: #8FA098; }
.loading-spinner i { font-size: 16px; animation: spin 0.8s linear infinite; }
@keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
.end-of-feed { font-size: 13px; color: #8FA098; }
</style>
