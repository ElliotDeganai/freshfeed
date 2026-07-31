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
    data() {
        return {
            form: useForm({ password: '' }),
        };
    },
    methods: {
        submit() {
            this.form.post(route('password.confirm'), {
                onFinish: () => this.form.reset(),
            });
        },
    },
};
</script>

<template>
    <Head title="Confirmer le mot de passe" />

    <div class="auth-header">
        <div class="auth-icon"><i class="ti ti-shield-lock"></i></div>
        <h1 class="auth-title">Zone sécurisée</h1>
        <p class="auth-subtitle">Confirme ton mot de passe avant de continuer.</p>
    </div>

    <form @submit.prevent="submit">
        <div class="auth-field">
            <InputLabel for="password" value="Mot de passe" />
            <TextInput id="password" v-model="form.password" type="password" autofocus autocomplete="current-password" />
            <InputError :message="form.errors.password" />
        </div>

        <PrimaryButton :disabled="form.processing">Confirmer</PrimaryButton>
    </form>
</template>

<style scoped>
.auth-header { text-align: center; margin-bottom: 24px; }
.auth-icon {
    width: 46px; height: 46px; border-radius: 14px; background: #FAECE7; color: #993C1D;
    display: flex; align-items: center; justify-content: center; font-size: 20px;
    margin: 0 auto 14px;
}
.auth-title { font-size: 18px; font-weight: 500; color: #10241D; margin-bottom: 4px; }
.auth-subtitle { font-size: 13px; color: #8FA098; }
.auth-field { margin-bottom: 20px; }
</style>
