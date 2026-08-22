<script setup>
import { onMounted, ref, watch } from 'vue';
import { useRoute } from 'vue-router';
import PublicLayout from '../../layouts/PublicLayout.vue';
import MarkdownContent from '../../components/MarkdownContent.vue';
import api from '../../services/api';
import { useSeo } from '../../composables/useSeo';

const route = useRoute();
const post = ref(null);
const loading = ref(true);
const formatDate = (value) => new Intl.DateTimeFormat('fr-TN', { day: '2-digit', month: 'long', year: 'numeric' }).format(new Date(value));

async function load() {
    loading.value = true;
    const { data } = await api.get(`/public/posts/${route.params.slug}`);
    post.value = data.data;
    useSeo(post.value.meta_title || post.value.title, post.value.meta_description || post.value.excerpt);
    loading.value = false;
}
onMounted(load);
watch(() => route.params.slug, load);
</script>

<template><PublicLayout><main><section v-if="loading" class="detail-loading">Chargement de l’article…</section><template v-else><section class="article-hero"><RouterLink to="/blog">← Retour au blog</RouterLink><span class="eyebrow-new light">{{ post.category }}</span><h1>{{ post.title }}</h1><p>{{ post.excerpt }}</p><div><span>{{ formatDate(post.published_at) }}</span><span>Équipe AISYSPRO</span></div></section><section class="article-shell"><article><MarkdownContent :content="post.content" /></article><aside><span class="eyebrow-new">Votre projet</span><h3>Transformez ces conseils en plan d’action.</h3><p>Échangez avec un expert AISYSPRO pour cadrer vos priorités.</p><RouterLink to="/devis" class="site-cta">Configurer mon offre ↗</RouterLink><div class="tag-list"><span v-for="tag in post.tags" :key="tag">#{{ tag }}</span></div></aside></section></template></main></PublicLayout></template>
