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
            <Link v-for="post in posts.data" :key="post.id" :href="route('posts.show', post.id)" class="explore-card">
                <div class="explore-card-image">
                    <img v-if="post.image_path" :src="`/storage/${post.image_path}`" alt="" />
                    <div v-else class="explore-card-fallback" :style="{ background: avatarColor(post.id).bg, color: avatarColor(post.id).text }">
                        <i class="ti ti-tools-kitchen-2"></i>
                    </div>
                </div>
                <div class="explore-card-title">{{ post.title }}</div>
                <span v-if="post.calories !== null" class="calorie-pill"><i class="ti ti-flame"></i> {{ post.calories }} kcal / 100{{ post.calories_unit || 'g' }}</span>
            </Link>

            <div v-if="posts.data.length === 0" class="empty-state">
                <i class="ti ti-compass"></i>
                <p>Aucune recette dans cette catégorie.</p>
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
import { avatarColor } from '@/Components/Admin/avatarPalette.js';

export default {
    layout: null,
    components: { AppLayout, Head, Link },
    props: {
        categories: Array,
        posts: Object,
        activeCategory: { type: Number, default: null },
    },
    methods: { avatarColor },
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
.explore-card-image { aspect-ratio: 1; border-radius: 14px; overflow: hidden; margin-bottom: 8px; background: #F0F1F0; }
.explore-card-image img { width: 100%; height: 100%; object-fit: cover; display: block; }
.explore-card-fallback { width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; font-size: 24px; }
.explore-card-title { font-size: 12.5px; font-weight: 500; color: #10241D; line-height: 1.35; }
.calorie-pill {
    display: inline-flex; align-items: center; gap: 4px; font-size: 10.5px; font-weight: 600;
    color: #993C1D; background: #FAECE7; padding: 3px 9px; border-radius: 999px; margin-top: 5px;
}

.empty-state { grid-column: 1 / -1; text-align: center; color: #8FA098; padding: 60px 20px; display: flex; flex-direction: column; align-items: center; gap: 8px; }
.empty-state i { font-size: 30px; }

.pagination { display: flex; gap: 4px; margin-top: 20px; justify-content: center; }
.page-link {
    padding: 6px 12px; border-radius: 20px; font-size: 13px; text-decoration: none; color: #4B5A54;
    border: 0.5px solid #E7E9E7;
}
.page-link.on { background: #1D9E75; color: #fff; border-color: #1D9E75; }
.page-link.off { opacity: .4; pointer-events: none; }
</style>
