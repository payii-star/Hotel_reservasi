<script setup>
import { ref, onMounted } from 'vue'
import api from '../../services/api'
import BookingDetailModal from '../BookingDetailModal.vue'

const bookings = ref([])
const filter = ref('')
const loading = ref(false)
const actionError = ref('')
const selectedBooking = ref(null)

const statusLabel = {
  pending: 'Menunggu',
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

// Aksi yang tersedia untuk tiap status saat ini.
// Konfirmasi pembayaran (pending -> confirmed) sengaja TIDAK ada di sini,
// karena itu hanya boleh terjadi otomatis lewat callback Midtrans.
const nextActionMap = {
  pending: { label: 'Batalkan', status: 'cancelled' },
  confirmed: { label: 'Check-in', status: 'checked_in' },
  checked_in: { label: 'Check-out', status: 'checked_out' },
}

async function load() {
  loading.value = true
  actionError.value = ''
  const res = await api.get('/admin/bookings', { params: filter.value ? { status: filter.value } : {} })
  bookings.value = res.data
  loading.value = false
}

async function doNextAction(booking) {
  const action = nextActionMap[booking.status]
  if (!action) return

  actionError.value = ''
  try {
    await api.patch(`/admin/bookings/${booking.id}/status`, { status: action.status })
    booking.status = action.status
  } catch (e) {
    actionError.value = e.response?.data?.message || 'Gagal mengubah status.'
  }
}

function openDetail(b) {
  selectedBooking.value = b
}

function closeDetail() {
  selectedBooking.value = null
}

function formatPrice(price) {
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(price)
}

onMounted(load)
</script>

<template>
  <div>
    <div class="flex items-center justify-between mb-6">
      <h1 class="font-display text-2xl text-ink">Reservasi</h1>
      <select v-model="filter" @change="load" class="border border-mist/40 rounded-lg px-3 py-2 text-sm">
        <option value="">Semua Status</option>
        <option value="pending">Menunggu</option>
        <option value="confirmed">Terkonfirmasi</option>
        <option value="checked_in">Check-in</option>
        <option value="checked_out">Check-out</option>
        <option value="cancelled">Dibatalkan</option>
      </select>
    </div>

    <p v-if="actionError" class="text-clay text-sm mb-4">{{ actionError }}</p>
    <p v-if="loading" class="text-ink/50">Memuat...</p>

    <div v-else class="bg-white rounded-2xl border border-mist/30 overflow-hidden">
      <table class="w-full text-sm">
        <thead class="bg-sand text-ink/60 text-left">
          <tr>
            <th class="px-4 py-3">Kode</th>
            <th class="px-4 py-3">Tamu</th>
            <th class="px-4 py-3">Kamar</th>
            <th class="px-4 py-3">Tanggal</th>
            <th class="px-4 py-3">Total</th>
            <th class="px-4 py-3">Status</th>
            <th class="px-4 py-3">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="b in bookings"
            :key="b.id"
            class="border-t border-mist/20 cursor-pointer hover:bg-sand/50"
            @click="openDetail(b)"
          >
            <td class="px-4 py-3 font-medium">
              {{ b.booking_code }}
              <span
                v-if="b.rescheduled_at"
                class="block mt-1 w-fit px-2 py-0.5 rounded-full text-[10px] font-medium bg-brass/20 text-brass"
              >
                Direschedule
              </span>
            </td>
            <td class="px-4 py-3">
              {{ b.guest_name }}<br />
              <span class="text-ink/40 text-xs">{{ b.guest_phone }}</span>
            </td>
            <td class="px-4 py-3">{{ b.room?.name }}</td>
            <td class="px-4 py-3">{{ b.check_in }} &rarr; {{ b.check_out }}</td>
            <td class="px-4 py-3">{{ formatPrice(b.total_price) }}</td>
            <td class="px-4 py-3">
              <span class="px-3 py-1 rounded-full text-xs font-medium" :class="statusColor[b.status]">
                {{ statusLabel[b.status] }}
              </span>
            </td>
            <td class="px-4 py-3">
              <button
                v-if="nextActionMap[b.status]"
                @click.stop="doNextAction(b)"
                class="text-xs font-medium px-3 py-1.5 rounded-lg bg-pine text-sand hover:opacity-90"
              >
                {{ nextActionMap[b.status].label }}
              </button>
              <span v-else class="text-xs text-ink/40">Tidak ada aksi</span>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <BookingDetailModal :booking="selectedBooking" :is-admin="true" @close="closeDetail" />
  </div>
</template>