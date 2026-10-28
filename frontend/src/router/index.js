import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const routes = [
  {
    path: '/login',
    name: 'Login',
    component: () => import('@/views/Login.vue'),
    meta: { requiresAuth: false },
  },
  {
    path: '/',
    component: () => import('@/layouts/DefaultLayout.vue'),
    meta: { requiresAuth: true },
    children: [
      { path: '', name: 'Dashboard', component: () => import('@/views/Dashboard.vue'), meta: { title: 'Dashboard' } },
      { path: 'users', name: 'Users', component: () => import('@/views/Users.vue'), meta: { title: 'Usuarios', roles: ['Administrador General'] } },
      { path: 'roles', name: 'Roles', component: () => import('@/views/Roles.vue'), meta: { title: 'Roles', roles: ['Administrador General'] } },
      { path: 'elections', name: 'Elections', component: () => import('@/views/Elections.vue'), meta: { title: 'Elecciones', roles: ['Administrador General'] } },
      { path: 'dignities', name: 'Dignities', component: () => import('@/views/Dignities.vue'), meta: { title: 'Dignidades', roles: ['Administrador General'] } },
      { path: 'parties', name: 'Parties', component: () => import('@/views/Parties.vue'), meta: { title: 'Partidos Políticos', roles: ['Administrador General'] } },
      { path: 'candidates', name: 'Candidates', component: () => import('@/views/Candidates.vue'), meta: { title: 'Candidatos', roles: ['Administrador General'] } },
      { path: 'territorial', name: 'Territorial', component: () => import('@/views/Territorial.vue'), meta: { title: 'División Territorial', roles: ['Administrador General'] } },
      { path: 'polling-stations', name: 'PollingStations', component: () => import('@/views/PollingStations.vue'), meta: { title: 'JRV' } },
      { path: 'assignments', name: 'Assignments', component: () => import('@/views/Assignments.vue'), meta: { title: 'Asignaciones', roles: ['Administrador General'] } },
      { path: 'results', name: 'Results', component: () => import('@/views/Results.vue'), meta: { title: 'Resultados' } },
      { path: 'results/register', name: 'RegisterResults', component: () => import('@/views/RegisterResults.vue'), meta: { title: 'Registrar Resultados', roles: ['Operador Electoral'] } },
      { path: 'minutes', name: 'Minutes', component: () => import('@/views/Minutes.vue'), meta: { title: 'Actas' } },
      { path: 'reports', name: 'Reports', component: () => import('@/views/Reports.vue'), meta: { title: 'Reportes' } },
      { path: 'audit', name: 'Audit', component: () => import('@/views/Audit.vue'), meta: { title: 'Auditoría', roles: ['Administrador General'] } },
      { path: 'profile', name: 'Profile', component: () => import('@/views/Profile.vue'), meta: { title: 'Mi Perfil' } },
    ],
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

router.beforeEach((to, from, next) => {
  const authStore = useAuthStore()
  if (to.meta.requiresAuth && !authStore.isAuthenticated) {
    return next('/login')
  }
  if (to.path === '/login' && authStore.isAuthenticated) {
    return next('/')
  }
  if (to.meta.roles && !to.meta.roles.includes(authStore.user?.role)) {
    return next('/')
  }
  next()
})

export default router
