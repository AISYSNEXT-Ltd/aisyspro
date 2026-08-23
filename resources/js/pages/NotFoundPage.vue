<script setup>
import { onMounted } from 'vue';
import PublicLayout from '../layouts/PublicLayout.vue';
import { useSeo } from '../composables/useSeo';
onMounted(() => useSeo({ title: 'Page introuvable | AISYSPRO', description: 'La page demandée est introuvable.', robots: 'noindex,nofollow' }));
</script>
<template><PublicLayout><main class="not-found-page"><span>404</span><h1>Cette page n’existe pas.</h1><p>Le contenu a peut-être été déplacé ou l’adresse est incorrecte.</p><div><RouterLink to="/" class="site-cta">Retour à l’accueil</RouterLink><RouterLink to="/solutions">Explorer les solutions →</RouterLink></div></main></PublicLayout></template>
