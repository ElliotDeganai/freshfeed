<template>
    <AppLayout>
        <Head :title="post.title" />

        <div class="recipe-page">
            <div v-if="isOwner && post.status !== 'published'" class="preview-banner">
                <i class="ti ti-eye"></i> Aperçu — cette recette est en brouillon, seul toi la vois.
                <Link :href="route('my-recipes.edit', post.id)" class="preview-banner-link">Éditer</Link>
            </div>

            <div class="cover">
                <img v-if="post.image_path" :src="`/storage/${post.image_path}`" alt="" />
                <div v-else class="cover-fallback" :style="{ background: avatarColor(post.id).bg, color: avatarColor(post.id).text }">
                    <i class="ti ti-tools-kitchen-2"></i>
                </div>
            </div>

            <div class="recipe-header">
                <div class="recipe-tags">
                    <span v-for="cat in post.categories" :key="cat.id" class="tag-pill">{{ cat.name }}</span>
                </div>
                <h1 class="recipe-title">{{ post.title }}</h1>

                <div class="recipe-meta">
                    <Link v-if="post.user" :href="route('users.show', post.user.id)" class="meta-author">
                        <UserAvatar :user="post.user" :size="28" />
                        <span>{{ post.user.name }}</span>
                    </Link>
                    <div v-else class="meta-author">
                        <UserAvatar :user="null" :size="28" />
                        <span>{{ $page.props.site.name }}</span>
                    </div>
                    <span v-if="post.calories !== null" class="meta-calories"><i class="ti ti-flame"></i> {{ post.calories }} kcal / 100{{ post.calories_unit || 'g' }}</span>
                </div>
            </div>

            <div v-if="post.content_safe" class="recipe-description" v-html="post.content_safe"></div>

            <div v-if="post.ingredients.length" class="recipe-section">
                <h2 class="section-title"><i class="ti ti-shopping-cart"></i> Ingrédients</h2>
                <ul class="ingredient-list">
                    <li v-for="ing in post.ingredients" :key="ing.id">
                        <span v-if="ing.amount || ing.unit" class="ingredient-qty">{{ ing.amount }} {{ ing.unit }}</span>
                        {{ ing.name }}
                    </li>
                </ul>
            </div>

            <div v-if="post.steps.length" class="recipe-section">
                <h2 class="section-title"><i class="ti ti-list-numbers"></i> Étapes</h2>
                <div v-for="(step, i) in post.steps" :key="step.id" class="step">
                    <div class="step-number">{{ i + 1 }}</div>
                    <div class="step-body">
                        <p class="step-instruction">{{ step.instruction }}</p>
                        <div v-if="step.images.length || step.video_path" class="step-media">
                            <img v-for="img in step.images" :key="img.id" :src="`/storage/${img.path}`" alt="" class="step-media-img" />
                            <video v-if="step.video_path" :src="`/storage/${step.video_path}`" controls class="step-media-video"></video>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { avatarColor } from '@/Components/Admin/avatarPalette.js';
import UserAvatar from '@/Components/UserAvatar.vue';

export default {
    layout: null,
    components: { AppLayout, Head, Link, UserAvatar },
    props: {
        post: Object,
        isOwner: Boolean,
    },
    methods: { avatarColor },
};
</script>

<style scoped>
.recipe-page { max-width: 760px; margin: 0 auto; }

.preview-banner {
    display: flex; align-items: center; gap: 8px; background: #FAEEDA; color: #854F0B;
    font-size: 13px; padding: 10px 16px; border-radius: 12px; margin-bottom: 16px;
}
.preview-banner-link { margin-left: auto; color: #854F0B; font-weight: 600; text-decoration: underline; }

.cover { height: 260px; border-radius: 18px; overflow: hidden; margin-bottom: 18px; background: #F0F1F0; }
.cover img { width: 100%; height: 100%; object-fit: cover; display: block; }
.cover-fallback { width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; font-size: 40px; }

.recipe-header { margin-bottom: 18px; }
.recipe-tags { display: flex; gap: 6px; flex-wrap: wrap; margin-bottom: 10px; }
.tag-pill { font-size: 11.5px; background: #F0F1F0; color: #6B7B74; padding: 3px 11px; border-radius: 999px; }
.recipe-title { font-size: 24px; font-weight: 500; color: #10241D; line-height: 1.3; margin-bottom: 12px; }

.recipe-meta { display: flex; align-items: center; gap: 16px; }
.meta-author { display: flex; align-items: center; gap: 8px; text-decoration: none; }
.meta-author:hover span { color: #1D9E75; }
.meta-avatar { width: 28px; height: 28px; border-radius: 50%; font-size: 11px; font-weight: 500; display: flex; align-items: center; justify-content: center; }
.meta-author span { font-size: 13px; color: #4B5A54; font-weight: 500; }
.meta-calories { display: flex; align-items: center; gap: 5px; font-size: 12.5px; color: #993C1D; background: #FAECE7; padding: 4px 11px; border-radius: 999px; }

.recipe-description { font-size: 13.5px; color: #4B5A54; line-height: 1.65; margin-bottom: 28px; }
.recipe-description :deep(h3) { font-size: 15px; font-weight: 500; color: #10241D; margin: 14px 0 6px; }
.recipe-description :deep(ul), .recipe-description :deep(ol) { padding-left: 20px; margin: 6px 0; }

.recipe-section { margin-bottom: 30px; }
.section-title { display: flex; align-items: center; gap: 8px; font-size: 16px; font-weight: 500; color: #10241D; margin-bottom: 14px; }
.section-title i { color: #1D9E75; font-size: 18px; }

.ingredient-list { list-style: none; display: flex; flex-direction: column; gap: 10px; }
.ingredient-list li { font-size: 13.5px; color: #10241D; padding-bottom: 10px; border-bottom: 0.5px solid #F0F1F0; }
.ingredient-qty { font-weight: 600; color: #1D9E75; margin-right: 6px; }

.step { display: flex; gap: 14px; margin-bottom: 22px; }
.step-number {
    width: 30px; height: 30px; border-radius: 50%; background: #E7F5EF; color: #1D9E75;
    font-size: 13px; font-weight: 600; display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.step-body { flex: 1; }
.step-instruction { font-size: 13.5px; color: #10241D; line-height: 1.6; margin-bottom: 10px; }
.step-media { display: flex; flex-wrap: wrap; gap: 8px; }
.step-media-img { width: 110px; height: 110px; border-radius: 12px; object-fit: cover; }
.step-media-video { width: 100%; max-width: 320px; border-radius: 12px; }
</style>
