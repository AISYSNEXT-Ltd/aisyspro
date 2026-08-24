<script setup>
import { onMounted, ref } from 'vue';
import PublicLayout from '../../layouts/PublicLayout.vue';
import PageHero from '../../components/PageHero.vue';
import ScaleCta from '../../components/ScaleCta.vue';
import api from '../../services/api';
import { useSeo } from '../../composables/useSeo';

const packs = ref([]);
const options = [
    ['Intégration WhatsApp Business', 'Bouton, notifications et accès conversation', 120],
    ['Meta Ads — Pixel & CAPI', 'Suivi des campagnes Facebook et Instagram', 150],
    ['Analytics & Search Console', 'Mesure d’audience et suivi des conversions', 90],
    ['SEO local', 'Fiche Google, zones et mots-clés prioritaires', 180],
    ['Paiement en ligne', 'Connexion à une passerelle compatible', 250],
    ['Module SMS', 'Configuration hors coût des SMS consommés', 80],
    ['Emails professionnels supplémentaires', 'Jusqu’à 5 boîtes, valables 1 an', 60],
    ['Support prioritaire', 'Suivi prioritaire pendant 12 mois', 240],
];

onMounted(async () => {
    useSeo('Packs et tarifs AISYSPRO', 'Des packs CRM, site web et hébergement avec des prix clairs et des options à la carte.');
    const { data } = await api.get('/public/content');
    packs.value = data.packs;
});
</script>

<template><PublicLayout><main><PageHero label="Packs & tarifs" title="Aucune surprise." accent="Votre budget reste visible." description="Des packs adaptés, des options clairement tarifées et un total calculé avant l’envoi de votre demande." />
    <section class="new-section"><div class="pricing-new-grid"><article v-for="pack in packs" :key="pack.id" :class="{ featured: pack.featured }"><span v-if="pack.featured" class="popular">Le plus choisi</span><small>{{ pack.description }}</small><h3>{{ pack.name }}</h3><div class="price"><strong>{{ Number(pack.price).toFixed(0) }}</strong><b>DT</b></div><p>{{ pack.billing_period }}</p><ul><li v-for="feature in pack.features" :key="feature">✓ {{ feature }}</li></ul><RouterLink :to="{ path: '/devis', query: { pack: pack.slug } }" class="site-cta">Choisir ce pack ↗</RouterLink></article></div></section>
    <section class="new-section options-section"><div class="new-heading"><span class="eyebrow-new">Options à la carte</span><h2>Ajoutez seulement<br><em>ce qui crée de la valeur.</em></h2><p>Chaque option peut être activée avec n’importe quel pack.</p></div><div class="options-grid"><article v-for="option in options" :key="option[0]"><span>+</span><div><h3>{{ option[0] }}</h3><p>{{ option[1] }}</p></div><b>+ {{ option[2] }} DT</b></article></div><p class="legal-note">Le coût unitaire des SMS, les budgets publicitaires Meta et les commissions des prestataires de paiement restent facturés par les plateformes concernées.</p></section>
    <section class="configurator-cta"><span>Besoin d’aide pour choisir ?</span><h2>Décrivez votre métier.<br><em>Nous recommandons le bon pack.</em></h2><RouterLink to="/devis" class="site-cta large">Lancer le configurateur ↗</RouterLink></section>
    <ScaleCta />
    </main></PublicLayout></template>
