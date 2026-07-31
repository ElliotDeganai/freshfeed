<template>
    <form @submit.prevent="submit">
        <label class="field">
            <span>Mot de passe actuel</span>
            <input v-model="form.current_password" ref="currentPasswordInput" type="password" class="input" autocomplete="current-password" />
            <p v-if="form.errors.current_password" class="field-error">{{ form.errors.current_password }}</p>
        </label>

        <label class="field">
            <span>Nouveau mot de passe</span>
            <input v-model="form.password" ref="passwordInput" type="password" class="input" autocomplete="new-password" />
            <p v-if="form.errors.password" class="field-error">{{ form.errors.password }}</p>
        </label>

        <label class="field">
            <span>Confirmer le mot de passe</span>
            <input v-model="form.password_confirmation" type="password" class="input" autocomplete="new-password" />
            <p v-if="form.errors.password_confirmation" class="field-error">{{ form.errors.password_confirmation }}</p>
        </label>

        <div class="form-actions">
            <button type="submit" class="btn-primary" :disabled="form.processing">Enregistrer</button>
            <transition name="fade">
                <span v-if="form.recentlySuccessful" class="saved-hint">Enregistré.</span>
            </transition>
        </div>
    </form>
</template>

<script>
import { useForm } from '@inertiajs/vue3';

export default {
    data() {
        return {
            form: useForm({
                current_password: '',
                password: '',
                password_confirmation: '',
            }),
        };
    },
    methods: {
        submit() {
            this.form.put(route('password.update'), {
                preserveScroll: true,
                onSuccess: () => this.form.reset(),
                onError: () => {
                    if (this.form.errors.password) {
                        this.form.reset('password', 'password_confirmation');
                        this.$refs.passwordInput?.focus();
                    }
                    if (this.form.errors.current_password) {
                        this.form.reset('current_password');
                        this.$refs.currentPasswordInput?.focus();
                    }
                },
            });
        },
    },
};
</script>

<style scoped>
.field { display: flex; flex-direction: column; gap: 7px; margin-bottom: 16px; font-size: 13px; color: #4B5A54; }
.input { border: 0.5px solid #D9DDD9; border-radius: 10px; padding: 9px 12px; font-size: 13.5px; background: #fff; font-family: inherit; }
.field-error { font-size: 12px; color: #B3261E; }

.form-actions { display: flex; align-items: center; gap: 12px; }
.btn-primary { background: #1D9E75; color: #fff; border: none; border-radius: 20px; padding: 9px 20px; font-size: 13.5px; font-weight: 500; cursor: pointer; }
.btn-primary:disabled { opacity: .6; cursor: default; }
.saved-hint { font-size: 12.5px; color: #146C4E; }
.fade-enter-active, .fade-leave-active { transition: opacity .3s; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>
