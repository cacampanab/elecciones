import { defineStore } from 'pinia'
import api from '@/api'
import { ref, computed } from 'vue'

export const useAuthStore = defineStore('auth', () => {
  const user = ref(null)
  const token = ref(localStorage.getItem('token'))
  const refreshToken = ref(localStorage.getItem('refresh_token'))
  const permissions = ref([])

  const isAuthenticated = computed(() => !!token.value)
  const isAdmin = computed(() => user.value?.role === 'Administrador General')
  const isOperator = computed(() => user.value?.role === 'Operador Electoral')
  const isViewer = computed(() => user.value?.role === 'Visualizador / Supervisor')

  function hasPermission(name) {
    return permissions.value.includes(name)
  }

  async function login(credentials) {
    const response = await api.login(credentials)
    const data = response.data.data
    token.value = data.token
    refreshToken.value = data.refresh_token
    localStorage.setItem('token', data.token)
    localStorage.setItem('refresh_token', data.refresh_token)
    await fetchUser()
    return data
  }

  async function fetchUser() {
    try {
      const response = await api.getMe()
      const data = response.data.data
      user.value = data.user
      permissions.value = data.permissions
    } catch {
      await logout()
    }
  }

  async function refreshTokenAction() {
    try {
      const response = await api.refresh({ refresh_token: refreshToken.value })
      const data = response.data.data
      token.value = data.token
      refreshToken.value = data.refresh_token
      localStorage.setItem('token', data.token)
      localStorage.setItem('refresh_token', data.refresh_token)
    } catch {
      throw new Error('Refresh failed')
    }
  }

  function logout() {
    try { api.logout() } catch {}
    user.value = null
    token.value = null
    refreshToken.value = null
    permissions.value = []
    localStorage.removeItem('token')
    localStorage.removeItem('refresh_token')
  }

  return { user, token, refreshToken, permissions, isAuthenticated, isAdmin, isOperator, isViewer, hasPermission, login, fetchUser, refreshToken: refreshTokenAction, logout }
})
