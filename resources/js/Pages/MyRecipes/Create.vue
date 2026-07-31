<template>
    <AppLayout>
        <Head title="Ajouter une recette" />

        <div class="page-header">
            <h1 class="page-title">Ajouter une recette</h1>
        </div>

        <form class="panel" @submit.prevent>
            <label class="field">
                <span>Titre</span>
                <input v-model="form.title" type="text" class="input" required />
            </label>

            <label class="field">
                <span>Description</span>
                <RichTextEditor v-model="form.content" placeholder="Présente ta recette, son origine, une astuce..." />
            </label>

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

            <label class="field">
                <span>Catégories</span>
                <div class="pill-checklist">
                    <label v-for="cat in categories" :key="cat.id" class="pill-check" :class="{ on: form.category_ids.includes(cat.id) }">
                        <input type="checkbox" :value="cat.id" v-model="form.category_ids" class="sr-only" />
                        {{ cat.name }}
                    </label>
                </div>
            </label>

            <label class="field">
                <span>Image de couverture</span>
                <input type="file" accept="image/*" @change="onFileChange" />
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
            },
        };
    },
    methods: {
        onFileChange(e) {
            this.form.image = e.target.files[0] ?? null;
        },
        addIngredient() {
            this.form.ingredients.push({ amount: '', unit: '', name: '' });
        },
        removeIngredient(i) {
            this.form.ingredients.splice(i, 1);
        },
        submit(publish) {
            const ingredients = this.form.ingredients.filter((ing) => ing.name.trim());
            router.post(route('my-recipes.store'), { ...this.form, ingredients, publish }, { forceFormData: true });
        },
    },
};
</script>

<style scoped>
.page-header { max-width: 560px; margin: 0 auto 18px; }
.page-title { font-size: 20px; font-weight: 500; color: #10241D; }

.panel { background: #fff; border: 0.5px solid #E7E9E7; border-radius: 16px; padding: 24px; max-width: 560px; margin: 0 auto; }
.field { display: flex; flex-direction: column; gap: 8px; margin-bottom: 18px; font-size: 13px; color: #4B5A54; }
.input { border: 0.5px solid #D9DDD9; border-radius: 10px; padding: 9px 12px; font-size: 13.5px; background: #fff; font-family: inherit; }
.input--sm { width: 70px; flex-shrink: 0; }
.calories-row { display: flex; gap: 10px; align-items: center; flex-wrap: wrap; }
.pill-toggle { display: flex; gap: 6px; }
.input--grow { flex: 1; }
.sr-only { position: absolute; width: 1px; height: 1px; overflow: hidden; clip: rect(0,0,0,0); }

.pill-checklist { display: flex; flex-wrap: wrap; gap: 8px; }
.pill-check { font-size: 12.5px; padding: 6px 13px; border-radius: 999px; border: 0.5px solid #E7E9E7; color: #6B7B74; cursor: pointer; background: #fff; }
.pill-check.on { background: #1D9E75; border-color: #1D9E75; color: #fff; font-weight: 500; }

.ingredient-row { display: flex; gap: 8px; margin-bottom: 8px; align-items: center; }
.btn-add-row {
    display: inline-flex; align-items: center; gap: 6px; background: none; border: 1px dashed #D9DDD9;
    border-radius: 10px; padding: 8px 14px; font-size: 12.5px; color: #6B7B74; cursor: pointer; margin-top: 4px;
}
.btn-add-row:hover { background: #F7F8F6; }

.icon-btn {
    width: 30px; height: 30px; display: flex; align-items: center; justify-content: center;
    border-radius: 50%; color: #6B7B74; background: transparent; border: none; cursor: pointer; flex-shrink: 0;
}
.icon-btn:hover { background: #F0F1F0; }
.icon-btn--danger:hover { background: #FDECEC; color: #B3261E; }

.form-actions { display: flex; gap: 10px; margin-top: 6px; }
.form-hint { font-size: 12px; color: #8FA098; margin-top: 10px; }
.btn-primary { background: #1D9E75; color: #fff; border: none; border-radius: 20px; padding: 9px 20px; font-size: 13.5px; font-weight: 500; cursor: pointer; }
.btn-secondary { background: transparent; color: #6B7B74; border: 0.5px solid #D9DDD9; border-radius: 20px; padding: 9px 20px; font-size: 13.5px; cursor: pointer; }
</style>
