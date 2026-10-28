import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '@/api'

export const useAppStore = defineStore('app', () => {
  const loading = ref(false)
  const elections = ref([])
  const currentElection = ref(null)
  const dignities = ref([])
  const drawer = ref(true)
  const notifications = ref([])

  function setLoading(val) {
    loading.value = val
  }

  function toggleDrawer() {
    drawer.value = !drawer.value
  }

  async function loadElections() {
    try {
      const response = await api.getElections()
      elections.value = response.data.data || []
    } catch {}
  }

  async function loadCurrentElection() {
    try {
      const response = await api.getCurrentElection()
      currentElection.value = response.data.data || null
    } catch {
      currentElection.value = null
    }
  }

  async function loadDignities() {
    try {
      const response = await api.getDignities()
      dignities.value = response.data.data || []
    } catch {}
  }

  return { loading, elections, currentElection, dignities, drawer, notifications, setLoading, toggleDrawer, loadElections, loadCurrentElection, loadDignities }
})
