<template>
  <div>
    <div class="d-flex justify-space-between align-center mb-4">
      <h2 class="text-h4">Partidos Políticos</h2>
      <v-dialog v-model="dialog" max-width="500">
        <template #activator="{ props }"><v-btn color="primary" prepend-icon="mdi-plus" v-bind="props">Nuevo Partido</v-btn></template>
        <v-card>
          <v-card-title>{{ editing ? 'Editar' : 'Nuevo' }} Partido</v-card-title>
          <v-card-text>
            <v-text-field v-model="form.name" label="Nombre" :rules="[v=>!!v||'Requerido']" />
            <v-text-field v-model="form.acronym" label="Siglas" :rules="[v=>!!v||'Requerido']" />
            <v-text-field v-model="form.list_number" label="Número de Lista" type="number" :rules="[v=>!!v||'Requerido']" />
            <v-text-field v-model="form.color" label="Color" type="color" />
          </v-card-text>
          <v-card-actions>
            <v-spacer /><v-btn variant="text" @click="dialog=false">Cancelar</v-btn><v-btn color="primary" @click="save">Guardar</v-btn>
          </v-card-actions>
        </v-card>
      </v-dialog>
    </div>
    <v-card>
      <v-data-table :headers="headers" :items="parties" :loading="loading">
        <template #item.color="{ value }"><v-chip :color="value" size="small" class="mr-2" />{{ value }}</template>
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
const parties = ref([]); const dialog = ref(false); const editing = ref(false); const loading = ref(false); const form = ref({})
const headers = [{ title: 'Nombre', key: 'name' }, { title: 'Siglas', key: 'acronym' }, { title: 'Lista', key: 'list_number' }, { title: 'Color', key: 'color' }, { title: 'Acciones', key: 'actions', sortable: false }]
async function load() { loading.value = true; try { parties.value = (await api.getParties()).data.data || [] } catch {}; loading.value = false }
function edit(p) { form.value = { ...p }; editing.value = true; dialog.value = true }
async function save() { try { editing.value ? await api.updateParty(form.value.id, form.value) : await api.createParty(form.value); snackbar('Guardado'); dialog.value = false; await load() } catch { snackbar('Error', 'error') } }
async function remove(p) { if (!confirm('¿Eliminar?')) return; try { await api.deleteParty(p.id); snackbar('Eliminado'); await load() } catch {} }
onMounted(load)
</script>
