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
                <h2 class="panel-title"><i class="ti ti-layout-grid" /> Galerie d'aperçu</h2>
                <p class="hint">Les 6 photos affichées en grille sous le hero.</p>

                <div class="gallery-grid">
                    <div v-for="(img, i) in content.preview_images" :key="i" class="image-slot image-slot--gallery">
                        <div class="image-preview image-preview--small">
                            <img v-if="galleryPreviews[i]" :src="galleryPreviews[i]" alt="" />
                            <div v-else class="image-preview-empty"><i class="ti ti-photo"></i></div>
                        </div>
                        <div class="image-actions">
                            <label class="btn-secondary btn-secondary--sm">
                                <input type="file" accept="image/*" class="sr-only" @change="onGalleryFileChange($event, i)" />
                                {{ img ? 'Remplacer' : 'Ajouter' }}
                            </label>
                            <button v-if="img" type="button" class="btn-danger btn-danger--sm" @click="removeImage(`homepage_preview_image_${i + 1}`)">
                                <i class="ti ti-trash"></i>
                            </button>
                        </div>
                    </div>
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
    props: { content: Object },
    data() {
        return {
            form: useForm({
                hero_title: this.content.hero_title,
                hero_subtitle: this.content.hero_subtitle,
                hero_badge: this.content.hero_badge,
                hero_image: null,
                preview_images: [null, null, null, null, null, null],
            }),
            heroPreviewLocal: null,
            galleryPreviewsLocal: [null, null, null, null, null, null],
        };
    },
    computed: {
        heroPreview() {
            return this.heroPreviewLocal || this.storagePath(this.content.hero_image);
        },
        galleryPreviews() {
            return this.content.preview_images.map(
                (img, i) => this.galleryPreviewsLocal[i] || this.storagePath(img)
            );
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
        onGalleryFileChange(e, i) {
            const file = e.target.files[0];
            if (!file) return;
            this.form.preview_images[i] = file;
            this.galleryPreviewsLocal[i] = URL.createObjectURL(file);
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
.content-form { display: flex; flex-direction: column; gap: 16px; max-width: 640px; }
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

.gallery-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; }

.btn-primary {
    background: #1D9E75; color: #fff; border: none; border-radius: 20px;
    padding: 10px 22px; font-size: 13.5px; font-weight: 500; cursor: pointer; align-self: flex-start;
}
.btn-primary:disabled { opacity: .6; cursor: default; }

@media (max-width: 560px) {
    .gallery-grid { grid-template-columns: repeat(2, 1fr); }
}
</style>
