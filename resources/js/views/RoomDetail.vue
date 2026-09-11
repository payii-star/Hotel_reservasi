<script setup>
import { ref, onMounted, computed, watch } from 'vue'
import { useRoute } from 'vue-router'
import api from '../services/api'

const props = defineProps({ id: [String, Number] })
const route = useRoute()

const STORAGE_URL = 'http://127.0.0.1:8000/storage/'
function imageUrl(path) {
  return STORAGE_URL + path
}

const room = ref(null)
const submitting = ref(false)
const result = ref(null)
const errorMsg = ref('')

const availableUnits = ref(null)
const checkingStock = ref(false)

const form = ref({
  guest_name: '',
  guest_email: '',
  guest_phone: '',
  check_in: route.query.check_in || '',
  check_out: route.query.check_out || '',
  total_guest: 1,
  notes: '',
})

const nights = computed(() => {
  if (!form.value.check_in || !form.value.check_out) return 0
  const d1 = new Date(form.value.check_in)
  const d2 = new Date(form.value.check_out)
  const diff = (d2 - d1) / (1000 * 60 * 60 * 24)
  return diff > 0 ? diff : 0
})

const totalPrice = computed(() => nights.value * (room.value?.price || 0))

function formatPrice(price) {
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(price)
}

async function loadRoom() {
  const params = {}
  if (form.value.check_in && form.value.check_out) {
    params.check_in = form.value.check_in
    params.check_out = form.value.check_out
  }
  const res = await api.get(`/rooms/${props.id}`, { params })
  room.value = res.data
  availableUnits.value = res.data.available_units ?? null
}

watch([() => form.value.check_in, () => form.value.check_out], async ([checkIn, checkOut]) => {
  if (!checkIn || !checkOut) {
    availableUnits.value = null
    return
  }
  checkingStock.value = true
  try {
    const res = await api.get(`/rooms/${props.id}`, { params: { check_in: checkIn, check_out: checkOut } })
    availableUnits.value = res.data.available_units
  } catch (e) {
    console.error(e)
  } finally {
    checkingStock.value = false
  }
})

onMounted(loadRoom)

async function submitBooking() {
  errorMsg.value = ''
  submitting.value = true
  try {
    const res = await api.post('/bookings', {
      room_id: room.value.id,
      ...form.value,
    })
    result.value = res.data.booking
  } catch (e) {
    errorMsg.value = e.response?.data?.message || 'Terjadi kesalahan, coba lagi.'
  } finally {
    submitting.value = false
  }
}
</script>

<template>
  <div class="max-w-4xl mx-auto px-6 pt-28 pb-20" v-if="room">
    <div v-if="result" class="bg-pine text-sand rounded-2xl p-8 text-center">
      <h2 class="font-display text-2xl mb-2">Reservasi Berhasil!</h2>
      <p class="text-sand/80 mb-4">Simpan kode booking berikut untuk cek status reservasi Anda:</p>
      <p class="font-display text-3xl text-brass mb-6">{{ result.booking_code }}</p>
      <RouterLink :to="{ name: 'booking-status', params: { code: result.booking_code } }" class="btn-outline !border-sand !text-sand hover:!bg-sand hover:!text-pine">
        Cek Status Booking
      </RouterLink>
    </div>

    <template v-else>
      <RouterLink to="/kamar" class="text-pine text-sm hover:underline mb-6 inline-block">&larr; Kembali ke daftar kamar</RouterLink>

      <div class="aspect-[16/7] bg-pine/10 rounded-2xl overflow-hidden mb-4">
        <img
          v-if="room.main_image"
          :src="imageUrl(room.main_image)"
          :alt="room.name"
          class="w-full h-full object-cover"
        />
        <div v-else class="w-full h-full flex items-center justify-center text-mist text-sm">
          Foto {{ room.name }}
        </div>
      </div>

      <div v-if="room.images && room.images.length" class="flex gap-3 mb-8 overflow-x-auto">
        <img
          v-for="img in room.images"
          :key="img.id"
          :src="imageUrl(img.image_path)"
          class="w-24 h-24 object-cover rounded-lg border border-mist/30 flex-shrink-0"
        />
      </div>

      <p class="uppercase text-xs tracking-widest text-mist mb-1">{{ room.type }}</p>
      <h1 class="font-display text-3xl mb-3">{{ room.name }}</h1>
      <p class="text-ink/70 mb-4">{{ room.description }}</p>

      <div class="flex flex-wrap gap-2 mb-6">
        <span v-for="f in room.facilities" :key="f.id" class="text-xs bg-mist/10 text-ink/70 px-3 py-1 rounded-full">
          {{ f.name }}
        </span>
      </div>

      <div class="mb-10">
        <p class="text-brass font-semibold text-xl mb-3">
          {{ formatPrice(room.price) }} <span class="text-ink/50 font-normal text-sm">/ malam</span>
        </p>

        <div class="inline-flex items-center gap-2 text-sm px-4 py-2 rounded-xl border" :class="
          checkingStock ? 'border-mist/30 text-ink/40 bg-white' :
          availableUnits === null ? 'border-mist/30 text-ink/60 bg-white' :
          availableUnits > 0 ? 'border-pine/20 text-pine bg-pine/5' : 'border-clay/20 text-clay bg-clay/5'
        ">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="flex-shrink-0">
            <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" />
            <polyline points="9 22 9 12 15 12 15 22" />
          </svg>

          <span v-if="checkingStock">Mengecek ketersediaan...</span>
          <span v-else-if="availableUnits === null">
            <strong>{{ room.total_unit }}</strong> dari {{ room.total_unit }} kamar tersedia
          </span>
          <span v-else-if="availableUnits > 0">
            <strong>{{ availableUnits }}</strong> dari {{ room.total_unit }} kamar tersedia
          </span>
          <span v-else>
            <strong>0</strong> dari {{ room.total_unit }} kamar tersedia
          </span>
        </div>

        <p v-if="availableUnits === null" class="text-xs text-ink/40 mt-1.5 ml-1">
          Pilih tanggal check-in & check-out untuk cek ketersediaan akurat
        </p>
      </div>

      <div class="border border-mist/30 rounded-2xl p-6 md:p-8">
        <h2 class="font-display text-2xl mb-6">Form Reservasi</h2>
        <p v-if="errorMsg" class="text-clay mb-4 text-sm">{{ errorMsg }}</p>

        <form @submit.prevent="submitBooking" class="grid sm:grid-cols-2 gap-5">
          <div>
            <label class="block text-sm text-ink/60 mb-1">Nama Lengkap</label>
            <input v-model="form.guest_name" type="text" required class="w-full border border-mist/40 rounded-lg px-3 py-2" />
          </div>
          <div>
            <label class="block text-sm text-ink/60 mb-1">Email</label>
            <input v-model="form.guest_email" type="email" required class="w-full border border-mist/40 rounded-lg px-3 py-2" />
          </div>
          <div>
            <label class="block text-sm text-ink/60 mb-1">No. HP</label>
            <input v-model="form.guest_phone" type="tel" required class="w-full border border-mist/40 rounded-lg px-3 py-2" />
          </div>
          <div>
            <label class="block text-sm text-ink/60 mb-1">Jumlah Tamu</label>
            <input v-model.number="form.total_guest" type="number" min="1" :max="room.capacity" required class="w-full border border-mist/40 rounded-lg px-3 py-2" />
          </div>
          <div>
            <label class="block text-sm text-ink/60 mb-1">Check-in</label>
            <input v-model="form.check_in" type="date" required class="w-full border border-mist/40 rounded-lg px-3 py-2" />
          </div>
          <div>
            <label class="block text-sm text-ink/60 mb-1">Check-out</label>
            <input v-model="form.check_out" type="date" required class="w-full border border-mist/40 rounded-lg px-3 py-2" />
          </div>
          <div class="sm:col-span-2">
            <label class="block text-sm text-ink/60 mb-1">Catatan (opsional)</label>
            <textarea v-model="form.notes" rows="3" class="w-full border border-mist/40 rounded-lg px-3 py-2"></textarea>
          </div>

          <div v-if="nights > 0" class="sm:col-span-2 bg-sand rounded-lg p-4 flex justify-between text-sm">
            <span>{{ nights }} malam</span>
            <span class="font-semibold">{{ formatPrice(totalPrice) }}</span>
          </div>

          <p v-if="availableUnits === 0" class="sm:col-span-2 text-clay text-sm">
            Maaf, kamar ini penuh di tanggal yang dipilih. Coba ganti tanggal.
          </p>

          <button
            type="submit"
            :disabled="submitting || availableUnits === 0"
            class="btn-primary sm:col-span-2 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            {{ submitting ? 'Memproses...' : 'Konfirmasi Reservasi' }}
          </button>
        </form>
      </div>
    </template>
  </div>
</template>