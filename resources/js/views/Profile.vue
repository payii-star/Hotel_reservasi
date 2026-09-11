<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import api from '../services/api'

const router = useRouter()
const user = ref(JSON.parse(localStorage.getItem('guest_user') || 'null'))
const bookings = ref([])
const loading = ref(true)

const statusLabel = {
  pending: 'Menunggu Konfirmasi',
  confirmed: 'Terkonfirmasi',
  checked_in: 'Sudah Check-in',
  checked_out: 'Sudah Check-out',
  cancelled: 'Dibatalkan',
}

function formatPrice(price) {
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(price)
}

function logout() {
  localStorage.removeItem('guest_token')
  localStorage.removeItem('guest_user')
  router.push('/')
}

onMounted(async () => {
  try {
    const res = await api.get('/my-bookings')
    bookings.value = res.data
  } catch (e) {
    console.error(e)
  } finally {
    loading.value = false
  }
})
</script>

<template>
  <div class="max-w-3xl mx-auto px-6 pt-28 pb-20">
    <div class="flex items-center justify-between mb-10">
      <div>
        <h1 class="font-display text-3xl text-ink mb-1">{{ user?.name }}</h1>
        <p class="text-ink/60">{{ user?.email }}</p>
      </div>
      <button @click="logout" class="btn-outline !py-2 !px-5 text-sm">Keluar</button>
    </div>

    <h2 class="font-display text-xl text-ink mb-4">Riwayat Booking</h2>

    <p v-if="loading" class="text-ink/50">Memuat...</p>
    <p v-else-if="bookings.length === 0" class="text-ink/50">Anda belum pernah melakukan reservasi.</p>

    <div v-else class="space-y-4">
      <RouterLink
        v-for="booking in bookings"
        :key="booking.id"
        :to="{ name: 'booking-status', params: { code: booking.booking_code } }"
        class="block border border-mist/30 rounded-2xl p-5 hover:shadow-md transition-shadow"
      >
        <div class="flex items-start justify-between mb-2">
          <div>
            <p class="text-xs uppercase tracking-widest text-mist mb-1">{{ booking.booking_code }}</p>
            <h3 class="font-display text-lg">{{ booking.room?.name }}</h3>
          </div>
          <span class="text-xs bg-brass/20 text-brass font-medium px-3 py-1 rounded-full whitespace-nowrap">
            {{ statusLabel[booking.status] }}
          </span>
        </div>
        <div class="flex justify-between text-sm text-ink/60">
          <span>{{ booking.check_in }} &rarr; {{ booking.check_out }}</span>
          <span class="font-semibold text-ink">{{ formatPrice(booking.total_price) }}</span>
        </div>
      </RouterLink>
    </div>
  </div>
</template>