<template>
    <AdminLayout>
        <template #title>Ingrédients &amp; nutrition</template>

        <div class="stats-row">
            <div class="stat-chip">{{ stats.total }} ingrédient{{ stats.total > 1 ? 's' : '' }} en base</div>
            <div class="stat-chip stat-chip--api">{{ stats.from_api }} appris via l'API</div>
        </div>

        <p class="panel-hint">
            Base utilisée pour l'estimation automatique des calories des recettes. Corrige une
            valeur si elle te semble fausse, ou ajoute un ingrédient manquant.
        </p>

        <div class="toolbar">
            <div class="search-box">
                <i class="ti ti-search"></i>
                <input v-model="search" type="text" placeholder="Rechercher un ingrédient..." @input="debouncedSearch" />
            </div>
            <div class="pill-tabs">
                <button class="pill-tab" :class="{ on: source === '' }" @click="setSource('')">Tous</button>
                <button class="pill-tab" :class="{ on: source === 'seed' }" @click="setSource('seed')">Référence</button>
                <button class="pill-tab" :class="{ on: source === 'api' }" @click="setSource('api')">Appris (API)</button>
            </div>
        </div>

        <div class="ingredient-list">
            <div class="list-head">
                <span>Ingrédient</span>
                <span>kcal / 100</span>
                <span>Type</span>
                <span>Poids / unité</span>
                <span>Source</span>
                <span></span>
            </div>

            <div v-for="ing in ingredients.data" :key="ing.id" class="ingredient-row">
                <template v-if="editingId === ing.id">
                    <input v-model="editForm.name" class="input input--inline" />
                    <input v-model.number="editForm.kcal_per_100" type="number" min="0" class="input input--inline input--num" />
                    <select v-model="editForm.kind" class="input input--inline">
                        <option value="solid">Solide (g)</option>
                        <option value="liquid">Liquide (ml)</option>
                    </select>
                    <input v-model.number="editForm.standard_unit_weight" type="number" min="0" placeholder="ex: 50" class="input input--inline input--num" />
                    <span class="tag-pill" :class="ing.source === 'api' ? 'tag-pill--api' : ''">{{ ing.source === 'api' ? 'API' : 'Référence' }}</span>
                    <div class="row-actions">
                        <button class="icon-btn" @click="saveEdit(ing)"><i class="ti ti-check"></i></button>
                        <button class="icon-btn" @click="editingId = null"><i class="ti ti-x"></i></button>
                    </div>
                </template>
                <template v-else>
                    <span class="ing-name">{{ ing.name }}</span>
                    <span class="ing-kcal">{{ ing.kcal_per_100 }}</span>
                    <span class="tag-pill">{{ ing.kind === 'liquid' ? 'Liquide' : 'Solide' }}</span>
                    <span class="ing-unit-weight">{{ ing.standard_unit_weight ? `≈ ${ing.standard_unit_weight}g / unité` : '—' }}</span>
                    <span class="tag-pill" :class="ing.source === 'api' ? 'tag-pill--api' : ''">{{ ing.source === 'api' ? 'API' : 'Référence' }}</span>
                    <div class="row-actions">
                        <button class="icon-btn" @click="startEdit(ing)"><i class="ti ti-pencil"></i></button>
                        <button class="icon-btn icon-btn--danger" @click="destroy(ing)"><i class="ti ti-trash"></i></button>
                    </div>
                </template>
            </div>

            <div v-if="ingredients.data.length === 0" class="empty-state">Aucun ingrédient pour ces filtres.</div>
        </div>

        <div class="pagination">
            <Link v-for="link in ingredients.links" :key="link.label" :href="link.url || ''"
                class="page-link" :class="{ on: link.active, off: !link.url }"
                v-html="link.label" />
        </div>

        <section class="add-panel">
            <h2 class="add-panel-title"><i class="ti ti-plus"></i> Ajouter un ingrédient</h2>
            <div class="add-form">
                <input v-model="newForm.name" type="text" placeholder="Nom (ex: chou kale)" class="input" />
                <input v-model.number="newForm.kcal_per_100" type="number" min="0" placeholder="kcal / 100" class="input input--num" />
                <select v-model="newForm.kind" class="input">
                    <option value="solid">Solide (g)</option>
                    <option value="liquid">Liquide (ml)</option>
                </select>
                <input v-model.number="newForm.standard_unit_weight" type="number" min="0" placeholder="poids/unité (g, optionnel)" class="input input--num" />
                <button class="btn-primary" @click="submitNew"><i class="ti ti-plus"></i> Ajouter</button>
            </div>
            <p class="add-form-hint">Le poids/unité sert quand une recette écrit "2 œufs" sans préciser de grammes.</p>
            <textarea v-model="newForm.aliases" class="input" rows="2" placeholder="Variantes connues (une par ligne, optionnel) — ex: chou frisé"></textarea>
        </section>
    </AdminLayout>
</template>

<script>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Link, router } from '@inertiajs/vue3';

export default {
    layout: null,
    components: { AdminLayout, Link },
    props: {
        ingredients: Object,
        filters: Object,
        stats: Object,
    },
    data() {
        return {
            search: this.filters.search || '',
            source: this.filters.source || '',
            debounceTimer: null,
            editingId: null,
            editForm: { name: '', kcal_per_100: 0, kind: 'solid', standard_unit_weight: null },
            newForm: { name: '', kcal_per_100: null, kind: 'solid', aliases: '', standard_unit_weight: null },
        };
    },
    methods: {
        debouncedSearch() {
            clearTimeout(this.debounceTimer);
            this.debounceTimer = setTimeout(() => this.applyFilters(), 350);
        },
        setSource(source) {
            this.source = source;
            this.applyFilters();
        },
        applyFilters() {
            router.get(route('admin.ingredients.index'), { search: this.search, source: this.source }, {
                preserveState: true,
                replace: true,
            });
        },
        startEdit(ing) {
            this.editingId = ing.id;
            this.editForm = { name: ing.name, kcal_per_100: ing.kcal_per_100, kind: ing.kind, standard_unit_weight: ing.standard_unit_weight };
        },
        saveEdit(ing) {
            router.put(route('admin.ingredients.update', ing.id), this.editForm, {
                preserveScroll: true,
                onSuccess: () => { this.editingId = null; },
            });
        },
        destroy(ing) {
            if (confirm(`Supprimer "${ing.name}" de la base nutritionnelle ?`)) {
                router.delete(route('admin.ingredients.destroy', ing.id), { preserveScroll: true });
            }
        },
        submitNew() {
            if (!this.newForm.name.trim() || this.newForm.kcal_per_100 === null) return;
            router.post(route('admin.ingredients.store'), this.newForm, {
                preserveScroll: true,
                onSuccess: () => { this.newForm = { name: '', kcal_per_100: null, kind: 'solid', aliases: '', standard_unit_weight: null }; },
            });
        },
    },
};
</script>

<style scoped>
.stats-row { display: flex; gap: 8px; margin-bottom: 10px; }
.stat-chip { background: #F0F1F0; color: #6B7B74; font-size: 12px; font-weight: 500; padding: 5px 12px; border-radius: 999px; }
.stat-chip--api { background: #EEEDFE; color: #534AB7; }
.panel-hint { font-size: 12.5px; color: #8FA098; margin-bottom: 18px; line-height: 1.5; max-width: 560px; }

.toolbar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; gap: 12px; flex-wrap: wrap; }
.search-box { display: flex; align-items: center; gap: 7px; background: #F0F1F0; border-radius: 20px; padding: 7px 14px; font-size: 13px; color: #6B7B74; }
.search-box input { border: none; background: transparent; outline: none; font-size: 13px; width: 180px; }
.pill-tabs { display: flex; gap: 6px; }
.pill-tab { padding: 7px 15px; border-radius: 20px; border: 0.5px solid #E7E9E7; background: #fff; color: #6B7B74; font-size: 13px; cursor: pointer; }
.pill-tab.on { background: #1D9E75; border-color: #1D9E75; color: #fff; font-weight: 500; }

.ingredient-list { background: #fff; border: 0.5px solid #E7E9E7; border-radius: 16px; overflow: hidden; }
.list-head, .ingredient-row {
    display: grid; grid-template-columns: 1.6fr 0.7fr 0.9fr 1.1fr 0.9fr 0.8fr; gap: 10px; align-items: center;
    padding: 10px 16px;
}
.list-head { font-size: 11.5px; color: #8FA098; font-weight: 500; background: #FAFBFA; border-bottom: 0.5px solid #E7E9E7; }
.ingredient-row { border-top: 0.5px solid #F0F1F0; font-size: 13px; }
.ingredient-row:first-of-type { border-top: none; }
.ing-name { color: #10241D; font-weight: 500; text-transform: capitalize; }
.ing-kcal { color: #4B5A54; }
.ing-unit-weight { color: #8FA098; font-size: 12px; }

.input { border: 0.5px solid #D9DDD9; border-radius: 8px; padding: 7px 10px; font-size: 13px; background: #fff; font-family: inherit; }
.input--inline { padding: 5px 8px; font-size: 12.5px; }
.input--num { width: 100px; }

.tag-pill { font-size: 10.5px; background: #F0F1F0; color: #6B7B74; padding: 3px 9px; border-radius: 999px; text-align: center; width: fit-content; }
.tag-pill--api { background: #EEEDFE; color: #534AB7; }

.row-actions { display: flex; gap: 4px; justify-content: flex-end; }
.icon-btn {
    width: 28px; height: 28px; display: flex; align-items: center; justify-content: center;
    border-radius: 50%; color: #6B7B74; background: transparent; border: none; cursor: pointer;
}
.icon-btn:hover { background: #F0F1F0; }
.icon-btn--danger:hover { background: #FDECEC; color: #B3261E; }

.empty-state { text-align: center; color: #8FA098; padding: 30px; font-size: 13px; }

.pagination { display: flex; gap: 4px; margin: 16px 0; justify-content: center; }
.page-link { padding: 6px 12px; border-radius: 20px; font-size: 13px; text-decoration: none; color: #4B5A54; border: 0.5px solid #E7E9E7; }
.page-link.on { background: #1D9E75; color: #fff; border-color: #1D9E75; }
.page-link.off { opacity: .4; pointer-events: none; }

.add-panel { background: #fff; border: 0.5px solid #E7E9E7; border-radius: 16px; padding: 18px; margin-top: 20px; max-width: 640px; }
.add-panel-title { display: flex; align-items: center; gap: 7px; font-size: 14px; font-weight: 500; color: #10241D; margin-bottom: 12px; }
.add-panel-title i { color: #1D9E75; }
.add-form { display: flex; gap: 8px; margin-bottom: 10px; }
.add-form .input:first-child { flex: 1; }
.add-form-hint { font-size: 11px; color: #8FA098; }
.btn-primary {
    background: #1D9E75; color: #fff; border: none; border-radius: 8px;
    padding: 8px 14px; font-size: 13px; font-weight: 500; display: inline-flex; align-items: center; gap: 5px;
    cursor: pointer; white-space: nowrap;
}
</style>
