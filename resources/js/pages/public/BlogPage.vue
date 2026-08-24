<script setup>
import { computed, onMounted, ref } from 'vue';
import PublicLayout from '../../layouts/PublicLayout.vue';
import PageHero from '../../components/PageHero.vue';
import ScaleCta from '../../components/ScaleCta.vue';
import api from '../../services/api';
import { useSeo } from '../../composables/useSeo';

const posts = ref([]);
const categories = ref([]);
const search = ref('');
const category = ref('');
const visible = ref(12);
const filtered = computed(() => posts.value.filter((post) => (!category.value || post.category === category.value) && `${post.title} ${post.excerpt} ${post.category}`.toLowerCase().includes(search.value.toLowerCase())));
const readTime = (content) => Math.max(3, Math.round(String(content || '').split(/\s+/).length / 210));
const formatDate = (value) => new Intl.DateTimeFormat('fr-TN', { day: '2-digit', month: 'short', year: 'numeric' }).format(new Date(value));

onMounted(async () => {
    useSeo('Guides CRM et transformation digitale par métier | AISYSPRO', 'Guides pratiques pour digitaliser votre activité, structurer votre CRM et automatiser vos processus.');
    const { data } = await api.get('/public/posts');
    posts.value = data.data;
    categories.value = data.categories;
});
</script>

<template><PublicLayout><main><PageHero label="Guides & expertises" title="Des réponses concrètes à vos enjeux métier." accent="Passez de l’idée à l’action." description="Explorez les analyses AISYSPRO pour simplifier vos processus, choisir les bonnes priorités et mesurer les bénéfices de votre transformation digitale."><a href="#articles" class="site-cta">Explorer les guides ↓</a><RouterLink to="/contact" class="outline-cta">Proposer un sujet ↗</RouterLink></PageHero>
    <section id="articles" class="new-section catalog-section"><div class="new-heading"><span class="eyebrow-new">Bibliothèque métier administrable</span><h2>Des guides utiles,<br><em>classés selon votre activité.</em></h2><p>Recherchez un métier ou une thématique, puis accédez à des contenus structurés pour le référencement, la décision et la conversion.</p></div>
        <div class="catalog-toolbar blog-toolbar"><label><span>Rechercher un article</span><input v-model="search" placeholder="Titre, métier ou sujet…" @input="visible = 12"></label><label><span>Métier / secteur</span><select v-model="category" @change="visible = 12"><option value="">Tous</option><option v-for="item in categories" :key="item">{{ item }}</option></select></label><p><strong>{{ filtered.length }}</strong> guides</p></div>
        <div class="blog-catalog-grid"><article v-for="(post, index) in filtered.slice(0, visible)" :key="post.id" class="blog-card"><div class="blog-cover"><span>{{ post.category?.charAt(0) || 'A' }}</span><small>{{ post.category }}</small></div><header><small>{{ formatDate(post.published_at) }} · {{ readTime(post.content) }} min</small><span>{{ String(index + 1).padStart(2, '0') }}</span></header><h3>{{ post.title }}</h3><p>{{ post.excerpt }}</p><RouterLink :to="`/blog/${post.slug}`"><b>Découvrir</b><span>+</span></RouterLink></article></div>
        <div v-if="filtered.length > visible" class="load-more"><p>{{ Math.min(visible, filtered.length) }} articles affichés sur {{ filtered.length }}</p><button @click="visible += 12">Charger 12 articles supplémentaires ↓</button></div>
    </section><ScaleCta /></main></PublicLayout></template>
