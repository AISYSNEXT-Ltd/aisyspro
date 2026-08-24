<script setup>
import { computed, onMounted, ref } from 'vue';
import PublicLayout from '../../layouts/PublicLayout.vue';
import PageHero from '../../components/PageHero.vue';
import api from '../../services/api';
import { useSeo } from '../../composables/useSeo';

const faqs = ref([]);
const category = ref('');
const open = ref(null);
const categories = computed(() => [...new Set(faqs.value.map((faq) => faq.category).filter(Boolean))]);
const filtered = computed(() => faqs.value.filter((faq) => !category.value || faq.category === category.value));
onMounted(async () => { useSeo('FAQ AISYSPRO', 'Réponses sur les packs, la personnalisation, les données, les délais, la sécurité et le support.'); const { data } = await api.get('/public/content'); faqs.value = data.faqs; open.value = faqs.value[0]?.id ?? null; });
</script>

<template><PublicLayout><main><PageHero label="Centre d’aide" title="Les réponses claires." accent="Avant de vous engager." description="Packs, personnalisation, données, délais, sécurité et support : retrouvez les informations essentielles." />
    <section class="new-section faq-page"><div class="faq-categories"><button :class="{ active: !category }" @click="category = ''">Toutes</button><button v-for="item in categories" :key="item" :class="{ active: category === item }" @click="category = item">{{ item }}</button><div><h3>Une autre question ?</h3><p>Un conseiller vous répond sous 24h ouvrées.</p><RouterLink to="/contact">Nous contacter ↗</RouterLink></div></div><div class="faq-list-new"><article v-for="faq in filtered" :key="faq.id"><small>{{ faq.category }}</small><button @click="open = open === faq.id ? null : faq.id"><span>{{ faq.question }}</span><b>{{ open === faq.id ? '−' : '+' }}</b></button><p v-if="open === faq.id">{{ faq.answer }}</p></article></div></section>
    </main></PublicLayout></template>
