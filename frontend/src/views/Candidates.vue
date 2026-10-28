<template>
  <div>
    <div class="d-flex justify-space-between align-center mb-4">
      <h2 class="text-h4">Candidatos</h2>
      <v-dialog v-model="dialog" max-width="700">
        <template #activator="{ props }"><v-btn color="primary" prepend-icon="mdi-plus" v-bind="props">Nuevo Candidato</v-btn></template>
        <v-card>
          <v-card-title>{{ editing ? 'Editar' : 'Nuevo' }} Candidato</v-card-title>
          <v-card-text>
            <v-row>
              <v-col cols="6"><v-text-field v-model="form.first_name" label="Nombres" :rules="[v=>!!v||'Requerido']" /></v-col>
              <v-col cols="6"><v-text-field v-model="form.last_name" label="Apellidos" :rules="[v=>!!v||'Requerido']" /></v-col>
              <v-col cols="6"><v-text-field v-model="form.cedula" label="Cédula" :rules="[v=>!!v||'Requerido']" /></v-col>
              <v-col cols="6">
                <v-select v-model="form.election_id" :items="elections" item-title="name" item-value="id" label="Elección" :rules="[v=>!!v||'Requerido']" />
              </v-col>
              <v-col cols="6">
                <v-select v-model="form.dignity_id" :items="dignities" item-title="name" item-value="id" label="Dignidad" :rules="[v=>!!v||'Requerido']" />
              </v-col>
              <v-col cols="6">
                <v-select v-model="form.political_party_id" :items="parties" item-title="name" item-value="id" label="Partido" :rules="[v=>!!v||'Requerido']" />
              </v-col>
              <v-col cols="6">
                <v-select v-model="form.candidate_list_id" :items="lists" item-title="name" item-value="id" label="Lista" />
              </v-col>
            </v-row>
          </v-card-text>
          <v-card-actions>
            <v-spacer /><v-btn variant="text" @click="dialog=false">Cancelar</v-btn><v-btn color="primary" @click="save">Guardar</v-btn>
          </v-card-actions>
        </v-card>
      </v-dialog>
    </div>
    <v-card>
      <v-data-table :headers="headers" :items="candidates" :loading="loading">
        <template #item.actions="{ item }">
          <v-icon size="small" class="mr-2" @click="edit(item)">mdi-pencil</v-icon>
          <v-icon size="small" color="error" @click="remove(item)">mdi-delete</v-icon>
        </template>
      </v-data-table>
    </v-card>
  </div>
</template>
<script setup>
import { ref, onMounted, inject, watch } from 'vue'
import api from '@/api'
const snackbar = inject('snackbar')
const candidates = ref([]); const elections = ref([]); const dignities = ref([]); const parties = ref([]); const lists = ref([])
const dialog = ref(false); const editing = ref(false); const loading = ref(false); const form = ref({})
const headers = [
  { title: 'Nombres', key: 'first_name' }, { title: 'Apellidos', key: 'last_name' }, { title: 'Cédula', key: 'cedula' },
  { title: 'Partido', key: 'party_name' }, { title: 'Lista', key: 'list_number' }, { title: 'Acciones', key: 'actions', sortable: false },
]

async function load() {
  loading.value = true
  try {
    const [c, e, d, p] = await Promise.all([api.getCandidates(), api.getElections(), api.getDignities(), api.getParties()])
    candidates.value = c.data.data || []; elections.value = e.data.data || []; dignities.value = d.data.data || []; parties.value = p.data.data || []
  } catch {}
  loading.value = false
}

watch(() => form.value.election_id, async (id) => {
  if (id) try { lists.value = (await api.getListsByElection(id)).data.data || [] } catch {}
})

function edit(c) { form.value = { ...c }; editing.value = true; dialog.value = true }
async function save() {
  try {
    editing.value ? await api.updateCandidate(form.value.id, form.value) : await api.createCandidate(form.value)
    snackbar('Guardado'); dialog.value = false; await load()
  } catch { snackbar('Error', 'error') }
}
async function remove(c) { if (!confirm('¿Eliminar?')) return; try { await api.deleteCandidate(c.id); snackbar('Eliminado'); await load() } catch {} }
onMounted(load)
</script>
