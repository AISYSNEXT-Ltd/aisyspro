<script setup>
import { reactive, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth';

const auth = useAuthStore();
const router = useRouter();
const route = useRoute();
const form = reactive({ credential: '', password: '', remember: false });
const error = ref('');
const loading = ref(false);

async function submit() {
    loading.value = true; error.value = '';
    try {
        await auth.login(form);
        router.push(route.query.redirect || '/admin');
    } catch (requestError) {
        error.value = requestError.response?.data?.errors?.credential?.[0] || 'Connexion impossible. Veuillez réessayer.';
    } finally { loading.value = false; }
}
</script>

<template>
    <main class="login-page"><section class="login-visual"><RouterLink to="/" class="brand"><span class="brand-mark">A</span><span>AISYS<strong>PRO</strong></span></RouterLink><div><span class="eyebrow light">PLATEFORME DE GESTION</span><h1>Votre activité,<br />pilotée simplement.</h1><p>CRM, ventes, tâches et contenus réunis dans un espace professionnel et sécurisé.</p></div><small>© {{ new Date().getFullYear() }} AISYSPRO</small></section>
        <section class="login-form-wrap"><form class="login-card" @submit.prevent="submit"><span class="eyebrow">ESPACE SÉCURISÉ</span><h2>Connexion au Back Office</h2><p>Utilisez votre identifiant ou votre adresse e-mail.</p>
            <label><span>Identifiant</span><input v-model="form.credential" autocomplete="username" required autofocus placeholder="Votre identifiant" /></label>
            <label><span>Mot de passe</span><input v-model="form.password" type="password" autocomplete="current-password" required placeholder="Votre mot de passe" /></label>
            <label class="remember"><input v-model="form.remember" type="checkbox" /> Rester connecté</label>
            <p v-if="error" class="form-error">{{ error }}</p><button class="primary-button login-submit" :disabled="loading">{{ loading ? 'Connexion…' : 'Se connecter' }}</button>
            <RouterLink to="/" class="back-link">← Retour au site public</RouterLink></form></section>
    </main>
</template>
