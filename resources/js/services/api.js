import axios from 'axios'

const api = axios.create({
  baseURL: '/api',
})

// Sisipkan token otomatis: prioritaskan admin_token (kalau lagi di panel admin),
// kalau nggak ada baru pakai guest_token (user/tamu yang login)
api.interceptors.request.use((config) => {
  const adminToken = localStorage.getItem('admin_token')
  const guestToken = localStorage.getItem('guest_token')
  const token = adminToken || guestToken

  if (token) {
    config.headers.Authorization = `Bearer ${token}`
  }
  return config
})

export default api