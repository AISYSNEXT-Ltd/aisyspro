<script setup>
import { computed, onMounted, reactive, ref } from 'vue';
import api from '../../services/api';

const values = ref([]);
const groups = ref([]);
const activeGroup = ref('lead_status');
const loading = ref(false);
const error = ref('');
const modal = ref(false);
const editingId = ref(null);
const form = reactive({});
const formErrors = ref({});
const filteredValues = computed(() => values.value.filter((item) => item.group_key === activeGroup.value));

async function load() {
    loading.value = true; error.value = '';
    try {
        const { data } = await api.get('/reference-values');
        values.value = data.data; groups.value = data.groups;
    } catch (requestError) {
        error.value = requestError.response?.data?.message || 'Impossible de charger les référentiels.';
    } finally { loading.value = false; }
}

function edit(value = null) {
    Object.keys(form).forEach((key) => delete form[key]);
    Object.assign(form, value || { group_key: activeGroup.value, code: '', label: '', color: '#7c3aed', sort_order: (filteredValues.value.length + 1) * 10, is_default: false, is_active: true });
    editingId.value = value?.id || null; formErrors.value = {}; modal.value = true;
}

async function save() {
    formErrors.value = {};
    try {
        editingId.value ? await api.put(`/reference-values/${editingId.value}`, form) : await api.post('/reference-values', form);
        modal.value = false; await load();
    } catch (requestError) {
        formErrors.value = requestError.response?.data?.errors || { form: [requestError.response?.data?.message || 'Enregistrement impossible.'] };
    }
}

async function remove(value) {
    if (!window.confirm(`Supprimer « ${value.label} » ?`)) return;
    try { await api.delete(`/reference-values/${value.id}`); await load(); }
    catch (requestError) { error.value = requestError.response?.data?.message || 'Suppression impossible.'; }
}

onMounted(load);
</script>

<template>
    <section>
        <div class="page-heading"><div><span class="eyebrow">SYSTÈME</span><h1>Référentiels</h1><p>Centralisez les listes de valeurs utilisées dans AISYSPRO.</p></div><button class="primary-button" @click="edit()">+ Ajouter une valeur</button></div>
        <div class="reference-layout">
            <aside class="panel reference-groups"><button v-for="group in groups" :key="group.key" :class="{ active: activeGroup === group.key }" @click="activeGroup = group.key">{{ group.label }}</button></aside>
            <div class="panel table-panel">
                <div v-if="loading" class="pipeline-message">Chargement…</div>
                <div v-else-if="error" class="pipeline-message error-state"><p>{{ error }}</p><button class="secondary-button" @click="load">Réessayer</button></div>
                <div v-else class="table-wrap"><table><thead><tr><th>Ordre</th><th>Libellé</th><th>Code</th><th>Couleur</th><th>Par défaut</th><th>Actif</th><th>Actions</th></tr></thead><tbody>
                    <tr v-for="value in filteredValues" :key="value.id"><td>{{ value.sort_order }}</td><td>{{ value.label }}</td><td><code>{{ value.code }}</code></td><td><span class="color-swatch" :style="{ background: value.color }" /> {{ value.color }}</td><td>{{ value.is_default ? 'Oui' : 'Non' }}</td><td>{{ value.is_active ? 'Oui' : 'Non' }}</td><td class="actions"><button @click="edit(value)">Modifier</button><button class="danger" @click="remove(value)">Supprimer</button></td></tr>
                </tbody></table></div>
            </div>
        </div>
        <div v-if="modal" class="modal-layer" @click.self="modal = false"><form class="modal-card" @submit.prevent="save"><div class="modal-header"><div><span class="eyebrow">RÉFÉRENTIEL</span><h2>{{ editingId ? 'Modifier' : 'Ajouter' }} une valeur</h2></div><button type="button" @click="modal = false">×</button></div>
            <div class="form-grid"><label><span>Groupe *</span><select v-model="form.group_key" required><option v-for="group in groups" :key="group.key" :value="group.key">{{ group.label }}</option></select></label><label><span>Libellé *</span><input v-model="form.label" required /></label><label><span>Code *</span><input v-model="form.code" required /></label><label><span>Couleur</span><input v-model="form.color" type="color" /></label><label><span>Ordre *</span><input v-model="form.sort_order" type="number" min="0" required /></label><label class="checkbox"><span>Valeur par défaut</span><input v-model="form.is_default" type="checkbox" /></label><label class="checkbox"><span>Valeur active</span><input v-model="form.is_active" type="checkbox" /></label></div>
            <p v-for="messages in formErrors" :key="messages[0]" class="form-error">{{ messages[0] }}</p><div class="modal-actions"><button type="button" class="secondary-button" @click="modal = false">Annuler</button><button class="primary-button">Enregistrer</button></div>
        </form></div>
    </section>
</template>
