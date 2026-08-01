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
.auth-field { margin-bottom: 20px; }
</style>
