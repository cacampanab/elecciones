<template>
  <div>
    <h2 class="text-h4 mb-4">Reportes</h2>
    <v-row>
      <v-col cols="12" md="6">
        <v-card>
          <v-card-title>Exportar Resultados</v-card-title>
          <v-card-text>
            <v-select v-model="reportForm.election_id" :items="elections" item-title="name" item-value="id" label="Elección" clearable />
            <v-select v-model="reportForm.dignity_id" :items="dignities" item-title="name" item-value="id" label="Dignidad" clearable />
            <v-row class="mt-2">
              <v-col cols="6"><v-btn color="primary" prepend-icon="mdi-file-pdf-box" block @click="exportPdf">PDF</v-btn></v-col>
              <v-col cols="6"><v-btn color="success" prepend-icon="mdi-file-excel" block @click="exportExcel">Excel</v-btn></v-col>
            </v-row>
          </v-card-text>
        </v-card>
      </v-col>
      <v-col cols="12" md="6">
        <v-card>
          <v-card-title>Avance de Carga</v-card-title>
          <v-card-text>
            <v-btn color="info" prepend-icon="mdi-file-pdf-box" block @click="exportAdvance">Exportar Avance PDF</v-btn>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>
  </div>
</template>
<script setup>
import { ref, onMounted } from 'vue'
import api from '@/api'
const elections = ref([]); const dignities = ref([])
const reportForm = ref({ election_id: '', dignity_id: '' })

async function load() {
  try { const [e, d] = await Promise.all([api.getElections(), api.getDignities()]); elections.value = e.data.data || []; dignities.value = d.data.data || [] } catch {}
}

function exportPdf() {
  const params = new URLSearchParams(reportForm.value).toString()
  window.open(`/api/reports/export-pdf?${params}`, '_blank')
}

function exportExcel() {
  const params = new URLSearchParams(reportForm.value).toString()
  window.open(`/api/reports/export-excel?${params}`, '_blank')
}

function exportAdvance() {
  window.open(`/api/reports/advance`, '_blank')
}

onMounted(load)
</script>
