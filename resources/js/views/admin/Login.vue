<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import api from '../../services/api'

const router = useRouter()
const email = ref('')
const password = ref('')
const errorMsg = ref('')
const loading = ref(false)

async function submit() {
  errorMsg.value = ''
  loading.value = true
  try {
    const res = await api.post('/admin/login', { email: email.value, password: password.value })
    localStorage.setItem('admin_token', res.data.token)
    localStorage.setItem('admin_name', res.data.user.name)
    router.push({ name: 'admin-bookings' })
  } catch (e) {
    errorMsg.value = e.response?.data?.message || 'Login gagal.'
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="min-h-screen bg-ink flex items-center justify-center px-6">
    <div class="bg-sand rounded-2xl p-8 w-full max-w-sm">
      <h1 class="font-display text-2xl text-ink mb-1">Login Admin</h1>
      <p class="text-ink/60 text-sm mb-6">Grand Nusantara Hotel</p>

      <p v-if="errorMsg" class="text-clay text-sm mb-4">{{ errorMsg }}</p>

      <form @submit.prevent="submit" class="space-y-4">
        <div>
          <label class="block text-sm text-ink/60 mb-1">Email</label>
          <input v-model="email" type="email" required class="w-full border border-mist/40 rounded-lg px-3 py-2" />
        </div>
        <div>
          <label class="block text-sm text-ink/60 mb-1">Password</label>
          <input v-model="password" type="password" required class="w-full border border-mist/40 rounded-lg px-3 py-2" />
        </div>
        <button type="submit" :disabled="loading" class="btn-primary w-full disabled:opacity-50">
          {{ loading ? 'Memproses...' : 'Masuk' }}
        </button>
      </form>
    </div>
  </div>
</template>