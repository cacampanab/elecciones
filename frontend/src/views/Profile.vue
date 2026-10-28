<template>
  <div>
    <h2 class="text-h4 mb-4">Mi Perfil</h2>
    <v-row>
      <v-col cols="12" md="6">
        <v-card>
          <v-card-title>Información Personal</v-card-title>
          <v-card-text>
            <v-text-field v-model="form.first_name" label="Nombres" />
            <v-text-field v-model="form.last_name" label="Apellidos" />
            <v-text-field v-model="form.email" label="Email" disabled />
            <v-text-field v-model="form.phone" label="Teléfono" />
            <v-btn color="primary" @click="updateProfile" :loading="loading">Actualizar Perfil</v-btn>
          </v-card-text>
        </v-card>
      </v-col>
      <v-col cols="12" md="6">
        <v-card>
          <v-card-title>Cambiar Contraseña</v-card-title>
          <v-card-text>
            <v-text-field v-model="passwordForm.current" label="Contraseña Actual" type="password" />
            <v-text-field v-model="passwordForm.newPass" label="Nueva Contraseña" type="password" :rules="[v=>!v||v.length>=8||'Mínimo 8 caracteres']" />
            <v-btn color="primary" @click="changePassword" :loading="loading">Cambiar Contraseña</v-btn>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>
  </div>
</template>
<script setup>
import { ref, onMounted, inject } from 'vue'
import { useAuthStore } from '@/stores/auth'
import api from '@/api'
const snackbar = inject('snackbar')
const authStore = useAuthStore()
const loading = ref(false)
const form = ref({ first_name: '', last_name: '', email: '', phone: '' })
const passwordForm = ref({ current: '', newPass: '' })

onMounted(() => {
  if (authStore.user) {
    form.value = { first_name: authStore.user.first_name, last_name: authStore.user.last_name, email: authStore.user.email, phone: authStore.user.phone || '' }
  }
})

async function updateProfile() {
  loading.value = true
  try { await api.updateProfile(form.value); snackbar('Perfil actualizado'); await authStore.fetchUser() } catch { snackbar('Error', 'error') }
  loading.value = false
}

async function changePassword() {
  if (passwordForm.value.newPass.length < 8) return
  loading.value = true
  try { await api.updateProfile(passwordForm.value); snackbar('Contraseña cambiada'); passwordForm.value = { current: '', newPass: '' } } catch { snackbar('Error', 'error') }
  loading.value = false
}
</script>
