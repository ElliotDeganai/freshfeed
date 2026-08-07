<template>
    <component :is="pageLayout">
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

            <div class="title-card-wrap">
                <div class="title-card">
                    <div class="recipe-tags">
                        <span v-for="cat in post.categories" :key="cat.id" class="tag-pill">{{ cat.name }}</span>
                        <span v-if="post.calories !== null" class="meta-calories"><i class="ti ti-flame"></i> {{ post.calories }} kcal / 100{{ post.calories_unit || 'g' }}</span>
                    </div>
                    <h1 class="recipe-title">{{ post.title }}</h1>

                    <Link v-if="post.user && $page.props.auth.user" :href="route('users.show', post.user.id)" class="meta-author">
                        <UserAvatar :user="post.user" :size="26" />
                        <span>{{ post.user.name }}</span>
                    </Link>
                    <div v-else class="meta-author meta-author--static">
                        <UserAvatar :user="post.user" :size="26" />
                        <span>{{ post.user?.name ?? $page.props.site.name }}</span>
                    </div>

                    <div v-if="ratingsCount" class="rating-summary">
                        <span class="rating-stars-static">
                            <i v-for="n in 5" :key="n" class="ti ti-star" :class="{ 'star-on': n <= Math.round(ratingsAverage) }"></i>
                        </span>
                        <span class="rating-summary-text">{{ ratingsAverage }} · {{ ratingsCount }} avis</span>
                    </div>
                    <p v-else class="rating-summary rating-summary--empty">Aucun avis pour l'instant</p>
                </div>

                <div v-if="$page.props.auth.user && !isOwner && post.status === 'published'" class="rate-card">
                    <span class="rate-card-label">{{ myRatingLocal ? 'Ton avis' : 'Note cette recette' }}</span>
                    <div class="rating-stars-interactive" @mouseleave="hoverRating = null">
                        <button
                            v-for="n in 5" :key="n" type="button" class="star-btn"
                            @mouseenter="hoverRating = n" @click="submitRating(n)"
                        >
                            <i class="ti ti-star" :class="{ 'star-on': n <= (hoverRating ?? myRatingLocal ?? 0) }"></i>
                        </button>
                    </div>
                    <button v-if="myRatingLocal" type="button" class="rate-clear-btn" @click="clearRating">Retirer mon avis</button>
                </div>
            </div>

            <div class="recipe-card">
                <div v-if="post.content_safe" class="card-section recipe-description" v-html="post.content_safe"></div>

                <div v-if="post.ingredients.length" class="card-section">
                    <h2 class="card-title">Ingrédients</h2>
                    <ul class="ingredient-list">
                        <li v-for="ing in post.ingredients" :key="ing.id">
                            <span v-if="ing.amount || ing.unit" class="ingredient-qty">{{ ing.amount }} {{ ing.unit }}</span>
                            {{ ing.name }}
                        </li>
                    </ul>
                </div>

                <div v-if="post.steps.length" class="card-section card-section--last">
                    <h2 class="card-title">Préparation</h2>
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
        </div>
    </component>
</template>

<script>
import AppLayout from '@/Layouts/AppLayout.vue';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { avatarColor } from '@/Components/Admin/avatarPalette.js';
import UserAvatar from '@/Components/UserAvatar.vue';

export default {
    layout: null,
    components: { AppLayout, PublicLayout, Head, Link, UserAvatar },
    props: {
        post: Object,
        isOwner: Boolean,
        ratingsAverage: { type: Number, default: null },
        ratingsCount: { type: Number, default: 0 },
        myRating: { type: Number, default: null },
    },
    data() {
        return {
            hoverRating: null,
            myRatingLocal: this.myRating,
        };
    },
    computed: {
        pageLayout() {
            return this.$page.props.auth.user ? 'AppLayout' : 'PublicLayout';
        },
    },
    methods: {
        avatarColor,
        submitRating(n) {
            this.myRatingLocal = n; // optimiste — corrigé si la requête échoue
            router.post(route('posts.rating.store', this.post.id), { rating: n }, {
                preserveScroll: true,
                preserveState: true,
            });
        },
        clearRating() {
            this.myRatingLocal = null;
            router.delete(route('posts.rating.destroy', this.post.id), {
                preserveScroll: true,
                preserveState: true,
            });
        },
    },
};
</script>

<style scoped>
.recipe-page { max-width: 760px; margin: 0 auto; }

.preview-banner {
    display: flex; align-items: center; gap: 8px; background: #FAEEDA; color: #854F0B;
    font-size: 13px; padding: 10px 16px; border-radius: 12px; margin-bottom: 16px;
}
.preview-banner-link { margin-left: auto; color: #854F0B; font-weight: 600; text-decoration: underline; }

.cover { height: 260px; border-radius: 18px; overflow: hidden; background: #F0F1F0; }
.cover img { width: 100%; height: 100%; object-fit: cover; display: block; }
.cover-fallback { width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; font-size: 40px; }

.title-card-wrap { padding: 0 18px; margin-top: -44px; margin-bottom: 18px; position: relative; z-index: 2; }
.title-card {
    background: #fff; border-radius: 16px; padding: 18px 20px;
    box-shadow: 0 10px 26px rgba(16,36,29,.12);
}
.recipe-tags { display: flex; gap: 6px; flex-wrap: wrap; margin-bottom: 10px; }
.tag-pill { font-size: 11.5px; background: #F0F1F0; color: #6B7B74; padding: 3px 11px; border-radius: 999px; }
.recipe-title { font-size: 22px; font-weight: 500; color: #10241D; line-height: 1.3; margin-bottom: 12px; }

.meta-author { display: flex; align-items: center; gap: 8px; text-decoration: none; width: fit-content; }
.meta-author:hover span { color: #1D9E75; }
.meta-author--static:hover span { color: #4B5A54; }
.meta-author span { font-size: 13px; color: #4B5A54; font-weight: 500; }
.meta-calories { display: flex; align-items: center; gap: 5px; font-size: 12.5px; color: #993C1D; background: #FAECE7; padding: 3px 11px; border-radius: 999px; }

.rating-summary { display: flex; align-items: center; gap: 8px; margin-top: 12px; }
.rating-summary--empty { font-size: 12.5px; color: #8FA098; margin: 12px 0 0; }
.rating-stars-static { display: flex; gap: 1px; color: #E3B23C; font-size: 15px; }
.rating-stars-static i.ti-star { color: #D9DDD9; }
.rating-stars-static i.star-on { color: #E3B23C; }
.rating-summary-text { font-size: 12.5px; color: #6B7B74; }

.rate-card {
    margin: 12px 0 0; background: #fff; border-radius: 16px; padding: 14px 20px;
    box-shadow: 0 6px 18px rgba(16,36,29,.08); display: flex; align-items: center; gap: 14px; flex-wrap: wrap;
}
.rate-card-label { font-size: 13px; font-weight: 500; color: #10241D; }
.rating-stars-interactive { display: flex; gap: 2px; }
.star-btn { background: none; border: none; padding: 2px; cursor: pointer; color: #D9DDD9; font-size: 22px; line-height: 1; }
.star-btn .star-on { color: #E3B23C; }
.rate-clear-btn { background: none; border: none; color: #8FA098; font-size: 11.5px; cursor: pointer; text-decoration: underline; margin-left: auto; }

.recipe-card {
    background: #F3FAF6; border: 1.5px solid #C7E8DA; border-radius: 6px;
    margin: 0 18px 24px; box-shadow: 0 4px 14px rgba(16,36,29,.05);
}
.card-section { padding: 20px 24px; border-bottom: 1px dashed #C7E8DA; }
.card-section--last { border-bottom: none; }
.card-title {
    font-family: Georgia, 'Times New Roman', serif; font-size: 15px; font-weight: 600;
    color: #0F6E56; margin: 0 0 14px;
}

.recipe-description { font-size: 13.5px; color: #4B5A54; line-height: 1.7; }
.recipe-description :deep(h3) { font-family: Georgia, serif; font-size: 15px; font-weight: 600; color: #0F6E56; margin: 14px 0 6px; }
.recipe-description :deep(ul), .recipe-description :deep(ol) { padding-left: 20px; margin: 6px 0; }

.ingredient-list { list-style: none; display: grid; grid-template-columns: 1fr 1fr; gap: 9px 24px; }
.ingredient-list li { font-size: 13px; color: #10241D; }
.ingredient-qty { font-weight: 600; color: #1D9E75; margin-right: 6px; }

.step { display: flex; gap: 14px; margin-bottom: 20px; }
.step:last-child { margin-bottom: 0; }
.step-number {
    width: 26px; height: 26px; border-radius: 50%; background: #E1F5EE; color: #0F6E56;
    font-family: Georgia, serif; font-size: 12.5px; font-weight: 600;
    display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.step-body { flex: 1; }
.step-instruction { font-size: 13px; color: #10241D; line-height: 1.65; margin-bottom: 10px; }
.step-media { display: flex; flex-wrap: wrap; gap: 8px; }
.step-media-img { width: 110px; height: 110px; border-radius: 8px; object-fit: cover; display: block; }
.step-media-video { width: 100%; max-width: 320px; border-radius: 8px; display: block; }
</style>
