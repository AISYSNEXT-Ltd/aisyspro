<script setup>
import { onMounted, reactive, ref } from 'vue';
import api from '../../services/api';
const saved = ref(false); const form = reactive({ brand_name: 'AISYSPRO', email: '', phone: '', locale: 'fr-TN', default_robots: 'index,follow' });
const definitions = { brand_name: 'general', email: 'general', phone: 'general', locale: 'general', default_robots: 'seo' };
async function load() { const { data } = await api.get('/settings'); Object.values(data.data).flat().forEach((setting) => { if (setting.key in form) form[setting.key] = setting.value; }); }
async function save() { await api.put('/settings', { settings: Object.entries(form).map(([key,value]) => ({ key, value, group: definitions[key] })) }); saved.value = true; setTimeout(() => { saved.value = false; }, 2500); }
onMounted(load);
</script>
<template><section><div class="page-heading"><div><span class="eyebrow">SYSTÈME · CONFIGURATION</span><h1>Paramètres</h1><p>Centralisez l’identité, les coordonnées et les règles SEO globales.</p></div><button class="primary-button" @click="save">Enregistrer les modifications</button></div>
    <p v-if="saved" class="success-banner">Configuration enregistrée.</p><div class="panel settings-grid"><label><span>Nom commercial</span><input v-model="form.brand_name"></label><label><span>E-mail principal</span><input v-model="form.email" type="email"></label><label><span>Téléphone</span><input v-model="form.phone"></label><label><span>Langue</span><select v-model="form.locale"><option value="fr-TN">Français — Tunisie</option><option value="ar-TN">العربية — تونس</option><option value="en">English</option></select></label><label><span>Indexation par défaut</span><select v-model="form.default_robots"><option value="index,follow">Indexer et suivre</option><option value="noindex,nofollow">Ne pas indexer</option></select></label></div>
</section></template>
