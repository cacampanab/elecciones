<template>
  <div>
    <h2 class="text-h4 mb-4">Registrar Resultados Electorales</h2>
    <v-row>
      <v-col cols="12" md="4">
        <v-card>
          <v-card-title>Mis JRV Asignadas</v-card-title>
          <v-list v-if="myStations.length">
            <v-list-item v-for="s in myStations" :key="s.id" :active="selectedStation?.id === s.id" @click="selectStation(s)">
              <v-list-item-title>JRV {{ s.numero_junta }}</v-list-item-title>
              <v-list-item-subtitle>{{ s.name_precinct }} - {{ s.name_parish }}</v-list-item-subtitle>
              <template #append>
                <v-chip :color="{pendiente:'error',en_proceso:'warning',reportada:'success'}[s.status]" size="x-small">{{ s.status }}</v-chip>
              </template>
            </v-list-item>
          </v-list>
          <v-card-text v-else class="text-center text-medium-emphasis">No tienes JRV asignadas</v-card-text>
        </v-card>
      </v-col>
      <v-col cols="12" md="8">
        <v-card v-if="selectedStation">
          <v-card-title>JRV {{ selectedStation.numero_junta }}</v-card-title>
          <v-card-subtitle>{{ selectedStation.name_province }} / {{ selectedStation.name_canton }} / {{ selectedStation.name_parish }} / {{ selectedStation.name_precinct }}</v-card-subtitle>
          <v-card-text>
            <v-row>
              <v-col cols="6"><v-text-field v-model="resultForm.registered_voters" label="Electores Registrados" type="number" readonly /></v-col>
              <v-col cols="6"><v-text-field v-model="resultForm.total_voters" label="Total Sufragantes" type="number" :rules="[v=>!v||parseInt(v)<=parseInt(resultForm.registered_voters)||'No puede exceder electores registrados']" /></v-col>
            </v-row>
            <v-row>
              <v-col cols="6"><v-text-field v-model="resultForm.blank_votes" label="Votos Blancos" type="number" /></v-col>
              <v-col cols="6"><v-text-field v-model="resultForm.null_votes" label="Votos Nulos" type="number" /></v-col>
            </v-row>
            <v-divider class="my-3" />
            <div class="text-subtitle-1 mb-2">Votos por Candidato</div>
            <v-row v-for="(c, i) in candidates" :key="c.id">
              <v-col cols="8">
                <v-chip :color="c.party_color" size="small" class="mr-2">{{ c.list_number }}</v-chip>
                {{ c.first_name }} {{ c.last_name }} ({{ c.party_acronym }})
              </v-col>
              <v-col cols="4"><v-text-field v-model="voteValues[c.id]" label="Votos" type="number" density="compact" hide-details /></v-col>
            </v-row>
            <v-btn color="primary" block class="mt-4" :loading="saving" @click="saveResults">Guardar Resultados</v-btn>
          </v-card-text>
        </v-card>
        <v-card v-else>
          <v-card-text class="text-center text-medium-emphasis pa-8">Selecciona una JRV para registrar resultados</v-card-text>
        </v-card>
      </v-col>
    </v-row>
  </div>
</template>
<script setup>
import { ref, onMounted, watch, inject } from 'vue'
import api from '@/api'
import { useAppStore } from '@/stores/app'
const snackbar = inject('snackbar')
const appStore = useAppStore()
const myStations = ref([]); const selectedStation = ref(null); const candidates = ref([])
const saving = ref(false)
const resultForm = ref({ registered_voters: 0, total_voters: 0, blank_votes: 0, null_votes: 0 })
const voteValues = ref({})

async function loadMyStations() {
  try {
    const res = await api.getMyStations()
    myStations.value = res.data.data || []
  } catch {}
}

async function selectStation(station) {
  selectedStation.value = station
  resultForm.value = {
    registered_voters: station.numero_electores || 0,
    total_voters: 0, blank_votes: 0, null_votes: 0,
  }
  voteValues.value = {}
  await loadCandidates()
}

async function loadCandidates() {
  if (!appStore.currentElection) return
  const dignities = appStore.dignities
  let allCands = []
  for (const d of dignities) {
    try {
      const res = await api.getCandidatesByDignity(appStore.currentElection.id, d.id)
      allCands = allCands.concat(res.data.data || [])
    } catch {}
  }
  candidates.value = allCands
}

async function saveResults() {
  saving.value = true
  try {
    const { empty, dignity_id } = candidates.value[0] || {}
    for (const c of candidates.value) {
      const votes = parseInt(voteValues.value[c.id] || 0)
      await api.createResult({
        polling_station_id: selectedStation.value.id,
        candidate_id: c.id,
        election_id: selectedStation.value.election_id,
        dignity_id: c.dignity_id,
        votes,
        blank_votes: parseInt(resultForm.value.blank_votes),
        null_votes: parseInt(resultForm.value.null_votes),
        total_voters: parseInt(resultForm.value.total_voters),
        registered_voters: parseInt(resultForm.value.registered_voters),
      })
    }
    snackbar('Resultados registrados exitosamente')
    await loadMyStations()
    selectedStation.value = null
  } catch (e) {
    snackbar(e.response?.data?.message || 'Error al guardar resultados', 'error')
  }
  saving.value = false
}

onMounted(loadMyStations)
</script>
