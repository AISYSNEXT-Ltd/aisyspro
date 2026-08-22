<script setup>
import { computed, onMounted, reactive, ref } from 'vue';
import { useRoute } from 'vue-router';
import api from '../../services/api';
import { useSeo } from '../../composables/useSeo';

const route = useRoute();
const step = ref(1);
const packs = ref([]);
const sending = ref(false);
const success = ref(false);
const feedback = ref('');
const optionCatalog = [['WhatsApp Business',120],['Pixel Meta & CAPI',150],['Analytics & Search Console',90],['SEO local',180],['Paiement en ligne',250],['Module SMS',80],['Emails professionnels',60],['Support prioritaire',240]];
const form = reactive({ pack: '', options: [], name: '', company: '', email: '', phone: '', activity: '', message: '', website: '' });
const selectedPack = computed(() => packs.value.find((pack) => pack.slug === form.pack));
const total = computed(() => Number(selectedPack.value?.price || 0) + form.options.reduce((sum, name) => sum + (optionCatalog.find((item) => item[0] === name)?.[1] || 0), 0));

onMounted(async () => {
    useSeo('Configurer mon offre AISYSPRO', 'Choisissez votre pack, vos options et visualisez le budget avant validation.');
    const { data } = await api.get('/public/content');
    packs.value = data.packs;
    form.pack = packs.value.some((pack) => pack.slug === route.query.pack) ? route.query.pack : (packs.value.find((pack) => pack.featured)?.slug || packs.value[0]?.slug);
});

async function submit() {
    sending.value = true; feedback.value = '';
    try {
        await api.post('/public/inquiries', { type: 'quote', name: form.name, email: form.email, phone: form.phone, company: form.company, requested_solution: selectedPack.value?.name, subject: `Configurateur — ${form.activity}`, website: form.website, message: `${form.message || 'Demande depuis le configurateur.'}\n\nPack : ${selectedPack.value?.name}\nOptions : ${form.options.join(', ') || 'Aucune'}\nBudget estimé : ${total.value} DT` });
        success.value = true;
    } catch (error) { feedback.value = error.response?.data?.message || 'La demande n’a pas pu être envoyée.'; }
    finally { sending.value = false; }
}
</script>

<template><main class="quote-builder"><header><RouterLink to="/">← Retour au site</RouterLink><span class="site-brand"><strong>AISYS</strong><span>PRO</span></span><p>Configurez votre solution et visualisez le budget complet avant validation.</p></header><section v-if="success" class="builder-success"><span>✓</span><h1>Votre demande est enregistrée.</h1><p>Notre équipe reviendra vers vous avec un périmètre, un délai et un devis final.</p><RouterLink to="/" class="site-cta">Retour à l’accueil</RouterLink></section><section v-else class="builder-card"><div class="builder-heading"><span class="eyebrow-new">Configurateur de devis</span><h1>Construisez votre solution</h1><p>Choisissez votre pack, vos options et visualisez le total avant validation.</p></div><div class="builder-steps"><button v-for="number in [1,2,3]" :key="number" :class="{ active: step >= number }" @click="step = number"><b>{{ number }}</b><span>{{ ['Pack','Entreprise','Options'][number-1] }}</span></button></div>
        <div class="builder-layout"><form @submit.prevent="submit"><section v-if="step === 1"><h2>1. Choisissez votre pack</h2><p>Comparez les offres disponibles, toutes ajustables selon votre métier.</p><label v-for="pack in packs" :key="pack.id" class="pack-choice" :class="{ selected: form.pack === pack.slug }"><input v-model="form.pack" type="radio" :value="pack.slug"><span><small>{{ pack.description }} · {{ pack.billing_period }}</small><b>{{ pack.name }}</b></span><strong>{{ Number(pack.price).toFixed(0) }} DT</strong></label></section>
            <section v-else-if="step === 2"><h2>2. Parlez-nous de votre entreprise</h2><div class="form-row"><label><span>Nom complet *</span><input v-model="form.name" required></label><label><span>Entreprise</span><input v-model="form.company"></label></div><div class="form-row"><label><span>Email *</span><input v-model="form.email" type="email" required></label><label><span>Téléphone *</span><input v-model="form.phone" required></label></div><label><span>Votre activité / métier *</span><input v-model="form.activity" required></label><label><span>Précisions utiles</span><textarea v-model="form.message" rows="5"></textarea></label></section>
            <section v-else><h2>3. Ajoutez les options utiles</h2><div class="builder-options"><label v-for="option in optionCatalog" :key="option[0]"><input v-model="form.options" type="checkbox" :value="option[0]"><span>{{ option[0] }}</span><b>+ {{ option[1] }} DT</b></label></div><label class="honeypot"><input v-model="form.website" tabindex="-1"></label><p v-if="feedback" class="form-error">{{ feedback }}</p></section>
            <div class="builder-nav"><button type="button" :disabled="step === 1" @click="step--">← Retour</button><button v-if="step < 3" type="button" class="site-cta" @click="step++">Continuer →</button><button v-else class="site-cta" :disabled="sending">{{ sending ? 'Envoi…' : 'Valider ma demande ↗' }}</button></div></form>
            <aside><span>Votre estimation</span><h3>{{ selectedPack?.name }}</h3><div><small>Prix du pack</small><b>{{ Number(selectedPack?.price || 0).toFixed(0) }} DT</b></div><div v-if="form.options.length"><small>{{ form.options.length }} option(s)</small><b>+ {{ total - Number(selectedPack?.price || 0) }} DT</b></div><div class="estimate-total"><small>Total estimé</small><strong>{{ total }} DT</strong></div><p>Prix affiché avant validation. Le devis final confirme le périmètre et les délais.</p></aside></div></section></main></template>
