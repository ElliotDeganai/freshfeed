<template>
    <AppLayout>
        <Head title="Mon profil" />

        <div class="page-header">
            <h1 class="page-title">Mon profil</h1>
        </div>

        <div class="profile-sections">
            <section class="panel">
                <h2 class="panel-title"><i class="ti ti-camera"></i> Photo de profil</h2>
                <p class="panel-hint">Affichée partout où ton nom apparaît dans l'app.</p>
                <UpdateAvatarForm />
            </section>

            <section class="panel">
                <h2 class="panel-title"><i class="ti ti-user"></i> Informations du profil</h2>
                <p class="panel-hint">Mets à jour ton nom et ton adresse email.</p>
                <UpdateProfileInformationForm :must-verify-email="mustVerifyEmail" :status="status" />
            </section>

            <section class="panel">
                <h2 class="panel-title"><i class="ti ti-lock"></i> Mot de passe</h2>
                <p class="panel-hint">Utilise un mot de passe long et unique pour rester en sécurité.</p>
                <UpdatePasswordForm />
            </section>

            <section class="panel panel--danger">
                <h2 class="panel-title panel-title--danger"><i class="ti ti-alert-triangle"></i> Supprimer le compte</h2>
                <p class="panel-hint">Une fois supprimé, toutes tes données sont définitivement perdues.</p>
                <DeleteUserForm />
            </section>
        </div>
    </AppLayout>
</template>

<script>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import UpdateAvatarForm from './Partials/UpdateAvatarForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';

export default {
    layout: null,
    components: { AppLayout, Head, UpdateAvatarForm, UpdateProfileInformationForm, UpdatePasswordForm, DeleteUserForm },
    props: {
        mustVerifyEmail: Boolean,
        status: String,
    },
};
</script>

<style scoped>
.page-header { max-width: 680px; margin: 0 auto 18px; }
.page-title { font-size: 20px; font-weight: 500; color: #10241D; }

.profile-sections { display: flex; flex-direction: column; gap: 14px; max-width: 680px; margin: 0 auto; }
.panel { background: #fff; border: 0.5px solid #E7E9E7; border-radius: 16px; padding: 22px; }
.panel--danger { border-color: #F5C4B3; }
.panel-title {
    font-size: 14.5px; font-weight: 500; color: #10241D; margin-bottom: 4px;
    display: flex; align-items: center; gap: 8px;
}
.panel-title i { color: #1D9E75; font-size: 16px; }
.panel-title--danger i { color: #993C1D; }
.panel-hint { font-size: 12.5px; color: #8FA098; margin-bottom: 18px; line-height: 1.5; }
</style>
