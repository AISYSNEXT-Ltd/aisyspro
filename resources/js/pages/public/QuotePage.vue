<script setup>
import { computed, onMounted, reactive, ref } from 'vue';
import { useRoute } from 'vue-router';
import api from '../../services/api';
import { useSeo } from '../../composables/useSeo';

const route = useRoute();
const step = ref(1);
const packs = ref([]);
const optionCatalog = ref([]);
const sending = ref(false);
const success = ref(false);
const feedback = ref('');
const reference = ref('');
const confirmedTotal = ref(null);
const makeSubmissionUuid = () => globalThis.crypto?.randomUUID?.() || 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, (character) => {
    const random = Math.floor(Math.random() * 16);
    return (character === 'x' ? random : (random & 0x3) | 0x8).toString(16);
});
const form = reactive({
    pack: '', option_slugs: [], name: '', company: '', email: '', phone: '', activity: '',
    company_size: '', user_count: '', current_tools: [], hosting_preference: 'undecided',
    desired_timeline: '', budget_range: 'undecided', message: '', consent: false,
    website: '', submission_uuid: makeSubmissionUuid(),
});
const selectedPack = computed(() => packs.value.find((pack) => pack.slug === form.pack));
const selectedOptions = computed(() => optionCatalog.value.filter((option) => form.option_slugs.includes(option.slug)));
const total = computed(() => Number(selectedPack.value?.price || 0) + selectedOptions.value.reduce((sum, option) => sum + Number(option.price), 0));
const stepLabels = ['Pack', 'Entreprise', 'Besoins', 'Validation'];

onMounted(async () => {
    useSeo('Configurer mon offre AISYSPRO', 'Choisissez votre pack, vos options et visualisez le budget avant validation.');
    const { data } = await api.get('/public/content');
    packs.value = data.packs;
    optionCatalog.value = data.offer_options || [];
    form.pack = packs.value.some((pack) => pack.slug === route.query.pack) ? route.query.pack : (packs.value.find((pack) => pack.featured)?.slug || packs.value[0]?.slug);
});

function nextStep() {
    feedback.value = '';
    if (step.value === 1 && !form.pack) feedback.value = 'Choisissez un pack pour continuer.';
    if (step.value === 2 && (!form.name || !form.email || !form.phone || !form.activity)) feedback.value = 'Renseignez les champs obligatoires pour continuer.';
    if (feedback.value) return;
    step.value = Math.min(4, step.value + 1);
}

async function submit() {
    sending.value = true; feedback.value = '';
    try {
        const { data } = await api.post('/public/inquiries', {
            type: 'quote', ...form, pack_slug: form.pack,
            user_count: form.user_count || null,
            source_url: window.location.href,
        });
        reference.value = data.data.reference;
        confirmedTotal.value = data.data.estimated_total;
        success.value = true;
    } catch (error) {
        const errors = error.response?.data?.errors;
        feedback.value = errors ? Object.values(errors)[0][0] : (error.response?.data?.message || 'La demande n’a pas pu être envoyée.');
    }
    finally { sending.value = false; }
}
</script>

<template>
    <main class="quote-builder">
        <header><RouterLink to="/">← Retour au site</RouterLink><span class="site-brand"><strong>AISYS</strong><span>PRO</span></span><p>Configurez votre solution et visualisez le budget complet avant validation.</p></header>
        <section v-if="success" class="builder-success">
            <span>✓</span><h1>Votre demande est enregistrée.</h1>
            <p>Référence <strong>{{ reference }}</strong><template v-if="confirmedTotal !== null"> · Estimation confirmée : <strong>{{ confirmedTotal }} DT</strong></template></p>
            <p>Notre équipe reviendra vers vous avec un périmètre, un délai et un devis final.</p>
            <RouterLink to="/" class="site-cta">Retour à l’accueil</RouterLink>
        </section>
        <section v-else class="builder-card">
            <div class="builder-heading"><span class="eyebrow-new">Configurateur de devis</span><h1>Construisez votre solution</h1><p>Un parcours guidé pour qualifier votre besoin avant l’échange avec notre équipe.</p></div>
            <div class="builder-steps">
                <button v-for="number in [1, 2, 3, 4]" :key="number" type="button" :class="{ active: step >= number }" :disabled="number > step" @click="step = number"><b>{{ number }}</b><span>{{ stepLabels[number - 1] }}</span></button>
            </div>
            <div class="builder-layout">
                <form @submit.prevent="submit">
                    <section v-if="step === 1">
                        <h2>1. Choisissez votre pack</h2><p>Les offres constituent une base : elles seront ajustées à votre métier et à vos processus.</p>
                        <label v-for="pack in packs" :key="pack.id" class="pack-choice" :class="{ selected: form.pack === pack.slug }"><input v-model="form.pack" type="radio" :value="pack.slug"><span><small>{{ pack.description }} · {{ pack.billing_period }}</small><b>{{ pack.name }}</b></span><strong>{{ Number(pack.price).toFixed(0) }} DT</strong></label>
                    </section>
                    <section v-else-if="step === 2">
                        <h2>2. Parlez-nous de votre entreprise</h2>
                        <div class="form-row"><label><span>Nom complet *</span><input v-model.trim="form.name" required autocomplete="name"></label><label><span>Entreprise</span><input v-model.trim="form.company" autocomplete="organization"></label></div>
                        <div class="form-row"><label><span>Email *</span><input v-model.trim="form.email" type="email" required autocomplete="email"></label><label><span>Téléphone *</span><input v-model.trim="form.phone" required autocomplete="tel"></label></div>
                        <label><span>Votre activité / métier *</span><input v-model.trim="form.activity" required placeholder="Ex. cabinet médical, BTP, société de services…"></label>
                        <div class="form-row"><label><span>Taille de l’entreprise</span><select v-model="form.company_size"><option value="">Non précisé</option><option value="solo">Indépendant</option><option value="2-10">2 à 10 personnes</option><option value="11-50">11 à 50 personnes</option><option value="51-200">51 à 200 personnes</option><option value="200-plus">Plus de 200 personnes</option></select></label><label><span>Utilisateurs prévus</span><input v-model.number="form.user_count" type="number" min="1" max="10000"></label></div>
                    </section>
                    <section v-else-if="step === 3">
                        <h2>3. Précisez votre besoin</h2><p>Ces informations nous permettent de préparer un premier cadrage pertinent.</p>
                        <div class="builder-options"><label v-for="option in optionCatalog" :key="option.slug"><input v-model="form.option_slugs" type="checkbox" :value="option.slug"><span>{{ option.name }}</span><b>+ {{ Number(option.price).toFixed(0) }} DT</b></label></div>
                        <div class="builder-field"><span>Outils actuels</span><div class="builder-options compact"><label v-for="tool in ['Excel / Google Sheets', 'CRM existant', 'ERP / logiciel métier', 'Aucun outil structuré']" :key="tool"><input v-model="form.current_tools" type="checkbox" :value="tool"><span>{{ tool }}</span></label></div></div>
                        <div class="form-row"><label><span>Hébergement souhaité</span><select v-model="form.hosting_preference"><option value="undecided">À conseiller</option><option value="included">Inclus par AISYSPRO</option><option value="existing">Infrastructure existante</option></select></label><label><span>Délai souhaité</span><select v-model="form.desired_timeline"><option value="">Non précisé</option><option value="urgent">Urgent</option><option value="1-3-months">1 à 3 mois</option><option value="3-6-months">3 à 6 mois</option><option value="flexible">Flexible</option></select></label></div>
                        <label><span>Budget envisagé</span><select v-model="form.budget_range"><option value="undecided">À définir</option><option value="under-1000">Moins de 1 000 DT</option><option value="1000-3000">1 000 à 3 000 DT</option><option value="3000-10000">3 000 à 10 000 DT</option><option value="over-10000">Plus de 10 000 DT</option></select></label>
                    </section>
                    <section v-else>
                        <h2>4. Vérifiez et validez</h2><p>Vous pourrez préciser le périmètre lors de l’échange de cadrage.</p>
                        <div class="quote-summary"><p><span>Offre</span><strong>{{ selectedPack?.name }}</strong></p><p><span>Options</span><strong>{{ selectedOptions.length ? selectedOptions.map((option) => option.name).join(', ') : 'Aucune' }}</strong></p><p><span>Activité</span><strong>{{ form.activity }}</strong></p><p><span>Contact</span><strong>{{ form.name }} · {{ form.email }}</strong></p></div>
                        <label><span>Précisions utiles</span><textarea v-model.trim="form.message" rows="5" maxlength="5000" placeholder="Processus prioritaires, difficultés actuelles, objectifs…"></textarea></label>
                        <label class="consent"><input v-model="form.consent" type="checkbox" required><span>J’accepte que mes informations soient utilisées pour étudier et répondre à cette demande.</span></label>
                        <label class="honeypot" aria-hidden="true"><input v-model="form.website" tabindex="-1" autocomplete="off"></label>
                    </section>
                    <p v-if="feedback" class="form-error">{{ feedback }}</p>
                    <div class="builder-nav"><button type="button" :disabled="step === 1" @click="step--">← Retour</button><button v-if="step < 4" type="button" class="site-cta" @click="nextStep">Continuer →</button><button v-else class="site-cta" :disabled="sending || !form.consent">{{ sending ? 'Envoi…' : 'Valider ma demande ↗' }}</button></div>
                </form>
                <aside><span>Votre estimation</span><h3>{{ selectedPack?.name }}</h3><div><small>Prix du pack</small><b>{{ Number(selectedPack?.price || 0).toFixed(0) }} DT</b></div><div v-if="selectedOptions.length"><small>{{ selectedOptions.length }} option(s)</small><b>+ {{ total - Number(selectedPack?.price || 0) }} DT</b></div><div class="estimate-total"><small>Total estimé</small><strong>{{ total }} DT</strong></div><p>Estimation recalculée et contrôlée par le serveur. Le devis final confirme le périmètre et les délais.</p></aside>
            </div>
        </section>
    </main>
</template>
