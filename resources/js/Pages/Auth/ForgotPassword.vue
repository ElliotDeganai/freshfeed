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
    props: { status: String },
    data() {
        return {
            form: useForm({ email: '' }),
        };
    },
    methods: {
        submit() {
            this.form.post(route('password.email'));
        },
    },
};
</script>

<template>
    <Head title="Mot de passe oublié" />

    <div class="auth-header">
        <div class="auth-icon"><i class="ti ti-key"></i></div>
        <h1 class="auth-title">Mot de passe oublié</h1>
        <p class="auth-subtitle">On t'envoie un lien de réinitialisation par email.</p>
    </div>

    <div v-if="status" class="auth-status">{{ status }}</div>

    <form @submit.prevent="submit">
        <div class="auth-field">
            <InputLabel for="email" value="Email" />
            <TextInput id="email" v-model="form.email" type="email" autofocus />
            <InputError :message="form.errors.email" />
        </div>

        <PrimaryButton :disabled="form.processing">Envoyer le lien</PrimaryButton>
    </form>
</template>

<style scoped>
.auth-header { text-align: center; margin-bottom: 24px; }
.auth-icon {
    width: 46px; height: 46px; border-radius: 14px; background: #E6F1FB; color: #0C447C;
    display: flex; align-items: center; justify-content: center; font-size: 20px;
    margin: 0 auto 14px;
}
.auth-title { font-size: 18px; font-weight: 500; color: #10241D; margin-bottom: 4px; }
.auth-subtitle { font-size: 13px; color: #8FA098; }
.auth-status { background: #E7F5EF; color: #146C4E; font-size: 13px; padding: 10px 14px; border-radius: 10px; margin-bottom: 18px; }
.auth-field { margin-bottom: 20px; }
</style>
