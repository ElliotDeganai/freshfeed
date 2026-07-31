<template>
    <AdminLayout>
        <template #title>Recettes</template>

        <div class="toolbar">
            <div class="pill-tabs">
                <button class="pill-tab" :class="{ on: status === '' }" @click="setStatus('')">Toutes</button>
                <button class="pill-tab" :class="{ on: status === 'published' }" @click="setStatus('published')">Publiées</button>
                <button class="pill-tab" :class="{ on: status === 'draft' }" @click="setStatus('draft')">Brouillons</button>
            </div>
            <div class="toolbar-right">
                <div class="search-box">
                    <i class="ti ti-search"></i>
                    <input v-model="search" type="text" placeholder="Rechercher..." @input="debouncedSearch" />
                </div>
                <Link :href="route('admin.posts.create')" class="btn-primary">
                    <i class="ti ti-plus"></i> Nouvelle recette
                </Link>
            </div>
        </div>

        <div class="post-list">
            <div v-for="post in posts.data" :key="post.id" class="post-card">
                <div class="post-card-avatar" :style="{ background: avatarColor(post.id).bg, color: avatarColor(post.id).text }">
                    <i class="ti ti-tools-kitchen-2"></i>
                </div>
                <div class="post-card-body">
                    <Link :href="route('admin.posts.edit', post.id)" class="post-card-title">{{ post.title }}</Link>
                    <div class="post-card-tags">
                        <span v-for="cat in post.categories" :key="cat.id" class="tag-pill">{{ cat.name }}</span>
                        <span v-if="post.categories.length === 0" class="muted">Sans catégorie</span>
                    </div>
                </div>
                <span class="badge" :class="post.status === 'published' ? 'badge--green' : 'badge--gray'">
                    {{ post.status === 'published' ? 'Publiée' : 'Brouillon' }}
                </span>
                <div class="row-actions">
                    <Link :href="route('admin.posts.edit', post.id)" class="icon-btn"><i class="ti ti-pencil"></i></Link>
                    <button class="icon-btn icon-btn--danger" @click="confirmDelete(post)"><i class="ti ti-trash"></i></button>
                </div>
            </div>

            <div v-if="posts.data.length === 0" class="empty-state">
                <i class="ti ti-tools-kitchen-2"></i>
                <p>Aucune recette pour ces filtres.</p>
            </div>
        </div>

        <div class="pagination">
            <Link v-for="link in posts.links" :key="link.label" :href="link.url || ''"
                class="page-link" :class="{ on: link.active, off: !link.url }"
                v-html="link.label" />
        </div>
    </AdminLayout>
</template>

<script>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import { avatarColor } from '@/Components/Admin/avatarPalette.js';

export default {
    layout: null,
    components: { AdminLayout, Link },
    props: {
        posts: Object,
        filters: Object,
        can: Object,
    },
    data() {
        return {
            search: this.filters.search || '',
            status: this.filters.status || '',
            debounceTimer: null,
        };
    },
    methods: {
        avatarColor,
        debouncedSearch() {
            clearTimeout(this.debounceTimer);
            this.debounceTimer = setTimeout(() => this.applyFilters(), 350);
        },
        setStatus(status) {
            this.status = status;
            this.applyFilters();
        },
        applyFilters() {
            router.get(route('admin.posts.index'), { search: this.search, status: this.status }, {
                preserveState: true,
                replace: true,
            });
        },
        confirmDelete(post) {
            if (confirm(`Supprimer la recette "${post.title}" ? Cette action est irréversible.`)) {
                router.delete(route('admin.posts.destroy', post.id));
            }
        },
    },
};
</script>

<style scoped>
.toolbar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px; gap: 12px; flex-wrap: wrap; }
.pill-tabs { display: flex; gap: 6px; }
.pill-tab {
    padding: 7px 15px; border-radius: 20px; border: 0.5px solid #E7E9E7; background: #fff;
    color: #6B7B74; font-size: 13px; cursor: pointer;
}
.pill-tab.on { background: #1D9E75; border-color: #1D9E75; color: #fff; font-weight: 500; }
.toolbar-right { display: flex; gap: 10px; align-items: center; }
.search-box {
    display: flex; align-items: center; gap: 7px; background: #F0F1F0; border-radius: 20px;
    padding: 7px 14px; font-size: 13px; color: #6B7B74;
}
.search-box input { border: none; background: transparent; outline: none; font-size: 13px; width: 160px; }
.btn-primary {
    background: #1D9E75; color: #fff; border: none; border-radius: 20px;
    padding: 8px 16px; font-size: 13px; font-weight: 500; text-decoration: none;
    display: inline-flex; align-items: center; gap: 6px;
}

.post-list { display: flex; flex-direction: column; gap: 10px; }
.post-card {
    display: flex; align-items: center; gap: 14px; background: #fff;
    border: 0.5px solid #E7E9E7; border-radius: 16px; padding: 14px 18px;
}
.post-card-avatar {
    width: 40px; height: 40px; border-radius: 50%; flex-shrink: 0;
    display: flex; align-items: center; justify-content: center; font-size: 17px;
}
.post-card-body { flex: 1; min-width: 0; display: flex; flex-direction: column; gap: 5px; }
.post-card-title { font-size: 14px; font-weight: 500; color: #10241D; text-decoration: none; }
.post-card-title:hover { color: #1D9E75; }
.post-card-tags { display: flex; gap: 6px; flex-wrap: wrap; }
.tag-pill { font-size: 11px; background: #F0F1F0; color: #6B7B74; padding: 2px 9px; border-radius: 999px; }
.muted { color: #8FA098; font-size: 12px; }

.badge { font-size: 11px; padding: 3px 10px; border-radius: 999px; font-weight: 600; flex-shrink: 0; }
.badge--green { background: #E7F5EF; color: #146C4E; }
.badge--gray { background: #F0F1F0; color: #6B7B74; }

.row-actions { display: flex; gap: 4px; flex-shrink: 0; }
.icon-btn {
    width: 30px; height: 30px; display: flex; align-items: center; justify-content: center;
    border-radius: 50%; color: #6B7B74; background: transparent; border: none; cursor: pointer; text-decoration: none;
}
.icon-btn:hover { background: #F0F1F0; }
.icon-btn--danger:hover { background: #FDECEC; color: #B3261E; }

.empty-state { text-align: center; color: #8FA098; padding: 50px 20px; display: flex; flex-direction: column; align-items: center; gap: 8px; }
.empty-state i { font-size: 28px; }

.pagination { display: flex; gap: 4px; margin-top: 18px; justify-content: center; }
.page-link {
    padding: 6px 12px; border-radius: 20px; font-size: 13px; text-decoration: none; color: #4B5A54;
    border: 0.5px solid #E7E9E7;
}
.page-link.on { background: #1D9E75; color: #fff; border-color: #1D9E75; }
.page-link.off { opacity: .4; pointer-events: none; }

@media (max-width: 640px) {
    .toolbar-right { width: 100%; }
    .search-box { flex: 1; }
    .search-box input { width: 100%; }
    .post-card { flex-wrap: wrap; }
    .post-card-body { min-width: 160px; }
}
</style>
