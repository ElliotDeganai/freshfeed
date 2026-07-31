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
            <article v-for="post in posts.data" :key="post.id" class="post-card">
                <div class="post-card-header">
                    <UserAvatar :user="post.user" :size="36" />
                    <div class="post-header-body">
                        <span class="post-author">{{ post.user?.name ?? 'FreshFeed' }}</span>
                        <span class="post-meta">{{ timeAgo(post.published_at) }} · {{ post.categories.map(c => c.name).join(', ') || 'Recette' }}</span>
                    </div>
                    <span v-if="post.calories !== null" class="calorie-pill"><i class="ti ti-flame"></i> {{ post.calories }} kcal / 100{{ post.calories_unit || 'g' }}</span>
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

            <div v-if="posts.data.length === 0" class="empty-state">
                <i class="ti ti-tools-kitchen-2"></i>
                <p>Aucune recette publiée pour l'instant.</p>
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
import { Head, Link } from '@inertiajs/vue3';
import UserAvatar from '@/Components/UserAvatar.vue';

export default {
    layout: null,
    components: { AppLayout, Head, Link, UserAvatar },
    props: {
        posts: Object,
    },
    methods: {
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
    max-width: 560px; margin: 0 auto 18px;
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

.feed-list { display: flex; flex-direction: column; gap: 14px; max-width: 560px; margin: 0 auto; }

.post-card { background: #fff; border: 0.5px solid #E7E9E7; border-radius: 16px; overflow: hidden; }
.post-card-header { display: flex; align-items: center; gap: 10px; padding: 12px 14px; }
.post-avatar {
    width: 34px; height: 34px; border-radius: 50%; flex-shrink: 0; font-size: 12px; font-weight: 500;
    display: flex; align-items: center; justify-content: center;
}
.post-header-body { display: flex; flex-direction: column; gap: 1px; }
.post-author { font-size: 13px; font-weight: 500; color: #10241D; }
.post-meta { font-size: 11.5px; color: #8FA098; }
.calorie-pill {
    display: inline-flex; align-items: center; gap: 4px; font-size: 11px; font-weight: 600;
    color: #993C1D; background: #FAECE7; padding: 4px 10px; border-radius: 999px; flex-shrink: 0;
}

.post-image-link { display: block; }
.post-image { height: 220px; background: #F0F1F0; }
.post-image img { width: 100%; height: 100%; object-fit: cover; display: block; }

.post-body { padding: 12px 14px; }
.post-title { font-size: 14.5px; font-weight: 500; color: #10241D; text-decoration: none; display: inline-block; }
.post-title:hover { color: #1D9E75; }

.empty-state { text-align: center; color: #8FA098; padding: 60px 20px; display: flex; flex-direction: column; align-items: center; gap: 8px; }
.empty-state i { font-size: 30px; }

.pagination { display: flex; gap: 4px; margin-top: 20px; justify-content: center; }
.page-link {
    padding: 6px 12px; border-radius: 20px; font-size: 13px; text-decoration: none; color: #4B5A54;
    border: 0.5px solid #E7E9E7;
}
.page-link.on { background: #1D9E75; color: #fff; border-color: #1D9E75; }
.page-link.off { opacity: .4; pointer-events: none; }
</style>
