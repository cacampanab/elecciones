<template>
  <v-layout>
    <v-app-bar :elevation="1" color="primary">
      <v-app-bar-nav-icon @click="appStore.toggleDrawer" />
      <v-app-bar-title>Sistema de Control Electoral</v-app-bar-title>
      <v-spacer />
      <v-chip v-if="currentElection" class="mr-3" color="accent" size="small">
        {{ currentElection.name }}
      </v-chip>
      <v-menu>
        <template #activator="{ props }">
          <v-btn icon v-bind="props">
            <v-icon>mdi-account-circle</v-icon>
          </v-btn>
        </template>
        <v-list>
          <v-list-item>
            <v-list-item-title>{{ authStore.user?.first_name }} {{ authStore.user?.last_name }}</v-list-item-title>
            <v-list-item-subtitle>{{ authStore.user?.role }}</v-list-item-subtitle>
          </v-list-item>
          <v-divider />
          <v-list-item to="/profile" prepend-icon="mdi-account-edit" title="Mi Perfil" />
          <v-list-item @click="logout" prepend-icon="mdi-logout" title="Cerrar Sesión" />
        </v-list>
      </v-menu>
    </v-app-bar>

    <v-navigation-drawer v-model="appStore.drawer" temporary>
      <v-list-item class="pa-4" color="primary">
        <template #prepend>
          <v-icon size="40" color="primary">mdi-ballot-outline</v-icon>
        </template>
        <v-list-item-title class="text-h6">Control Electoral</v-list-item-title>
        <v-list-item-subtitle>{{ authStore.user?.role }}</v-list-item-subtitle>
      </v-list-item>
      <v-divider />
      <v-list nav density="compact">
        <v-list-item to="/" prepend-icon="mdi-view-dashboard" title="Dashboard" />
        <v-list-group v-if="authStore.isAdmin" value="admin">
          <template #activator="{ props }">
            <v-list-item v-bind="props" prepend-icon="mdi-cog" title="Administración" />
          </template>
          <v-list-item to="/users" prepend-icon="mdi-account-group" title="Usuarios" />
          <v-list-item to="/roles" prepend-icon="mdi-shield-account" title="Roles" />
          <v-list-item to="/elections" prepend-icon="mdi-vote" title="Elecciones" />
          <v-list-item to="/dignities" prepend-icon="mdi-badge-account" title="Dignidades" />
          <v-list-item to="/parties" prepend-icon="mdi-party-popper" title="Partidos" />
          <v-list-item to="/candidates" prepend-icon="mdi-account-tie" title="Candidatos" />
        </v-list-group>
        <v-list-group value="territorial">
          <template #activator="{ props }">
            <v-list-item v-bind="props" prepend-icon="mdi-map" title="Territorial" />
          </template>
          <v-list-item to="/territorial" prepend-icon="mdi-map-marker" title="División Territorial" />
          <v-list-item to="/polling-stations" prepend-icon="mdi-domain" title="JRV" />
        </v-list-group>
        <v-list-item v-if="authStore.isAdmin" to="/assignments" prepend-icon="mdi-account-switch" title="Asignaciones" />
        <v-list-item v-if="authStore.isOperator" to="/results/register" prepend-icon="mdi-file-document-edit" title="Registrar Resultados" />
        <v-list-item to="/results" prepend-icon="mdi-chart-bar" title="Resultados" />
        <v-list-item to="/minutes" prepend-icon="mdi-file-image" title="Actas" />
        <v-list-item to="/reports" prepend-icon="mdi-file-pdf-box" title="Reportes" />
        <v-list-item v-if="authStore.isAdmin" to="/audit" prepend-icon="mdi-clipboard-list" title="Auditoría" />
      </v-list>
    </v-navigation-drawer>

    <v-main>
      <v-container fluid class="pa-6">
        <router-view />
      </v-container>
    </v-main>
  </v-layout>
</template>

<script setup>
import { onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useAppStore } from '@/stores/app'

const authStore = useAuthStore()
const appStore = useAppStore()
const router = useRouter()
const currentElection = appStore.currentElection

onMounted(async () => {
  await appStore.loadCurrentElection()
  await appStore.loadDignities()
})

function logout() {
  authStore.logout()
  router.push('/login')
}
</script>
