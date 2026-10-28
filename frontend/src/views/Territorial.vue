<template>
  <div>
    <h2 class="text-h4 mb-4">División Territorial</h2>
    <v-row>
      <v-col cols="12" md="6">
        <v-card>
          <v-card-title>Provincias</v-card-title>
          <v-card-text>
            <v-text-field v-model="provinceForm.name" label="Nombre" hide-details class="mb-2" />
            <v-text-field v-model="provinceForm.code" label="Código" hide-details class="mb-2" />
            <v-btn color="primary" size="small" @click="saveProvince" :loading="loading">Agregar</v-btn>
          </v-card-text>
          <v-list>
            <v-list-item v-for="p in provinces" :key="p.id">
              <template #prepend><v-icon>mdi-map-marker</v-icon></template>
              <v-list-item-title>{{ p.name }} ({{ p.code }})</v-list-item-title>
              <template #append><v-icon size="small" color="error" @click="deleteProvince(p.id)">mdi-delete</v-icon></template>
            </v-list-item>
          </v-list>
        </v-card>
      </v-col>
      <v-col cols="12" md="6">
        <v-card>
          <v-card-title>Gestión de Cantones</v-card-title>
          <v-card-text>
            <v-select v-model="selectedProvince" :items="provinces" item-title="name" item-value="id" label="Provincia" />
            <v-text-field v-model="cantonForm.name" label="Nombre" hide-details class="mb-2" />
            <v-text-field v-model="cantonForm.code" label="Código" hide-details class="mb-2" />
            <v-btn color="primary" size="small" @click="saveCanton" :loading="loading">Agregar</v-btn>
          </v-card-text>
          <v-list v-if="selectedProvince">
            <v-list-item v-for="c in cantons" :key="c.id">
              <v-list-item-title>{{ c.name }}</v-list-item-title>
              <template #append><v-icon size="small" color="error" @click="deleteCanton(c.id)">mdi-delete</v-icon></template>
            </v-list-item>
          </v-list>
        </v-card>
      </v-col>
    </v-row>
  </div>
</template>
<script setup>
import { ref, onMounted, watch, inject } from 'vue'
import api from '@/api'
const snackbar = inject('snackbar')
const provinces = ref([]); const cantons = ref([]); const selectedProvince = ref(null)
const loading = ref(false)
const provinceForm = ref({ name: '', code: '' })
const cantonForm = ref({ name: '', code: '' })

async function loadProvinces() { try { provinces.value = (await api.getProvinces()).data.data || [] } catch {} }
async function loadCantons() { if (!selectedProvince.value) return; try { cantons.value = (await api.getCantons({ province_id: selectedProvince.value })).data.data || [] } catch {} }
watch(selectedProvince, loadCantons)

async function saveProvince() {
  try { await api.createProvince(provinceForm.value); snackbar('Provincia creada'); provinceForm.value = { name: '', code: '' }; await loadProvinces() } catch { snackbar('Error', 'error') }
}
async function deleteProvince(id) { try { await api.deleteProvince(id); snackbar('Eliminada'); await loadProvinces() } catch {} }
async function saveCanton() {
  if (!selectedProvince.value) return
  try { await api.createCanton({ ...cantonForm.value, province_id: selectedProvince.value }); snackbar('Cantón creado'); cantonForm.value = { name: '', code: '' }; await loadCantons() } catch { snackbar('Error', 'error') }
}
async function deleteCanton(id) { try { await api.deleteCanton(id); snackbar('Eliminado'); await loadCantons() } catch {} }

onMounted(loadProvinces)
</script>
