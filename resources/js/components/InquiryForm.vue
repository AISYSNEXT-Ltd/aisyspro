<script setup>
import { reactive, ref } from 'vue';
import api from '../services/api';

const props = defineProps({ type: { type: String, default: 'contact' }, solutions: { type: Array, default: () => [] } });
const form = reactive({ type: props.type, name: '', email: '', phone: '', company: '', subject: '', requested_solution: '', message: '', website: '' });
const sending = ref(false);
const feedback = ref({ type: '', message: '' });

async function submit() {
    sending.value = true;
    feedback.value = { type: '', message: '' };
    try {
        const { data } = await api.post('/public/inquiries', form);
        feedback.value = { type: 'success', message: data.message };
        Object.assign(form, { type: props.type, name: '', email: '', phone: '', company: '', subject: '', requested_solution: '', message: '', website: '' });
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
        <div class="form-row"><label><span>Nom complet *</span><input v-model="form.name" required maxlength="255"></label><label><span>Entreprise</span><input v-model="form.company" maxlength="255"></label></div>
        <div class="form-row"><label><span>Email professionnel *</span><input v-model="form.email" type="email" required></label><label><span>Téléphone</span><input v-model="form.phone" maxlength="40"></label></div>
        <label><span>Objet de la demande</span><select v-model="form.subject"><option value="">Choisir un sujet</option><option>CRM / application métier</option><option>Site web professionnel</option><option>Hébergement & domaine</option><option>Support / maintenance</option><option>Partenariat</option><option>Autre demande</option></select></label>
        <label v-if="solutions.length"><span>Solution métier</span><select v-model="form.requested_solution"><option value="">À définir ensemble</option><option v-for="solution in solutions" :key="solution.id" :value="solution.title">{{ solution.title }}</option></select></label>
        <label><span>Votre message *</span><textarea v-model="form.message" required minlength="10" maxlength="5000" rows="6"></textarea></label>
        <label class="consent"><input type="checkbox" required><span>J’accepte que mes informations soient utilisées pour répondre à cette demande.</span></label>
        <label class="honeypot" aria-hidden="true"><span>Site web</span><input v-model="form.website" tabindex="-1" autocomplete="off"></label>
        <p v-if="feedback.message" :class="feedback.type === 'success' ? 'success-message' : 'form-error'">{{ feedback.message }}</p>
        <button class="site-cta form-submit" :disabled="sending">{{ sending ? 'Envoi…' : 'Envoyer mon message ↗' }}</button>
    </form>
</template>
