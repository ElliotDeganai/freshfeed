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

                <div class="field">
                    <span>Description</span>
                    <RichTextEditor v-model="postForm.content" />
                </div>

                <div class="field">
                    <span>Calories (optionnel)</span>
                    <div class="calories-row">
                        <input v-model.number="postForm.calories" type="number" min="0" class="input input--sm" />
                        <div class="pill-toggle">
                            <button type="button" class="pill-check" :class="{ on: postForm.calories_unit === 'g' }" @click="postForm.calories_unit = 'g'">pour 100 g</button>
                            <button type="button" class="pill-check" :class="{ on: postForm.calories_unit === 'ml' }" @click="postForm.calories_unit = 'ml'">pour 100 ml</button>
                        </div>
                    </div>

                    <!-- 🔒 Estimation automatique des calories — masquée temporairement.
                         Code conservé pour réactivation future (voir aussi Create.vue,
                         MyRecipesController.php, AdminLayout.vue "Ingrédients").

                    <div class="calories-meta">
                        <span v-if="post.calories_is_auto" class="auto-badge"><i class="ti ti-wand"></i> Estimation automatique</span>
                        <button type="button" class="link-btn" @click="recalculateCalories">Recalculer depuis les ingrédients</button>
                        <button v-if="post.calories_is_auto && post.calories_breakdown?.length" type="button" class="link-btn" @click="detailOpen = !detailOpen">
                            {{ detailOpen ? 'Masquer le détail' : 'Voir le détail du calcul' }}
                            <i class="ti" :class="detailOpen ? 'ti-chevron-up' : 'ti-chevron-down'"></i>
                        </button>
                    </div>
                    <p class="field-hint">Laisse le champ vide et enregistre pour une estimation automatique.</p>

                    <transition name="detail-collapse">
                        <div v-if="detailOpen && post.calories_breakdown?.length" class="calories-detail">
                            <div v-for="(item, i) in post.calories_breakdown" :key="i" class="detail-row" :class="{ 'detail-row--skipped': item.status === 'skipped' }">
                                <span class="detail-label">{{ item.label }}</span>
                                <template v-if="item.status === 'matched'">
                                    <span class="detail-matched">reconnu comme "{{ item.matched_as }}"<span v-if="item.source === 'api'" class="detail-api-tag">API</span></span>
                                    <span class="detail-calc">{{ item.grams }}g × {{ item.kcal_per_100 }} kcal/100 = <strong>{{ item.kcal_contributed }} kcal</strong></span>
                                </template>
                                <span v-else class="detail-reason"><i class="ti ti-alert-triangle"></i> ignoré — {{ item.reason }}</span>
                            </div>
                            <p class="detail-footnote">
                                Seuls les ingrédients reconnus avec une quantité exploitable comptent dans le total.
                                Corrige une valeur dans <Link :href="route('admin.ingredients.index')">Ingrédients (admin)</Link> si besoin.
                            </p>
                        </div>
                    </transition>
                    -->
                </div>

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
                            <label v-for="cat in filteredCategories" :key="cat.id" class="pill-check pill-check--row" :class="{ on: postForm.category_ids.includes(cat.id) }">
                                <input type="checkbox" :value="cat.id" v-model="postForm.category_ids" class="sr-only" />
                                {{ cat.name }}
                                <i v-if="postForm.category_ids.includes(cat.id)" class="ti ti-check"></i>
                            </label>
                            <p v-if="filteredCategories.length === 0" class="category-empty">Aucune catégorie trouvée.</p>
                        </div>
                    </div>
                </div>

                <div class="field">
                    <span>Statut</span>
                    <span class="badge" :class="post.status === 'published' ? 'badge--green' : 'badge--gray'">
                        {{ post.status === 'published' ? 'Publiée' : 'Brouillon' }}
                    </span>
                </div>

                <label class="field">
                    <span>Remplacer l'image de couverture</span>
                    <input type="file" accept="image/*" @change="onCoverChange" />
                    <img v-if="coverPreview" :src="coverPreview" class="cover-preview" alt="" />
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
                        <div class="step-order-btns">
                            <button type="button" class="icon-btn" :disabled="i === 0" @click="moveStep(i, -1)"><i class="ti ti-chevron-up"></i></button>
                            <button type="button" class="icon-btn" :disabled="i === post.steps.length - 1" @click="moveStep(i, 1)"><i class="ti ti-chevron-down"></i></button>
                        </div>
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
import { Head, Link, router } from '@inertiajs/vue3';

export default {
    layout: null,
    components: { AppLayout, RichTextEditor, Head, Link },
    props: {
        post: Object,
        categories: Array,
    },
    data() {
        return {
            detailOpen: false,
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
            categorySearch: '',
            // Initialisé avec l'image déjà enregistrée — remplacé par un aperçu
            // local dès qu'un nouveau fichier est choisi (voir onCoverChange).
            coverPreview: this.post.image_path ? `/storage/${this.post.image_path}` : null,
        };
    },
    computed: {
        filteredCategories() {
            const q = this.categorySearch.trim().toLowerCase();
            return this.categories.filter((c) => !q || c.name.toLowerCase().includes(q));
        },
        selectedCategories() {
            return this.categories.filter((c) => this.postForm.category_ids.includes(c.id));
        },
    },
    methods: {
        toggleCategory(id) {
            const i = this.postForm.category_ids.indexOf(id);
            if (i > -1) this.postForm.category_ids.splice(i, 1);
            else this.postForm.category_ids.push(id);
        },
        onCoverChange(e) {
            const file = e.target.files[0] ?? null;
            this.postForm.image = file;
            if (file) this.coverPreview = URL.createObjectURL(file);
        },
        recalculateCalories() {
            router.post(route('my-recipes.estimate-calories', this.post.id), {}, { preserveScroll: true });
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

        moveStep(i, direction) {
            const target = i + direction;
            if (target < 0 || target >= this.post.steps.length) return;

            // Échange immédiat côté client pour un retour visuel instantané...
            const steps = this.post.steps;
            [steps[i], steps[target]] = [steps[target], steps[i]];

            // ...puis on persiste le nouvel ordre complet côté serveur.
            router.post(route('my-recipes.steps.reorder', this.post.id), {
                order: steps.map((s) => s.id),
            }, { preserveScroll: true, preserveState: true });
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
.calories-meta { display: flex; align-items: center; gap: 12px; margin-top: 8px; flex-wrap: wrap; }
.auto-badge {
    display: inline-flex; align-items: center; gap: 4px; font-size: 11px; font-weight: 600;
    color: #534AB7; background: #EEEDFE; padding: 3px 9px; border-radius: 999px;
}
.link-btn {
    background: none; border: none; color: #1D9E75; font-size: 11.5px; font-weight: 500; cursor: pointer;
    padding: 0; display: inline-flex; align-items: center; gap: 3px;
}
.link-btn:hover { text-decoration: underline; }
.field-hint { font-size: 11.5px; color: #8FA098; margin-top: 6px; }

.calories-detail {
    margin-top: 10px; border: 0.5px solid #E7E9E7; border-radius: 12px; padding: 12px 14px; background: #FAFBFA;
}
.detail-row {
    display: flex; flex-wrap: wrap; align-items: baseline; gap: 6px 10px;
    padding: 7px 0; border-top: 0.5px solid #EEEFEC; font-size: 12px;
}
.detail-row:first-child { border-top: none; padding-top: 0; }
.detail-label { font-weight: 500; color: #10241D; min-width: 130px; }
.detail-matched { color: #6B7B74; }
.detail-api-tag {
    font-size: 9.5px; font-weight: 600; color: #534AB7; background: #EEEDFE;
    padding: 1px 6px; border-radius: 999px; margin-left: 5px;
}
.detail-calc { color: #4B5A54; margin-left: auto; white-space: nowrap; }
.detail-calc strong { color: #146C4E; }
.detail-row--skipped { opacity: .75; }
.detail-reason { color: #993C1D; font-size: 11.5px; display: inline-flex; align-items: center; gap: 4px; }
.detail-footnote { font-size: 11px; color: #8FA098; margin-top: 10px; padding-top: 10px; border-top: 0.5px solid #EEEFEC; line-height: 1.5; }
.detail-footnote a { color: #1D9E75; }

.detail-collapse-enter-active, .detail-collapse-leave-active { transition: opacity .15s; }
.detail-collapse-enter-from, .detail-collapse-leave-to { opacity: 0; }
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
