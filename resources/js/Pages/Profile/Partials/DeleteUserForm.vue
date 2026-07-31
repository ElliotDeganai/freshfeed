<template>
    <button type="button" class="btn-danger" @click="confirmOpen = true">Supprimer mon compte</button>

    <div v-if="confirmOpen" class="modal-overlay" @click.self="close">
        <div class="modal-card">
            <h3 class="modal-title">Es-tu sûr de vouloir supprimer ton compte ?</h3>
            <p class="modal-text">
                Cette action est irréversible. Toutes tes recettes et données seront définitivement
                supprimées. Entre ton mot de passe pour confirmer.
            </p>

            <form @submit.prevent="submit">
                <input
                    v-model="form.password"
                    ref="passwordInput"
                    type="password"
                    class="input"
                    placeholder="Mot de passe"
                    autofocus
                />
                <p v-if="form.errors.password" class="field-error">{{ form.errors.password }}</p>

                <div class="modal-actions">
                    <button type="button" class="btn-secondary" @click="close">Annuler</button>
                    <button type="submit" class="btn-danger" :disabled="form.processing">Supprimer définitivement</button>
                </div>
            </form>
        </div>
    </div>
</template>

<script>
import { useForm } from '@inertiajs/vue3';

export default {
    data() {
        return {
            confirmOpen: false,
            form: useForm({ password: '' }),
        };
    },
    methods: {
        submit() {
            this.form.delete(route('profile.destroy'), {
                preserveScroll: true,
                onSuccess: () => this.close(),
                onError: () => this.$refs.passwordInput?.focus(),
                onFinish: () => this.form.reset(),
            });
        },
        close() {
            this.confirmOpen = false;
            this.form.clearErrors();
            this.form.reset();
        },
    },
};
</script>

<style scoped>
.btn-danger {
    background: #FDECEC; color: #B3261E; border: none; border-radius: 20px;
    padding: 9px 20px; font-size: 13.5px; font-weight: 500; cursor: pointer;
}
.btn-danger:hover { background: #FBDADA; }
.btn-danger:disabled { opacity: .6; cursor: default; }

.modal-overlay {
    position: fixed; inset: 0; background: rgba(16,36,29,.45); z-index: 50;
    display: flex; align-items: center; justify-content: center; padding: 20px;
}
.modal-card { background: #fff; border-radius: 18px; padding: 26px; max-width: 400px; width: 100%; }
.modal-title { font-size: 15px; font-weight: 500; color: #10241D; margin-bottom: 8px; }
.modal-text { font-size: 12.5px; color: #6B7B74; line-height: 1.55; margin-bottom: 18px; }
.input { width: 100%; border: 0.5px solid #D9DDD9; border-radius: 10px; padding: 9px 12px; font-size: 13.5px; font-family: inherit; }
.field-error { font-size: 12px; color: #B3261E; margin-top: 6px; }
.modal-actions { display: flex; justify-content: flex-end; gap: 10px; margin-top: 18px; }
.btn-secondary { background: transparent; color: #6B7B74; border: 0.5px solid #D9DDD9; border-radius: 20px; padding: 9px 20px; font-size: 13.5px; cursor: pointer; }
</style>
