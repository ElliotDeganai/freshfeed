<script>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

export default {
    layout: GuestLayout,
    components: { PrimaryButton, Head, Link },
    props: { status: String },
    data() {
        return {
            form: useForm({}),
        };
    },
    computed: {
        verificationLinkSent() {
            return this.status === 'verification-link-sent';
        },
    },
    methods: {
        submit() {
            this.form.post(route('verification.send'));
        },
    },
};
</script>

<template>
    <Head title="Vérification de l'email" />

    <div v-if="verificationLinkSent" class="auth-status">
        Un nouveau lien de vérification a été envoyé à l'adresse fournie lors de l'inscription.
    </div>

    <form @submit.prevent="submit">
        <PrimaryButton :disabled="form.processing">Renvoyer l'email de vérification</PrimaryButton>

        <p class="auth-switch">
            <Link :href="route('logout')" method="post" as="button" class="auth-link">
                Se déconnecter
            </Link>
        </p>
    </form>
</template>

<style scoped>
.auth-status { background: #E7F5EF; color: #146C4E; font-size: 13px; padding: 10px 14px; border-radius: 10px; margin-bottom: 18px; }
.auth-link { color: #1D9E75; font-size: 12.5px; text-decoration: none; font-weight: 500; background: none; border: none; cursor: pointer; padding: 0; }
.auth-link:hover { text-decoration: underline; }
.auth-switch { text-align: center; font-size: 12.5px; color: #6B7B74; margin-top: 16px; }
</style>
