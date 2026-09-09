<script setup>
import { onMounted, ref } from 'vue';
import api from '../../services/api';

const columns = ref([]);
const loading = ref(false);
const error = ref('');
const search = ref('');
const moving = ref(false);
let draggedId = null;
let debounce;

async function load() {
    loading.value = true;
    error.value = '';
    try {
        const { data } = await api.get('/lead-pipeline', { params: { search: search.value } });
        columns.value = data.data;
    } catch (requestError) {
        error.value = requestError.response?.data?.message || 'Impossible de charger le pipeline CRM.';
    } finally {
        loading.value = false;
    }
}

function scheduleSearch() {
    clearTimeout(debounce);
    debounce = setTimeout(load, 300);
}

function dragStart(lead) { draggedId = lead.id; }

async function drop(stage) {
    if (!draggedId || moving.value) return;
    const source = columns.value.find((column) => column.leads.some((lead) => lead.id === draggedId));
    if (!source || source.stage.code === stage.code) return;
    const lead = source.leads.find((item) => item.id === draggedId);
    source.leads = source.leads.filter((item) => item.id !== draggedId);
    const destination = columns.value.find((column) => column.stage.code === stage.code);
    destination.leads.unshift({ ...lead, status: stage.code });
    moving.value = true;
    try {
        await api.patch(`/leads/${draggedId}/stage`, { status: stage.code });
    } catch (requestError) {
        error.value = requestError.response?.data?.message || 'Le changement d’étape n’a pas été enregistré.';
        await load();
    } finally {
        moving.value = false;
        draggedId = null;
    }
}

onMounted(load);
</script>

<template>
    <section>
        <div class="page-heading"><div><span class="eyebrow">CRM & VENTES</span><h1>Pipeline des prospects</h1><p>Déplacez une carte pour enregistrer sa nouvelle étape.</p></div><div class="heading-actions"><RouterLink class="secondary-button" to="/admin/leads/liste">Vue liste</RouterLink><RouterLink class="primary-button" to="/admin/leads/liste">+ Prospect</RouterLink></div></div>
        <div class="pipeline-toolbar panel"><label class="search-field"><span>⌕</span><input v-model="search" placeholder="Rechercher un prospect…" @input="scheduleSearch" /></label><RouterLink to="/admin/referentiels">Configurer les colonnes</RouterLink></div>
        <div v-if="loading" class="panel pipeline-message">Chargement du pipeline…</div>
        <div v-else-if="error" class="panel pipeline-message error-state"><p>{{ error }}</p><button class="secondary-button" @click="load">Réessayer</button></div>
        <div v-else class="kanban-board">
            <section v-for="column in columns" :key="column.stage.id" class="kanban-column" @dragover.prevent @drop="drop(column.stage)">
                <header :style="{ '--stage-color': column.stage.color || '#7c3aed' }"><strong>{{ column.stage.label }}</strong><span>{{ column.leads.length }}</span></header>
                <div class="kanban-cards">
                    <article v-for="lead in column.leads" :key="lead.id" class="kanban-card" draggable="true" @dragstart="dragStart(lead)">
                        <strong>{{ lead.name }}</strong><small>{{ lead.company || 'Sans société' }}</small>
                        <div><span>{{ lead.value || 0 }} DT</span><span>{{ lead.source || '—' }}</span></div>
                        <small v-if="lead.quotes?.[0]">{{ lead.quotes[0].reference }} · {{ lead.quotes[0].status }}</small>
                    </article>
                    <p v-if="!column.leads.length" class="kanban-empty">Déposez un prospect ici</p>
                </div>
            </section>
        </div>
    </section>
</template>
