<template>
    <AppLayout>
        <Head title="Mes favoris" />

        <div class="page-header">
            <h1 class="page-title">Mes favoris</h1>
        </div>

        <div class="search-box">
            <i class="ti ti-search"></i>
            <input v-model="search" type="text" placeholder="Rechercher dans mes favoris..." @input="debouncedSearch" />
        </div>

        <div class="recipe-list">
            <div v-for="post in items" :key="post.id" class="recipe-card">
                <Link :href="route('posts.show', post.id)" class="recipe-card-image">
                    <img v-if="post.image_path" :src="`/storage/${post.image_path}`" alt="" />
                    <div v-else class="recipe-card-icon" :style="{ background: avatarColor(post.id).bg, color: avatarColor(post.id).text }">
                        <i class="ti ti-tools-kitchen-2"></i>
                    </div>
                </Link>

                <div class="recipe-card-main">
                    <div class="recipe-card-top">
                        <Link :href="route('posts.show', post.id)" class="recipe-title">{{ post.title }}</Link>
                    </div>
                    <div class="recipe-meta">
                        <span v-if="post.user" class="recipe-author">par {{ post.user.name }}</span>
                        <span v-for="cat in post.categories" :key="cat.id" class="tag-pill">{{ cat.name }}</span>
                        <span v-if="post.ratings_count" class="rating-pill"><i class="ti ti-star"></i> {{ Number(post.ratings_avg_rating).toFixed(1) }} ({{ post.ratings_count }})</span>
                    </div>
                </div>

                <div class="row-actions">
                    <FavoriteButton :post-id="post.id" :favorited="true" @click="removeLocal(post.id)" />
                </div>
            </div>

            <div v-if="items.length === 0" class="empty-state">
                <i class="ti ti-heart"></i>
                <p v-if="search">Aucun favori ne correspond à "{{ search }}".</p>
                <template v-else>
                    <p>Aucune recette en favori pour l'instant.</p>
                    <Link :href="route('explore')" class="btn-add-recipe">
                        <i class="ti ti-compass"></i> <span>Explorer les recettes</span>
                    </Link>
                </template>
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
import { Head, Link, router } from '@inertiajs/vue3';
import { avatarColor } from '@/Components/Admin/avatarPalette.js';
import FavoriteButton from '@/Components/FavoriteButton.vue';

export default {
    layout: null,
    components: { AppLayout, Head, Link, FavoriteButton },
    props: {
        posts: Object,
        filters: { type: Object, default: () => ({}) },
    },
    data() {
        return {
            items: [...this.posts.data],
            currentPage: this.posts.current_page,
            hasMore: this.posts.next_page_url !== null,
            loading: false,
            observer: null,
            search: this.filters.search || '',
            debounceTimer: null,
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
    watch: {
        posts(newPosts) {
            this.items = [...newPosts.data];
            this.currentPage = newPosts.current_page;
            this.hasMore = newPosts.next_page_url !== null;
        },
    },
    methods: {
        avatarColor,
        debouncedSearch() {
            clearTimeout(this.debounceTimer);
            this.debounceTimer = setTimeout(() => {
                router.get(route('favorites.index'), { search: this.search || undefined }, {
                    preserveState: true,
                    replace: true,
                });
            }, 350);
        },
        loadMore() {
            if (this.loading || !this.hasMore) return;
            this.loading = true;

            const params = new URLSearchParams({ page: this.currentPage + 1 });
            if (this.search) params.set('search', this.search);

            fetch(`${route('favorites.index')}?${params}`, {
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
        // Un item retiré des favoris disparaît de la liste immédiatement, sans
        // attendre un rechargement — cohérent avec le retrait "en un clic".
        removeLocal(postId) {
            this.items = this.items.filter((p) => p.id !== postId);
        },
    },
};
</script>

<style scoped>
.page-header { max-width: 760px; margin: 0 auto 18px; }
.page-title { font-size: 20px; font-weight: 500; color: #10241D; }

.search-box {
    display: flex; align-items: center; gap: 8px; background: #fff; border: 0.5px solid #E7E9E7;
    border-radius: 20px; padding: 9px 16px; max-width: 760px; margin: 0 auto 14px; color: #8FA098;
}
.search-box i { font-size: 15px; }
.search-box input { border: none; outline: none; background: transparent; font-size: 13.5px; width: 100%; font-family: inherit; color: #10241D; }

.btn-add-recipe {
    display: inline-flex; align-items: center; gap: 6px; background: #1D9E75; color: #fff;
    border-radius: 20px; padding: 8px 16px; font-size: 13px; font-weight: 500; text-decoration: none; flex-shrink: 0;
}
.btn-add-recipe:hover { background: #178563; }

.recipe-list { display: flex; flex-direction: column; gap: 10px; max-width: 760px; margin: 0 auto; }
.recipe-card {
    display: flex; align-items: center; gap: 16px; background: #fff; border: 0.5px solid #E7E9E7;
    border-radius: 16px; padding: 14px 16px; transition: border-color .15s;
}
.recipe-card:hover { border-color: #C7E8DA; }

.recipe-card-image { width: 64px; height: 64px; border-radius: 12px; flex-shrink: 0; overflow: hidden; display: block; }
.recipe-card-image img { width: 100%; height: 100%; object-fit: cover; display: block; }
.recipe-card-icon { width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; font-size: 22px; }

.recipe-card-main { flex: 1; min-width: 0; display: flex; flex-direction: column; gap: 7px; }
.recipe-title {
    font-size: 14.5px; font-weight: 500; color: #10241D; text-decoration: none;
    overflow: hidden; text-overflow: ellipsis; white-space: nowrap; min-width: 0;
}
.recipe-title:hover { color: #1D9E75; }

.recipe-meta { display: flex; gap: 6px; flex-wrap: wrap; align-items: center; }
.recipe-author { font-size: 11.5px; color: #8FA098; }
.tag-pill { font-size: 11px; background: #F0F1F0; color: #6B7B74; padding: 2px 9px; border-radius: 999px; }
.rating-pill {
    display: inline-flex; align-items: center; gap: 4px; font-size: 11px; font-weight: 600;
    color: #854F0B; background: #FAEEDA; padding: 2px 9px; border-radius: 999px;
}
.rating-pill i { color: #E3B23C; }

.row-actions { flex-shrink: 0; padding-left: 8px; border-left: 0.5px solid #F0F1F0; }

.empty-state { text-align: center; color: #8FA098; padding: 50px 20px; display: flex; flex-direction: column; align-items: center; gap: 14px; }
.empty-state i { font-size: 28px; }

.scroll-sentinel { display: flex; justify-content: center; padding: 24px 0; }
.loading-spinner { display: flex; align-items: center; gap: 8px; font-size: 13px; color: #8FA098; }
.loading-spinner i { font-size: 16px; animation: spin 0.8s linear infinite; }
@keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
.end-of-feed { font-size: 13px; color: #8FA098; }
</style>
