<template>
  <div>
    <div class="d-flex justify-space-between align-center mb-4">
      <h2 class="text-h4">Usuarios</h2>
      <v-dialog v-model="dialog" max-width="600">
        <template #activator="{ props }">
          <v-btn color="primary" prepend-icon="mdi-plus" v-bind="props">Nuevo Usuario</v-btn>
        </template>
        <v-card>
          <v-card-title>{{ editing ? 'Editar' : 'Nuevo' }} Usuario</v-card-title>
          <v-card-text>
            <v-form ref="formRef">
              <v-row>
                <v-col cols="6"><v-text-field v-model="form.username" label="Usuario" :rules="[v=>!!v||'Requerido']" /></v-col>
                <v-col cols="6"><v-text-field v-model="form.email" label="Email" :rules="[v=>!!v||'Requerido']" /></v-col>
                <v-col cols="6"><v-text-field v-model="form.first_name" label="Nombres" :rules="[v=>!!v||'Requerido']" /></v-col>
                <v-col cols="6"><v-text-field v-model="form.last_name" label="Apellidos" :rules="[v=>!!v||'Requerido']" /></v-col>
                <v-col cols="6"><v-text-field v-model="form.cedula" label="Cédula" /></v-col>
                <v-col cols="6"><v-text-field v-model="form.phone" label="Teléfono" /></v-col>
                <v-col cols="6">
                  <v-select v-model="form.role_id" :items="roles" item-title="name" item-value="id" label="Rol" :rules="[v=>!!v||'Requerido']" />
                </v-col>
                <v-col cols="6">
                  <v-text-field v-if="!editing" v-model="form.password" label="Contraseña" type="password" :rules="[v=>!!v||'Requerido']" />
                </v-col>
              </v-row>
            </v-form>
          </v-card-text>
          <v-card-actions>
            <v-spacer />
            <v-btn variant="text" @click="dialog = false">Cancelar</v-btn>
            <v-btn color="primary" :loading="loading" @click="save">Guardar</v-btn>
          </v-card-actions>
        </v-card>
      </v-dialog>
    </div>
    <v-card>
      <v-data-table :headers="headers" :items="users" :loading="loading">
        <template #item.is_active="{ value }">
          <v-chip :color="value ? 'success' : 'error'" size="small">{{ value ? 'Activo' : 'Inactivo' }}</v-chip>
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
const users = ref([])
const roles = ref([])
const dialog = ref(false)
const editing = ref(false)
const loading = ref(false)
const formRef = ref()
const form = ref({})

const headers = [
  { title: 'Usuario', key: 'username' }, { title: 'Nombres', key: 'first_name' }, { title: 'Apellidos', key: 'last_name' },
  { title: 'Email', key: 'email' }, { title: 'Rol', key: 'role_name' }, { title: 'Estado', key: 'is_active' },
  { title: 'Acciones', key: 'actions', sortable: false },
]

async function load() {
  loading.value = true
  try {
    const [u, r] = await Promise.all([api.getUsers(), api.getRoles()])
    users.value = u.data.data || []
    roles.value = r.data.data || []
  } catch {}
  loading.value = false
}

function edit(user) {
  form.value = { ...user, password: '' }
  editing.value = true
  dialog.value = true
}

function newItem() {
  form.value = { username: '', email: '', first_name: '', last_name: '', cedula: '', phone: '', role_id: '', password: '' }
  editing.value = false
  dialog.value = true
}

async function save() {
  loading.value = true
  try {
    if (editing.value) {
      await api.updateUser(form.value.id, form.value)
      snackbar('Usuario actualizado')
    } else {
      await api.createUser(form.value)
      snackbar('Usuario creado')
    }
    dialog.value = false
    await load()
  } catch { snackbar('Error al guardar', 'error') }
  loading.value = false
}

async function remove(user) {
  if (!confirm('¿Eliminar usuario?')) return
  try {
    await api.deleteUser(user.id)
    snackbar('Usuario eliminado')
    await load()
  } catch {}
}

onMounted(load)
</script>
