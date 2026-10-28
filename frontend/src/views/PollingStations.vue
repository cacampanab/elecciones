<template>
  <div>
    <div class="d-flex justify-space-between align-center mb-4">
      <h2 class="text-h4">Juntas Receptoras del Voto</h2>
      <div>
        <v-dialog v-model="importDialog" max-width="500">
          <template #activator="{ props }"><v-btn color="secondary" prepend-icon="mdi-file-excel" v-bind="props" class="mr-2">Importar Excel</v-btn></template>
          <v-card>
            <v-card-title>Importar JRV desde Excel</v-card-title>
            <v-card-text>
              <v-select v-model="importForm.election_id" :items="elections" item-title="name" item-value="id" label="Elección" :rules="[v=>!!v||'Requerido']" />
              <v-file-input v-model="importForm.file" label="Archivo Excel" accept=".xlsx,.xls" />
            </v-card-text>
            <v-card-actions>
              <v-spacer /><v-btn variant="text" @click="importDialog=false">Cancelar</v-btn><v-btn color="primary" @click="importFile" :loading="importing">Importar</v-btn>
            </v-card-actions>
          </v-card>
        </v-dialog>
      </div>
    </div>
    <v-card>
      <v-data-table :headers="headers" :items="stations" :loading="loading">
        <template #item.status="{ value }">
          <v-chip :color="{pendiente:'error',en_proceso:'warning',reportada:'success',revisada:'info',aprobada:'primary'}[value]" size="small">{{ value }}</v-chip>
        </template>
      </v-data-table>
    </v-card>
  </div>
</template>
<script setup>
import { ref, onMounted, inject } from 'vue'
import api from '@/api'
const snackbar = inject('snackbar')
const stations = ref([]); const elections = ref([])
const loading = ref(false); const importing = ref(false)
const importDialog = ref(false); const importForm = ref({ election_id: '', file: null })
const headers = [
  { title: 'Código', key: 'numero_junta' }, { title: 'Provincia', key: 'name_province' },
  { title: 'Cantón', key: 'name_canton' }, { title: 'Parroquia', key: 'name_parish' },
  { title: 'Recinto', key: 'name_precinct' }, { title: 'Electores', key: 'numero_electores' },
  { title: 'Estado', key: 'status' },
]

async function load() {
  loading.value = true
  try { const [s, e] = await Promise.all([api.getPollingStations(), api.getElections()]); stations.value = s.data.data || []; elections.value = e.data.data || [] } catch {}
  loading.value = false
}

async function importFile() {
  importing.value = true
  try {
    const fd = new FormData()
    fd.append('election_id', importForm.value.election_id)
    fd.append('file', importForm.value.file)
    await api.importPollingStations(fd)
    snackbar('JRV importadas correctamente')
    importDialog.value = false; await load()
  } catch { snackbar('Error al importar', 'error') }
  importing.value = false
}

onMounted(load)
</script>
