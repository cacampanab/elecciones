<template>
  <div>
    <div class="d-flex justify-space-between align-center mb-4">
      <h2 class="text-h4">Dignidades</h2>
      <v-dialog v-model="dialog" max-width="500">
        <template #activator="{ props }"><v-btn color="primary" prepend-icon="mdi-plus" v-bind="props">Nueva Dignidad</v-btn></template>
        <v-card>
          <v-card-title>{{ editing ? 'Editar' : 'Nueva' }} Dignidad</v-card-title>
          <v-card-text>
            <v-text-field v-model="form.name" label="Nombre" :rules="[v=>!!v||'Requerido']" />
            <v-text-field v-model="form.slug" label="Slug" :rules="[v=>!!v||'Requerido']" />
            <v-text-field v-model="form.description" label="Descripción" />
            <v-select v-model="form.level" :items="['nacional','provincial','cantonal','parroquial']" label="Nivel" />
          </v-card-text>
          <v-card-actions>
            <v-spacer /><v-btn variant="text" @click="dialog=false">Cancelar</v-btn><v-btn color="primary" @click="save">Guardar</v-btn>
          </v-card-actions>
        </v-card>
      </v-dialog>
    </div>
    <v-card>
      <v-data-table :headers="headers" :items="dignities" :loading="loading">
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
const dignities = ref([])
const dialog = ref(false); const editing = ref(false); const loading = ref(false); const form = ref({})
const headers = [{ title: 'Nombre', key: 'name' }, { title: 'Slug', key: 'slug' }, { title: 'Nivel', key: 'level' }, { title: 'Acciones', key: 'actions', sortable: false }]
async function load() { loading.value = true; try { dignities.value = (await api.getDignities()).data.data || [] } catch {}; loading.value = false }
function edit(d) { form.value = { ...d }; editing.value = true; dialog.value = true }
async function save() { try { editing.value ? await api.updateDignity(form.value.id, form.value) : await api.createDignity(form.value); snackbar('Guardado'); dialog.value = false; await load() } catch { snackbar('Error', 'error') } }
async function remove(d) { if (!confirm('¿Eliminar?')) return; try { await api.deleteDignity(d.id); snackbar('Eliminado'); await load() } catch {} }
onMounted(load)
</script>
