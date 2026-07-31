<template>
    <AdminLayout>
        <template #title>Tableau de bord</template>

        <div class="sections-grid">
            <!-- Recettes -->
            <section v-if="can.posts" class="section-card">
                <div class="section-head">
                    <div class="section-icon" style="background:#E1F5EE;color:#0F6E56"><i class="ti ti-tools-kitchen-2"></i></div>
                    <div class="section-head-body">
                        <h2 class="section-title">Recettes</h2>
                        <span class="section-subtitle">{{ posts.total }} au total · {{ posts.published }} publiées · {{ posts.draft }} brouillons</span>
                    </div>
                    <Link :href="route('admin.posts.index')" class="section-link">Voir tout <i class="ti ti-arrow-right"></i></Link>
                </div>
                <div class="section-body">
                    <div v-for="post in posts.recent" :key="post.id" class="mini-row">
                        <span class="mini-row-title">{{ post.title }}</span>
                        <span class="badge" :class="post.status === 'published' ? 'badge--green' : 'badge--gray'">
                            {{ post.status === 'published' ? 'Publiée' : 'Brouillon' }}
                        </span>
                    </div>
                    <div v-if="posts.recent.length === 0" class="mini-empty">Aucune recette pour l'instant.</div>
                </div>
            </section>

            <!-- Catégories -->
            <section v-if="can.categories" class="section-card">
                <div class="section-head">
                    <div class="section-icon" style="background:#EEEDFE;color:#534AB7"><i class="ti ti-tags"></i></div>
                    <div class="section-head-body">
                        <h2 class="section-title">Catégories</h2>
                        <span class="section-subtitle">{{ categories.total }} catégorie{{ categories.total > 1 ? 's' : '' }}</span>
                    </div>
                    <Link :href="route('admin.categories.index')" class="section-link">Gérer <i class="ti ti-arrow-right"></i></Link>
                </div>
                <div class="section-body">
                    <div v-for="cat in categories.recent" :key="cat.id" class="mini-row">
                        <span class="mini-row-title">{{ cat.name }}</span>
                        <span class="tag-pill">{{ cat.posts_count }} recette{{ cat.posts_count > 1 ? 's' : '' }}</span>
                    </div>
                    <div v-if="categories.recent.length === 0" class="mini-empty">Aucune catégorie pour l'instant.</div>
                </div>
            </section>

            <!-- Pages -->
            <section v-if="can.pages" class="section-card">
                <div class="section-head">
                    <div class="section-icon" style="background:#FAEEDA;color:#854F0B"><i class="ti ti-file-stack"></i></div>
                    <div class="section-head-body">
                        <h2 class="section-title">Pages</h2>
                        <span class="section-subtitle">{{ pages.total }} page{{ pages.total > 1 ? 's' : '' }} · {{ pages.active }} active{{ pages.active > 1 ? 's' : '' }}</span>
                    </div>
                    <Link :href="route('admin.pages.index')" class="section-link">Gérer <i class="ti ti-arrow-right"></i></Link>
                </div>
                <div class="section-body section-body--simple">
                    <p class="section-hint">Crée des pages avec leur propre URL, composées de sections (grille, hero, carrousel...) reliées à des catégories.</p>
                </div>
            </section>

            <!-- Utilisateurs -->
            <section v-if="can.users" class="section-card">
                <div class="section-head">
                    <div class="section-icon" style="background:#FAECE7;color:#993C1D"><i class="ti ti-users"></i></div>
                    <div class="section-head-body">
                        <h2 class="section-title">Utilisateurs</h2>
                        <span class="section-subtitle">{{ users.total }} compte{{ users.total > 1 ? 's' : '' }}</span>
                    </div>
                    <Link :href="route('admin.users.index')" class="section-link">Gérer <i class="ti ti-arrow-right"></i></Link>
                </div>
                <div class="section-body">
                    <div v-for="(count, role) in users.by_role" :key="role" class="mini-row">
                        <span class="mini-row-title" style="text-transform:capitalize">{{ role }}</span>
                        <span class="tag-pill">{{ count }}</span>
                    </div>
                </div>
            </section>

            <!-- Accueil du site -->
            <section v-if="can.settings" class="section-card">
                <div class="section-head">
                    <div class="section-icon" style="background:#E6F1FB;color:#0C447C"><i class="ti ti-home"></i></div>
                    <div class="section-head-body">
                        <h2 class="section-title">Accueil du site</h2>
                        <span class="section-subtitle">Titre, texte et photos de la page d'accueil</span>
                    </div>
                    <Link :href="route('admin.homepage.index')" class="section-link">Modifier <i class="ti ti-arrow-right"></i></Link>
                </div>
                <div class="section-body section-body--simple">
                    <p class="section-hint">Change le titre du hero, le badge, et les 7 photos du bandeau diagonal sans toucher au code.</p>
                </div>
            </section>

            <!-- Paramètres -->
            <section v-if="can.settings" class="section-card">
                <div class="section-head">
                    <div class="section-icon" style="background:#F0F1F0;color:#4B5A54"><i class="ti ti-settings"></i></div>
                    <div class="section-head-body">
                        <h2 class="section-title">Paramètres</h2>
                        <span class="section-subtitle">Nom, logo, SEO, URLs</span>
                    </div>
                    <Link :href="route('admin.settings.index')" class="section-link">Modifier <i class="ti ti-arrow-right"></i></Link>
                </div>
                <div class="section-body section-body--simple">
                    <p class="section-hint">Logo, nom de l'app, meta description avec aperçu Google, et les slugs des pages principales.</p>
                </div>
            </section>
        </div>
    </AdminLayout>
</template>

<script>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Link } from '@inertiajs/vue3';

export default {
    layout: null,
    components: { AdminLayout, Link },
    props: {
        can: Object,
        posts: Object,
        categories: Object,
        pages: Object,
        users: Object,
    },
};
</script>

<style scoped>
.sections-grid {
    display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 14px;
}

.section-card { background: #fff; border: 0.5px solid #E7E9E7; border-radius: 16px; overflow: hidden; }

.section-head { display: flex; align-items: center; gap: 12px; padding: 16px 18px; border-bottom: 0.5px solid #F0F1F0; }
.section-icon {
    width: 38px; height: 38px; border-radius: 12px; flex-shrink: 0;
    display: flex; align-items: center; justify-content: center; font-size: 17px;
}
.section-head-body { flex: 1; min-width: 0; }
.section-title { font-size: 14.5px; font-weight: 500; color: #10241D; }
.section-subtitle { font-size: 11.5px; color: #8FA098; }
.section-link {
    font-size: 12px; color: #1D9E75; font-weight: 500; text-decoration: none;
    display: flex; align-items: center; gap: 4px; flex-shrink: 0; white-space: nowrap;
}
.section-link i { font-size: 13px; }
.section-link:hover { text-decoration: underline; }

.section-body { padding: 6px 18px 14px; }
.section-body--simple { padding: 14px 18px 16px; }
.section-hint { font-size: 12.5px; color: #8FA098; line-height: 1.5; }

.mini-row {
    display: flex; align-items: center; justify-content: space-between; gap: 10px;
    padding: 9px 0; border-top: 0.5px solid #F5F6F4; font-size: 13px;
}
.mini-row:first-child { border-top: none; }
.mini-row-title { color: #10241D; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.mini-empty { color: #8FA098; font-size: 12.5px; padding: 10px 0; }

.badge { font-size: 10.5px; padding: 3px 9px; border-radius: 999px; font-weight: 600; flex-shrink: 0; }
.badge--green { background: #E7F5EF; color: #146C4E; }
.badge--gray { background: #F0F1F0; color: #6B7B74; }
.tag-pill { font-size: 10.5px; background: #F0F1F0; color: #6B7B74; padding: 2px 9px; border-radius: 999px; flex-shrink: 0; }
</style>
