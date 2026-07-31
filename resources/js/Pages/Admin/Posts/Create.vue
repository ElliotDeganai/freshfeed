<template>
    <AdminLayout>
        <template #title>Nouvelle recette</template>

        <form class="panel post-form" @submit.prevent="submit(false)">
            <label class="field">
                <span>Titre</span>
                <input v-model="form.title" type="text" class="input" required />
            </label>

            <label class="field">
                <span>Contenu</span>
                <textarea v-model="form.content" class="input" rows="10"></textarea>
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

            <div class="form-actions">
                <button type="button" class="btn-secondary" @click="submit(false)">
                    {{ can.publish ? 'Enregistrer en brouillon' : 'Enregistrer ma recette' }}
                </button>
                <button v-if="can.publish" type="button" class="btn-primary" @click="submit(true)">Publier</button>
            </div>
            <p v-if="!can.publish" class="form-hint">
                Ta recette sera enregistrée en brouillon — un éditeur la publiera après relecture.
            </p>
        </form>
    </AdminLayout>
</template>

<script>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { router } from '@inertiajs/vue3';

export default {
    layout: null,
    components: { AdminLayout },
    props: { categories: Array, can: Object },
    data() {
        return {
            form: {
                title: '',
                content: '',
                calories: null,
                calories_unit: 'g',
                category_ids: [],
                image: null,
            },
        };
    },
    methods: {
        onFileChange(e) {
            this.form.image = e.target.files[0] ?? null;
        },
        submit(publish) {
            router.post(route('admin.posts.store'), { ...this.form, publish }, {
                forceFormData: true,
            });
        },
    },
};
</script>

<style scoped>
.panel { background: #fff; border: 0.5px solid #E7E9E7; border-radius: 16px; padding: 24px; max-width: 640px; }
.field { display: flex; flex-direction: column; gap: 8px; margin-bottom: 18px; font-size: 13px; color: #4B5A54; }
.input { border: 0.5px solid #D9DDD9; border-radius: 10px; padding: 9px 12px; font-size: 13.5px; background: #fff; font-family: inherit; }
.input--sm { width: 90px; flex-shrink: 0; }
.calories-row { display: flex; gap: 10px; align-items: center; flex-wrap: wrap; }
.pill-toggle { display: flex; gap: 6px; }
.sr-only { position: absolute; width: 1px; height: 1px; overflow: hidden; clip: rect(0,0,0,0); }
.pill-checklist { display: flex; flex-wrap: wrap; gap: 8px; }
.pill-check {
    font-size: 12.5px; padding: 6px 13px; border-radius: 999px; border: 0.5px solid #E7E9E7;
    color: #6B7B74; cursor: pointer; background: #fff;
}
.pill-check.on { background: #1D9E75; border-color: #1D9E75; color: #fff; font-weight: 500; }
.form-actions { display: flex; gap: 10px; margin-top: 6px; }
.form-hint { font-size: 12px; color: #8FA098; margin-top: 10px; }
.btn-primary {
    background: #1D9E75; color: #fff; border: none; border-radius: 20px;
    padding: 9px 20px; font-size: 13.5px; font-weight: 500; cursor: pointer;
}
.btn-secondary {
    background: transparent; color: #6B7B74; border: 0.5px solid #D9DDD9; border-radius: 20px;
    padding: 9px 20px; font-size: 13.5px; cursor: pointer;
}
</style>
