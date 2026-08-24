<script setup>
import { computed } from 'vue';
import { useRoute } from 'vue-router';

const route = useRoute();
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
const error = computed(() => {
    if (route.query.error === 'credentials') return 'Identifiants incorrects ou compte désactivé.';
    if (route.query.error === 'expired') return 'La tentative de connexion a expiré. Veuillez réessayer.';
    return '';
});
</script>

<template>
    <main class="login-page"><section class="login-visual"><RouterLink to="/" class="site-brand"><strong>AISYS</strong><span>PRO</span></RouterLink><div class="login-visual-copy"><span class="eyebrow light">Votre système métier unifié</span><h1>Pilotez mieux.<br />Décidez plus vite.</h1><p>CRM, ventes, contenu et configuration réunis dans un environnement sécurisé, pensé pour faire avancer votre activité.</p><ul><li>✓ Données centralisées</li><li>✓ Accès sécurisé</li><li>✓ Processus paramétrables</li></ul></div><div class="login-demo" aria-hidden="true"><header><i></i><i></i><i></i><small>workspace.aisyspro.tn</small></header><div><aside><b>A</b><span>▦</span><span>⌁</span><span>▤</span></aside><section><small>Vue d’ensemble</small><h3>Bonjour, Ahmed 👋</h3><div><article><small>Nouveaux leads</small><b>48</b></article><article><small>Devis ouverts</small><b>17</b></article><article><small>Conversion</small><b>68%</b></article></div><footer><i v-for="height in [34,52,45,70,61,82,96]" :key="height" :style="{ height: `${height}%` }"></i></footer></section></div></div></section>
        <section class="login-form-wrap"><form class="login-card" method="post" action="/connexion-admin/session"><input type="hidden" name="_token" :value="csrfToken" /><span class="site-brand dark"><strong>AISYS</strong><span>PRO</span></span><span class="eyebrow">ESPACE SÉCURISÉ</span><h2>Connexion au back-office</h2><p>Accédez à votre espace de gestion AISYSPRO.</p>
            <label><span>Identifiant</span><input name="credential" autocomplete="username" required autofocus placeholder="Votre identifiant" /></label>
            <label><span>Mot de passe</span><input name="password" type="password" autocomplete="current-password" required placeholder="Votre mot de passe" /></label>
            <label class="remember"><input name="remember" type="checkbox" value="1" /> Rester connecté</label>
            <p v-if="error" class="form-error">{{ error }}</p><button class="site-cta login-submit">Accéder au back-office</button>
            <div class="login-separator"><span>ou</span></div><button type="button" class="chatgpt-login" disabled title="Authentification ChatGPT non configurée">Continuer avec ChatGPT</button>
            <RouterLink to="/" class="back-link">← Retour au site</RouterLink></form></section>
    </main>
</template>
