<template>
    <AppLayout>
        <Head :title="`Éditer ${post.title}`" />

        <div class="page-header">
            <h1 class="page-title">Éditer "{{ post.title }}"</h1>
        </div>

        <div class="builder-grid">
            <!-- Infos de base -->
            <form class="panel" @submit.prevent="submitPost">
                <label class="field">
                    <span>Titre</span>
                    <input v-model="postForm.title" type="text" class="input" required />
                </label>

                <label class="field">
                    <span>Description</span>
                    <RichTextEditor v-model="postForm.content" />
                </label>

                <label class="field">
                    <span>Calories (optionnel)</span>
                    <div class="calories-row">
                        <input v-model.number="postForm.calories" type="number" min="0" class="input input--sm" />
                        <div class="pill-toggle">
                            <button type="button" class="pill-check" :class="{ on: postForm.calories_unit === 'g' }" @click="postForm.calories_unit = 'g'">pour 100 g</button>
                            <button type="button" class="pill-check" :class="{ on: postForm.calories_unit === 'ml' }" @click="postForm.calories_unit = 'ml'">pour 100 ml</button>
                        </div>
                    </div>
                </label>

                <label class="field">
                    <span>Catégories</span>
                    <div class="pill-checklist">
                        <label v-for="cat in categories" :key="cat.id" class="pill-check" :class="{ on: postForm.category_ids.includes(cat.id) }">
                            <input type="checkbox" :value="cat.id" v-model="postForm.category_ids" class="sr-only" />
                            {{ cat.name }}
                        </label>
                    </div>
                </label>

                <div class="field">
                    <span>Statut</span>
                    <span class="badge" :class="post.status === 'published' ? 'badge--green' : 'badge--gray'">
                        {{ post.status === 'published' ? 'Publiée' : 'Brouillon' }}
                    </span>
                </div>

                <label class="field">
                    <span>Remplacer l'image de couverture</span>
                    <input type="file" accept="image/*" @change="onCoverChange" />
                    <img v-if="post.image_path" :src="`/storage/${post.image_path}`" class="cover-preview" alt="" />
                </label>

                <div class="field">
                    <span>Ingrédients</span>
                    <div v-for="(ing, i) in postForm.ingredients" :key="i" class="ingredient-row">
                        <input v-model="ing.amount" type="text" placeholder="200" class="input input--sm" />
                        <input v-model="ing.unit" type="text" placeholder="g" class="input input--sm" />
                        <input v-model="ing.name" type="text" placeholder="farine de blé" class="input input--grow" />
                        <button type="button" class="icon-btn icon-btn--danger" @click="postForm.ingredients.splice(i, 1)"><i class="ti ti-trash"></i></button>
                    </div>
                    <button type="button" class="btn-add-row" @click="postForm.ingredients.push({ amount: '', unit: '', name: '' })">
                        <i class="ti ti-plus"></i> Ajouter un ingrédient
                    </button>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-secondary">Enregistrer</button>
                    <button v-if="post.status !== 'published'" type="button" class="btn-primary" @click="publish">
                        Publier
                    </button>
                    <button v-else type="button" class="btn-tertiary" @click="unpublish">
                        Remettre en brouillon
                    </button>
                </div>
            </form>

            <!-- Étapes -->
            <section class="panel">
                <h2 class="panel-title"><i class="ti ti-list-numbers"></i> Étapes ({{ post.steps.length }})</h2>

                <div v-for="(step, i) in post.steps" :key="step.id" class="step-card">
                    <div class="step-card-header">
                        <span class="step-number">{{ i + 1 }}</span>
                        <p class="step-instruction">{{ step.instruction }}</p>
                        <button class="icon-btn icon-btn--danger" @click="deleteStep(step)"><i class="ti ti-trash"></i></button>
                    </div>

                    <div v-if="step.images.length || step.video_path" class="step-media">
                        <div v-for="img in step.images" :key="img.id" class="step-media-item">
                            <img :src="`/storage/${img.path}`" alt="" />
                            <button class="media-remove" @click="deleteStepImage(step, img)"><i class="ti ti-x"></i></button>
                        </div>
                        <div v-if="step.video_path" class="step-media-item step-media-item--video">
                            <video :src="`/storage/${step.video_path}`" controls></video>
                            <button class="media-remove" @click="deleteStepVideo(step)"><i class="ti ti-x"></i></button>
                        </div>
                    </div>

                    <div class="step-add-media">
                        <label class="btn-secondary btn-secondary--sm">
                            <input type="file" accept="image/*" multiple class="sr-only" @change="onAddImages(step, $event)" />
                            <i class="ti ti-photo-plus"></i> Ajouter des photos
                        </label>
                        <label v-if="!step.video_path" class="btn-secondary btn-secondary--sm">
                            <input type="file" accept="video/*" class="sr-only" @change="onAddVideo(step, $event)" />
                            <i class="ti ti-video-plus"></i> Ajouter une vidéo
                        </label>
                    </div>
                </div>

                <form class="step-form" @submit.prevent="submitNewStep">
                    <h3 class="step-form-title">Ajouter une étape</h3>
                    <textarea v-model="stepForm.instruction" class="input" rows="3" placeholder="Décris cette étape..." required></textarea>

                    <div class="step-form-media">
                        <label class="btn-secondary btn-secondary--sm">
                            <input type="file" accept="image/*" multiple class="sr-only" @change="onNewStepImages" />
                            <i class="ti ti-photo-plus"></i> Photos ({{ stepForm.images.length }})
                        </label>
                        <label class="btn-secondary btn-secondary--sm">
                            <input type="file" accept="video/*" class="sr-only" @change="onNewStepVideo" />
                            <i class="ti ti-video-plus"></i> {{ stepForm.video ? 'Vidéo sélectionnée' : 'Vidéo' }}
                        </label>
                    </div>

                    <button type="submit" class="btn-primary">Ajouter l'étape</button>
                </form>
            </section>
        </div>
    </AppLayout>
</template>

<script>
import AppLayout from '@/Layouts/AppLayout.vue';
import RichTextEditor from '@/Components/RichTextEditor.vue';
import { Head, router } from '@inertiajs/vue3';

export default {
    layout: null,
    components: { AppLayout, RichTextEditor, Head },
    props: {
        post: Object,
        categories: Array,
    },
    data() {
        return {
            postForm: {
                title: this.post.title,
                content: this.post.content,
                calories: this.post.calories,
                calories_unit: this.post.calories_unit ?? 'g',
                category_ids: this.post.categories.map((c) => c.id),
                status: this.post.status,
                image: null,
                ingredients: this.post.ingredients.length
                    ? this.post.ingredients.map((i) => ({ amount: i.amount, unit: i.unit, name: i.name }))
                    : [{ amount: '', unit: '', name: '' }],
            },
            stepForm: {
                instruction: '',
                images: [],
                video: null,
            },
        };
    },
    methods: {
        onCoverChange(e) {
            this.postForm.image = e.target.files[0] ?? null;
        },
        submitPost() {
            const ingredients = this.postForm.ingredients.filter((ing) => ing.name.trim());
            router.post(route('my-recipes.update', this.post.id), {
                ...this.postForm,
                ingredients,
                _method: 'put',
            }, { forceFormData: true, preserveScroll: true });
        },
        publish() {
            router.put(route('my-recipes.status', this.post.id), { status: 'published' }, { preserveScroll: true });
        },
        unpublish() {
            router.put(route('my-recipes.status', this.post.id), { status: 'draft' }, { preserveScroll: true });
        },

        onNewStepImages(e) {
            this.stepForm.images = Array.from(e.target.files);
        },
        onNewStepVideo(e) {
            this.stepForm.video = e.target.files[0] ?? null;
        },
        submitNewStep() {
            router.post(route('my-recipes.steps.store', this.post.id), this.stepForm, {
                forceFormData: true,
                preserveScroll: true,
                onSuccess: () => { this.stepForm = { instruction: '', images: [], video: null }; },
            });
        },

        onAddImages(step, e) {
            router.post(route('my-recipes.steps.update', [this.post.id, step.id]), {
                instruction: step.instruction,
                images: Array.from(e.target.files),
                _method: 'put',
            }, { forceFormData: true, preserveScroll: true });
        },
        onAddVideo(step, e) {
            router.post(route('my-recipes.steps.update', [this.post.id, step.id]), {
                instruction: step.instruction,
                video: e.target.files[0],
                _method: 'put',
            }, { forceFormData: true, preserveScroll: true });
        },

        deleteStep(step) {
            if (confirm('Supprimer cette étape et ses médias ?')) {
                router.delete(route('my-recipes.steps.destroy', [this.post.id, step.id]), { preserveScroll: true });
            }
        },
        deleteStepImage(step, image) {
            router.delete(route('my-recipes.steps.images.destroy', [this.post.id, step.id, image.id]), { preserveScroll: true });
        },
        deleteStepVideo(step) {
            router.delete(route('my-recipes.steps.video.destroy', [this.post.id, step.id]), { preserveScroll: true });
        },
    },
};
</script>

<style scoped>
.page-header { max-width: 900px; margin: 0 auto 18px; }
.page-title { font-size: 20px; font-weight: 500; color: #10241D; }

.builder-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 18px; max-width: 900px; margin: 0 auto; align-items: start; }
@media (max-width: 800px) { .builder-grid { grid-template-columns: 1fr; } }

.panel { background: #fff; border: 0.5px solid #E7E9E7; border-radius: 16px; padding: 22px; }
.panel-title { font-size: 14.5px; font-weight: 500; color: #10241D; margin-bottom: 16px; display: flex; align-items: center; gap: 8px; }
.panel-title i { color: #1D9E75; font-size: 16px; }

.field { display: flex; flex-direction: column; gap: 8px; margin-bottom: 18px; font-size: 13px; color: #4B5A54; }
.input { border: 0.5px solid #D9DDD9; border-radius: 10px; padding: 9px 12px; font-size: 13.5px; background: #fff; font-family: inherit; }
.input--sm { width: 70px; flex-shrink: 0; }
.calories-row { display: flex; gap: 10px; align-items: center; flex-wrap: wrap; }
.pill-toggle { display: flex; gap: 6px; }
.input--grow { flex: 1; }
.sr-only { position: absolute; width: 1px; height: 1px; overflow: hidden; clip: rect(0,0,0,0); }
.cover-preview { max-width: 160px; margin-top: 8px; border-radius: 12px; }

.pill-checklist { display: flex; flex-wrap: wrap; gap: 8px; }
.pill-check { font-size: 12.5px; padding: 6px 13px; border-radius: 999px; border: 0.5px solid #E7E9E7; color: #6B7B74; cursor: pointer; background: #fff; }
.pill-check.on { background: #1D9E75; border-color: #1D9E75; color: #fff; font-weight: 500; }

.ingredient-row { display: flex; gap: 8px; margin-bottom: 8px; align-items: center; }
.btn-add-row {
    display: inline-flex; align-items: center; gap: 6px; background: none; border: 1px dashed #D9DDD9;
    border-radius: 10px; padding: 8px 14px; font-size: 12.5px; color: #6B7B74; cursor: pointer; margin-top: 4px;
}
.btn-add-row:hover { background: #F7F8F6; }

.form-actions { display: flex; gap: 10px; margin-top: 6px; flex-wrap: wrap; }
.badge { display: inline-flex; width: fit-content; font-size: 11px; padding: 4px 11px; border-radius: 999px; font-weight: 600; }
.badge--green { background: #E7F5EF; color: #146C4E; }
.badge--gray { background: #F0F1F0; color: #6B7B74; }
.btn-primary { background: #1D9E75; color: #fff; border: none; border-radius: 20px; padding: 9px 20px; font-size: 13.5px; font-weight: 500; cursor: pointer; }
.btn-tertiary { background: #FAEEDA; color: #854F0B; border: none; border-radius: 20px; padding: 9px 20px; font-size: 13.5px; font-weight: 500; cursor: pointer; }
.btn-secondary {
    display: inline-flex; align-items: center; gap: 6px; background: transparent; color: #6B7B74;
    border: 0.5px solid #D9DDD9; border-radius: 20px; padding: 9px 16px; font-size: 12.5px; cursor: pointer;
}
.btn-secondary--sm { padding: 7px 13px; font-size: 12px; }
.btn-secondary:hover { background: #F0F1F0; }

.icon-btn {
    width: 28px; height: 28px; display: flex; align-items: center; justify-content: center;
    border-radius: 50%; color: #6B7B74; background: transparent; border: none; cursor: pointer; flex-shrink: 0;
}
.icon-btn:hover { background: #F0F1F0; }
.icon-btn--danger:hover { background: #FDECEC; color: #B3261E; }

.step-card { border: 0.5px solid #E7E9E7; border-radius: 14px; padding: 14px; margin-bottom: 12px; }
.step-card-header { display: flex; align-items: flex-start; gap: 10px; }
.step-number {
    width: 24px; height: 24px; border-radius: 50%; background: #E7F5EF; color: #1D9E75;
    font-size: 11.5px; font-weight: 600; display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.step-instruction { flex: 1; font-size: 13px; color: #10241D; line-height: 1.5; }

.step-media { display: flex; flex-wrap: wrap; gap: 8px; margin: 10px 0; }
.step-media-item { position: relative; width: 72px; height: 72px; border-radius: 10px; overflow: hidden; }
.step-media-item img, .step-media-item video { width: 100%; height: 100%; object-fit: cover; display: block; }
.step-media-item--video { width: 110px; }
.media-remove {
    position: absolute; top: 3px; right: 3px; width: 20px; height: 20px; border-radius: 50%;
    background: rgba(16,36,29,.65); color: #fff; border: none; display: flex; align-items: center; justify-content: center;
    font-size: 11px; cursor: pointer;
}

.step-add-media { display: flex; gap: 8px; flex-wrap: wrap; }

.step-form { border-top: 1px dashed #E7E9E7; padding-top: 16px; margin-top: 6px; }
.step-form-title { font-size: 13.5px; font-weight: 500; color: #10241D; margin-bottom: 10px; }
.step-form textarea { width: 100%; margin-bottom: 10px; }
.step-form-media { display: flex; gap: 8px; margin-bottom: 12px; flex-wrap: wrap; }
</style>
