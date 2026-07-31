<script>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import Checkbox from '@/Components/Checkbox.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

export default {
    layout: GuestLayout,
    components: { InputError, InputLabel, PrimaryButton, TextInput, Checkbox, Head, Link },
    props: {
        canResetPassword: Boolean,
        status: String,
    },
    data() {
        return {
            form: useForm({
                email: '',
                password: '',
                remember: false,
            }),
        };
    },
    methods: {
        submit() {
            this.form.transform((data) => ({
                ...data,
                remember: this.form.remember ? 'on' : '',
            })).post(route('login'), {
                onFinish: () => this.form.reset('password'),
            });
        },
    },
};
</script>

<template>
    <Head title="Connexion" />

    <div class="auth-header">
        <div class="auth-icon"><i class="ti ti-tools-kitchen-2"></i></div>
        <h1 class="auth-title">Content de te revoir</h1>
        <p class="auth-subtitle">Connecte-toi à ton compte FreshFeed.</p>
    </div>

    <div v-if="status" class="auth-status">{{ status }}</div>

    <form @submit.prevent="submit">
        <div class="auth-field">
            <InputLabel for="email" value="Email" />
            <TextInput
                id="email"
                v-model="form.email"
                type="email"
                autofocus
                autocomplete="username"
            />
            <InputError :message="form.errors.email" />
        </div>

        <div class="auth-field">
            <InputLabel for="password" value="Mot de passe" />
            <TextInput
                id="password"
                v-model="form.password"
                type="password"
                autocomplete="current-password"
            />
            <InputError :message="form.errors.password" />
        </div>

        <div class="auth-row">
            <label class="auth-checkbox">
                <Checkbox v-model:checked="form.remember" />
                <span>Se souvenir de moi</span>
            </label>
            <Link v-if="canResetPassword" :href="route('password.request')" class="auth-link">
                Mot de passe oublié ?
            </Link>
        </div>

        <PrimaryButton :disabled="form.processing">Se connecter</PrimaryButton>

        <p class="auth-switch">
            Pas encore de compte ?
            <Link :href="route('register')" class="auth-link">Créer un compte</Link>
        </p>
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
.auth-status { background: #E7F5EF; color: #146C4E; font-size: 13px; padding: 10px 14px; border-radius: 10px; margin-bottom: 18px; }
.auth-field { margin-bottom: 16px; }
.auth-row { display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; }
.auth-checkbox { display: flex; align-items: center; gap: 7px; font-size: 12.5px; color: #4B5A54; }
.auth-link { color: #1D9E75; font-size: 12.5px; text-decoration: none; font-weight: 500; }
.auth-link:hover { text-decoration: underline; }
.auth-switch { text-align: center; font-size: 12.5px; color: #6B7B74; margin-top: 18px; }
</style>
