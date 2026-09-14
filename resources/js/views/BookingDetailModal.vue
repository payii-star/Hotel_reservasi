<script setup>
defineProps({
  booking: { type: Object, default: null },
  isAdmin: { type: Boolean, default: false },
})
defineEmits(['close'])

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

const monthNames = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember']

function formatPrice(price) {
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(price)
}

function formatDate(dateStr) {
  if (!dateStr) return '-'
  const [y, m, d] = String(dateStr).slice(0, 10).split('-').map(Number)
  return `${d} ${monthNames[m - 1]} ${y}`
}
</script>

<template>
  <Teleport to="body">
    <div v-if="booking" class="fixed inset-0 z-50 flex items-center justify-center p-4">
      <div class="absolute inset-0 bg-ink/50" @click="$emit('close')"></div>

      <div class="relative bg-white rounded-2xl max-w-lg w-full p-6 shadow-xl max-h-[90vh] overflow-y-auto">
        <button
          @click="$emit('close')"
          class="absolute top-4 right-4 text-ink/40 hover:text-ink text-xl leading-none"
          aria-label="Tutup"
        >
          &times;
        </button>

        <p class="text-xs uppercase tracking-widest text-mist mb-1">{{ booking.booking_code }}</p>
        <h2 class="font-display text-2xl mb-1">{{ booking.guest_name }}</h2>
        <p class="text-ink/60 text-sm mb-3"> {{ booking.room?.name }}</p>

        <div class="flex flex-wrap gap-2 mb-5">
          <span class="inline-block font-medium px-3 py-1 rounded-full text-xs" :class="statusColor[booking.status]">
            {{ statusLabel[booking.status] }}
          </span>
          <span
            v-if="booking.rescheduled_at"
            class="inline-block font-medium px-3 py-1 rounded-full text-xs bg-brass/20 text-brass"
          >
            Sudah direschedule
          </span>
        </div>

        <div class="space-y-4 text-sm">
          <div class="grid grid-cols-2 gap-3">
            <p><span class="text-ink/50 block text-xs">Telepon</span>{{ booking.guest_phone }}</p>
            <p><span class="text-ink/50 block text-xs">Email</span>{{ booking.guest_email }}</p>
          </div>

          <div class="border-t border-mist/20 pt-4 grid grid-cols-2 gap-3">
            <p><span class="text-ink/50 block text-xs">Check-in</span>{{ formatDate(booking.check_in) }}</p>
            <p><span class="text-ink/50 block text-xs">Check-out</span>{{ formatDate(booking.check_out) }}</p>
            <p><span class="text-ink/50 block text-xs">Jumlah Tamu</span>{{ booking.total_guest }} orang</p>
            <p><span class="text-ink/50 block text-xs">Total Harga</span><span class="font-semibold">{{ formatPrice(booking.total_price) }}</span></p>
          </div>

          <div v-if="booking.notes" class="border-t border-mist/20 pt-4">
            <span class="text-ink/50 block text-xs mb-1">Catatan</span>
            <p>{{ booking.notes }}</p>
          </div>

          <div v-if="isAdmin" class="border-t border-mist/20 pt-4 grid grid-cols-2 gap-3 text-xs">
            <p><span class="text-ink/50 block">ID Booking</span><span class="text-ink">{{ booking.id }}</span></p>
            <p><span class="text-ink/50 block">Order ID Midtrans</span><span class="text-ink">{{ booking.midtrans_order_id || '-' }}</span></p>
          </div>
        </div>
      </div>
    </div>
  </Teleport>
</template>