<script setup>
import { onMounted, reactive, ref } from 'vue';
import api from '../../services/api';
const rows = ref([]); const search = ref(''); const upload = reactive({ file: null, alt_text: '' }); const loading = ref(false);
async function load() { const { data } = await api.get('/media', { params: { search: search.value, per_page: 48 } }); rows.value = data.data; }
async function send() { if (!upload.file) return; loading.value = true; const body = new FormData(); body.append('file', upload.file); body.append('alt_text', upload.alt_text); await api.post('/media', body); upload.file = null; upload.alt_text = ''; loading.value = false; await load(); }
async function saveAlt(media) { await api.put(`/media/${media.id}`, { alt_text: media.alt_text }); }
async function remove(media) { if (!window.confirm(`Supprimer définitivement « ${media.original_name} » ?`)) return; await api.delete(`/media/${media.id}`); await load(); }
onMounted(load);
</script>
<template><section><div class="page-heading"><div><span class="eyebrow">CMS · MÉDIATHÈQUE</span><h1>Médias</h1><p>Importez, décrivez et réutilisez les images et documents du site.</p></div></div>
    <form class="panel media-upload" @submit.prevent="send"><label><span>Fichier JPG, PNG, WebP, GIF ou PDF · 5 Mo max.</span><input type="file" accept=".jpg,.jpeg,.png,.webp,.gif,.pdf" required @change="upload.file=$event.target.files[0]"></label><label><span>Texte alternatif</span><input v-model="upload.alt_text" placeholder="Description accessible du média"></label><button class="primary-button" :disabled="loading">{{ loading ? 'Téléversement…' : 'Téléverser' }}</button></form>
    <div class="panel table-toolbar"><label class="search-field"><span>⌕</span><input v-model="search" placeholder="Rechercher un média…" @input="load"></label></div>
    <div v-if="!rows.length" class="panel empty-state">Aucun média enregistré.</div><div v-else class="media-grid"><article v-for="media in rows" :key="media.id" class="panel media-card"><img v-if="media.mime_type.startsWith('image/')" :src="media.url" :alt="media.alt_text || ''" loading="lazy"><div v-else class="media-document">PDF</div><strong>{{ media.original_name }}</strong><small>{{ Math.ceil(media.size/1024) }} Ko</small><input v-model="media.alt_text" placeholder="Texte alternatif"><div class="actions"><button @click="saveAlt(media)">Enregistrer ALT</button><button class="danger" @click="remove(media)">Supprimer</button></div></article></div>
</section></template>
