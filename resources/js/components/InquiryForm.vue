<script setup>
import { reactive, ref } from 'vue';
import api from '../services/api';

const props = defineProps({ type: { type: String, default: 'contact' } });
const makeSubmissionUuid = () => globalThis.crypto?.randomUUID?.() || 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, (character) => {
    const random = Math.floor(Math.random() * 16);
    return (character === 'x' ? random : (random & 0x3) | 0x8).toString(16);
});
const form = reactive({ type: props.type, firstName: '', lastName: '', email: '', phone: '', company: '', subject: '', message: '', consent: false, website: '', submission_uuid: makeSubmissionUuid() });
const sending = ref(false);
const feedback = ref({ type: '', message: '' });

async function submit() {
    sending.value = true;
    feedback.value = { type: '', message: '' };
    try {
        const payload = { ...form, name: `${form.firstName} ${form.lastName}`.trim(), source_url: window.location.href };
        delete payload.firstName;
        delete payload.lastName;
        const { data } = await api.post('/public/inquiries', payload);
        feedback.value = { type: 'success', message: data.message };
        Object.assign(form, { type: props.type, firstName: '', lastName: '', email: '', phone: '', company: '', subject: '', message: '', consent: false, website: '', submission_uuid: makeSubmissionUuid() });
    } catch (error) {
        const errors = error.response?.data?.errors;
        feedback.value = { type: 'error', message: errors ? Object.values(errors)[0][0] : 'Envoi impossible. Veuillez réessayer.' };
    } finally {
        sending.value = false;
    }
}
</script>

<template>
    <form class="inquiry-form" @submit.prevent="submit">
        <div class="form-row"><label><span>Prénom *</span><input v-model="form.firstName" required maxlength="120" placeholder="Votre prénom" autocomplete="given-name"></label><label><span>Nom *</span><input v-model="form.lastName" required maxlength="120" placeholder="Votre nom" autocomplete="family-name"></label></div>
        <label><span>Entreprise</span><input v-model="form.company" maxlength="255" placeholder="Nom de votre société" autocomplete="organization"></label>
        <div class="form-row"><label><span>Email professionnel *</span><input v-model="form.email" type="email" required placeholder="vous@entreprise.tn" autocomplete="email"></label><label><span>Téléphone *</span><input v-model="form.phone" required maxlength="40" placeholder="+216 ..." autocomplete="tel"></label></div>
        <label><span>Objet de la demande *</span><select v-model="form.subject" required><option value="">Choisir un sujet</option><option>CRM / application métier</option><option>Site web professionnel</option><option>Hébergement & domaine</option><option>Support / maintenance</option><option>Partenariat</option><option>Autre demande</option></select></label>
        <label><span>Votre message *</span><textarea v-model="form.message" required minlength="10" maxlength="5000" rows="6" placeholder="Décrivez votre besoin, vos priorités et vos délais..."></textarea></label>
        <label class="consent"><input v-model="form.consent" type="checkbox" required><span>J’accepte que mes informations soient utilisées pour répondre à cette demande.</span></label>
        <label class="honeypot" aria-hidden="true"><span>Site web</span><input v-model="form.website" tabindex="-1" autocomplete="off"></label>
        <p v-if="feedback.message" :class="feedback.type === 'success' ? 'success-message' : 'form-error'">{{ feedback.message }}</p>
        <button class="site-cta form-submit" :disabled="sending">{{ sending ? 'Envoi…' : 'Envoyer mon message ↗' }}</button>
    </form>
</template>
