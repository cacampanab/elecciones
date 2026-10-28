<template>
  <v-container class="fill-height d-flex align-center justify-center" style="background: linear-gradient(135deg, #1a237e 0%, #0d47a1 50%, #1565c0 100%);">
    <v-card class="pa-8" max-width="420" width="100%" :elevation="12">
      <v-card-title class="text-center pa-0 mb-6">
        <v-icon size="60" color="primary" class="mb-2">mdi-ballot-outline</v-icon>
        <div class="text-h5 font-weight-bold">Sistema de Control Electoral</div>
        <div class="text-body-2 text-medium-emphasis mt-1">Iniciar Sesión</div>
      </v-card-title>
      <v-card-text>
        <v-alert v-if="error" type="error" closable class="mb-4" density="compact">{{ error }}</v-alert>
        <v-form @submit.prevent="handleLogin">
          <v-text-field v-model="form.username" label="Usuario" prepend-inner-icon="mdi-account" :rules="[v => !!v || 'Usuario requerido']" />
          <v-text-field v-model="form.password" label="Contraseña" prepend-inner-icon="mdi-lock" type="password" :rules="[v => !!v || 'Contraseña requerida']" />
          <v-btn type="submit" color="primary" size="large" block :loading="loading" class="mt-4">Ingresar</v-btn>
        </v-form>
      </v-card-text>
    </v-card>
  </v-container>
</template>

<script setup>
import { ref, inject } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const authStore = useAuthStore()
const router = useRouter()
const snackbar = inject('snackbar')

const form = ref({ username: '', password: '' })
const loading = ref(false)
const error = ref('')

async function handleLogin() {
  error.value = ''
  loading.value = true
  try {
    await authStore.login(form.value)
    snackbar('Inicio de sesión exitoso')
    router.push('/')
  } catch (e) {
    error.value = e.response?.data?.message || 'Error al iniciar sesión'
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.fill-height { min-height: 100vh; }
</style>
