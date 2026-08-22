<script setup>
import { computed, onMounted, ref } from 'vue';
import PublicLayout from '../../layouts/PublicLayout.vue';
import PageHero from '../../components/PageHero.vue';
import api from '../../services/api';
import { useSeo } from '../../composables/useSeo';

const solutions = ref([]);
const categories = ref([]);
const search = ref('');
const category = ref('');
const visible = ref(15);
const filtered = computed(() => solutions.value.filter((item) => {
    const matchesCategory = !category.value || item.category === category.value;
    const haystack = `${item.title} ${item.short_description} ${item.category}`.toLowerCase();
    return matchesCategory && haystack.includes(search.value.toLowerCase());
}));

onMounted(async () => {
    useSeo('Solutions métier configurables | AISYSPRO', 'Plus de 50 solutions CRM et applications métier adaptées aux processus de chaque secteur.');
    const { data } = await api.get('/public/solutions');
    solutions.value = data.data;
    categories.value = data.categories;
});
</script>

<template><PublicLayout><main><PageHero label="Solutions métier" title="Votre métier a ses règles." accent="Votre solution doit les comprendre." description="Explorez les solutions AISYSPRO conçues pour les processus réels de chaque secteur : moins de tâches manuelles, plus de visibilité et une équipe mieux coordonnée."><a href="#catalogue" class="site-cta">Trouver mon métier ↓</a><RouterLink to="/contact" class="outline-cta">Décrire mon besoin ↗</RouterLink></PageHero>
    <section id="catalogue" class="new-section catalog-section"><div class="new-heading"><span class="eyebrow-new">Catalogue métier administrable</span><h2>Une solution adaptée<br><em>à chaque activité professionnelle.</em></h2><p>Recherchez votre secteur, découvrez les bénéfices et ouvrez une présentation détaillée alimentée directement par le Back Office AISYSPRO.</p></div>
        <div class="catalog-toolbar"><label><span>Rechercher un métier</span><input v-model="search" placeholder="Nom, secteur ou besoin…" @input="visible = 15"></label><label><span>Famille d’activité</span><select v-model="category" @change="visible = 15"><option value="">Toutes</option><option v-for="item in categories" :key="item">{{ item }}</option></select></label><p><strong>{{ filtered.length }}</strong> solutions disponibles</p></div>
        <div class="solution-catalog-grid"><RouterLink v-for="(solution, index) in filtered.slice(0, visible)" :key="solution.id" :to="`/solutions/${solution.slug}`" class="solution-widget"><header><span>{{ String(index + 1).padStart(2, '0') }}</span><small>{{ solution.category }}</small></header><h3>{{ solution.title }}</h3><p>{{ solution.short_description }}</p><div><em v-for="benefit in solution.benefits?.slice(0, 2)" :key="benefit">✓ {{ benefit }}</em></div><b>Découvrir +</b></RouterLink></div>
        <div v-if="filtered.length > visible" class="load-more"><p>{{ Math.min(visible, filtered.length) }} solutions affichées sur {{ filtered.length }}</p><button @click="visible += 15">Charger 15 solutions supplémentaires ↓</button></div>
    </section></main></PublicLayout></template>
