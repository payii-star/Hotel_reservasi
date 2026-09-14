<script setup>
import { ref, watch, onMounted } from 'vue'
import api from '../services/api'
import RescheduleCalendar from './RescheduleCalendar.vue'
import BookingDetailModal from './BookingDetailModal.vue'

const props = defineProps({ code: String })

const isLoggedIn = !!localStorage.getItem('guest_token')

const inputCode = ref(props.code || '')
const booking = ref(null)
const notFound = ref(false)
const loading = ref(false)

const myBookings = ref([])
const loadingHistory = ref(false)

const payingCode = ref(null)
const payError = ref('')

const selectedBooking = ref(null)

const statusLabel = {
  pending: 'Menunggu Konfirmasi',
  confirmed: 'Terkonfirmasi',
  checked_in: 'Check-in',
  checked_out: 'Check-out',
  cancelled: 'Dibatalkan',
}

const statusColor = {
  pending: 'bg-brass/20 text-brass',
  confirmed: 'bg-pine/20 text-pine',
  checked_in: 'bg-pine text-sand',
  checked_out: 'bg-mist/20 text-mist',
  cancelled: 'bg-clay/20 text-clay',
}

// Status yang boleh direschedule (sudah dibayar, belum checkout/cancelled)
const reschedulableStatuses = ['confirmed']

const reschedulingCode = ref(null)
const rescheduleForm = ref({ check_in: '', check_out: '' })
const rescheduleNights = ref(1)
const reschedulingSubmit = ref(false)
const rescheduleError = ref('')

// Tanggal paling cepat yang boleh dipilih buat check-in baru: besok
const minCheckInDate = new Date(Date.now() + 24 * 60 * 60 * 1000).toISOString().slice(0, 10)

// Ambil YYYY-MM-DD dari string tanggal apapun (ISO datetime ataupun date polos),
// biar cocok sama format yang dibutuhkan <input type="date">
function toDateInputValue(dateStr) {
  return String(dateStr).slice(0, 10)
}

function addDays(dateStr, days) {
  const d = new Date(dateStr + 'T00:00:00')
  d.setDate(d.getDate() + days)
  return d.toISOString().slice(0, 10)
}

// Setiap check-in baru dipilih, check-out otomatis ngikut biar durasi nginep
// tetap sama persis kayak booking aslinya (nggak bisa diubah bebas oleh user)
watch(() => rescheduleForm.value.check_in, (newCheckIn) => {
  if (!newCheckIn) return
  rescheduleForm.value.check_out = addDays(newCheckIn, rescheduleNights.value)
})

function formatPrice(price) {
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(price)
}

function openDetail(b) {
  selectedBooking.value = b
}

function closeDetail() {
  selectedBooking.value = null
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

async function payNow(code) {
  payError.value = ''
  payingCode.value = code
  try {
    const res = await api.post(`/bookings/${code}/pay`)
    window.snap.pay(res.data.snap_token, {
      onSuccess: () => {
        if (isLoggedIn) loadHistory()
        else search()
      },
      onPending: () => {
        if (isLoggedIn) loadHistory()
        else search()
      },
      onError: () => {
        payError.value = 'Pembayaran gagal, silakan coba lagi.'
      },
      onClose: () => {
        payError.value = 'Anda menutup jendela pembayaran.'
      },
    })
  } catch (e) {
    payError.value = e.response?.data?.message || 'Gagal memuat pembayaran, coba lagi.'
  } finally {
    payingCode.value = null
  }
}

function openReschedule(b) {
  reschedulingCode.value = b.booking_code
  const checkIn = toDateInputValue(b.check_in)
  const checkOut = toDateInputValue(b.check_out)
  const nights = Math.round((new Date(checkOut) - new Date(checkIn)) / 86400000) || 1
  rescheduleNights.value = nights
  rescheduleForm.value = { check_in: '', check_out: '' }
  rescheduleError.value = ''
}

function cancelReschedule() {
  reschedulingCode.value = null
  rescheduleError.value = ''
}

async function submitReschedule(b) {
  rescheduleError.value = ''
  reschedulingSubmit.value = true
  try {
    const res = await api.patch(`/bookings/${b.booking_code}/reschedule`, rescheduleForm.value)
    b.check_in = res.data.booking.check_in
    b.check_out = res.data.booking.check_out
    b.total_price = res.data.booking.total_price
    b.rescheduled_at = res.data.booking.rescheduled_at
    reschedulingCode.value = null
  } catch (e) {
    rescheduleError.value = e.response?.data?.message || 'Gagal reschedule booking.'
  } finally {
    reschedulingSubmit.value = false
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
        <div
          v-for="b in myBookings"
          :key="b.id"
          class="border border-mist/30 rounded-2xl p-5 bg-white"
        >
          <div
            @click="openDetail(b)"
            class="cursor-pointer hover:opacity-80 transition-opacity"
          >
            <div class="flex items-start justify-between mb-2">
              <div>
                <p class="text-xs uppercase tracking-widest text-mist mb-1">{{ b.booking_code }}</p>
                <h3 class="font-display text-lg">{{ b.room?.name }}</h3>
              </div>
              <span class="text-xs font-medium px-3 py-1 rounded-full whitespace-nowrap" :class="statusColor[b.status]">
                {{ statusLabel[b.status] }}
              </span>
            </div>
            <div class="flex justify-between text-sm text-ink/60">
              <span>{{ b.check_in }} &rarr; {{ b.check_out }}</span>
              <span class="font-semibold text-ink">{{ formatPrice(b.total_price) }}</span>
            </div>
          </div>

          <div class="flex gap-2 mt-4">
            <button
              v-if="b.status === 'pending'"
              @click.stop="payNow(b.booking_code)"
              :disabled="payingCode === b.booking_code"
              class="btn-primary !py-2 !text-sm flex-1 disabled:opacity-50"
            >
              {{ payingCode === b.booking_code ? 'Memuat...' : 'Bayar Sekarang' }}
            </button>

            <button
              v-if="reschedulableStatuses.includes(b.status) && !b.rescheduled_at && reschedulingCode !== b.booking_code"
              @click.stop="openReschedule(b)"
              class="text-sm font-medium px-4 py-2 rounded-lg border border-mist/40 text-ink/70 hover:bg-sand flex-1"
            >
              Reschedule
            </button>
          </div>

          <div v-if="reschedulingCode === b.booking_code" class="mt-4 pt-4 border-t border-mist/20 space-y-3" @click.stop>
            <div class="flex flex-wrap items-start gap-4">
              <RescheduleCalendar
                v-model="rescheduleForm.check_in"
                :nights="rescheduleNights"
                :min-date="minCheckInDate"
                :original-start="toDateInputValue(b.check_in)"
                :original-end="toDateInputValue(b.check_out)"
              />
              <div class="text-sm text-ink/70 pt-1 space-y-1">
                <p>Check-in baru: <span class="font-medium text-ink">{{ rescheduleForm.check_in || '-' }}</span></p>
                <p>Check-out otomatis: <span class="font-medium text-ink">{{ rescheduleForm.check_out || '-' }}</span></p>
                <p class="text-xs text-ink/40">Durasi menginap tetap {{ rescheduleNights }} malam, mengikuti booking awal.</p>
              </div>
            </div>
            <div class="flex gap-3">
              <button
                @click="submitReschedule(b)"
                :disabled="reschedulingSubmit || !rescheduleForm.check_in"
                class="text-xs font-medium px-4 py-2 rounded-lg bg-pine text-sand hover:opacity-90 disabled:opacity-50"
              >
                {{ reschedulingSubmit ? 'Menyimpan...' : 'Simpan' }}
              </button>
              <button
                @click="cancelReschedule"
                class="text-xs font-medium px-4 py-2 rounded-lg border border-mist/40 text-ink/70 hover:bg-white"
              >
                Batal
              </button>
            </div>
            <p v-if="rescheduleError" class="text-clay text-sm">{{ rescheduleError }}</p>
          </div>
        </div>

        <p v-if="payError" class="text-clay text-sm">{{ payError }}</p>
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
        <div @click="openDetail(booking)" class="cursor-pointer hover:opacity-80 transition-opacity">
          <p class="text-xs uppercase tracking-widest text-mist mb-1">{{ booking.booking_code }}</p>
          <h2 class="font-display text-2xl mb-4">{{ booking.room?.name }}</h2>
          <div class="grid grid-cols-2 gap-3 text-sm mb-4">
            <p><span class="text-ink/50">Nama:</span> {{ booking.guest_name }}</p>
            <p><span class="text-ink/50">Check-in:</span> {{ booking.check_in }}</p>
            <p><span class="text-ink/50">Check-out:</span> {{ booking.check_out }}</p>
            <p><span class="text-ink/50">Tamu:</span> {{ booking.total_guest }} orang</p>
          </div>
          <span class="inline-block font-medium px-4 py-1.5 rounded-full text-sm" :class="statusColor[booking.status]">
            {{ statusLabel[booking.status] }}
          </span>
        </div>

        <div class="flex gap-2 mt-4">
          <button
            v-if="booking.status === 'pending'"
            @click="payNow(booking.booking_code)"
            :disabled="payingCode === booking.booking_code"
            class="btn-primary !py-2 !text-sm flex-1 disabled:opacity-50"
          >
            {{ payingCode === booking.booking_code ? 'Memuat...' : 'Bayar Sekarang' }}
          </button>

          <button
            v-if="reschedulableStatuses.includes(booking.status) && !booking.rescheduled_at && reschedulingCode !== booking.booking_code"
            @click="openReschedule(booking)"
            class="text-sm font-medium px-4 py-2 rounded-lg border border-mist/40 text-ink/70 hover:bg-sand flex-1"
          >
            Reschedule
          </button>
        </div>

        <div v-if="reschedulingCode === booking.booking_code" class="mt-4 pt-4 border-t border-mist/20 space-y-3">
          <div class="flex flex-wrap items-start gap-4">
            <RescheduleCalendar
              v-model="rescheduleForm.check_in"
              :nights="rescheduleNights"
              :min-date="minCheckInDate"
              :original-start="toDateInputValue(booking.check_in)"
              :original-end="toDateInputValue(booking.check_out)"
            />
            <div class="text-sm text-ink/70 pt-1 space-y-1">
              <p>Check-in baru: <span class="font-medium text-ink">{{ rescheduleForm.check_in || '-' }}</span></p>
              <p>Check-out otomatis: <span class="font-medium text-ink">{{ rescheduleForm.check_out || '-' }}</span></p>
              <p class="text-xs text-ink/40">Durasi menginap tetap {{ rescheduleNights }} malam, mengikuti booking awal.</p>
            </div>
          </div>
          <div class="flex gap-3">
            <button
              @click="submitReschedule(booking)"
              :disabled="reschedulingSubmit || !rescheduleForm.check_in"
              class="text-xs font-medium px-4 py-2 rounded-lg bg-pine text-sand hover:opacity-90 disabled:opacity-50"
            >
              {{ reschedulingSubmit ? 'Menyimpan...' : 'Simpan' }}
            </button>
            <button
              @click="cancelReschedule"
              class="text-xs font-medium px-4 py-2 rounded-lg border border-mist/40 text-ink/70 hover:bg-white"
            >
              Batal
            </button>
          </div>
          <p v-if="rescheduleError" class="text-clay text-sm">{{ rescheduleError }}</p>
        </div>

        <p v-if="payError" class="text-clay text-sm mt-3">{{ payError }}</p>
      </div>
    </template>

    <BookingDetailModal :booking="selectedBooking" @close="closeDetail" />
  </div>
</template>