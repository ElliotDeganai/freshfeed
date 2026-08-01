<script>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

export default {
    layout: GuestLayout,
    components: { InputError, InputLabel, PrimaryButton, TextInput, Head, Link },
    data() {
        return {
            form: useForm({
                name: '',
                email: '',
                password: '',
                password_confirmation: '',
            }),
        };
    },
    methods: {
        submit() {
            this.form.post(route('register'), {
                onFinish: () => this.form.reset('password', 'password_confirmation'),
            });
        },
    },
};
</script>

<template>
    <Head title="Créer un compte" />

    <div class="auth-header">
        <div class="auth-icon"><i class="ti ti-user-plus"></i></div>
        <h1 class="auth-title">Rejoindre {{ $page.props.site.name }}</h1>
        <p class="auth-subtitle">Crée ton compte pour partager tes recettes.</p>
    </div>

    <form @submit.prevent="submit">
        <div class="auth-field">
            <InputLabel for="name" value="Nom" />
            <TextInput id="name" v-model="form.name" type="text" autofocus autocomplete="name" />
            <InputError :message="form.errors.name" />
        </div>

        <div class="auth-field">
            <InputLabel for="email" value="Email" />
            <TextInput id="email" v-model="form.email" type="email" autocomplete="username" />
            <InputError :message="form.errors.email" />
        </div>

        <div class="auth-field">
            <InputLabel for="password" value="Mot de passe" />
            <TextInput id="password" v-model="form.password" type="password" autocomplete="new-password" />
            <InputError :message="form.errors.password" />
        </div>

        <div class="auth-field">
            <InputLabel for="password_confirmation" value="Confirmer le mot de passe" />
            <TextInput id="password_confirmation" v-model="form.password_confirmation" type="password" autocomplete="new-password" />
            <InputError :message="form.errors.password_confirmation" />
        </div>

        <PrimaryButton :disabled="form.processing">Créer mon compte</PrimaryButton>

        <p class="auth-switch">
            Déjà un compte ?
            <Link :href="route('login')" class="auth-link">Se connecter</Link>
        </p>
    </form>
</template>

<style scoped>
.auth-header { text-align: center; margin-bottom: 24px; }
.auth-icon {
    width: 46px; height: 46px; border-radius: 14px; background: #EEEDFE; color: #534AB7;
    display: flex; align-items: center; justify-content: center; font-size: 20px;
    margin: 0 auto 14px;
}
.auth-title { font-size: 18px; font-weight: 500; color: #10241D; margin-bottom: 4px; }
.auth-subtitle { font-size: 13px; color: #8FA098; }
.auth-field { margin-bottom: 16px; }
.auth-link { color: #1D9E75; font-size: 12.5px; text-decoration: none; font-weight: 500; }
.auth-link:hover { text-decoration: underline; }
.auth-switch { text-align: center; font-size: 12.5px; color: #6B7B74; margin-top: 18px; }
</style>
