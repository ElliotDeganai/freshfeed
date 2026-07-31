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
            <div v-for="post in posts.data" :key="post.id" class="recipe-card">
                <div class="recipe-card-icon" :style="{ background: avatarColor(post.id).bg, color: avatarColor(post.id).text }">
                    <i class="ti ti-tools-kitchen-2"></i>
                </div>
                <div class="recipe-card-body">
                    <Link :href="route('posts.show', post.id)" class="recipe-title">{{ post.title }}</Link>
                    <div class="recipe-tags">
                        <span v-for="cat in post.categories" :key="cat.id" class="tag-pill">{{ cat.name }}</span>
                        <span v-if="post.calories !== null" class="calorie-pill"><i class="ti ti-flame"></i> {{ post.calories }} kcal / 100{{ post.calories_unit || 'g' }}</span>
                    </div>
                </div>
                <span class="badge" :class="post.status === 'published' ? 'badge--green' : 'badge--gray'">
                    {{ post.status === 'published' ? 'Publiée' : 'Brouillon' }}
                </span>
                <div class="row-actions">
                    <Link :href="route('posts.show', post.id)" class="icon-btn"><i class="ti ti-eye"></i></Link>
                    <Link :href="route('my-recipes.edit', post.id)" class="icon-btn"><i class="ti ti-pencil"></i></Link>
                    <button class="icon-btn icon-btn--danger" @click="destroy(post)"><i class="ti ti-trash"></i></button>
                </div>
            </div>

            <div v-if="posts.data.length === 0" class="empty-state">
                <i class="ti ti-tools-kitchen-2"></i>
                <p>Tu n'as pas encore ajouté de recette.</p>
                <Link :href="route('my-recipes.create')" class="btn-add-recipe">
                    <i class="ti ti-plus"></i> <span>Ajouter ma première recette</span>
                </Link>
            </div>
        </div>

        <div class="pagination">
            <Link v-for="link in posts.links" :key="link.label" :href="link.url || ''"
                class="page-link" :class="{ on: link.active, off: !link.url }"
                v-html="link.label" />
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
    methods: {
        avatarColor,
        destroy(post) {
            if (confirm(`Supprimer la recette "${post.title}" ?`)) {
                router.delete(route('my-recipes.destroy', post.id));
            }
        },
    },
};
</script>

<style scoped>
.page-header { display: flex; align-items: center; justify-content: space-between; gap: 12px; max-width: 560px; margin: 0 auto 18px; }
.page-title { font-size: 20px; font-weight: 500; color: #10241D; }

.btn-add-recipe {
    display: inline-flex; align-items: center; gap: 6px; background: #1D9E75; color: #fff;
    border-radius: 20px; padding: 8px 16px; font-size: 13px; font-weight: 500; text-decoration: none; flex-shrink: 0;
}
.btn-add-recipe:hover { background: #178563; }
.btn-add-recipe i { font-size: 15px; }

.recipe-list { display: flex; flex-direction: column; gap: 10px; max-width: 560px; margin: 0 auto; }
.recipe-card { display: flex; align-items: center; gap: 14px; background: #fff; border: 0.5px solid #E7E9E7; border-radius: 16px; padding: 14px 18px; }
.recipe-card-icon { width: 40px; height: 40px; border-radius: 50%; flex-shrink: 0; display: flex; align-items: center; justify-content: center; font-size: 17px; }
.recipe-card-body { flex: 1; min-width: 0; display: flex; flex-direction: column; gap: 5px; }
.recipe-title { font-size: 14px; font-weight: 500; color: #10241D; text-decoration: none; }
.recipe-title:hover { color: #1D9E75; }
.recipe-tags { display: flex; gap: 6px; flex-wrap: wrap; }
.tag-pill { font-size: 11px; background: #F0F1F0; color: #6B7B74; padding: 2px 9px; border-radius: 999px; }
.calorie-pill {
    display: inline-flex; align-items: center; gap: 4px; font-size: 11px; font-weight: 600;
    color: #993C1D; background: #FAECE7; padding: 2px 9px; border-radius: 999px;
}

.badge { font-size: 11px; padding: 4px 11px; border-radius: 999px; font-weight: 600; flex-shrink: 0; }
.badge--green { background: #E7F5EF; color: #146C4E; }
.badge--gray { background: #F0F1F0; color: #6B7B74; }

.row-actions { display: flex; gap: 4px; flex-shrink: 0; }
.icon-btn {
    width: 30px; height: 30px; display: flex; align-items: center; justify-content: center;
    border-radius: 50%; color: #6B7B74; background: transparent; border: none; cursor: pointer; text-decoration: none;
}
.icon-btn:hover { background: #F0F1F0; }
.icon-btn--danger:hover { background: #FDECEC; color: #B3261E; }

.empty-state { text-align: center; color: #8FA098; padding: 50px 20px; display: flex; flex-direction: column; align-items: center; gap: 14px; }
.empty-state i { font-size: 28px; }

.pagination { display: flex; gap: 4px; margin-top: 20px; justify-content: center; }
.page-link { padding: 6px 12px; border-radius: 20px; font-size: 13px; text-decoration: none; color: #4B5A54; border: 0.5px solid #E7E9E7; }
.page-link.on { background: #1D9E75; color: #fff; border-color: #1D9E75; }
.page-link.off { opacity: .4; pointer-events: none; }

@media (max-width: 420px) {
    .btn-add-recipe span { display: none; }
    .btn-add-recipe { padding: 9px; }
    .empty-state .btn-add-recipe span { display: inline; }
    .empty-state .btn-add-recipe { padding: 8px 16px; }
}
</style>
