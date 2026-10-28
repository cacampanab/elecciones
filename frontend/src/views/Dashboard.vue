<template>
  <div>
    <h2 class="text-h4 mb-4">Dashboard Ejecutivo</h2>
    <v-row>
      <v-col cols="12" md="3" v-for="card in statCards" :key="card.title">
        <v-card>
          <v-card-text class="pa-4">
            <div class="d-flex align-center">
              <v-avatar :color="card.color" size="56" class="elevation-2">
                <v-icon size="28" color="white">{{ card.icon }}</v-icon>
              </v-avatar>
              <div class="ml-4">
                <div class="text-h4 font-weight-bold">{{ card.value }}</div>
                <div class="text-body-2 text-medium-emphasis">{{ card.title }}</div>
              </div>
            </div>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>

    <v-row class="mt-2">
      <v-col cols="12" md="8">
        <v-card>
          <v-card-title>Avance por Provincia</v-card-title>
          <v-card-text>
            <v-table density="compact">
              <thead>
                <tr>
                  <th>Provincia</th>
                  <th>Total JRV</th>
                  <th>Reportadas</th>
                  <th>% Avance</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="p in provinceStats" :key="p.code_province">
                  <td>{{ p.name_province }}</td>
                  <td>{{ p.total }}</td>
                  <td>{{ p.reportadas }}</td>
                  <td>
                    <v-progress-linear :model-value="p.avance" :color="p.avance >= 80 ? 'success' : p.avance >= 50 ? 'warning' : 'error'" height="20" rounded>
                      <template #default>{{ p.avance }}%</template>
                    </v-progress-linear>
                  </td>
                </tr>
              </tbody>
            </v-table>
          </v-card-text>
        </v-card>
      </v-col>
      <v-col cols="12" md="4">
        <v-card>
          <v-card-title>Estado JRV</v-card-title>
          <v-card-text>
            <div v-for="est in dashboardData.jrv_por_estado || []" :key="est.status" class="d-flex align-center mb-3">
              <v-chip :color="statusColor(est.status)" size="small" class="mr-2">{{ est.status }}</v-chip>
              <span class="text-h6">{{ est.total }}</span>
            </div>
          </v-card-text>
        </v-card>
        <v-card class="mt-4">
          <v-card-title>Participación</v-card-title>
          <v-card-text class="text-center">
            <div class="text-h2 font-weight-bold text-primary">{{ dashboardData.participacion || 0 }}%</div>
            <div class="text-body-2">Electores: {{ formatNumber(dashboardData.total_electores) }} | Votantes: {{ formatNumber(dashboardData.total_votantes) }}</div>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '@/api'

const dashboardData = ref({})
const provinceStats = ref([])

const statCards = ref([
  { title: 'Total JRV', value: 0, icon: 'mdi-domain', color: 'primary' },
  { title: 'Reportadas', value: 0, icon: 'mdi-check-circle', color: 'success' },
  { title: 'Pendientes', value: 0, icon: 'mdi-clock-outline', color: 'error' },
  { title: 'Avance', value: '0%', icon: 'mdi-percent', color: 'info' },
])

async function loadData() {
  try {
    const res = await api.getDashboard()
    dashboardData.value = res.data.data || {}
    statCards.value[0].value = dashboardData.value.total_jrv || 0
    statCards.value[1].value = dashboardData.value.jrv_reportadas || 0
    statCards.value[2].value = dashboardData.value.jrv_pendientes || 0
    statCards.value[3].value = dashboardData.value.porcentaje_avance + '%' || '0%'

    const res2 = await api.getDashboardByProvince()
    provinceStats.value = res2.data.data || []
  } catch {}
}

function statusColor(status) {
  const colors = { pendiente: 'error', en_proceso: 'warning', reportada: 'success', revisada: 'info', aprobada: 'primary' }
  return colors[status] || 'grey'
}

function formatNumber(n) {
  return new Intl.NumberFormat('es-EC').format(n || 0)
}

onMounted(loadData)
</script>
