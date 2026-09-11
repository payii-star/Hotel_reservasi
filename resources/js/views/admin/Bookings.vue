<script setup>
import { ref, onMounted } from 'vue'
import api from '../../services/api'

const bookings = ref([])
const filter = ref('')
const loading = ref(false)

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

async function load() {
  loading.value = true
  const res = await api.get('/admin/bookings', { params: filter.value ? { status: filter.value } : {} })
  bookings.value = res.data
  loading.value = false
}

async function updateStatus(booking, status) {
  await api.patch(`/admin/bookings/${booking.id}/status`, { status })
  booking.status = status
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
          <tr v-for="b in bookings" :key="b.id" class="border-t border-mist/20">
            <td class="px-4 py-3 font-medium">{{ b.booking_code }}</td>
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
              <select :value="b.status" @change="updateStatus(b, $event.target.value)" class="border border-mist/40 rounded-lg px-2 py-1 text-xs">
                <option value="pending">Menunggu</option>
                <option value="confirmed">Konfirmasi</option>
                <option value="checked_in">Check-in</option>
                <option value="checked_out">Check-out</option>
                <option value="cancelled">Batalkan</option>
              </select>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>
