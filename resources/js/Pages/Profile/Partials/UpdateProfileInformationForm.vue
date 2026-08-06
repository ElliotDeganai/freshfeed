<template>
    <form @submit.prevent="submit">
        <label class="field">
            <span>Nom</span>
            <input v-model="form.name" type="text" class="input" required autocomplete="name" />
            <p v-if="form.errors.name" class="field-error">{{ form.errors.name }}</p>
        </label>

        <label class="field">
            <span>Email</span>
            <input v-model="form.email" type="email" class="input" required autocomplete="username" />
            <p v-if="form.errors.email" class="field-error">{{ form.errors.email }}</p>
        </label>

        <div class="field">
            <span>Description</span>
            <RichTextEditor v-model="form.bio" placeholder="Parle un peu de toi, de ta cuisine..." />
            <p v-if="form.errors.bio" class="field-error">{{ form.errors.bio }}</p>
        </div>

        <div v-if="mustVerifyEmail && !$page.props.auth.user.email_verified_at" class="verify-notice">
            <p>Ton email n'est pas encore vérifié.
                <Link :href="route('verification.send')" method="post" as="button" class="verify-link">
                    Renvoyer l'email de vérification
                </Link>
            </p>
            <p v-if="status === 'verification-link-sent'" class="verify-sent">
                Un nouveau lien de vérification a été envoyé.
            </p>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn-primary" :disabled="form.processing">Enregistrer</button>
            <transition name="fade">
                <span v-if="form.recentlySuccessful" class="saved-hint">Enregistré.</span>
            </transition>
        </div>
    </form>
</template>

<script>
import { Link, useForm, usePage } from '@inertiajs/vue3';
import RichTextEditor from '@/Components/RichTextEditor.vue';

export default {
    components: { Link, RichTextEditor },
    props: {
        mustVerifyEmail: Boolean,
        status: String,
    },
    data() {
        const user = usePage().props.auth.user;
        return {
            form: useForm({
                name: user.name,
                email: user.email,
                bio: user.bio,
            }),
        };
    },
    methods: {
        submit() {
            this.form.patch(route('profile.update'));
        },
    },
};
</script>

<style scoped>
.field { display: flex; flex-direction: column; gap: 7px; margin-bottom: 16px; font-size: 13px; color: #4B5A54; }
.input { border: 0.5px solid #D9DDD9; border-radius: 10px; padding: 9px 12px; font-size: 13.5px; background: #fff; font-family: inherit; }
.field-error { font-size: 12px; color: #B3261E; }

.verify-notice { font-size: 12.5px; color: #4B5A54; background: #FAEEDA; padding: 12px 14px; border-radius: 10px; margin-bottom: 16px; line-height: 1.5; }
.verify-link { color: #854F0B; font-weight: 500; background: none; border: none; padding: 0; cursor: pointer; text-decoration: underline; font-size: inherit; font-family: inherit; }
.verify-sent { color: #146C4E; margin-top: 6px; }

.form-actions { display: flex; align-items: center; gap: 12px; }
.btn-primary { background: #1D9E75; color: #fff; border: none; border-radius: 20px; padding: 9px 20px; font-size: 13.5px; font-weight: 500; cursor: pointer; }
.btn-primary:disabled { opacity: .6; cursor: default; }
.saved-hint { font-size: 12.5px; color: #146C4E; }
.fade-enter-active, .fade-leave-active { transition: opacity .3s; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>
