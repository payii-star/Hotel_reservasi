import axios from 'axios'

const api = axios.create({
  baseURL: '/api',
})

// Sisipkan token SESUAI JENIS ENDPOINT, bukan asal ada token dipakai.
// Ini penting biar admin & user bisa login bareng di browser yang sama
// (misal 2 tab beda) tanpa saling menimpa identitas satu sama lain.
api.interceptors.request.use((config) => {
  const isAdminEndpoint = config.url?.startsWith('/admin')

  const token = isAdminEndpoint
    ? localStorage.getItem('admin_token')
    : localStorage.getItem('guest_token')

  if (token) {
    config.headers.Authorization = `Bearer ${token}`
  }
  return config
})

export default api