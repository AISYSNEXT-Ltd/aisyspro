<script setup>
import { computed, onMounted, reactive, ref, watch } from 'vue';
import { useRoute } from 'vue-router';
import { modules } from '../../config/modules';
import api from '../../services/api';

const route = useRoute();
const moduleKey = computed(() => route.meta.module);
const config = computed(() => modules[moduleKey.value]);
const rows = ref([]);
const meta = reactive({ current_page: 1, last_page: 1, total: 0, from: 0, to: 0 });
const filters = reactive({ search: '', status: '', per_page: 10, page: 1 });
const loading = ref(false);
const modal = ref(false);
const editingId = ref(null);
const form = reactive({});
const errors = ref({});
let debounce;

const getValue = (row, path) => path.split('.').reduce((value, key) => value?.[key], row) ?? '—';
const formatValue = (value) => value === true ? 'Oui' : value === false ? 'Non' : value;

async function load() {
    loading.value = true;
    if (moduleKey.value === 'users') {
        const roleField = config.value.fields.find((field) => field.key === 'role_id');
        if (!roleField.options.length) {
            const { data } = await api.get('/roles');
            roleField.options = data.data.map((role) => ({ value: role.id, label: role.name }));
        }
    }
    const { data } = await api.get(config.value.endpoint, { params: filters });
    rows.value = data.data;
    Object.assign(meta, data);
    loading.value = false;
}

function resetForm(row = null) {
    Object.keys(form).forEach((key) => delete form[key]);
    Object.assign(form, config.value.defaults || {}, row || {});
    if (moduleKey.value === 'users' && row) {
        form.role_id = row.role_id;
        form.password = '';
        form.password_confirmation = '';
    }
    editingId.value = row?.id || null;
    errors.value = {};
    modal.value = true;
}

async function save() {
    errors.value = {};
    try {
        editingId.value
            ? await api.put(`${config.value.endpoint}/${editingId.value}`, form)
            : await api.post(config.value.endpoint, form);
        modal.value = false;
        await load();
    } catch (error) {
        errors.value = error.response?.data?.errors || { form: ['Une erreur est survenue.'] };
    }
}

async function remove(row) {
    if (!window.confirm(`Supprimer ce ${config.value.singular} ?`)) return;
    await api.delete(`${config.value.endpoint}/${row.id}`);
    if (rows.value.length === 1 && filters.page > 1) filters.page -= 1;
    await load();
}

function changePage(page) { if (page >= 1 && page <= meta.last_page) { filters.page = page; load(); } }

watch(() => filters.search, () => { clearTimeout(debounce); filters.page = 1; debounce = setTimeout(load, 300); });
watch(() => [filters.status, filters.per_page], () => { filters.page = 1; load(); });
watch(moduleKey, () => { filters.search = ''; filters.status = ''; filters.page = 1; load(); });
onMounted(load);
</script>

<template>
    <section>
        <div class="page-heading"><div><span class="eyebrow">GESTION</span><h1>{{ config.title }}</h1><p>{{ meta.total }} élément{{ meta.total > 1 ? 's' : '' }} au total</p></div><button class="primary-button" @click="resetForm()">+ Ajouter</button></div>
        <div class="panel table-panel">
            <div class="table-toolbar">
                <label class="search-field"><span>⌕</span><input v-model="filters.search" :placeholder="`Rechercher par ${config.search}…`" /></label>
                <select v-if="config.fields.some((field) => field.key === 'status')" v-model="filters.status"><option value="">Tous les statuts</option><option v-for="option in config.fields.find((field) => field.key === 'status')?.options" :key="option.value" :value="option.value">{{ option.label }}</option></select>
                <label class="per-page">Afficher <select v-model="filters.per_page"><option v-for="size in [5, 10, 25, 50, 100]" :key="size" :value="size">{{ size }}</option></select></label>
            </div>
            <div class="table-wrap">
                <table><thead><tr><th v-for="column in config.columns" :key="column[0]">{{ column[1] }}</th><th>Actions</th></tr></thead>
                    <tbody><tr v-if="loading"><td :colspan="config.columns.length + 1" class="empty-state">Chargement…</td></tr>
                        <tr v-else-if="!rows.length"><td :colspan="config.columns.length + 1" class="empty-state">Aucun résultat. Ajoutez le premier élément.</td></tr>
                        <tr v-for="row in rows" v-else :key="row.id"><td v-for="column in config.columns" :key="column[0]"><span :class="column[0] === 'status' ? 'status-badge' : ''">{{ formatValue(getValue(row, column[0])) }}</span></td><td class="actions"><button @click="resetForm(row)">Modifier</button><button class="danger" @click="remove(row)">Supprimer</button></td></tr>
                    </tbody></table>
            </div>
            <div class="pagination"><span>{{ meta.from || 0 }}–{{ meta.to || 0 }} sur {{ meta.total }}</span><div><button :disabled="meta.current_page <= 1" @click="changePage(meta.current_page - 1)">Précédent</button><strong>{{ meta.current_page }} / {{ meta.last_page }}</strong><button :disabled="meta.current_page >= meta.last_page" @click="changePage(meta.current_page + 1)">Suivant</button></div></div>
        </div>

        <div v-if="modal" class="modal-layer" @click.self="modal = false"><form class="modal-card" @submit.prevent="save"><div class="modal-header"><div><span class="eyebrow">{{ editingId ? 'MODIFICATION' : 'CRÉATION' }}</span><h2>{{ editingId ? 'Modifier' : 'Ajouter' }} un {{ config.singular }}</h2></div><button type="button" @click="modal = false">×</button></div>
            <div class="form-grid"><label v-for="field in config.fields" :key="field.key" :class="{ wide: field.wide, checkbox: field.type === 'checkbox' }"><span>{{ field.label }} <b v-if="field.required">*</b></span>
                <textarea v-if="field.type === 'textarea'" v-model="form[field.key]" rows="4" />
                <select v-else-if="field.type === 'select'" v-model="form[field.key]" :required="field.required"><option v-for="option in field.options" :key="option.value" :value="option.value">{{ option.label }}</option></select>
                <input v-else-if="field.type === 'checkbox'" v-model="form[field.key]" type="checkbox" />
                <input v-else v-model="form[field.key]" :type="field.type || 'text'" :required="field.required" step="0.001" />
                <small v-if="errors[field.key]" class="field-error">{{ errors[field.key][0] }}</small></label></div>
            <p v-if="errors.form" class="form-error">{{ errors.form[0] }}</p><div class="modal-actions"><button type="button" class="secondary-button" @click="modal = false">Annuler</button><button class="primary-button">Enregistrer</button></div>
        </form></div>
    </section>
</template>
