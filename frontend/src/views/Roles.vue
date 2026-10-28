<template>
  <div>
    <div class="d-flex justify-space-between align-center mb-4">
      <h2 class="text-h4">Roles y Permisos</h2>
      <v-dialog v-model="dialog" max-width="800">
        <template #activator="{ props }"><v-btn color="primary" prepend-icon="mdi-plus" v-bind="props">Nuevo Rol</v-btn></template>
        <v-card>
          <v-card-title>{{ editing ? 'Editar' : 'Nuevo' }} Rol</v-card-title>
          <v-card-text>
            <v-text-field v-model="form.name" label="Nombre" :rules="[v=>!!v||'Requerido']" />
            <v-text-field v-model="form.description" label="Descripción" />
            <v-divider class="my-3" />
            <div class="text-subtitle-1 mb-2">Permisos</div>
            <v-row>
              <v-col v-for="perm in allPermissions" :key="perm.id" cols="6" md="4">
                <v-checkbox v-model="selectedPerms" :label="perm.description || perm.name" :value="perm.id" hide-details density="compact" />
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
      <v-data-table :headers="headers" :items="roles" :loading="loading">
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
const roles = ref([]); const allPermissions = ref([]); const selectedPerms = ref([])
const dialog = ref(false); const editing = ref(false); const loading = ref(false); const form = ref({})
const headers = [{ title: 'Nombre', key: 'name' }, { title: 'Descripción', key: 'description' }, { title: 'Acciones', key: 'actions', sortable: false }]

async function load() {
  loading.value = true
  try { const [r, p] = await Promise.all([api.getRoles(), api.getPermissions()]); roles.value = r.data.data || []; allPermissions.value = p.data.data || [] } catch {}
  loading.value = false
}

async function edit(role) {
  form.value = { ...role }
  try { const r = await api.getRole(role.id); selectedPerms.value = r.data.data?.permissions?.map(p => p.id) || [] } catch {}
  editing.value = true; dialog.value = true
}

async function save() {
  try {
    const data = { ...form.value, permissions: selectedPerms.value }
    editing.value ? await api.updateRole(form.value.id, data) : await api.createRole(data)
    snackbar('Guardado'); dialog.value = false; await load()
  } catch { snackbar('Error', 'error') }
}

async function remove(r) { if (!confirm('¿Eliminar?')) return; try { await api.deleteRole(r.id); snackbar('Eliminado'); await load() } catch {} }
onMounted(load)
</script>
