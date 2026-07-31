<template>
    <AdminLayout>
        <template #title>{{ page ? `Éditer "${page.title}"` : 'Nouvelle page' }}</template>

        <div class="builder-grid">
            <section class="panel">
                <h2 class="panel-title"><i class="ti ti-file-stack"></i> Informations de la page</h2>

                <label class="field">
                    <span>Titre</span>
                    <input v-model="pageForm.title" type="text" class="input" />
                </label>

                <label class="field">
                    <span>URL (slug)</span>
                    <div class="slug-input">
                        <span class="slug-prefix">/</span>
                        <input v-model="pageForm.slug" type="text" class="input" placeholder="auto-généré si vide" />
                    </div>
                </label>

                <label class="field">
                    <span>Meta title (SEO)</span>
                    <input v-model="pageForm.meta_title" type="text" class="input" />
                </label>

                <label class="field">
                    <span>Meta description (SEO)</span>
                    <textarea v-model="pageForm.meta_description" class="input" rows="2"></textarea>
                </label>

                <label class="field field--inline">
                    <input v-model="pageForm.is_active" type="checkbox" />
                    <span>Page active</span>
                </label>

                <button class="btn-primary" @click="savePage">
                    {{ page ? 'Enregistrer la page' : 'Créer la page' }}
                </button>
            </section>

            <section v-if="page" class="panel">
                <h2 class="panel-title"><i class="ti ti-layout-grid"></i> Sections ({{ page.sections.length }})</h2>

                <div v-if="page.sections.length === 0" class="empty-hint">
                    Aucune section. Ajoute-en une ci-dessous — c'est elle qui détermine quel
                    contenu (et de quelles catégories) apparaît sur cette page.
                </div>

                <div v-for="(section, i) in page.sections" :key="section.id" class="section-card">
                    <div class="section-card-icon" :style="{ background: avatarColor(section.id).bg, color: avatarColor(section.id).text }">
                        {{ i + 1 }}
                    </div>
                    <div class="section-card-body">
                        <div class="section-card-header">
                            <strong>{{ section.title || '(sans titre)' }}</strong>
                            <span class="tag-pill">{{ sectionTypes[section.type] }}</span>
                        </div>
                        <div class="section-card-meta">
                            <span v-if="section.type !== 'custom_html'">
                                {{ section.categories.map(c => c.name).join(', ') || 'toutes les catégories' }}
                            </span>
                            <span v-if="section.settings?.limit"> · {{ section.settings.limit }} éléments</span>
                            <span v-if="section.settings?.sort"> · tri {{ section.settings.sort }}</span>
                        </div>
                    </div>
                    <div class="row-actions">
                        <button class="icon-btn" @click="startEditSection(section)"><i class="ti ti-pencil"></i></button>
                        <button class="icon-btn icon-btn--danger" @click="deleteSection(section)"><i class="ti ti-trash"></i></button>
                    </div>
                </div>

                <form class="section-form" @submit.prevent="submitSection">
                    <h3 class="section-form-title">
                        {{ editingSectionId ? 'Modifier la section' : 'Ajouter une section' }}
                    </h3>

                    <label class="field">
                        <span>Titre de la section</span>
                        <input v-model="sectionForm.title" type="text" class="input" />
                    </label>

                    <label class="field">
                        <span>Type</span>
                        <div class="pill-checklist">
                            <button v-for="(label, key) in sectionTypes" :key="key" type="button"
                                class="pill-check" :class="{ on: sectionForm.type === key }"
                                @click="sectionForm.type = key">
                                {{ label }}
                            </button>
                        </div>
                    </label>

                    <label v-if="sectionForm.type === 'custom_html'" class="field">
                        <span>HTML libre</span>
                        <textarea v-model="sectionForm.custom_html" class="input input--code" rows="5"></textarea>
                    </label>

                    <template v-else>
                        <label class="field">
                            <span>Catégories utilisées (vide = toutes)</span>
                            <div class="pill-checklist">
                                <label v-for="cat in categories" :key="cat.id" class="pill-check" :class="{ on: sectionForm.category_ids.includes(cat.id) }">
                                    <input type="checkbox" :value="cat.id" v-model="sectionForm.category_ids" class="sr-only" />
                                    {{ cat.name }}
                                </label>
                            </div>
                        </label>

                        <div class="field-row">
                            <label class="field">
                                <span>Tri</span>
                                <select v-model="sectionForm.settings.sort" class="input">
                                    <option value="recent">Plus récent</option>
                                    <option value="popular">Populaire</option>
                                    <option value="random">Aléatoire</option>
                                </select>
                            </label>
                            <label class="field">
                                <span>Nombre d'éléments</span>
                                <input v-model.number="sectionForm.settings.limit" type="number" min="1" max="48" class="input" />
                            </label>
                        </div>
                    </template>

                    <div class="section-form-actions">
                        <button type="submit" class="btn-primary">
                            {{ editingSectionId ? 'Enregistrer' : 'Ajouter la section' }}
                        </button>
                        <button v-if="editingSectionId" type="button" class="btn-secondary" @click="cancelEditSection">
                            Annuler
                        </button>
                    </div>
                </form>
            </section>
        </div>
    </AdminLayout>
</template>

<script>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { router } from '@inertiajs/vue3';
import { avatarColor } from '@/Components/Admin/avatarPalette.js';

export default {
    layout: null,
    components: { AdminLayout },
    props: {
        page: { type: Object, default: null },
        categories: Array,
        sectionTypes: Object,
    },
    data() {
        return {
            pageForm: {
                title: this.page?.title ?? '',
                slug: this.page?.slug ?? '',
                meta_title: this.page?.meta_title ?? '',
                meta_description: this.page?.meta_description ?? '',
                is_active: this.page?.is_active ?? true,
            },
            editingSectionId: null,
            sectionForm: this.emptySectionForm(),
        };
    },
    methods: {
        avatarColor,
        emptySectionForm() {
            return {
                title: '',
                type: 'masonry_grid',
                category_ids: [],
                custom_html: '',
                settings: { sort: 'recent', limit: 12 },
            };
        },
        savePage() {
            if (this.page) {
                router.put(route('admin.pages.update', this.page.id), this.pageForm, { preserveScroll: true });
            } else {
                router.post(route('admin.pages.store'), this.pageForm);
            }
        },
        startEditSection(section) {
            this.editingSectionId = section.id;
            this.sectionForm = {
                title: section.title ?? '',
                type: section.type,
                category_ids: section.categories.map((c) => c.id),
                custom_html: section.custom_html ?? '',
                settings: { sort: section.settings?.sort ?? 'recent', limit: section.settings?.limit ?? 12 },
            };
        },
        cancelEditSection() {
            this.editingSectionId = null;
            this.sectionForm = this.emptySectionForm();
        },
        submitSection() {
            const url = this.editingSectionId
                ? route('admin.pages.sections.update', [this.page.id, this.editingSectionId])
                : route('admin.pages.sections.store', this.page.id);

            const method = this.editingSectionId ? 'put' : 'post';

            router[method](url, this.sectionForm, {
                preserveScroll: true,
                onSuccess: () => this.cancelEditSection(),
            });
        },
        deleteSection(section) {
            if (confirm('Supprimer cette section ?')) {
                router.delete(route('admin.pages.sections.destroy', [this.page.id, section.id]), { preserveScroll: true });
            }
        },
    },
};
</script>

<style scoped>
.builder-grid { display: grid; grid-template-columns: 320px 1fr; gap: 18px; align-items: start; }
.panel { background: #fff; border: 0.5px solid #E7E9E7; border-radius: 16px; padding: 20px 22px; }
.panel-title {
    font-size: 14.5px; font-weight: 500; margin-bottom: 16px; color: #10241D;
    display: flex; align-items: center; gap: 8px;
}
.panel-title i { color: #1D9E75; font-size: 16px; }
.field { display: flex; flex-direction: column; gap: 7px; margin-bottom: 16px; font-size: 13px; color: #4B5A54; }
.field--inline { flex-direction: row; align-items: center; gap: 8px; }
.field-row { display: flex; gap: 12px; }
.field-row .field { flex: 1; }
.input { border: 0.5px solid #D9DDD9; border-radius: 10px; padding: 9px 12px; font-size: 13.5px; background: #fff; font-family: inherit; }
.input--code { font-family: ui-monospace, monospace; font-size: 12.5px; }
.slug-input { display: flex; align-items: center; }
.slug-prefix { padding: 0 8px; color: #8FA098; }
.sr-only { position: absolute; width: 1px; height: 1px; overflow: hidden; clip: rect(0,0,0,0); }
.btn-primary {
    background: #1D9E75; color: #fff; border: none; border-radius: 20px;
    padding: 9px 18px; font-size: 13.5px; font-weight: 500; cursor: pointer;
}
.btn-secondary {
    background: transparent; color: #6B7B74; border: 0.5px solid #D9DDD9; border-radius: 20px;
    padding: 9px 18px; font-size: 13.5px; cursor: pointer;
}
.empty-hint { color: #8FA098; font-size: 13px; padding: 8px 0 18px; }

.section-card { display: flex; align-items: center; gap: 12px; border: 0.5px solid #E7E9E7; border-radius: 14px; padding: 12px 14px; margin-bottom: 10px; }
.section-card-icon {
    width: 34px; height: 34px; border-radius: 50%; flex-shrink: 0; font-size: 13px; font-weight: 500;
    display: flex; align-items: center; justify-content: center;
}
.section-card-body { flex: 1; min-width: 0; }
.section-card-header { display: flex; align-items: center; gap: 8px; font-size: 13.5px; }
.section-card-meta { font-size: 12px; color: #8FA098; margin-top: 3px; }
.tag-pill { font-size: 10.5px; background: #E7F5EF; color: #1D9E75; padding: 2px 9px; border-radius: 999px; font-weight: 500; }

.section-form { border-top: 1px dashed #E7E9E7; padding-top: 18px; margin-top: 6px; }
.section-form-title { font-size: 13.5px; font-weight: 500; margin-bottom: 14px; color: #10241D; }
.section-form-actions { display: flex; gap: 8px; }

.pill-checklist { display: flex; flex-wrap: wrap; gap: 8px; }
.pill-check {
    font-size: 12.5px; padding: 6px 13px; border-radius: 999px; border: 0.5px solid #E7E9E7;
    color: #6B7B74; cursor: pointer; background: #fff; font-family: inherit;
}
.pill-check.on { background: #1D9E75; border-color: #1D9E75; color: #fff; font-weight: 500; }

.row-actions { display: flex; gap: 4px; flex-shrink: 0; }
.icon-btn {
    width: 28px; height: 28px; display: flex; align-items: center; justify-content: center;
    border-radius: 50%; color: #6B7B74; background: transparent; border: none; cursor: pointer;
}
.icon-btn:hover { background: #F0F1F0; }
.icon-btn--danger:hover { background: #FDECEC; color: #B3261E; }

@media (max-width: 900px) {
    .builder-grid { grid-template-columns: 1fr; }
    .field-row { flex-direction: column; gap: 0; }
}
@media (max-width: 480px) {
    .section-card { flex-wrap: wrap; }
    .section-card-body { min-width: 160px; }
}
</style>
