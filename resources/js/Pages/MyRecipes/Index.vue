<template>
    <AppLayout>
        <Head title="Mes recettes" />

        <div class="page-header">
            <h1 class="page-title">Mes recettes</h1>
            <Link :href="route('my-recipes.create')" class="btn-add-recipe">
                <i class="ti ti-plus"></i> <span>Ajouter une recette</span>
            </Link>
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
                        <span class="badge" :class="post.status === 'published' ? 'badge--green' : 'badge--gray'">
                            {{ post.status === 'published' ? 'Publiée' : 'Brouillon' }}
                        </span>
                    </div>
                    <div class="recipe-tags">
                        <span v-for="cat in post.categories" :key="cat.id" class="tag-pill">{{ cat.name }}</span>
                        <span v-if="post.calories !== null" class="calorie-pill"><i class="ti ti-flame"></i> {{ post.calories }} kcal / 100{{ post.calories_unit || 'g' }}</span>
                    </div>
                </div>

                <div class="row-actions">
                    <Link :href="route('posts.show', post.id)" class="icon-btn"><i class="ti ti-eye"></i></Link>
                    <Link :href="route('my-recipes.edit', post.id)" class="icon-btn"><i class="ti ti-pencil"></i></Link>
                    <button class="icon-btn icon-btn--danger" @click="destroy(post)"><i class="ti ti-trash"></i></button>
                </div>
            </div>

            <div v-if="items.length === 0" class="empty-state">
                <i class="ti ti-tools-kitchen-2"></i>
                <p>Tu n'as pas encore ajouté de recette.</p>
                <Link :href="route('my-recipes.create')" class="btn-add-recipe">
                    <i class="ti ti-plus"></i> <span>Ajouter ma première recette</span>
                </Link>
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

export default {
    layout: null,
    components: { AppLayout, Head, Link },
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

            fetch(`${route('my-recipes.index')}?page=${this.currentPage + 1}`, {
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
        destroy(post) {
            if (confirm(`Supprimer la recette "${post.title}" ?`)) {
                router.delete(route('my-recipes.destroy', post.id), {
                    preserveState: true,
                    preserveScroll: true,
                    preserveUrl: true,
                    onSuccess: () => {
                        this.items = this.items.filter((p) => p.id !== post.id);
                    },
                });
            }
        },
    },
};
</script>

<style scoped>
.page-header { display: flex; align-items: center; justify-content: space-between; gap: 12px; max-width: 760px; margin: 0 auto 18px; }
.page-title { font-size: 20px; font-weight: 500; color: #10241D; }

.btn-add-recipe {
    display: inline-flex; align-items: center; gap: 6px; background: #1D9E75; color: #fff;
    border-radius: 20px; padding: 8px 16px; font-size: 13px; font-weight: 500; text-decoration: none; flex-shrink: 0;
}
.btn-add-recipe:hover { background: #178563; }
.btn-add-recipe i { font-size: 15px; }

.recipe-list { display: flex; flex-direction: column; gap: 10px; max-width: 760px; margin: 0 auto; }
.recipe-card {
    display: flex; align-items: center; gap: 16px; background: #fff; border: 0.5px solid #E7E9E7;
    border-radius: 16px; padding: 14px 16px; transition: border-color .15s;
}
.recipe-card:hover { border-color: #C7E8DA; }

.recipe-card-image {
    width: 64px; height: 64px; border-radius: 12px; flex-shrink: 0; overflow: hidden; display: block;
}
.recipe-card-image img { width: 100%; height: 100%; object-fit: cover; display: block; }
.recipe-card-icon { width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; font-size: 22px; }

.recipe-card-main { flex: 1; min-width: 0; display: flex; flex-direction: column; gap: 7px; }
.recipe-card-top { display: flex; align-items: center; gap: 10px; }
.recipe-title {
    font-size: 14.5px; font-weight: 500; color: #10241D; text-decoration: none;
    overflow: hidden; text-overflow: ellipsis; white-space: nowrap; min-width: 0;
}
.recipe-title:hover { color: #1D9E75; }

.recipe-tags { display: flex; gap: 6px; flex-wrap: wrap; }
.tag-pill { font-size: 11px; background: #F0F1F0; color: #6B7B74; padding: 2px 9px; border-radius: 999px; }
.calorie-pill {
    display: inline-flex; align-items: center; gap: 4px; font-size: 11px; font-weight: 600;
    color: #993C1D; background: #FAECE7; padding: 2px 9px; border-radius: 999px;
}

.badge { font-size: 10.5px; padding: 3px 10px; border-radius: 999px; font-weight: 600; flex-shrink: 0; white-space: nowrap; }
.badge--green { background: #E7F5EF; color: #146C4E; }
.badge--gray { background: #F0F1F0; color: #6B7B74; }

.row-actions { display: flex; gap: 2px; flex-shrink: 0; padding-left: 8px; border-left: 0.5px solid #F0F1F0; }
.icon-btn {
    width: 30px; height: 30px; display: flex; align-items: center; justify-content: center;
    border-radius: 50%; color: #6B7B74; background: transparent; border: none; cursor: pointer; text-decoration: none;
}
.icon-btn:hover { background: #F0F1F0; }
.icon-btn--danger:hover { background: #FDECEC; color: #B3261E; }

.empty-state { text-align: center; color: #8FA098; padding: 50px 20px; display: flex; flex-direction: column; align-items: center; gap: 14px; }
.empty-state i { font-size: 28px; }

.scroll-sentinel { display: flex; justify-content: center; padding: 24px 0; }
.loading-spinner { display: flex; align-items: center; gap: 8px; font-size: 13px; color: #8FA098; }
.loading-spinner i { font-size: 16px; animation: spin 0.8s linear infinite; }
@keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
.end-of-feed { font-size: 13px; color: #8FA098; }

@media (max-width: 420px) {
    .btn-add-recipe span { display: none; }
    .btn-add-recipe { padding: 9px; }
    .empty-state .btn-add-recipe span { display: inline; }
    .empty-state .btn-add-recipe { padding: 8px 16px; }
}
</style>
