<template>
    <AppLayout>
        <Head title="Ajouter une recette" />

        <div class="page-header">
            <h1 class="page-title">Ajouter une recette</h1>
        </div>

        <div class="builder-grid">
            <!-- Infos de base -->
            <form class="panel" @submit.prevent>
                <label class="field">
                    <span>Titre</span>
                    <input v-model="form.title" type="text" class="input" required />
                </label>

                <div class="field">
                    <span>Description</span>
                    <RichTextEditor v-model="form.content" placeholder="Présente ta recette, son origine, une astuce..." />
                </div>

                <label class="field">
                    <span>Calories (optionnel)</span>
                    <div class="calories-row">
                        <input v-model.number="form.calories" type="number" min="0" class="input input--sm" placeholder="ex: 250" />
                        <div class="pill-toggle">
                            <button type="button" class="pill-check" :class="{ on: form.calories_unit === 'g' }" @click="form.calories_unit = 'g'">pour 100 g</button>
                            <button type="button" class="pill-check" :class="{ on: form.calories_unit === 'ml' }" @click="form.calories_unit = 'ml'">pour 100 ml</button>
                        </div>
                    </div>
                </label>

                <div class="field">
                    <span>Catégories</span>

                    <div v-if="selectedCategories.length" class="pill-checklist pill-checklist--selected">
                        <button
                            v-for="cat in selectedCategories" :key="cat.id" type="button"
                            class="pill-check on" @click="toggleCategory(cat.id)"
                        >
                            {{ cat.name }} <i class="ti ti-x"></i>
                        </button>
                    </div>

                    <div class="category-picker">
                        <input v-model="categorySearch" type="text" placeholder="Rechercher une catégorie..." class="input category-search" />
                        <div class="category-picker-list">
                            <label v-for="cat in filteredCategories" :key="cat.id" class="pill-check pill-check--row" :class="{ on: form.category_ids.includes(cat.id) }">
                                <input type="checkbox" :value="cat.id" v-model="form.category_ids" class="sr-only" />
                                {{ cat.name }}
                                <i v-if="form.category_ids.includes(cat.id)" class="ti ti-check"></i>
                            </label>
                            <p v-if="filteredCategories.length === 0" class="category-empty">Aucune catégorie trouvée.</p>
                        </div>
                    </div>
                </div>

                <label class="field">
                    <span>Image de couverture</span>
                    <input type="file" accept="image/*" @change="onFileChange" />
                    <img v-if="coverPreview" :src="coverPreview" class="cover-preview" alt="" />
                </label>

                <div class="field">
                    <span>Ingrédients</span>
                    <div v-for="(ing, i) in form.ingredients" :key="i" class="ingredient-row">
                        <input v-model="ing.amount" type="text" placeholder="200" class="input input--sm" />
                        <input v-model="ing.unit" type="text" placeholder="g" class="input input--sm" />
                        <input v-model="ing.name" type="text" placeholder="farine de blé" class="input input--grow" />
                        <button type="button" class="icon-btn icon-btn--danger" @click="removeIngredient(i)"><i class="ti ti-trash"></i></button>
                    </div>
                    <button type="button" class="btn-add-row" @click="addIngredient"><i class="ti ti-plus"></i> Ajouter un ingrédient</button>
                </div>

                <div class="form-actions">
                    <button type="button" class="btn-secondary" @click="submit(false)">Enregistrer en brouillon</button>
                    <button type="button" class="btn-primary" @click="submit(true)">Publier</button>
                </div>
            </form>

            <!-- Étapes -->
            <section class="panel">
                <h2 class="panel-title"><i class="ti ti-list-numbers"></i> Étapes ({{ form.steps.length }})</h2>

                <div v-for="(step, i) in form.steps" :key="i" class="step-card">
                    <div class="step-card-header">
                        <span class="step-number">{{ i + 1 }}</span>
                        <p class="step-instruction">{{ step.instruction }}</p>
                        <div class="step-order-btns">
                            <button type="button" class="icon-btn" :disabled="i === 0" @click="moveStep(i, -1)"><i class="ti ti-chevron-up"></i></button>
                            <button type="button" class="icon-btn" :disabled="i === form.steps.length - 1" @click="moveStep(i, 1)"><i class="ti ti-chevron-down"></i></button>
                        </div>
                        <button type="button" class="icon-btn icon-btn--danger" @click="removeStep(i)"><i class="ti ti-trash"></i></button>
                    </div>
                    <div v-if="step.imagePreviews.length || step.video" class="step-media">
                        <div v-for="(src, j) in step.imagePreviews" :key="j" class="step-media-item">
                            <img :src="src" alt="" />
                        </div>
                        <div v-if="step.video" class="step-media-item step-media-item--video">
                            <span class="step-media-video-tag"><i class="ti ti-video"></i> vidéo</span>
                        </div>
                    </div>
                </div>

                <div class="step-form">
                    <p class="step-form-title">Ajouter une étape</p>
                    <textarea v-model="stepDraft.instruction" class="input" rows="3" placeholder="Décris cette étape..."></textarea>
                    <div class="step-form-media">
                        <label class="btn-secondary btn-secondary--sm">
                            <input type="file" accept="image/*" multiple class="sr-only" @change="onStepImages" />
                            <i class="ti ti-photo-plus"></i> Photos ({{ stepDraft.images.length }})
                        </label>
                        <label class="btn-secondary btn-secondary--sm">
                            <input type="file" accept="video/*" class="sr-only" @change="onStepVideo" />
                            <i class="ti ti-video-plus"></i> {{ stepDraft.video ? 'Vidéo sélectionnée' : 'Vidéo' }}
                        </label>
                    </div>
                    <button type="button" class="btn-add-row" @click="addStep"><i class="ti ti-plus"></i> Ajouter l'étape</button>
                </div>
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
    props: { categories: Array },
    data() {
        return {
            form: {
                title: '',
                content: '',
                calories: null,
                calories_unit: 'g',
                category_ids: [],
                image: null,
                ingredients: [{ amount: '', unit: '', name: '' }],
                steps: [],
            },
            coverPreview: null,
            stepDraft: { instruction: '', images: [], imagePreviews: [], video: null },
            categorySearch: '',
        };
    },
    computed: {
        filteredCategories() {
            const q = this.categorySearch.trim().toLowerCase();
            return this.categories.filter((c) => !q || c.name.toLowerCase().includes(q));
        },
        selectedCategories() {
            return this.categories.filter((c) => this.form.category_ids.includes(c.id));
        },
    },
    methods: {
        toggleCategory(id) {
            const i = this.form.category_ids.indexOf(id);
            if (i > -1) this.form.category_ids.splice(i, 1);
            else this.form.category_ids.push(id);
        },
        onFileChange(e) {
            const file = e.target.files[0] ?? null;
            this.form.image = file;
            this.coverPreview = file ? URL.createObjectURL(file) : null;
        },
        addIngredient() {
            this.form.ingredients.push({ amount: '', unit: '', name: '' });
        },
        removeIngredient(i) {
            this.form.ingredients.splice(i, 1);
        },
        onStepImages(e) {
            const files = Array.from(e.target.files);
            this.stepDraft.images = files;
            this.stepDraft.imagePreviews = files.map((f) => URL.createObjectURL(f));
        },
        onStepVideo(e) {
            this.stepDraft.video = e.target.files[0] ?? null;
        },
        addStep() {
            if (!this.stepDraft.instruction.trim()) return;
            this.form.steps.push({ ...this.stepDraft });
            this.stepDraft = { instruction: '', images: [], imagePreviews: [], video: null };
        },
        removeStep(i) {
            this.form.steps.splice(i, 1);
        },
        moveStep(i, direction) {
            const target = i + direction;
            if (target < 0 || target >= this.form.steps.length) return;
            const steps = this.form.steps;
            [steps[i], steps[target]] = [steps[target], steps[i]];
        },
        submit(publish) {
            const ingredients = this.form.ingredients.filter((ing) => ing.name.trim());
            const steps = this.form.steps.map((s) => ({ instruction: s.instruction, images: s.images, video: s.video }));
            router.post(route('my-recipes.store'), { ...this.form, ingredients, steps, publish }, { forceFormData: true });
        },
    },
};
</script>

<style scoped>
.page-header { max-width: 1080px; margin: 0 auto 18px; }
.page-title { font-size: 20px; font-weight: 500; color: #10241D; }

.builder-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 18px; max-width: 1080px; margin: 0 auto; align-items: start; }
@media (max-width: 800px) { .builder-grid { grid-template-columns: 1fr; } }

.panel { background: #fff; border: 0.5px solid #E7E9E7; border-radius: 16px; padding: 22px; }
.panel-title { font-size: 14.5px; font-weight: 500; color: #10241D; margin-bottom: 16px; display: flex; align-items: center; gap: 8px; }
.panel-title i { color: #1D9E75; font-size: 16px; }

.field { display: flex; flex-direction: column; gap: 8px; margin-bottom: 18px; font-size: 13px; color: #4B5A54; }
.input { border: 0.5px solid #D9DDD9; border-radius: 10px; padding: 9px 12px; font-size: 13.5px; background: #fff; font-family: inherit; box-sizing: border-box; }
.input--sm { width: 70px; flex-shrink: 0; }
.calories-row { display: flex; gap: 10px; align-items: center; flex-wrap: wrap; }
.pill-toggle { display: flex; gap: 6px; }
.input--grow { flex: 1; }
.sr-only { position: absolute; width: 1px; height: 1px; overflow: hidden; clip: rect(0,0,0,0); }
.cover-preview { max-width: 160px; margin-top: 8px; border-radius: 12px; }

.pill-checklist { display: flex; flex-wrap: wrap; gap: 8px; }
.pill-checklist--selected { margin-bottom: 10px; }
.pill-checklist--selected .pill-check { display: inline-flex; align-items: center; gap: 5px; }
.pill-checklist--selected .pill-check i { font-size: 13px; }

.category-picker { border: 0.5px solid #E7E9E7; border-radius: 12px; padding: 10px; }
.category-search { width: 100%; margin-bottom: 8px; }
.category-picker-list { display: flex; flex-wrap: wrap; gap: 7px; max-height: 160px; overflow-y: auto; padding: 2px; }
.pill-check--row { display: inline-flex; align-items: center; gap: 5px; }
.pill-check--row i { font-size: 13px; color: #1D9E75; }
.category-empty { font-size: 12px; color: #8FA098; padding: 6px 2px; }
.pill-check { font-size: 12.5px; padding: 6px 13px; border-radius: 999px; border: 0.5px solid #E7E9E7; color: #6B7B74; cursor: pointer; background: #fff; }
.pill-check.on { background: #1D9E75; border-color: #1D9E75; color: #fff; font-weight: 500; }

.ingredient-row { display: flex; gap: 8px; margin-bottom: 8px; align-items: center; }
.btn-add-row {
    display: inline-flex; align-items: center; gap: 6px; background: none; border: 1px dashed #D9DDD9;
    border-radius: 10px; padding: 8px 14px; font-size: 12.5px; color: #6B7B74; cursor: pointer; margin-top: 4px;
}
.btn-add-row:hover { background: #F7F8F6; }

.form-actions { display: flex; gap: 10px; margin-top: 6px; flex-wrap: wrap; }
.btn-primary { background: #1D9E75; color: #fff; border: none; border-radius: 20px; padding: 9px 20px; font-size: 13.5px; font-weight: 500; cursor: pointer; }
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
.step-order-btns { display: flex; flex-direction: column; gap: 0; flex-shrink: 0; }
.step-order-btns .icon-btn { width: 22px; height: 18px; }
.step-order-btns .icon-btn i { font-size: 14px; }
.step-order-btns .icon-btn:disabled { opacity: .25; cursor: default; }
.step-order-btns .icon-btn:disabled:hover { background: none; }
.step-number {
    width: 24px; height: 24px; border-radius: 50%; background: #E7F5EF; color: #1D9E75;
    font-size: 11.5px; font-weight: 600; display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.step-instruction { flex: 1; font-size: 13px; color: #10241D; line-height: 1.5; }

.step-media { display: flex; flex-wrap: wrap; gap: 8px; margin: 10px 0 0 34px; }
.step-media-item { width: 72px; height: 72px; border-radius: 10px; overflow: hidden; }
.step-media-item img { width: 100%; height: 100%; object-fit: cover; display: block; }
.step-media-item--video {
    width: auto; height: auto; display: flex; align-items: center;
}
.step-media-video-tag {
    display: inline-flex; align-items: center; gap: 4px; font-size: 11px; color: #6B7B74;
    background: #F0F1F0; padding: 5px 10px; border-radius: 999px; white-space: nowrap;
}

.step-form { border-top: 1px dashed #E7E9E7; padding-top: 16px; margin-top: 6px; }
.step-form-title { font-size: 13.5px; font-weight: 500; color: #10241D; margin-bottom: 10px; }
.step-form textarea { width: 100%; margin-bottom: 10px; }
.step-form-media { display: flex; gap: 8px; margin-bottom: 12px; flex-wrap: wrap; }
</style>
