<template>
    <AdminLayout>
        <template #title>Paramètres du site</template>

        <form class="settings-form" @submit.prevent="submit">
            <section class="panel">
                <h2 class="panel-title"><i class="ti ti-sparkles"></i> Identité</h2>

                <label class="field">
                    <span>Nom de l'application</span>
                    <input v-model="form.app_name" type="text" class="input" />
                </label>

                <label class="field">
                    <span>Titre du navigateur</span>
                    <input v-model="form.browser_title" type="text" class="input" />
                </label>

                <label class="field">
                    <span>Logo</span>
                    <input type="file" accept="image/png,image/jpeg,image/svg+xml" @change="onLogoChange" />
                    <img v-if="settings.logo_path" :src="`/storage/${settings.logo_path}`" class="logo-preview" alt="Logo actuel" />
                </label>
            </section>

            <section class="panel">
                <h2 class="panel-title"><i class="ti ti-search"></i> SEO</h2>

                <label class="field">
                    <span>Meta description</span>
                    <textarea v-model="form.meta_description" class="input" rows="3" maxlength="160"></textarea>
                    <span class="char-count">{{ (form.meta_description || '').length }}/160</span>
                </label>

                <label class="field">
                    <span>Domaine canonique</span>
                    <input v-model="form.canonical_domain" type="text" placeholder="freshfeed.ch" class="input" />
                </label>

                <div class="field">
                    <span>Aperçu Google</span>
                    <div class="google-preview">
                        <div class="google-preview-url">
                            <div class="google-preview-favicon">
                                <img v-if="settings.logo_path" :src="`/storage/${settings.logo_path}`" alt="" />
                                <i v-else class="ti ti-world"></i>
                            </div>
                            <span>{{ form.canonical_domain || 'freshfeed.ch' }}</span>
                        </div>
                        <div class="google-preview-title">{{ form.browser_title || form.app_name }}</div>
                        <div class="google-preview-desc">
                            {{ form.meta_description || 'Aucune meta description définie pour le moment.' }}
                        </div>
                    </div>
                </div>
            </section>

            <section class="panel">
                <h2 class="panel-title"><i class="ti ti-link"></i> URLs des pages</h2>
                <p class="hint">Changer un slug crée automatiquement une redirection 301 depuis l'ancienne URL.</p>

                <label class="field">
                    <span>Page fil d'actualité</span>
                    <input v-model="form.slug_feed" type="text" class="input" />
                </label>

                <label class="field">
                    <span>Page explorer</span>
                    <input v-model="form.slug_explore" type="text" class="input" />
                </label>
            </section>

            <button type="submit" class="btn-primary">Enregistrer</button>
        </form>
    </AdminLayout>
</template>

<script>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { useForm } from '@inertiajs/vue3';

export default {
    layout: null,
    components: { AdminLayout },
    props: { settings: Object },
    data() {
        return {
            form: useForm({
                app_name: this.settings.app_name,
                browser_title: this.settings.browser_title,
                meta_description: this.settings.meta_description,
                canonical_domain: this.settings.canonical_domain,
                slug_feed: this.settings.slug_feed,
                slug_explore: this.settings.slug_explore,
                logo: null,
            }),
        };
    },
    methods: {
        onLogoChange(e) {
            this.form.logo = e.target.files[0] ?? null;
        },
        submit() {
            this.form.transform((data) => ({ ...data, _method: 'put' }))
                .post(route('admin.settings.update'), {
                    forceFormData: true,
                    preserveScroll: true,
                });
        },
    },
};
</script>

<style scoped>
.settings-form { display: flex; flex-direction: column; gap: 16px; max-width: 680px; }
.panel { background: #fff; border: 0.5px solid #E7E9E7; border-radius: 16px; padding: 20px 22px; }
.panel-title {
    font-size: 14.5px; font-weight: 500; margin-bottom: 16px; color: #10241D;
    display: flex; align-items: center; gap: 8px;
}
.panel-title i { color: #1D9E75; font-size: 16px; }
.field { display: flex; flex-direction: column; gap: 7px; margin-bottom: 16px; font-size: 13px; color: #4B5A54; }
.field:last-child { margin-bottom: 0; }
.input { border: 0.5px solid #D9DDD9; border-radius: 10px; padding: 9px 12px; font-size: 13.5px; background: #fff; font-family: inherit; }
.hint { font-size: 12.5px; color: #8FA098; margin-bottom: 16px; }
.logo-preview { max-width: 120px; max-height: 60px; margin-top: 8px; object-fit: contain; border-radius: 8px; }
.char-count { font-size: 11px; color: #8FA098; align-self: flex-end; }

.google-preview {
    border: 0.5px solid #E7E9E7; border-radius: 12px; padding: 14px 16px; background: #FBFCFB;
}
.google-preview-url { display: flex; align-items: center; gap: 8px; margin-bottom: 4px; }
.google-preview-favicon {
    width: 18px; height: 18px; border-radius: 50%; background: #F0F1F0; flex-shrink: 0;
    display: flex; align-items: center; justify-content: center; overflow: hidden;
}
.google-preview-favicon img { width: 100%; height: 100%; object-fit: contain; }
.google-preview-favicon i { font-size: 11px; color: #8FA098; }
.google-preview-url span { font-size: 12.5px; color: #4B5A54; }
.google-preview-title { font-size: 16px; color: #1a0dab; line-height: 1.3; margin-bottom: 2px; }
.google-preview-desc { font-size: 12.5px; color: #4d5156; line-height: 1.4; }
.btn-primary {
    background: #1D9E75; color: #fff; border: none; border-radius: 20px;
    padding: 10px 22px; font-size: 13.5px; font-weight: 500; cursor: pointer; align-self: flex-start;
}
</style>
