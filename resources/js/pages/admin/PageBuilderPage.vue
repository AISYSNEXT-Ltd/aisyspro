<script setup>
import { computed, onMounted, reactive, ref, watch } from 'vue';
import api from '../../services/api';

const pages = ref([]);
const sections = ref([]);
const selectedPage = ref('');
const modal = ref(false);
const editingId = ref(null);
const errors = ref({});
const settingsJson = ref('{}');
const types = ['hero', 'text', 'image-text', 'gallery', 'cards', 'benefits', 'testimonials', 'stats', 'faq', 'cta', 'logos', 'banner', 'columns', 'custom', 'form', 'blog', 'solutions'];
const form = reactive({});
const currentPage = computed(() => pages.value.find((page) => String(page.id) === String(selectedPage.value)));

function resetForm(section = null) {
    editingId.value = section?.id || null;
    Object.assign(form, section || {
        page_id: Number(selectedPage.value), type: 'text', title: 'Nouvelle section', content: '',
        display_mode: 'append', animation: 'fade', duration: 500, delay: 0, intensity: 30,
        is_visible: true, sort_order: sections.value.length * 10,
    });
    settingsJson.value = JSON.stringify(section?.settings || {}, null, 2);
    errors.value = {};
    modal.value = true;
}

async function loadPages() {
    const { data } = await api.get('/pages', { params: { per_page: 100 } });
    pages.value = data.data;
    if (!selectedPage.value && pages.value.length) selectedPage.value = String(pages.value[0].id);
}

async function loadSections() {
    if (!selectedPage.value) return;
    const { data } = await api.get('/page-sections', { params: { page_id: selectedPage.value } });
    sections.value = data.data;
}

async function save() {
    try {
        const settings = JSON.parse(settingsJson.value || '{}');
        const payload = { ...form, page_id: Number(selectedPage.value), settings };
        editingId.value ? await api.put(`/page-sections/${editingId.value}`, payload) : await api.post('/page-sections', payload);
        modal.value = false;
        await loadSections();
    } catch (error) {
        errors.value = error instanceof SyntaxError ? { settings: ['Le JSON des paramètres est invalide.'] } : (error.response?.data?.errors || { form: ['Une erreur est survenue.'] });
    }
}

async function remove(section) {
    if (!window.confirm(`Supprimer définitivement la section « ${section.title || section.type} » ?`)) return;
    await api.delete(`/page-sections/${section.id}`);
    await loadSections();
}

async function move(index, direction) {
    const target = index + direction;
    if (target < 0 || target >= sections.value.length) return;
    [sections.value[index], sections.value[target]] = [sections.value[target], sections.value[index]];
    await api.put('/page-sections/reorder', { items: sections.value.map((section, order) => ({ id: section.id, sort_order: order * 10 })) });
    await loadSections();
}

watch(selectedPage, loadSections);
onMounted(async () => { await loadPages(); await loadSections(); });
</script>

<template>
    <section>
        <div class="page-heading"><div><span class="eyebrow">CMS · CONSTRUCTEUR DE PAGES</span><h1>Page Builder</h1><p>Composez les pages publiques avec des blocs réutilisables sans perdre le contenu existant.</p></div><button class="primary-button" :disabled="!selectedPage" @click="resetForm()">+ Ajouter un bloc</button></div>
        <div class="panel builder-toolbar">
            <label><span>Page administrée</span><select v-model="selectedPage"><option v-for="page in pages" :key="page.id" :value="String(page.id)">{{ page.title }}</option></select></label>
            <div><strong>{{ sections.length }} bloc(s)</strong><span>{{ sections.filter((item) => item.is_visible).length }} visible(s)</span></div>
            <a v-if="currentPage" :href="currentPage.slug === 'accueil' ? '/' : `/${currentPage.slug}`" target="_blank" rel="noopener">Prévisualiser ↗</a>
        </div>
        <div v-if="!sections.length" class="panel empty-state"><h2>Cette page utilise son contenu système.</h2><p>Ajoutez un premier bloc : il sera injecté sans supprimer le contenu existant.</p><button class="primary-button" @click="resetForm()">Créer le premier bloc</button></div>
        <div v-else class="section-list">
            <article v-for="(section, index) in sections" :key="section.id" class="panel section-row">
                <div><span class="eyebrow">{{ section.type }} · {{ section.display_mode === 'replace' ? 'REMPLACE LE SYSTÈME' : 'APRÈS LE SYSTÈME' }}</span><h2>{{ section.title || 'Section sans titre' }}</h2><p>{{ section.content || 'Aucun contenu textuel.' }}</p></div>
                <div class="section-status"><span :class="['status-badge', { muted: !section.is_visible }]">{{ section.is_visible ? 'Visible' : 'Masqué' }}</span><small>{{ section.animation }} · {{ section.duration }} ms</small></div>
                <div class="actions"><button :disabled="index === 0" @click="move(index, -1)">↑</button><button :disabled="index === sections.length - 1" @click="move(index, 1)">↓</button><button @click="resetForm(section)">Modifier</button><button class="danger" @click="remove(section)">Supprimer</button></div>
            </article>
        </div>

        <div v-if="modal" class="modal-layer" @click.self="modal = false"><form class="modal-card wide-modal" @submit.prevent="save">
            <div class="modal-header"><div><span class="eyebrow">{{ editingId ? 'MODIFICATION' : 'NOUVEAU BLOC' }}</span><h2>{{ editingId ? 'Modifier la section' : 'Nouvelle section' }}</h2></div><button type="button" @click="modal = false">×</button></div>
            <div class="form-grid">
                <label><span>Type de bloc *</span><select v-model="form.type" required><option v-for="type in types" :key="type" :value="type">{{ type }}</option></select></label>
                <label><span>Mode d’affichage *</span><select v-model="form.display_mode" required><option value="append">Ajouter après le contenu existant</option><option value="replace">Remplacer le contenu existant</option></select></label>
                <label class="wide"><span>Titre</span><input v-model="form.title"></label>
                <label class="wide"><span>Contenu</span><textarea v-model="form.content" rows="7" placeholder="Markdown accepté"></textarea></label>
                <label class="wide"><span>Configuration avancée JSON</span><textarea v-model="settingsJson" rows="7" placeholder='{"eyebrow":"Titre court","image":"/storage/..."}'></textarea><small v-if="errors.settings" class="field-error">{{ errors.settings[0] }}</small></label>
                <label><span>Animation</span><select v-model="form.animation"><option v-for="animation in ['none','fade','slide-up','slide-left','slide-right','zoom','scale','reveal']" :key="animation">{{ animation }}</option></select></label>
                <label><span>Durée (ms)</span><input v-model.number="form.duration" type="number" min="0" max="3000"></label>
                <label><span>Délai (ms)</span><input v-model.number="form.delay" type="number" min="0" max="3000"></label>
                <label><span>Intensité</span><input v-model.number="form.intensity" type="number" min="0" max="100"></label>
                <label class="checkbox"><span>Bloc visible</span><input v-model="form.is_visible" type="checkbox"></label>
            </div>
            <p v-if="errors.form" class="form-error">{{ errors.form[0] }}</p><div class="modal-actions"><button type="button" class="secondary-button" @click="modal = false">Annuler</button><button class="primary-button">Enregistrer le bloc</button></div>
        </form></div>
    </section>
</template>
