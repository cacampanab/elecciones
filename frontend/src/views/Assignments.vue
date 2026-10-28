<template>
  <div>
    <div class="d-flex justify-space-between align-center mb-4">
      <h2 class="text-h4">Asignaciones</h2>
      <v-dialog v-model="dialog" max-width="600">
        <template #activator="{ props }"><v-btn color="primary" prepend-icon="mdi-plus" v-bind="props">Nueva Asignación</v-btn></template>
        <v-card>
          <v-card-title>Asignar Operador a JRV</v-card-title>
          <v-card-text>
            <v-select v-model="form.user_id" :items="operators" item-title="text" item-value="id" label="Operador" :rules="[v=>!!v||'Requerido']" />
            <v-select v-model="form.polling_station_id" :items="stations" item-title="text" item-value="id" label="JRV" :rules="[v=>!!v||'Requerido']" />
            <v-select v-model="form.election_id" :items="elections" item-title="name" item-value="id" label="Elección" :rules="[v=>!!v||'Requerido']" />
            <v-select v-model="form.assignment_type" :items="['operador','coordinador','supervisor']" label="Tipo" />
          </v-card-text>
          <v-card-actions>
            <v-spacer /><v-btn variant="text" @click="dialog=false">Cancelar</v-btn><v-btn color="primary" :loading="loading" @click="save">Guardar</v-btn>
          </v-card-actions>
        </v-card>
      </v-dialog>
    </div>
    <v-card>
      <v-data-table :headers="headers" :items="assignments" :loading="loading">
        <template #item.actions="{ item }">
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
const assignments = ref([]); const operators = ref([]); const stations = ref([]); const elections = ref([])
const dialog = ref(false); const loading = ref(false); const form = ref({})

const headers = [
  { title: 'Operador', key: 'first_name' }, { title: 'JRV', key: 'numero_junta' },
  { title: 'Provincia', key: 'name_province' }, { title: 'Tipo', key: 'assignment_type' },
  { title: 'Acciones', key: 'actions', sortable: false },
]

async function load() {
  loading.value = true
  try {
    const [a, o, s, e] = await Promise.all([
      api.getAssignments(), api.getOperators(), api.getPollingStations(), api.getElections()
    ])
    assignments.value = a.data.data || []
    operators.value = (o.data.data || []).map(u => ({ id: u.id, text: `${u.first_name} ${u.last_name} - ${u.cedula}` }))
    stations.value = (s.data.data || []).map(ps => ({ id: ps.id, text: `JRV ${ps.numero_junta} - ${ps.name_precinct}` }))
    elections.value = e.data.data || []
  } catch {}
  loading.value = false
}

async function save() {
  try { await api.createAssignment(form.value); snackbar('Asignación creada'); dialog.value = false; await load() } catch { snackbar('Error', 'error') }
}

async function remove(a) { if (!confirm('¿Eliminar asignación?')) return; try { await api.deleteAssignment(a.id); snackbar('Eliminada'); await load() } catch {} }

onMounted(load)
</script>
