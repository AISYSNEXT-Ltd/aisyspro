<script setup>
import { onMounted, ref, watch } from 'vue';
import { useRoute } from 'vue-router';
import PublicLayout from '../../layouts/PublicLayout.vue';
import MarkdownContent from '../../components/MarkdownContent.vue';
import api from '../../services/api';
import { useSeo } from '../../composables/useSeo';

const route = useRoute();
const solution = ref(null);
const loading = ref(true);

async function load() {
    loading.value = true;
    const { data } = await api.get(`/public/solutions/${route.params.slug}`);
    solution.value = data.data;
    useSeo(solution.value.meta_title || solution.value.title, solution.value.meta_description || solution.value.short_description);
    loading.value = false;
}

onMounted(load);
watch(() => route.params.slug, load);
</script>

<template><PublicLayout><main><section v-if="loading" class="detail-loading">Chargement de la solution…</section><template v-else><section class="detail-hero"><div><RouterLink to="/solutions" class="detail-back">← Toutes les solutions</RouterLink><span class="eyebrow-new light">{{ solution.category }}</span><h1>{{ solution.title }}</h1><p>{{ solution.short_description }}</p><div class="tag-list"><span v-for="tag in solution.tags" :key="tag">#{{ tag }}</span></div><RouterLink to="/devis" class="site-cta large">Cadrer mon projet ↗</RouterLink></div><aside><span>A</span><small>Solution métier AISYSPRO</small><b>{{ solution.value_proposition }}</b></aside></section>
    <section class="new-section detail-overview"><div><span class="eyebrow-new">Modules prioritaires</span><h2>Un périmètre adapté<br><em>à vos opérations réelles.</em></h2></div><div class="module-grid"><article v-for="(module, index) in solution.modules" :key="module"><span>0{{ index + 1 }}</span><h3>{{ module }}</h3><p>Configuré selon les actions, informations et responsabilités réellement utilisées par votre équipe.</p></article></div></section>
    <section class="article-shell"><article><MarkdownContent :content="solution.description" /></article><aside><span class="eyebrow-new">Bénéfices attendus</span><ul><li v-for="benefit in solution.benefits" :key="benefit">✓ {{ benefit }}</li></ul><RouterLink to="/devis" class="site-cta">Recevoir une proposition ↗</RouterLink></aside></section></template></main></PublicLayout></template>
