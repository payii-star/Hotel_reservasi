<script setup>
import { ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import api from '../services/api'

const route = useRoute()
const router = useRouter()

const email = ref('')
const password = ref('')
const showPassword = ref(false)
const errorMsg = ref('')
const loading = ref(false)

async function submit() {
  errorMsg.value = ''
  loading.value = true
  try {
    const res = await api.post('/login', { email: email.value, password: password.value })
    localStorage.setItem('guest_token', res.data.token)
    localStorage.setItem('guest_user', JSON.stringify(res.data.user))
    router.push(route.query.redirect || '/')
  } catch (e) {
    errorMsg.value = e.response?.data?.message || 'Gagal login, coba lagi.'
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="max-w-md mx-auto px-6 pt-28 pb-20">
    <h1 class="font-display text-3xl mb-2 text-ink">Masuk</h1>
    <p class="text-ink/60 mb-8">Masuk ke akun Anda untuk melanjutkan reservasi.</p>

    <p v-if="errorMsg" class="text-clay text-sm mb-4">{{ errorMsg }}</p>

    <form @submit.prevent="submit" class="space-y-4">
      <div>
        <label class="block text-sm text-ink/60 mb-1">Email</label>
        <input v-model="email" type="email" required class="w-full border border-mist/40 rounded-lg px-3 py-2" />
      </div>
      <div>
        <label class="block text-sm text-ink/60 mb-1">Password</label>
        <div class="relative">
          <input
            v-model="password"
            :type="showPassword ? 'text' : 'password'"
            required
            class="w-full border border-mist/40 rounded-lg px-3 py-2 pr-10"
          />
          <button
            type="button"
            @click="showPassword = !showPassword"
            class="absolute right-3 top-1/2 -translate-y-1/2 text-ink/40 hover:text-ink/70"
            tabindex="-1"
          >
            <svg v-if="!showPassword" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z" />
              <circle cx="12" cy="12" r="3" />
            </svg>
            <svg v-else xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M9.88 9.88a3 3 0 1 0 4.24 4.24" />
              <path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c6.5 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68" />
              <path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3.5 7 10 7a9.74 9.74 0 0 0 5.39-1.61" />
              <line x1="2" y1="2" x2="22" y2="22" />
            </svg>
          </button>
        </div>
      </div>
      <button type="submit" :disabled="loading" class="btn-primary w-full disabled:opacity-50">
        {{ loading ? 'Memproses...' : 'Masuk' }}
      </button>
    </form>

    <p class="text-sm text-ink/60 mt-6 text-center">
      Belum punya akun?
      <RouterLink :to="{ name: 'guest-register', query: route.query }" class="text-pine hover:underline">Daftar di sini</RouterLink>
    </p>
  </div>
</template>