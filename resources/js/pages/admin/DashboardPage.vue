<script setup>
import { onMounted, ref } from 'vue';
import api from '../../services/api';

const stats = ref({});
const loading = ref(true);
const cards = [
    ['leads', 'Prospects', '/admin/leads'], ['qualified_leads', 'Prospects qualifiés', '/admin/leads'],
    ['clients', 'Clients actifs', '/admin/clients'], ['quotes', 'Devis', '/admin/quotes'],
    ['open_tasks', 'Tâches ouvertes', '/admin/tasks'], ['published_posts', 'Articles publiés', '/admin/blog-posts'],
    ['published_solutions', 'Solutions publiées', '/admin/solutions'], ['accepted_quotes_total', 'CA accepté (DT)', '/admin/quotes'],
];

onMounted(async () => {
    const { data } = await api.get('/dashboard');
    stats.value = data.data;
    loading.value = false;
});
</script>

<template>
    <section>
        <div class="page-heading"><div><span class="eyebrow">VUE D’ENSEMBLE</span><h1>Tableau de bord</h1><p>Les indicateurs essentiels de l’activité commerciale et éditoriale.</p></div></div>
        <div v-if="loading" class="panel empty-state">Chargement des indicateurs…</div>
        <div v-else class="stats-grid">
            <RouterLink v-for="card in cards" :key="card[0]" :to="card[2]" class="stat-card">
                <small>{{ card[1] }}</small><strong>{{ stats[card[0]] ?? 0 }}</strong><span>Consulter →</span>
            </RouterLink>
        </div>
        <div class="panel next-panel"><div><span class="eyebrow">PROCHAINE ÉTAPE</span><h2>Pilotez le pipeline depuis un seul espace</h2></div><p>Ajoutez vos prospects et clients, préparez les devis, planifiez les tâches et gérez les contenus publiés sur le site.</p></div>
    </section>
</template>
