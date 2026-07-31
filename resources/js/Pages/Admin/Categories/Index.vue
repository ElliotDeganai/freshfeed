<template>
    <AdminLayout>
        <template #title>Catégories</template>

        <div class="panel panel--form">
            <input v-model="form.name" type="text" placeholder="Nom de la catégorie" class="input" />
            <input v-model="form.description" type="text" placeholder="Description (optionnel)" class="input input--wide" />
            <button class="btn-primary" @click="submit"><i class="ti ti-plus"></i> Ajouter</button>
        </div>

        <div class="category-list">
            <div v-for="cat in categories" :key="cat.id" class="category-card" :style="{ borderColor: avatarColor(cat.id).text + '33' }">
                <div class="category-tag" :style="{ background: avatarColor(cat.id).bg, color: avatarColor(cat.id).text }">
                    <i class="ti ti-tag"></i>
                </div>

                <div class="category-body">
                    <input v-if="editingId === cat.id" v-model="editForm.name" class="input input--inline" />
                    <span v-else class="category-name">{{ cat.name }}</span>

                    <input v-if="editingId === cat.id" v-model="editForm.description" class="input input--inline" placeholder="Description" />
                    <span v-else class="category-desc">{{ cat.description || 'Sans description' }}</span>
                </div>

                <span class="tag-pill">{{ cat.posts_count }} recette{{ cat.posts_count > 1 ? 's' : '' }}</span>

                <div class="row-actions">
                    <template v-if="editingId === cat.id">
                        <button class="icon-btn" @click="saveEdit(cat)"><i class="ti ti-check"></i></button>
                        <button class="icon-btn" @click="editingId = null"><i class="ti ti-x"></i></button>
                    </template>
                    <template v-else>
                        <button class="icon-btn" @click="startEdit(cat)"><i class="ti ti-pencil"></i></button>
                        <button class="icon-btn icon-btn--danger" @click="destroy(cat)"><i class="ti ti-trash"></i></button>
                    </template>
                </div>
            </div>
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
    props: { categories: Array },
    data() {
        return {
            form: { name: '', description: '' },
            editingId: null,
            editForm: { name: '', description: '' },
        };
    },
    methods: {
        avatarColor,
        submit() {
            if (!this.form.name.trim()) return;
            router.post(route('admin.categories.store'), this.form, {
                preserveScroll: true,
                onSuccess: () => { this.form = { name: '', description: '' }; },
            });
        },
        startEdit(cat) {
            this.editingId = cat.id;
            this.editForm = { name: cat.name, description: cat.description };
        },
        saveEdit(cat) {
            router.put(route('admin.categories.update', cat.id), this.editForm, {
                preserveScroll: true,
                onSuccess: () => { this.editingId = null; },
            });
        },
        destroy(cat) {
            if (confirm(`Supprimer la catégorie "${cat.name}" ?`)) {
                router.delete(route('admin.categories.destroy', cat.id), { preserveScroll: true });
            }
        },
    },
};
</script>

<style scoped>
.panel { background: #fff; border: 0.5px solid #E7E9E7; border-radius: 16px; }
.panel--form { display: flex; gap: 10px; padding: 16px 18px; margin-bottom: 18px; }
.input { border: 0.5px solid #D9DDD9; border-radius: 10px; padding: 8px 12px; font-size: 13.5px; background: #fff; }
.input--wide { flex: 1; }
.input--inline { padding: 5px 9px; font-size: 13px; margin-bottom: 3px; }
.btn-primary {
    background: #1D9E75; color: #fff; border: none; border-radius: 20px;
    padding: 9px 16px; font-size: 13.5px; font-weight: 500;
    display: inline-flex; align-items: center; gap: 6px; cursor: pointer; white-space: nowrap;
}

.category-list { display: flex; flex-direction: column; gap: 10px; }
.category-card {
    display: flex; align-items: center; gap: 14px; background: #fff;
    border: 0.5px solid #E7E9E7; border-radius: 16px; padding: 12px 18px;
}
.category-tag {
    width: 38px; height: 38px; border-radius: 12px; flex-shrink: 0;
    display: flex; align-items: center; justify-content: center; font-size: 16px;
}
.category-body { flex: 1; display: flex; flex-direction: column; gap: 2px; min-width: 0; }
.category-name { font-size: 14px; font-weight: 500; color: #10241D; }
.category-desc { font-size: 12px; color: #8FA098; }
.tag-pill { font-size: 11px; background: #F0F1F0; color: #6B7B74; padding: 4px 11px; border-radius: 999px; flex-shrink: 0; }
.row-actions { display: flex; gap: 4px; flex-shrink: 0; }
.icon-btn {
    width: 30px; height: 30px; display: flex; align-items: center; justify-content: center;
    border-radius: 50%; color: #6B7B74; background: transparent; border: none; cursor: pointer;
}
.icon-btn:hover { background: #F0F1F0; }
.icon-btn--danger:hover { background: #FDECEC; color: #B3261E; }

@media (max-width: 640px) {
    .panel--form { flex-wrap: wrap; }
    .panel--form .input { flex: 1 1 100%; }
    .category-card { flex-wrap: wrap; }
    .category-body { min-width: 140px; }
}
</style>
