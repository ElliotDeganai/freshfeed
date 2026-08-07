<template>
    <button
        type="button" class="fav-btn" :class="{ on: localFavorited, 'fav-btn--overlay': overlay }"
        :title="localFavorited ? 'Retirer des favoris' : 'Ajouter aux favoris'"
        @click.stop.prevent="toggle"
    >
        <i class="ti ti-heart" :class="{ 'fav-icon-on': localFavorited }"></i>
    </button>
</template>

<script>
import { router } from '@inertiajs/vue3';

export default {
    props: {
        postId: { type: Number, required: true },
        favorited: { type: Boolean, default: false },
        overlay: { type: Boolean, default: false },
    },
    data() {
        return {
            localFavorited: this.favorited,
        };
    },
    watch: {
        favorited(v) {
            this.localFavorited = v;
        },
    },
    methods: {
        toggle() {
            this.localFavorited = !this.localFavorited; // optimiste
            const action = this.localFavorited
                ? router.post(route('posts.favorite.store', this.postId), {}, { preserveScroll: true, preserveState: true })
                : router.delete(route('posts.favorite.destroy', this.postId), { preserveScroll: true, preserveState: true });
        },
    },
};
</script>

<style scoped>
.fav-btn {
    width: 30px; height: 30px; border-radius: 50%; border: none; cursor: pointer;
    display: flex; align-items: center; justify-content: center; flex-shrink: 0;
    background: #F0F1F0; color: #6B7B74; font-size: 15px; transition: transform .1s;
}
.fav-btn:hover { transform: scale(1.08); }
.fav-btn.on { background: #FBEAF0; }
.fav-btn .fav-icon-on { color: #E0457B; }

.fav-btn--overlay { background: rgba(255,255,255,.92); }
.fav-btn--overlay.on { background: rgba(255,255,255,.95); }
</style>
