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
    <main class="login-page"><section class="login-visual"><RouterLink to="/" class="site-brand"><strong>AISYS</strong><span>PRO</span></RouterLink><div class="login-visual-copy"><span class="eyebrow light">Votre système métier unifié</span><h1>Pilotez mieux.<br />Décidez plus vite.</h1><p>CRM, ventes, contenu et configuration réunis dans un environnement sécurisé, pensé pour faire avancer votre activité.</p><ul><li>✓ Données centralisées</li><li>✓ Accès sécurisé</li><li>✓ Processus paramétrables</li></ul></div><div class="login-demo" aria-hidden="true"><header><i></i><i></i><i></i><small>workspace.aisyspro.tn</small></header><div><aside><b>A</b><span>▦</span><span>⌁</span><span>▤</span></aside><section><small>Vue d’ensemble</small><h3>Bonjour, Ahmed 👋</h3><div><article><small>Nouveaux leads</small><b>48</b></article><article><small>Devis ouverts</small><b>17</b></article><article><small>Conversion</small><b>68%</b></article></div><footer><i v-for="height in [34,52,45,70,61,82,96]" :key="height" :style="{ height: `${height}%` }"></i></footer></section></div></div></section>
        <section class="login-form-wrap"><form class="login-card" @submit.prevent="submit"><span class="site-brand dark"><strong>AISYS</strong><span>PRO</span></span><span class="eyebrow">ESPACE SÉCURISÉ</span><h2>Connexion au back-office</h2><p>Accédez à votre espace de gestion AISYSPRO.</p>
            <label><span>Identifiant</span><input v-model="form.credential" autocomplete="username" required autofocus placeholder="Votre identifiant" /></label>
            <label><span>Mot de passe</span><input v-model="form.password" type="password" autocomplete="current-password" required placeholder="Votre mot de passe" /></label>
            <label class="remember"><input v-model="form.remember" type="checkbox" /> Rester connecté</label>
            <p v-if="error" class="form-error">{{ error }}</p><button class="site-cta login-submit" :disabled="loading">{{ loading ? 'Connexion…' : 'Accéder au back-office' }}</button>
            <div class="login-separator"><span>ou</span></div><button type="button" class="chatgpt-login" disabled title="Authentification ChatGPT non configurée">Continuer avec ChatGPT</button>
            <RouterLink to="/" class="back-link">← Retour au site</RouterLink></form></section>
    </main>
</template>
