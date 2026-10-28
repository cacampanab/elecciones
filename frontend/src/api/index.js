import apiClient from './client'

export default {
  // Auth
  login(data) { return apiClient.post('/auth/login', data) },
  refresh(data) { return apiClient.post('/auth/refresh', data) },
  getMe() { return apiClient.get('/auth/me') },
  logout() { return apiClient.post('/auth/logout') },
  updateProfile(data) { return apiClient.put('/auth/profile', data) },

  // Users
  getUsers() { return apiClient.get('/users') },
  getUser(id) { return apiClient.get(`/users/${id}`) },
  createUser(data) { return apiClient.post('/users', data) },
  updateUser(id, data) { return apiClient.put(`/users/${id}`, data) },
  deleteUser(id) { return apiClient.delete(`/users/${id}`) },
  getOperators() { return apiClient.get('/users/operators') },
  getSupervisors() { return apiClient.get('/users/supervisors') },

  // Roles
  getRoles() { return apiClient.get('/roles') },
  getRole(id) { return apiClient.get(`/roles/${id}`) },
  createRole(data) { return apiClient.post('/roles', data) },
  updateRole(id, data) { return apiClient.put(`/roles/${id}`, data) },
  deleteRole(id) { return apiClient.delete(`/roles/${id}`) },
  getPermissions() { return apiClient.get('/permissions') },

  // Elections
  getElections() { return apiClient.get('/elections') },
  getCurrentElection() { return apiClient.get('/elections/current') },
  getElection(id) { return apiClient.get(`/elections/${id}`) },
  createElection(data) { return apiClient.post('/elections', data) },
  updateElection(id, data) { return apiClient.put(`/elections/${id}`, data) },
  deleteElection(id) { return apiClient.delete(`/elections/${id}`) },

  // Dignities
  getDignities() { return apiClient.get('/dignities') },
  getDignity(id) { return apiClient.get(`/dignities/${id}`) },
  createDignity(data) { return apiClient.post('/dignities', data) },
  updateDignity(id, data) { return apiClient.put(`/dignities/${id}`, data) },
  deleteDignity(id) { return apiClient.delete(`/dignities/${id}`) },

  // Political Parties
  getParties() { return apiClient.get('/parties') },
  getParty(id) { return apiClient.get(`/parties/${id}`) },
  createParty(data) { return apiClient.post('/parties', data) },
  updateParty(id, data) { return apiClient.put(`/parties/${id}`, data) },
  deleteParty(id) { return apiClient.delete(`/parties/${id}`) },

  // Candidate Lists
  getLists() { return apiClient.get('/lists') },
  getList(id) { return apiClient.get(`/lists/${id}`) },
  createList(data) { return apiClient.post('/lists', data) },
  updateList(id, data) { return apiClient.put(`/lists/${id}`, data) },
  deleteList(id) { return apiClient.delete(`/lists/${id}`) },
  getListsByElection(id) { return apiClient.get(`/lists/by-election/${id}`) },

  // Candidates
  getCandidates(params) { return apiClient.get('/candidates', { params }) },
  getCandidate(id) { return apiClient.get(`/candidates/${id}`) },
  createCandidate(data) { return apiClient.post('/candidates', data, { headers: { 'Content-Type': 'multipart/form-data' } }) },
  updateCandidate(id, data) { return apiClient.put(`/candidates/${id}`, data) },
  deleteCandidate(id) { return apiClient.delete(`/candidates/${id}`) },
  getCandidatesByElection(id) { return apiClient.get(`/candidates/by-election/${id}`) },
  getCandidatesByDignity(electionId, dignityId) { return apiClient.get(`/candidates/by-dignity/${electionId}/${dignityId}`) },

  // Provinces
  getProvinces() { return apiClient.get('/provinces') },
  getProvincesWithCantons() { return apiClient.get('/provinces/with-cantons') },
  getProvince(id) { return apiClient.get(`/provinces/${id}`) },
  createProvince(data) { return apiClient.post('/provinces', data) },
  updateProvince(id, data) { return apiClient.put(`/provinces/${id}`, data) },
  deleteProvince(id) { return apiClient.delete(`/provinces/${id}`) },

  // Cantons
  getCantons(params) { return apiClient.get('/cantons', { params }) },
  getCanton(id) { return apiClient.get(`/cantons/${id}`) },
  createCanton(data) { return apiClient.post('/cantons', data) },
  updateCanton(id, data) { return apiClient.put(`/cantons/${id}`, data) },
  deleteCanton(id) { return apiClient.delete(`/cantons/${id}`) },

  // Parishes
  getParishes(params) { return apiClient.get('/parishes', { params }) },
  getParish(id) { return apiClient.get(`/parishes/${id}`) },
  createParish(data) { return apiClient.post('/parishes', data) },
  updateParish(id, data) { return apiClient.put(`/parishes/${id}`, data) },
  deleteParish(id) { return apiClient.delete(`/parishes/${id}`) },

  // Precincts
  getPrecincts(params) { return apiClient.get('/precincts', { params }) },
  getPrecinct(id) { return apiClient.get(`/precincts/${id}`) },
  createPrecinct(data) { return apiClient.post('/precincts', data) },
  updatePrecinct(id, data) { return apiClient.put(`/precincts/${id}`, data) },
  deletePrecinct(id) { return apiClient.delete(`/precincts/${id}`) },

  // Polling Stations
  getPollingStations(params) { return apiClient.get('/polling-stations', { params }) },
  getPollingStation(id) { return apiClient.get(`/polling-stations/${id}`) },
  createPollingStation(data) { return apiClient.post('/polling-stations', data) },
  updatePollingStation(id, data) { return apiClient.put(`/polling-stations/${id}`, data) },
  deletePollingStation(id) { return apiClient.delete(`/polling-stations/${id}`) },
  importPollingStations(data) { return apiClient.post('/polling-stations/import', data, { headers: { 'Content-Type': 'multipart/form-data' } }) },
  getPollingStationStats() { return apiClient.get('/polling-stations/stats') },
  getMyStations(params) { return apiClient.get('/polling-stations/my-stations', { params }) },
  updateStationStatus(id, status) { return apiClient.put(`/polling-stations/${id}/status`, { status }) },

  // Assignments
  getAssignments(params) { return apiClient.get('/assignments', { params }) },
  getAssignment(id) { return apiClient.get(`/assignments/${id}`) },
  createAssignment(data) { return apiClient.post('/assignments', data) },
  createBatchAssignments(data) { return apiClient.post('/assignments/batch', data) },
  deleteAssignment(id) { return apiClient.delete(`/assignments/${id}`) },
  getMyAssignments(params) { return apiClient.get('/assignments/my-assignments', { params }) },

  // Results
  getResults(params) { return apiClient.get('/results', { params }) },
  getResult(id) { return apiClient.get(`/results/${id}`) },
  createResult(data) { return apiClient.post('/results', data) },
  updateResult(id, data) { return apiClient.put(`/results/${id}`, data) },
  verifyResult(id) { return apiClient.put(`/results/${id}/verify`) },
  deleteResult(id) { return apiClient.delete(`/results/${id}`) },
  getResultsByStation(id, params) { return apiClient.get(`/results/by-station/${id}`, { params }) },
  getResultsByDignity(electionId, dignityId) { return apiClient.get(`/results/by-dignity/${electionId}/${dignityId}`) },

  // Minutes
  getMinutes(params) { return apiClient.get('/minutes', { params }) },
  getMinute(id) { return apiClient.get(`/minutes/${id}`) },
  uploadMinute(data) { return apiClient.post('/minutes/upload', data, { headers: { 'Content-Type': 'multipart/form-data' } }) },
  downloadMinute(id) { return apiClient.get(`/minutes/${id}/download`, { responseType: 'blob' }) },
  previewMinute(id) { return apiClient.get(`/minutes/${id}/preview`, { responseType: 'blob' }) },
  deleteMinute(id) { return apiClient.delete(`/minutes/${id}`) },

  // Dashboard
  getDashboard(params) { return apiClient.get('/dashboard', { params }) },
  getDashboardByProvince(params) { return apiClient.get('/dashboard/by-province', { params }) },
  getParticipation(params) { return apiClient.get('/dashboard/participation', { params }) },
  getPendingStations(params) { return apiClient.get('/dashboard/pending-stations', { params }) },

  // Reports
  getResultsByProvince(electionId, params) { return apiClient.get(`/reports/results-by-province/${electionId}`, { params }) },
  exportPdf(params) { return apiClient.get('/reports/export-pdf', { params, responseType: 'blob' }) },
  exportExcel(params) { return apiClient.get('/reports/export-excel', { params, responseType: 'blob' }) },

  // Audit
  getAudits(params) { return apiClient.get('/audits', { params }) },
  getAudit(id) { return apiClient.get(`/audits/${id}`) },
}
