<template>
    <AdminLayout>
        <template #title>Pages</template>

        <div class="toolbar">
            <Link :href="route('admin.pages.create')" class="btn-primary">
                <i class="ti ti-plus"></i> Nouvelle page
            </Link>
        </div>

        <div class="page-list">
            <div v-for="page in pages" :key="page.id" class="page-card">
                <div class="page-card-icon" :style="{ background: avatarColor(page.id).bg, color: avatarColor(page.id).text }">
                    <i class="ti ti-file-stack"></i>
                </div>
                <div class="page-card-body">
                    <Link :href="route('admin.pages.edit', page.id)" class="page-card-title">{{ page.title }}</Link>
                    <span class="page-card-slug">/{{ page.slug }}</span>
                </div>
                <span class="tag-pill">{{ page.sections_count }} section{{ page.sections_count > 1 ? 's' : '' }}</span>
                <span class="badge" :class="page.is_active ? 'badge--green' : 'badge--gray'">
                    {{ page.is_active ? 'Active' : 'Inactive' }}
                </span>
                <div class="row-actions">
                    <Link :href="route('admin.pages.edit', page.id)" class="icon-btn"><i class="ti ti-pencil"></i></Link>
                    <button class="icon-btn icon-btn--danger" @click="destroy(page)"><i class="ti ti-trash"></i></button>
                </div>
            </div>

            <div v-if="pages.length === 0" class="empty-state">
                <i class="ti ti-file-stack"></i>
                <p>Aucune page créée pour l'instant.</p>
            </div>
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
    props: { pages: Array },
    methods: {
        avatarColor,
        destroy(page) {
            if (confirm(`Supprimer la page "${page.title}" et toutes ses sections ?`)) {
                router.delete(route('admin.pages.destroy', page.id));
            }
        },
    },
};
</script>

<style scoped>
.toolbar { display: flex; justify-content: flex-end; margin-bottom: 18px; }
.btn-primary {
    background: #1D9E75; color: #fff; border: none; border-radius: 20px;
    padding: 9px 16px; font-size: 13.5px; font-weight: 500; text-decoration: none;
    display: inline-flex; align-items: center; gap: 6px;
}
.page-list { display: flex; flex-direction: column; gap: 10px; }
.page-card {
    display: flex; align-items: center; gap: 14px; background: #fff;
    border: 0.5px solid #E7E9E7; border-radius: 16px; padding: 12px 18px;
}
.page-card-icon {
    width: 40px; height: 40px; border-radius: 12px; flex-shrink: 0;
    display: flex; align-items: center; justify-content: center; font-size: 17px;
}
.page-card-body { flex: 1; display: flex; flex-direction: column; gap: 2px; min-width: 0; }
.page-card-title { font-size: 14px; font-weight: 500; color: #10241D; text-decoration: none; }
.page-card-title:hover { color: #1D9E75; }
.page-card-slug { font-size: 12px; color: #8FA098; }
.tag-pill { font-size: 11px; background: #F0F1F0; color: #6B7B74; padding: 4px 11px; border-radius: 999px; flex-shrink: 0; }
.badge { font-size: 11px; padding: 4px 11px; border-radius: 999px; font-weight: 600; flex-shrink: 0; }
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

@media (max-width: 640px) {
    .page-card { flex-wrap: wrap; }
    .page-card-body { min-width: 160px; }
    .tag-pill { display: none; }
}
</style>
