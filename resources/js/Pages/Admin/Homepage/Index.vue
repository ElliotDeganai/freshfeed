<template>
    <AdminLayout>
        <template #title>Contenu de la page d'accueil</template>

        <form class="content-form" @submit.prevent="submit">
            <section class="panel">
                <h2 class="panel-title"><i class="ti ti-typography"></i> Texte du hero</h2>

                <label class="field">
                    <span>Badge (petite étiquette au-dessus du titre)</span>
                    <input v-model="form.hero_badge" type="text" class="input" maxlength="80" />
                </label>

                <label class="field">
                    <span>Titre</span>
                    <input v-model="form.hero_title" type="text" class="input" maxlength="120" required />
                </label>

                <label class="field">
                    <span>Sous-titre</span>
                    <textarea v-model="form.hero_subtitle" class="input" rows="3" maxlength="300" required></textarea>
                    <span class="char-count">{{ form.hero_subtitle.length }}/300</span>
                </label>
            </section>

            <section class="panel">
                <h2 class="panel-title"><i class="ti ti-photo"></i> Photo du hero</h2>
                <p class="hint">Affichée à côté du titre sur l'accueil.</p>

                <div class="image-slot">
                    <div class="image-preview">
                        <img v-if="heroPreview" :src="heroPreview" alt="" />
                        <div v-else class="image-preview-empty"><i class="ti ti-photo"></i></div>
                    </div>
                    <div class="image-actions">
                        <label class="btn-secondary">
                            <input type="file" accept="image/*" class="sr-only" @change="onHeroFileChange" />
                            {{ content.hero_image ? 'Remplacer' : 'Ajouter une photo' }}
                        </label>
                        <button v-if="content.hero_image" type="button" class="btn-danger" @click="removeImage('homepage_hero_image')">
                            Supprimer
                        </button>
                    </div>
                </div>
            </section>

            <section class="panel">
                <h2 class="panel-title"><i class="ti ti-star" /> Recette du moment</h2>
                <p class="hint">Mise en avant en grand sur l'accueil.</p>

                <div class="recipe-picker">
                    <input v-model="featuredSearch" type="text" placeholder="Rechercher une recette..." class="input" />
                    <div class="recipe-picker-list">
                        <button
                            v-for="post in filteredFeaturedOptions" :key="post.id" type="button"
                            class="recipe-option" :class="{ on: form.featured_post_id === post.id }"
                            @click="form.featured_post_id = form.featured_post_id === post.id ? null : post.id"
                        >
                            <div class="recipe-option-thumb" :style="!post.image_path ? { background: '#E1F5EE' } : {}">
                                <img v-if="post.image_path" :src="`/storage/${post.image_path}`" alt="" />
                                <i v-else class="ti ti-tools-kitchen-2"></i>
                            </div>
                            <span>{{ post.title }}</span>
                            <i v-if="form.featured_post_id === post.id" class="ti ti-check recipe-option-check"></i>
                        </button>
                        <p v-if="filteredFeaturedOptions.length === 0" class="hint">Aucune recette trouvée.</p>
                    </div>
                </div>
            </section>

            <section class="panel">
                <h2 class="panel-title"><i class="ti ti-layout-grid" /> Recettes de la grille</h2>
                <p class="hint">Jusqu'à {{ maxGridItems }} recettes affichées en tuiles sous la recette du moment.</p>

                <div class="recipe-picker">
                    <input v-model="gridSearch" type="text" placeholder="Rechercher une recette..." class="input" />
                    <div class="recipe-picker-list">
                        <button
                            v-for="post in filteredGridOptions" :key="post.id" type="button"
                            class="recipe-option" :class="{ on: form.grid_post_ids.includes(post.id) }"
                            :disabled="!form.grid_post_ids.includes(post.id) && form.grid_post_ids.length >= maxGridItems"
                            @click="toggleGridPost(post.id)"
                        >
                            <div class="recipe-option-thumb" :style="!post.image_path ? { background: '#E1F5EE' } : {}">
                                <img v-if="post.image_path" :src="`/storage/${post.image_path}`" alt="" />
                                <i v-else class="ti ti-tools-kitchen-2"></i>
                            </div>
                            <span>{{ post.title }}</span>
                            <i v-if="form.grid_post_ids.includes(post.id)" class="ti ti-check recipe-option-check"></i>
                        </button>
                        <p v-if="filteredGridOptions.length === 0" class="hint">Aucune recette trouvée.</p>
                    </div>
                    <p class="hint">{{ form.grid_post_ids.length }} / {{ maxGridItems }} sélectionnée(s)</p>
                </div>
            </section>

            <button type="submit" class="btn-primary" :disabled="form.processing">Enregistrer</button>
        </form>
    </AdminLayout>
</template>

<script>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { useForm, router } from '@inertiajs/vue3';

export default {
    layout: null,
    components: { AdminLayout },
    props: { content: Object, availablePosts: Array, maxGridItems: Number },
    data() {
        return {
            form: useForm({
                hero_title: this.content.hero_title,
                hero_subtitle: this.content.hero_subtitle,
                hero_badge: this.content.hero_badge,
                hero_image: null,
                featured_post_id: this.content.featured_post_id,
                grid_post_ids: [...this.content.grid_post_ids],
            }),
            heroPreviewLocal: null,
            featuredSearch: '',
            gridSearch: '',
        };
    },
    computed: {
        heroPreview() {
            return this.heroPreviewLocal || this.storagePath(this.content.hero_image);
        },
        filteredFeaturedOptions() {
            const q = this.featuredSearch.trim().toLowerCase();
            return this.availablePosts.filter((p) => !q || p.title.toLowerCase().includes(q));
        },
        filteredGridOptions() {
            const q = this.gridSearch.trim().toLowerCase();
            return this.availablePosts.filter((p) => !q || p.title.toLowerCase().includes(q));
        },
    },
    methods: {
        storagePath(path) {
            return path ? `/storage/${path}` : null;
        },
        onHeroFileChange(e) {
            const file = e.target.files[0];
            if (!file) return;
            this.form.hero_image = file;
            this.heroPreviewLocal = URL.createObjectURL(file);
        },
        toggleGridPost(id) {
            const i = this.form.grid_post_ids.indexOf(id);
            if (i > -1) {
                this.form.grid_post_ids.splice(i, 1);
            } else if (this.form.grid_post_ids.length < this.maxGridItems) {
                this.form.grid_post_ids.push(id);
            }
        },
        removeImage(key) {
            if (!confirm('Supprimer cette image ?')) return;
            router.delete(route('admin.homepage.image.destroy'), {
                data: { key },
                preserveScroll: true,
            });
        },
        submit() {
            this.form.post(route('admin.homepage.update'), {
                forceFormData: true,
                preserveScroll: true,
            });
        },
    },
};
</script>

<style scoped>
.content-form { display: flex; flex-direction: column; gap: 16px; max-width: 760px; }
.panel { background: #fff; border: 0.5px solid #E7E9E7; border-radius: 16px; padding: 20px 22px; }
.panel-title {
    font-size: 14.5px; font-weight: 500; margin-bottom: 6px; color: #10241D;
    display: flex; align-items: center; gap: 8px;
}
.panel-title i { color: #1D9E75; font-size: 16px; }
.hint { font-size: 12.5px; color: #8FA098; margin-bottom: 16px; }

.field { display: flex; flex-direction: column; gap: 7px; margin: 16px 0; font-size: 13px; color: #4B5A54; }
.input { border: 0.5px solid #D9DDD9; border-radius: 10px; padding: 9px 12px; font-size: 13.5px; background: #fff; font-family: inherit; }
.char-count { font-size: 11px; color: #8FA098; align-self: flex-end; }
.sr-only { position: absolute; width: 1px; height: 1px; overflow: hidden; clip: rect(0,0,0,0); }

.image-slot { display: flex; align-items: center; gap: 14px; }
.image-preview {
    width: 100px; height: 76px; border-radius: 12px; overflow: hidden; background: #EDEFEC; flex-shrink: 0;
}
.image-preview--small { width: 72px; height: 60px; border-radius: 10px; }
.image-preview img { width: 100%; height: 100%; object-fit: cover; display: block; }
.image-preview-empty {
    width: 100%; height: 100%; display: flex; align-items: center; justify-content: center;
    color: rgba(16, 36, 29, 0.25); font-size: 20px;
}
.image-actions { display: flex; flex-direction: column; gap: 6px; align-items: flex-start; }

.btn-secondary {
    background: transparent; color: #4B5A54; border: 0.5px solid #D9DDD9; border-radius: 20px;
    padding: 7px 14px; font-size: 12.5px; cursor: pointer; display: inline-block;
}
.btn-secondary:hover { background: #F0F1F0; }
.btn-secondary--sm { padding: 5px 10px; font-size: 11.5px; }
.btn-danger {
    background: transparent; color: #B3261E; border: none; font-size: 12px; cursor: pointer; padding: 2px 4px;
}
.btn-danger--sm { display: flex; align-items: center; justify-content: center; }

.recipe-picker { display: flex; flex-direction: column; gap: 10px; }
.recipe-picker-list {
    display: flex; flex-direction: column; gap: 4px; max-height: 260px; overflow-y: auto;
    border: 0.5px solid #E7E9E7; border-radius: 12px; padding: 6px;
}
.recipe-option {
    display: flex; align-items: center; gap: 10px; padding: 7px 9px; border-radius: 9px;
    border: none; background: transparent; cursor: pointer; text-align: left; width: 100%; font-family: inherit;
}
.recipe-option:hover { background: #F7F8F6; }
.recipe-option.on { background: #E7F5EF; }
.recipe-option:disabled { opacity: .35; cursor: not-allowed; }
.recipe-option-thumb { width: 36px; height: 36px; border-radius: 8px; overflow: hidden; flex-shrink: 0; display: flex; align-items: center; justify-content: center; color: #0F6E56; font-size: 15px; }
.recipe-option-thumb img { width: 100%; height: 100%; object-fit: cover; display: block; }
.recipe-option span { font-size: 12.5px; color: #10241D; flex: 1; }
.recipe-option-check { color: #1D9E75; font-size: 16px; flex-shrink: 0; }

.btn-primary {
    background: #1D9E75; color: #fff; border: none; border-radius: 20px;
    padding: 10px 22px; font-size: 13.5px; font-weight: 500; cursor: pointer; align-self: flex-start;
}
.btn-primary:disabled { opacity: .6; cursor: default; }
</style>
