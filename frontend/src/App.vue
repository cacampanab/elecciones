<template>
  <v-app>
    <router-view />
    <v-snackbar v-model="snackbar.show" :color="snackbar.color" :timeout="3000">
      {{ snackbar.message }}
      <template #actions>
        <v-btn variant="text" @click="snackbar.show = false">Cerrar</v-btn>
      </template>
    </v-snackbar>
  </v-app>
</template>

<script setup>
import { ref, provide } from 'vue'

const snackbar = ref({ show: false, message: '', color: 'success' })

function showSnackbar(message, color = 'success') {
  snackbar.value = { show: true, message, color }
}

provide('snackbar', showSnackbar)
</script>
