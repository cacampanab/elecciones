<template>
  <div>
    <div class="d-flex justify-space-between align-center mb-4">
      <h2 class="text-h4">Elecciones</h2>
      <v-dialog v-model="dialog" max-width="600">
        <template #activator="{ props }"><v-btn color="primary" prepend-icon="mdi-plus" v-bind="props">Nueva Elección</v-btn></template>
        <v-card>
          <v-card-title>{{ editing ? 'Editar' : 'Nueva' }} Elección</v-card-title>
          <v-card-text>
            <v-text-field v-model="form.name" label="Nombre" :rules="[v=>!!v||'Requerido']" />
            <v-textarea v-model="form.description" label="Descripción" />
            <v-text-field v-model="form.election_date" label="Fecha" type="date" :rules="[v=>!!v||'Requerido']" />
            <v-select v-model="form.election_type" :items="['nacional','provincial','cantonal','parroquial']" label="Tipo" />
            <v-select v-model="form.status" :items="['pendiente','en_curso','finalizada','anulada']" label="Estado" />
          </v-card-text>
          <v-card-actions>
            <v-spacer /><v-btn variant="text" @click="dialog=false">Cancelar</v-btn><v-btn color="primary" @click="save">Guardar</v-btn>
          </v-card-actions>
        </v-card>
      </v-dialog>
    </div>
    <v-card>
      <v-data-table :headers="headers" :items="elections" :loading="loading">
        <template #item.status="{ value }">
          <v-chip :color="{pendiente:'error',en_curso:'warning',finalizada:'success',anulada:'grey'}[value]" size="small">{{ value }}</v-chip>
        </template>
        <template #item.actions="{ item }">
          <v-icon size="small" class="mr-2" @click="edit(item)">mdi-pencil</v-icon>
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
const elections = ref([])
const dialog = ref(false)
const editing = ref(false)
const loading = ref(false)
const form = ref({})

const headers = [
  { title: 'Nombre', key: 'name' }, { title: 'Fecha', key: 'election_date' },
  { title: 'Tipo', key: 'election_type' }, { title: 'Estado', key: 'status' },
  { title: 'Acciones', key: 'actions', sortable: false },
]

async function load() {
  loading.value = true
  try { elections.value = (await api.getElections()).data.data || [] } catch {}
  loading.value = false
}

function edit(e) { form.value = { ...e }; editing.value = true; dialog.value = true }

async function save() {
  try {
    editing.value ? await api.updateElection(form.value.id, form.value) : await api.createElection(form.value)
    snackbar(editing.value ? 'Elección actualizada' : 'Elección creada')
    dialog.value = false; await load()
  } catch { snackbar('Error al guardar', 'error') }
}

async function remove(e) {
  if (!confirm('¿Eliminar elección?')) return
  try { await api.deleteElection(e.id); snackbar('Elección eliminada'); await load() } catch {}
}

onMounted(load)
</script>
