<template>
    <AdminLayout>
        <template #title>Utilisateurs</template>

        <section class="invite-panel">
            <h2 class="invite-panel-title"><i class="ti ti-mail-plus"></i> Inviter quelqu'un</h2>
            <p class="invite-panel-hint">
                L'inscription publique est fermée — c'est le seul moyen de créer un compte.
                La personne reçoit un email pour définir son mot de passe.
            </p>
            <div class="invite-form">
                <input v-model="inviteForm.name" type="text" placeholder="Nom" class="input" />
                <input v-model="inviteForm.email" type="email" placeholder="Email" class="input" />
                <select v-model="inviteForm.role" class="input">
                    <option value="" disabled>Rôle</option>
                    <option v-for="role in roles" :key="role" :value="role">{{ role }}</option>
                </select>
                <button class="btn-primary" @click="submitInvite"><i class="ti ti-send"></i> Envoyer l'invitation</button>
            </div>
        </section>

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
                <span class="status-badge" :class="statusInfo(user).class" :title="statusInfo(user).title">
                    <i class="ti" :class="statusInfo(user).icon"></i> {{ statusInfo(user).label }}
                </span>
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
    data() {
        return {
            inviteForm: { name: '', email: '', role: '' },
        };
    },
    methods: {
        statusInfo(user) {
            if (!user.activated_at) {
                return {
                    label: 'Invitation en attente',
                    class: 'status-badge--pending',
                    icon: 'ti-clock',
                    title: "N'a pas encore défini son mot de passe",
                };
            }
            if (!user.last_login_at) {
                return {
                    label: 'Activé, jamais connecté',
                    class: 'status-badge--activated',
                    icon: 'ti-key',
                    title: `Mot de passe défini le ${new Date(user.activated_at).toLocaleDateString('fr-CH')}, aucune connexion depuis`,
                };
            }
            return {
                label: `Actif — vu le ${new Date(user.last_login_at).toLocaleDateString('fr-CH')}`,
                class: 'status-badge--active',
                icon: 'ti-circle-check',
                title: `Dernière connexion le ${new Date(user.last_login_at).toLocaleDateString('fr-CH')} à ${new Date(user.last_login_at).toLocaleTimeString('fr-CH', { hour: '2-digit', minute: '2-digit' })}`,
            };
        },
        avatarColor,
        initials,
        submitInvite() {
            if (!this.inviteForm.name.trim() || !this.inviteForm.email.trim() || !this.inviteForm.role) return;
            router.post(route('admin.users.store'), this.inviteForm, {
                preserveScroll: true,
                onSuccess: () => { this.inviteForm = { name: '', email: '', role: '' }; },
            });
        },
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
.invite-panel { background: #fff; border: 0.5px solid #E7E9E7; border-radius: 16px; padding: 18px; margin-bottom: 18px; }
.invite-panel-title { display: flex; align-items: center; gap: 7px; font-size: 14px; font-weight: 500; color: #10241D; margin-bottom: 6px; }
.invite-panel-title i { color: #1D9E75; }
.invite-panel-hint { font-size: 12px; color: #8FA098; margin-bottom: 14px; line-height: 1.5; }
.invite-form { display: flex; gap: 8px; flex-wrap: wrap; }
.invite-form .input { flex: 1; min-width: 140px; }
.invite-form select.input { flex: 0 0 140px; }
.btn-primary {
    background: #1D9E75; color: #fff; border: none; border-radius: 8px;
    padding: 9px 16px; font-size: 13px; font-weight: 500; cursor: pointer;
    display: inline-flex; align-items: center; gap: 6px; white-space: nowrap;
}

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
.status-badge {
    display: inline-flex; align-items: center; gap: 4px; font-size: 11px; font-weight: 600;
    padding: 4px 11px; border-radius: 999px; flex-shrink: 0; white-space: nowrap;
}
.status-badge i { font-size: 13px; }
.status-badge--pending { background: #FAEEDA; color: #854F0B; }
.status-badge--activated { background: #EEEDFE; color: #534AB7; }
.status-badge--active { background: #E7F5EF; color: #146C4E; }
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
