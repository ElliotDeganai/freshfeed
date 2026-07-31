<script>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';

export default {
    layout: GuestLayout,
    components: { InputError, InputLabel, PrimaryButton, TextInput, Head },
    props: {
        email: String,
        token: String,
    },
    data() {
        return {
            form: useForm({
                token: this.token,
                email: this.email,
                password: '',
                password_confirmation: '',
            }),
        };
    },
    methods: {
        submit() {
            this.form.post(route('password.store'), {
                onFinish: () => this.form.reset('password', 'password_confirmation'),
            });
        },
    },
};
</script>

<template>
    <Head title="Réinitialiser le mot de passe" />

    <div class="auth-header">
        <div class="auth-icon"><i class="ti ti-lock-check"></i></div>
        <h1 class="auth-title">Nouveau mot de passe</h1>
        <p class="auth-subtitle">Choisis un nouveau mot de passe pour ton compte.</p>
    </div>

    <form @submit.prevent="submit">
        <div class="auth-field">
            <InputLabel for="email" value="Email" />
            <TextInput id="email" v-model="form.email" type="email" autocomplete="username" />
            <InputError :message="form.errors.email" />
        </div>

        <div class="auth-field">
            <InputLabel for="password" value="Nouveau mot de passe" />
            <TextInput id="password" v-model="form.password" type="password" autofocus autocomplete="new-password" />
            <InputError :message="form.errors.password" />
        </div>

        <div class="auth-field">
            <InputLabel for="password_confirmation" value="Confirmer le mot de passe" />
            <TextInput id="password_confirmation" v-model="form.password_confirmation" type="password" autocomplete="new-password" />
            <InputError :message="form.errors.password_confirmation" />
        </div>

        <PrimaryButton :disabled="form.processing">Réinitialiser le mot de passe</PrimaryButton>
    </form>
</template>

<style scoped>
.auth-header { text-align: center; margin-bottom: 24px; }
.auth-icon {
    width: 46px; height: 46px; border-radius: 14px; background: #E7F5EF; color: #1D9E75;
    display: flex; align-items: center; justify-content: center; font-size: 20px;
    margin: 0 auto 14px;
}
.auth-title { font-size: 18px; font-weight: 500; color: #10241D; margin-bottom: 4px; }
.auth-subtitle { font-size: 13px; color: #8FA098; }
.auth-field { margin-bottom: 16px; }
</style>
