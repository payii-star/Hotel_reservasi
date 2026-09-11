<script setup>
import { ref, onMounted } from 'vue'
import api from '../services/api'

const props = defineProps({ code: String })

const isLoggedIn = !!localStorage.getItem('guest_token')

const inputCode = ref(props.code || '')
const booking = ref(null)
const notFound = ref(false)
const loading = ref(false)

const myBookings = ref([])
const loadingHistory = ref(false)

const statusLabel = {
  pending: 'Menunggu Konfirmasi',
  confirmed: 'Terkonfirmasi',
  checked_in: 'Check-in',
  checked_out: 'Check-out',
  cancelled: 'Dibatalkan',
}

function formatPrice(price) {
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(price)
}

async function search() {
  if (!inputCode.value) return
  loading.value = true
  notFound.value = false
  booking.value = null
  try {
    const res = await api.get(`/bookings/${inputCode.value}`)
    booking.value = res.data
  } catch (e) {
    notFound.value = true
  } finally {
    loading.value = false
  }
}

async function loadHistory() {
  loadingHistory.value = true
  try {
    const res = await api.get('/my-bookings')
    myBookings.value = res.data
  } catch (e) {
    console.error(e)
  } finally {
    loadingHistory.value = false
  }
}

onMounted(() => {
  if (isLoggedIn) {
    loadHistory()
  }
  if (props.code) {
    search()
  }
})
</script>

<template>
  <div class="max-w-2xl mx-auto px-6 pt-28 pb-20">
    <h1 class="font-display text-3xl mb-6 text-ink">Cek Status Booking</h1>

    <!-- USER LOGIN: tampilin riwayat booking otomatis -->
    <template v-if="isLoggedIn">
      <p v-if="loadingHistory" class="text-ink/50">Memuat riwayat booking...</p>

      <p v-else-if="myBookings.length === 0" class="text-ink/50 bg-white border border-mist/30 rounded-2xl p-6 text-center">
        Anda belum pernah melakukan reservasi.
      </p>

      <div v-else class="space-y-4">
        <RouterLink
          v-for="b in myBookings"
          :key="b.id"
          :to="{ name: 'booking-status', params: { code: b.booking_code } }"
          class="block border border-mist/30 rounded-2xl p-5 hover:shadow-md transition-shadow bg-white"
        >
          <div class="flex items-start justify-between mb-2">
            <div>
              <p class="text-xs uppercase tracking-widest text-mist mb-1">{{ b.booking_code }}</p>
              <h3 class="font-display text-lg">{{ b.room?.name }}</h3>
            </div>
            <span class="text-xs bg-brass/20 text-brass font-medium px-3 py-1 rounded-full whitespace-nowrap">
              {{ statusLabel[b.status] }}
            </span>
          </div>
          <div class="flex justify-between text-sm text-ink/60">
            <span>{{ b.check_in }} &rarr; {{ b.check_out }}</span>
            <span class="font-semibold text-ink">{{ formatPrice(b.total_price) }}</span>
          </div>
        </RouterLink>
      </div>
    </template>

    <!-- BELUM LOGIN: form cari manual pakai kode -->
    <template v-else>
      <p class="text-ink/60 mb-6">Masukkan kode booking yang Anda terima saat reservasi.</p>

      <form @submit.prevent="search" class="flex gap-3 mb-8">
        <input v-model="inputCode" type="text" placeholder="Masukkan kode booking, ex: BK-20260826-0001" class="flex-1 border border-mist/40 rounded-lg px-3 py-2" />
        <button type="submit" class="btn-primary !py-2">Cari</button>
      </form>

      <p v-if="loading" class="text-ink/50">Mencari...</p>
      <p v-else-if="notFound" class="text-clay">Kode booking tidak ditemukan.</p>

      <div v-else-if="booking" class="border border-mist/30 rounded-2xl p-6 bg-white">
        <p class="text-xs uppercase tracking-widest text-mist mb-1">{{ booking.booking_code }}</p>
        <h2 class="font-display text-2xl mb-4">{{ booking.room?.name }}</h2>
        <div class="grid grid-cols-2 gap-3 text-sm mb-4">
          <p><span class="text-ink/50">Nama:</span> {{ booking.guest_name }}</p>
          <p><span class="text-ink/50">Check-in:</span> {{ booking.check_in }}</p>
          <p><span class="text-ink/50">Check-out:</span> {{ booking.check_out }}</p>
          <p><span class="text-ink/50">Tamu:</span> {{ booking.total_guest }} orang</p>
        </div>
        <span class="inline-block bg-brass/20 text-brass font-medium px-4 py-1.5 rounded-full text-sm">
          {{ statusLabel[booking.status] }}
        </span>
      </div>
    </template>
  </div>
</template>