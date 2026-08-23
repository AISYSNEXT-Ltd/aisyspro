<script setup>
import { onMounted, reactive, ref, watch } from 'vue';
import api from '../../services/api';

const menus = ref([]); const items = ref([]); const selectedMenu = ref(''); const modal = ref(false); const editingId = ref(null); const errors = ref({});
const form = reactive({});
async function loadMenus() { const { data } = await api.get('/menus'); menus.value = data.data; if (!selectedMenu.value && menus.value.length) selectedMenu.value = String(menus.value[0].id); }
async function loadItems() { if (!selectedMenu.value) return; const { data } = await api.get('/menu-items', { params: { menu_id: selectedMenu.value } }); items.value = data.data; }
function resetForm(item = null) { editingId.value = item?.id || null; Object.assign(form, item || { menu_id: Number(selectedMenu.value), parent_id: null, label: '', url: '/', link_type: 'internal', target: '_self', is_visible: true, sort_order: items.value.length * 10 }); errors.value = {}; modal.value = true; }
async function save() { try { const payload = { ...form, menu_id: Number(selectedMenu.value), parent_id: form.parent_id || null }; editingId.value ? await api.put(`/menu-items/${editingId.value}`, payload) : await api.post('/menu-items', payload); modal.value = false; await loadItems(); } catch (error) { errors.value = error.response?.data?.errors || { form: ['Une erreur est survenue.'] }; } }
async function remove(item) { if (!window.confirm(`Supprimer le lien « ${item.label} » ?`)) return; await api.delete(`/menu-items/${item.id}`); await loadItems(); }
async function move(index, direction) { const target = index + direction; if (target < 0 || target >= items.value.length) return; [items.value[index], items.value[target]] = [items.value[target], items.value[index]]; await api.put('/menu-items/reorder', { items: items.value.map((item, order) => ({ id: item.id, sort_order: order * 10 })) }); await loadItems(); }
watch(selectedMenu, loadItems); onMounted(async () => { await loadMenus(); await loadItems(); });
</script>
<template><section>
    <div class="page-heading"><div><span class="eyebrow">CMS · NAVIGATION</span><h1>Menus</h1><p>Gérez les liens internes, externes, leur ordre, leur visibilité et les sous-menus.</p></div><button class="primary-button" @click="resetForm()">+ Ajouter un lien</button></div>
    <div class="panel builder-toolbar"><label><span>Emplacement</span><select v-model="selectedMenu"><option v-for="menu in menus" :key="menu.id" :value="String(menu.id)">{{ menu.name }}</option></select></label><strong>{{ items.length }} lien(s)</strong></div>
    <div class="panel table-panel"><div class="table-wrap"><table><thead><tr><th>Libellé</th><th>URL</th><th>Parent</th><th>Type</th><th>Affichage</th><th>Actions</th></tr></thead><tbody>
        <tr v-if="!items.length"><td colspan="6" class="empty-state">Aucun lien dans ce menu.</td></tr>
        <tr v-for="(item,index) in items" :key="item.id"><td>{{ item.label }}</td><td>{{ item.url }}</td><td>{{ items.find((parent) => parent.id === item.parent_id)?.label || '—' }}</td><td>{{ item.link_type }}</td><td><span class="status-badge">{{ item.is_visible ? 'Visible' : 'Masqué' }}</span></td><td class="actions"><button :disabled="index===0" @click="move(index,-1)">↑</button><button :disabled="index===items.length-1" @click="move(index,1)">↓</button><button @click="resetForm(item)">Modifier</button><button class="danger" @click="remove(item)">Supprimer</button></td></tr>
    </tbody></table></div></div>
    <div v-if="modal" class="modal-layer" @click.self="modal=false"><form class="modal-card" @submit.prevent="save"><div class="modal-header"><h2>{{ editingId ? 'Modifier le lien' : 'Ajouter un lien' }}</h2><button type="button" @click="modal=false">×</button></div><div class="form-grid">
        <label><span>Libellé *</span><input v-model="form.label" required></label><label><span>URL *</span><input v-model="form.url" required></label>
        <label><span>Parent</span><select v-model="form.parent_id"><option :value="null">Aucun</option><option v-for="item in items.filter((candidate)=>candidate.id!==editingId)" :key="item.id" :value="item.id">{{ item.label }}</option></select></label>
        <label><span>Type</span><select v-model="form.link_type"><option value="internal">Interne</option><option value="external">Externe</option></select></label>
        <label><span>Ouverture</span><select v-model="form.target"><option value="_self">Même fenêtre</option><option value="_blank">Nouvelle fenêtre</option></select></label><label class="checkbox"><span>Visible</span><input v-model="form.is_visible" type="checkbox"></label>
    </div><p v-if="errors.form" class="form-error">{{ errors.form[0] }}</p><div class="modal-actions"><button type="button" class="secondary-button" @click="modal=false">Annuler</button><button class="primary-button">Enregistrer</button></div></form></div>
</section></template>
