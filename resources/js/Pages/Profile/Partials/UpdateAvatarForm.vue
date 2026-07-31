<template>
    <div class="avatar-row">
        <UserAvatar :user="$page.props.auth.user" :size="72" />

        <div class="avatar-actions">
            <label class="btn-secondary">
                <input type="file" accept="image/png,image/jpeg,image/svg+xml" class="sr-only" @change="onFileChange" />
                <i class="ti ti-upload"></i> {{ $page.props.auth.user.avatar_path ? 'Changer la photo' : 'Ajouter une photo' }}
            </label>
            <button v-if="$page.props.auth.user.avatar_path" type="button" class="btn-text-danger" @click="removeAvatar">
                Supprimer
            </button>
            <p class="avatar-hint">PNG, JPG ou SVG — 2 Mo max.</p>
        </div>
    </div>
</template>

<script>
import { router } from '@inertiajs/vue3';
import UserAvatar from '@/Components/UserAvatar.vue';

export default {
    components: { UserAvatar },
    methods: {
        onFileChange(e) {
            const file = e.target.files[0];
            if (!file) return;
            router.post(route('profile.avatar.update'), { avatar: file }, {
                forceFormData: true,
                preserveScroll: true,
            });
        },
        removeAvatar() {
            if (confirm('Supprimer ta photo de profil ?')) {
                router.delete(route('profile.avatar.destroy'), { preserveScroll: true });
            }
        },
    },
};
</script>

<style scoped>
.avatar-row { display: flex; align-items: center; gap: 18px; }
.avatar-actions { display: flex; flex-direction: column; align-items: flex-start; gap: 8px; }
.sr-only { position: absolute; width: 1px; height: 1px; overflow: hidden; clip: rect(0,0,0,0); }
.btn-secondary {
    display: inline-flex; align-items: center; gap: 6px; background: transparent; color: #6B7B74;
    border: 0.5px solid #D9DDD9; border-radius: 20px; padding: 8px 16px; font-size: 12.5px; cursor: pointer;
}
.btn-secondary:hover { background: #F0F1F0; }
.btn-text-danger {
    background: none; border: none; color: #B3261E; font-size: 12px; cursor: pointer; padding: 0; text-decoration: underline;
}
.avatar-hint { font-size: 11.5px; color: #8FA098; }
</style>
