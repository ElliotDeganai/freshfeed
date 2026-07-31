<template>
    <AdminLayout>
        <template #title>Utilisateurs</template>

        <div class="user-list">
            <div v-for="user in users" :key="user.id" class="user-card">
                <div class="user-avatar" :style="{ background: avatarColor(user.id).bg, color: avatarColor(user.id).text }">
                    {{ initials(user.name) }}
                </div>
                <div class="user-body">
                    <span class="user-name">{{ user.name }}</span>
                    <span class="user-email">{{ user.email }}</span>
                </div>
                <span class="tag-pill">Depuis le {{ new Date(user.created_at).toLocaleDateString('fr-CH') }}</span>
                <select class="role-select" :value="user.roles[0]?.name || ''" @change="updateRole(user, $event.target.value)">
                    <option v-for="role in roles" :key="role" :value="role">{{ role }}</option>
                </select>
                <button class="icon-btn icon-btn--danger" @click="destroy(user)"><i class="ti ti-trash"></i></button>
            </div>
        </div>
    </AdminLayout>
</template>

<script>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { router } from '@inertiajs/vue3';
import { avatarColor, initials } from '@/Components/Admin/avatarPalette.js';

export default {
    layout: null,
    components: { AdminLayout },
    props: { users: Array, roles: Array },
    methods: {
        avatarColor,
        initials,
        updateRole(user, role) {
            router.put(route('admin.users.role', user.id), { role }, { preserveScroll: true });
        },
        destroy(user) {
            if (confirm(`Supprimer l'utilisateur "${user.name}" ?`)) {
                router.delete(route('admin.users.destroy', user.id), { preserveScroll: true });
            }
        },
    },
};
</script>

<style scoped>
.user-list { display: flex; flex-direction: column; gap: 10px; }
.user-card {
    display: flex; align-items: center; gap: 14px; background: #fff;
    border: 0.5px solid #E7E9E7; border-radius: 16px; padding: 12px 18px;
}
.user-avatar {
    width: 40px; height: 40px; border-radius: 50%; flex-shrink: 0;
    display: flex; align-items: center; justify-content: center; font-size: 13px; font-weight: 500;
}
.user-body { flex: 1; display: flex; flex-direction: column; gap: 2px; min-width: 0; }
.user-name { font-size: 14px; font-weight: 500; color: #10241D; }
.user-email { font-size: 12px; color: #8FA098; }
.tag-pill { font-size: 11px; background: #F0F1F0; color: #6B7B74; padding: 4px 11px; border-radius: 999px; flex-shrink: 0; }
.role-select {
    border: 0.5px solid #D9DDD9; border-radius: 20px; padding: 6px 12px; font-size: 12.5px;
    background: #fff; color: #4B5A54; text-transform: capitalize;
}
.icon-btn {
    width: 30px; height: 30px; display: flex; align-items: center; justify-content: center;
    border-radius: 50%; color: #6B7B74; background: transparent; border: none; cursor: pointer; flex-shrink: 0;
}
.icon-btn--danger:hover { background: #FDECEC; color: #B3261E; }

@media (max-width: 640px) {
    .user-card { flex-wrap: wrap; }
    .user-body { min-width: 160px; }
    .tag-pill { display: none; }
}
</style>
