<template>
  <div>
    <div class="d-flex justify-space-between align-center mb-4">
      <h2 class="text-h4">Actas Electorales</h2>
      <v-dialog v-model="dialog" max-width="500">
        <template #activator="{ props }"><v-btn color="primary" prepend-icon="mdi-upload" v-bind="props">Subir Acta</v-btn></template>
        <v-card>
          <v-card-title>Cargar Acta</v-card-title>
          <v-card-text>
            <v-select v-model="form.election_id" :items="elections" item-title="name" item-value="id" label="Elección" :rules="[v=>!!v||'Requerido']" />
            <v-select v-model="form.polling_station_id" :items="myStations" item-title="text" item-value="id" label="JRV" :rules="[v=>!!v||'Requerido']" />
            <v-file-input v-model="form.file" label="Acta (PDF, JPG, PNG)" accept=".pdf,.jpg,.jpeg,.png" :rules="[v=>!!v||'Requerido']" />
          </v-card-text>
          <v-card-actions>
            <v-spacer /><v-btn variant="text" @click="dialog=false">Cancelar</v-btn><v-btn color="primary" :loading="uploading" @click="upload">Subir</v-btn>
          </v-card-actions>
        </v-card>
      </v-dialog>
    </div>
    <v-card>
      <v-data-table :headers="headers" :items="minutes" :loading="loading">
        <template #item.file_type="{ value }">
          <v-chip size="small">{{ value }}</v-chip>
        </template>
        <template #item.actions="{ item }">
          <v-icon size="small" class="mr-2" @click="preview(item)">mdi-eye</v-icon>
          <v-icon size="small" class="mr-2" @click="download(item)">mdi-download</v-icon>
          <v-icon size="small" color="error" @click="remove(item)">mdi-delete</v-icon>
        </template>
      </v-data-table>
    </v-card>
  </div>
</template>
<script setup>
import { ref, onMounted, inject } from 'vue'
import api from '@/api'
const snackbar = inject('snackbar')
const minutes = ref([]); const elections = ref([]); const myStations = ref([])
const dialog = ref(false); const loading = ref(false); const uploading = ref(false)
const form = ref({ election_id: '', polling_station_id: '', file: null })

const headers = [
  { title: 'JRV', key: 'numero_junta' }, { title: 'Archivo', key: 'original_name' },
  { title: 'Tipo', key: 'file_type' }, { title: 'Versión', key: 'version' },
  { title: 'Subido por', key: 'first_name' }, { title: 'Acciones', key: 'actions', sortable: false },
]

async function load() {
  loading.value = true
  try {
    const [m, e, s] = await Promise.all([api.getMinutes(), api.getElections(), api.getMyStations()])
    minutes.value = m.data.data || []; elections.value = e.data.data || []
    myStations.value = (s.data.data || []).map(ps => ({ id: ps.id, text: `JRV ${ps.numero_junta} - ${ps.name_precinct}` }))
  } catch {}
  loading.value = false
}

async function upload() {
  uploading.value = true
  try {
    const fd = new FormData()
    fd.append('election_id', form.value.election_id)
    fd.append('polling_station_id', form.value.polling_station_id)
    fd.append('file', form.value.file)
    await api.uploadMinute(fd)
    snackbar('Acta subida correctamente'); dialog.value = false; await load()
  } catch { snackbar('Error al subir', 'error') }
  uploading.value = false
}

async function preview(m) { window.open(`/api/minutes/${m.id}/preview`, '_blank') }
async function download(m) { try { const r = await api.downloadMinute(m.id); const url = URL.createObjectURL(r.data); window.open(url) } catch {} }
async function remove(m) { if (!confirm('¿Eliminar acta?')) return; try { await api.deleteMinute(m.id); snackbar('Eliminada'); await load() } catch {} }

onMounted(load)
</script>
