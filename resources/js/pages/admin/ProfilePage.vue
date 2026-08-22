<script setup>
import { reactive, ref } from 'vue';
import api from '../../services/api';

const form = reactive({ current_password: '', password: '', password_confirmation: '' });
const errors = ref({});
const message = ref('');
const loading = ref(false);

async function save() {
    loading.value = true;
    errors.value = {};
    message.value = '';
    try {
        const { data } = await api.put('/profile/password', form);
        message.value = data.message;
        Object.keys(form).forEach((key) => { form[key] = ''; });
    } catch (error) {
        errors.value = error.response?.data?.errors || { form: ['Une erreur est survenue.'] };
    } finally {
        loading.value = false;
    }
}
</script>

<template>
    <section>
        <div class="page-heading"><div><span class="eyebrow">SÉCURITÉ</span><h1>Mon profil</h1><p>Modifiez votre mot de passe d’accès au Back Office.</p></div></div>
        <form class="panel profile-panel" @submit.prevent="save">
            <div class="form-grid">
                <label class="wide"><span>Mot de passe actuel</span><input v-model="form.current_password" type="password" required autocomplete="current-password" /><small v-if="errors.current_password" class="field-error">{{ errors.current_password[0] }}</small></label>
                <label><span>Nouveau mot de passe</span><input v-model="form.password" type="password" required minlength="10" autocomplete="new-password" /><small v-if="errors.password" class="field-error">{{ errors.password[0] }}</small></label>
                <label><span>Confirmation</span><input v-model="form.password_confirmation" type="password" required minlength="10" autocomplete="new-password" /></label>
            </div>
            <p v-if="message" class="success-message">{{ message }}</p><p v-if="errors.form" class="form-error">{{ errors.form[0] }}</p>
            <button class="primary-button" :disabled="loading">{{ loading ? 'Enregistrement…' : 'Modifier le mot de passe' }}</button>
        </form>
    </section>
</template>
