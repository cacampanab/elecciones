<template>
  <div>
    <h2 class="text-h4 mb-4">Resultados Electorales</h2>
    <v-card class="mb-4">
      <v-card-text>
        <v-row>
          <v-col cols="4">
            <v-select v-model="filterElection" :items="elections" item-title="name" item-value="id" label="Elección" clearable />
          </v-col>
          <v-col cols="4">
            <v-select v-model="filterDignity" :items="dignities" item-title="name" item-value="id" label="Dignidad" clearable />
          </v-col>
        </v-row>
      </v-card-text>
    </v-card>
    <v-card>
      <v-data-table :headers="headers" :items="results" :loading="loading">
        <template #item.total_votes="{ value }">
          <strong>{{ formatNumber(value) }}</strong>
        </template>
      </v-data-table>
    </v-card>
  </div>
</template>
<script setup>
import { ref, onMounted, watch, inject } from 'vue'
import api from '@/api'
const snackbar = inject('snackbar')
const results = ref([]); const elections = ref([]); const dignities = ref([])
const filterElection = ref(null); const filterDignity = ref(null)
const loading = ref(false)

const headers = [
  { title: 'Candidato', key: 'first_name' }, { title: 'Partido', key: 'party_name' },
  { title: 'Votos', key: 'total_votes' },
]

async function loadFilters() {
  try { const [e, d] = await Promise.all([api.getElections(), api.getDignities()]); elections.value = e.data.data || []; dignities.value = d.data.data || [] } catch {}
}

watch([filterElection, filterDignity], async ([eid, did]) => {
  if (eid && did) {
    loading.value = true
    try { results.value = (await api.getResultsByDignity(eid, did)).data.data || [] } catch {}
    loading.value = false
  }
})

function formatNumber(n) { return new Intl.NumberFormat('es-EC').format(n || 0) }
onMounted(loadFilters)
</script>
