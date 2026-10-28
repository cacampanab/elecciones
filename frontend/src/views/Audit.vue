<template>
  <div>
    <h2 class="text-h4 mb-4">Auditoría</h2>
    <v-card>
      <v-card-text>
        <v-row>
          <v-col cols="4"><v-text-field v-model="filter.action" label="Acción" clearable /></v-col>
          <v-col cols="4"><v-text-field v-model="filter.user_id" label="ID Usuario" type="number" clearable /></v-col>
        </v-row>
      </v-card-text>
      <v-data-table :headers="headers" :items="audits" :loading="loading">
        <template #item.created_at="{ value }">{{ formatDate(value) }}</template>
      </v-data-table>
      <v-pagination v-if="totalPages > 1" v-model="page" :length="totalPages" class="pa-4" />
    </v-card>
  </div>
</template>
<script setup>
import { ref, onMounted, watch, inject } from 'vue'
import api from '@/api'
const audits = ref([]); const loading = ref(false)
const page = ref(1); const totalPages = ref(1)
const filter = ref({ action: '', user_id: '' })

const headers = [
  { title: 'Usuario', key: 'username' }, { title: 'Acción', key: 'action' },
  { title: 'Tabla', key: 'table_name' }, { title: 'IP', key: 'ip_address' },
  { title: 'Fecha', key: 'created_at' },
]

async function load() {
  loading.value = true
  try {
    const res = await api.getAudits({ page: page.value, ...filter.value })
    audits.value = res.data.data?.data || []
    totalPages.value = res.data.data?.total_pages || 1
  } catch {}
  loading.value = false
}

function formatDate(d) { return d ? new Date(d).toLocaleString('es-EC') : '' }
watch(page, load); watch(filter, () => { page.value = 1; load() }, { deep: true })
onMounted(load)
</script>
