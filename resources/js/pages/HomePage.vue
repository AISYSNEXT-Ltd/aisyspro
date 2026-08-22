<script setup>
import { onMounted, reactive, ref } from 'vue';
import api from '../services/api';

const content = reactive({ solutions: [], packs: [], faqs: [], posts: [] });
const openFaq = ref(null);
const form = reactive({ type: 'quote', name: '', email: '', phone: '', company: '', requested_solution: '', subject: '', message: '', website: '' });
const sending = ref(false);
const feedback = ref({ type: '', message: '' });

onMounted(async () => {
    try {
        const { data } = await api.get('/public/content');
        Object.assign(content, data);
    } catch {
        feedback.value = { type: 'error', message: 'Certains contenus sont momentanément indisponibles.' };
    }
});

async function submitInquiry() {
    sending.value = true;
    feedback.value = { type: '', message: '' };
    try {
        const { data } = await api.post('/public/inquiries', form);
        feedback.value = { type: 'success', message: data.message };
        Object.assign(form, { type: 'quote', name: '', email: '', phone: '', company: '', requested_solution: '', subject: '', message: '', website: '' });
    } catch (error) {
        const errors = error.response?.data?.errors;
        feedback.value = { type: 'error', message: errors ? Object.values(errors)[0][0] : 'Envoi impossible. Veuillez réessayer.' };
    } finally {
        sending.value = false;
    }
}
</script>

<template>
    <main class="public-site">
        <header class="public-header">
            <RouterLink to="/" class="brand"><span class="brand-mark">A</span><span>AISYS<strong>PRO</strong></span></RouterLink>
            <nav><a href="#solutions">Solutions</a><a href="#tarifs">Tarifs</a><a href="#faq">FAQ</a><a href="#contact">Contact</a></nav>
            <RouterLink to="/connexion-admin" class="header-login">Connexion</RouterLink>
        </header>

        <section class="public-hero">
            <div><span class="eyebrow light">PLATEFORME MÉTIER MODULAIRE</span><h1>Votre entreprise.<br><em>Mieux pilotée.</em></h1><p>Centralisez vos prospects, clients, devis, tâches et contenus dans une plateforme claire, sécurisée et adaptée à votre métier.</p><div class="hero-actions"><a href="#contact" class="primary-button">Demander une démonstration</a><a href="#solutions" class="ghost-button">Découvrir les solutions</a></div></div>
            <div class="hero-dashboard" aria-label="Aperçu du tableau de bord"><div class="preview-top"><span></span><span></span><span></span></div><strong>Vue d’ensemble</strong><div class="preview-stats"><span><b>128</b>Prospects</span><span><b>42</b>Clients</span><span><b>18</b>Devis</span></div><div class="preview-chart"><i v-for="height in [38, 62, 48, 75, 58, 88, 72]" :key="height" :style="{ height: `${height}%` }"></i></div></div>
        </section>

        <section id="solutions" class="public-section"><div class="section-heading"><span class="eyebrow">SOLUTIONS</span><h2>Un socle commun, adapté à votre métier</h2><p>Des modules pensés pour organiser l’activité sans multiplier les outils.</p></div><div class="solution-grid"><article v-for="(solution, index) in content.solutions" :key="solution.id"><span>0{{ index + 1 }}</span><h3>{{ solution.title }}</h3><p>{{ solution.short_description }}</p><a href="#contact" @click="form.requested_solution = solution.title">Découvrir →</a></article><p v-if="!content.solutions.length" class="content-loading">Chargement des solutions…</p></div></section>

        <section class="benefits"><div><span class="eyebrow light">POURQUOI AISYSPRO</span><h2>Une seule plateforme pour avancer</h2></div><div class="benefit-list"><article><b>01</b><div><h3>Vue unifiée</h3><p>Les données commerciales et opérationnelles restent accessibles au même endroit.</p></div></article><article><b>02</b><div><h3>Processus maîtrisés</h3><p>Chaque demande est tracée, assignée et suivie jusqu’à sa résolution.</p></div></article><article><b>03</b><div><h3>Évolution progressive</h3><p>Activez les fonctions nécessaires au rythme de votre organisation.</p></div></article></div></section>

        <section id="tarifs" class="public-section soft-section"><div class="section-heading"><span class="eyebrow">PACKS & TARIFS</span><h2>Une formule pour chaque étape</h2><p>Des offres lisibles, avec une configuration ajustée à votre activité.</p></div><div class="pricing-grid"><article v-for="pack in content.packs" :key="pack.id" :class="{ featured: pack.featured }"><span v-if="pack.featured" class="popular">RECOMMANDÉ</span><h3>{{ pack.name }}</h3><p>{{ pack.description }}</p><div class="price"><strong>{{ pack.price ? Number(pack.price).toFixed(0) : 'Sur devis' }}</strong><small v-if="pack.price"> DT / {{ pack.billing_period }}</small></div><ul><li v-for="feature in pack.features" :key="feature">✓ {{ feature }}</li></ul><a href="#contact" class="secondary-button" @click="form.requested_solution = `Pack ${pack.name}`">Choisir ce pack</a></article></div></section>

        <section id="faq" class="public-section faq-section"><div class="section-heading"><span class="eyebrow">FAQ</span><h2>Vos questions, nos réponses</h2></div><div class="faq-list"><article v-for="faq in content.faqs" :key="faq.id"><button type="button" @click="openFaq = openFaq === faq.id ? null : faq.id"><span>{{ faq.question }}</span><b>{{ openFaq === faq.id ? '−' : '+' }}</b></button><p v-if="openFaq === faq.id">{{ faq.answer }}</p></article></div></section>

        <section id="contact" class="contact-section"><div><span class="eyebrow light">PARLONS DE VOTRE PROJET</span><h2>Prêt à simplifier votre activité ?</h2><p>Décrivez votre besoin. Votre demande sera enregistrée dans notre espace de suivi pour une prise en charge structurée.</p><ul><li>✓ Réponse personnalisée</li><li>✓ Démonstration adaptée à votre métier</li><li>✓ Aucun engagement</li></ul></div><form @submit.prevent="submitInquiry"><div class="form-choice"><button type="button" :class="{ active: form.type === 'quote' }" @click="form.type = 'quote'">Demande de devis</button><button type="button" :class="{ active: form.type === 'contact' }" @click="form.type = 'contact'">Contact</button></div><label><span>Nom complet *</span><input v-model="form.name" required maxlength="255"></label><div class="form-row"><label><span>E-mail *</span><input v-model="form.email" type="email" required></label><label><span>Téléphone</span><input v-model="form.phone" maxlength="40"></label></div><label><span>Société</span><input v-model="form.company" maxlength="255"></label><label><span>Solution recherchée</span><select v-model="form.requested_solution"><option value="">À définir ensemble</option><option v-for="solution in content.solutions" :key="solution.id" :value="solution.title">{{ solution.title }}</option></select></label><label><span>Votre besoin *</span><textarea v-model="form.message" required minlength="10" maxlength="5000" rows="5"></textarea></label><label class="honeypot" aria-hidden="true"><span>Site web</span><input v-model="form.website" tabindex="-1" autocomplete="off"></label><p v-if="feedback.message" :class="feedback.type === 'success' ? 'success-message' : 'form-error'">{{ feedback.message }}</p><button class="primary-button" :disabled="sending">{{ sending ? 'Envoi…' : 'Envoyer ma demande' }}</button></form></section>

        <footer class="public-footer"><RouterLink to="/" class="brand"><span class="brand-mark">A</span><span>AISYS<strong>PRO</strong></span></RouterLink><p>Solutions digitales et plateformes métier.</p><small>© {{ new Date().getFullYear() }} AISYSPRO. Tous droits réservés.</small></footer>
    </main>
</template>
