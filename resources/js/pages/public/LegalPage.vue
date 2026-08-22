<script setup>
import { onMounted } from 'vue';
import { useRoute } from 'vue-router';
import PublicLayout from '../../layouts/PublicLayout.vue';
import PageHero from '../../components/PageHero.vue';
import { useSeo } from '../../composables/useSeo';
const route = useRoute();
const privacy = route.name === 'privacy';
onMounted(() => useSeo(privacy ? 'Confidentialité | AISYSPRO' : 'Mentions légales | AISYSPRO'));
</script>
<template><PublicLayout><main><PageHero label="Informations" :title="privacy ? 'Protection des données.' : 'Mentions légales.'" :accent="privacy ? 'Une utilisation transparente.' : 'Informations de l’éditeur.'" description="Les informations essentielles relatives au site AISYSPRO et à l’utilisation de vos données." /><section class="article-shell legal-content"><article v-if="privacy"><h2>Données collectées</h2><p>Les formulaires collectent uniquement les informations nécessaires pour répondre à votre demande commerciale ou technique.</p><h2>Utilisation et conservation</h2><p>Les informations ne sont utilisées que pour traiter votre demande, assurer le suivi et respecter nos obligations. Leur durée de conservation est limitée au besoin correspondant.</p><h2>Vos droits</h2><p>Vous pouvez demander l’accès, la rectification ou la suppression de vos données à contact.aisyspro@gmail.com.</p></article><article v-else><h2>Éditeur</h2><p>AISYSPRO — solution portée par AISYSNEXT. Contact : contact.aisyspro@gmail.com — +216 51 912 668.</p><h2>Hébergement</h2><p>Les informations de l’hébergeur et de l’infrastructure sont communiquées selon l’environnement concerné.</p><h2>Propriété intellectuelle</h2><p>Les marques, contenus, interfaces et éléments graphiques AISYSPRO sont protégés. Toute réutilisation nécessite une autorisation préalable.</p></article></section></main></PublicLayout></template>
