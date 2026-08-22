<script setup>
import { onMounted, ref } from 'vue';
import PublicLayout from '../../layouts/PublicLayout.vue';
import PageHero from '../../components/PageHero.vue';
import InquiryForm from '../../components/InquiryForm.vue';
import api from '../../services/api';
import { useSeo } from '../../composables/useSeo';
const solutions = ref([]);
onMounted(async () => { useSeo('Contact AISYSPRO', 'Parlez-nous de votre projet CRM, site web, hébergement ou transformation digitale.'); const { data } = await api.get('/public/solutions'); solutions.value = data.data; });
</script>

<template><PublicLayout><main><PageHero label="Contact" title="Parlons de votre activité." accent="Pas seulement de technologie." description="Expliquez-nous votre contexte. Nous vous aidons à identifier le bon point de départ, même sans cahier des charges finalisé." />
    <section class="new-section contact-page"><div class="contact-info"><span class="eyebrow-new">Contact direct</span><h2>Une question,<br><em>un projet, un défi ?</em></h2><p>Notre équipe vous répond avec une première orientation claire : solution, pack recommandé et prochaines étapes.</p><div class="contact-cards"><a href="tel:+21651912668"><small>Téléphone</small><b>+216 51 912 668</b></a><a href="mailto:contact.aisyspro@gmail.com"><small>Email</small><b>contact.aisyspro@gmail.com</b></a><article><small>Zone d’intervention</small><b>Tunisie & Maghreb</b></article><article><small>Horaires</small><b>Lundi — Vendredi<br>08:30 — 17:30</b></article></div></div><InquiryForm :solutions="solutions" /></section>
    </main></PublicLayout></template>
