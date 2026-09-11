<script setup>
import { ref, onMounted } from 'vue'
import api from '../services/api'

const rooms = ref([])
const checkIn = ref('')
const checkOut = ref('')
const loading = ref(false)
const searched = ref(false)

function formatPrice(price) {
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(price)
}

async function loadRooms() {
  loading.value = true
  try {
    const params = {}
    if (checkIn.value && checkOut.value) {
      params.check_in = checkIn.value
      params.check_out = checkOut.value
      searched.value = true
    }
    const res = await api.get('/rooms', { params })
    rooms.value = res.data
  } catch (e) {
    console.error(e)
  } finally {
    loading.value = false
  }
}

onMounted(loadRooms)
</script>

<template>
  <div class="max-w-6xl mx-auto px-6 pt-28 pb-14">
    <h1 class="font-display text-4xl mb-2 text-ink">Pilihan Kamar</h1>
    <p class="text-ink/60 mb-8">Cek ketersediaan kamar sesuai tanggal menginap Anda.</p>

    <form @submit.prevent="loadRooms" class="flex flex-wrap gap-4 items-end bg-white border border-mist/30 rounded-2xl p-6 mb-10">
      <div>
        <label class="block text-sm text-ink/60 mb-1">Check-in</label>
        <input v-model="checkIn" type="date" class="border border-mist/40 rounded-lg px-3 py-2" required />
      </div>
      <div>
        <label class="block text-sm text-ink/60 mb-1">Check-out</label>
        <input v-model="checkOut" type="date" class="border border-mist/40 rounded-lg px-3 py-2" required />
      </div>
      <button type="submit" class="btn-primary !py-2">Cek Ketersediaan</button>
    </form>

    <p v-if="loading" class="text-ink/50">Memuat kamar...</p>
    <p v-else-if="rooms.length === 0" class="text-ink/50">
      {{ searched ? 'Tidak ada kamar tersedia di tanggal tersebut.' : 'Belum ada kamar.' }}
    </p>

    <div v-else class="grid sm:grid-cols-2 md:grid-cols-3 gap-6">
      <RouterLink
        v-for="room in rooms"
        :key="room.id"
        :to="{ name: 'room-detail', params: { id: room.id }, query: { check_in: checkIn, check_out: checkOut } }"
        class="border border-mist/30 rounded-2xl overflow-hidden hover:shadow-lg transition-shadow group"
      >
        <div class="aspect-[4/3] bg-pine/10 overflow-hidden">
          <img
            v-if="room.main_image"
            :src="`http://127.0.0.1:8000/storage/${room.main_image}`"
            :alt="room.name"
            class="w-full h-full object-cover group-hover:scale-105 transition-transform"
          />
          <div v-else class="w-full h-full flex items-center justify-center text-mist text-sm">
            Foto {{ room.name }}
          </div>
        </div>
        <div class="p-5">
          <p class="uppercase text-xs tracking-widest text-mist mb-1">{{ room.type }}</p>
          <h3 class="font-display text-xl mb-1 group-hover:text-pine">{{ room.name }}</h3>
          <p v-if="room.available_units !== undefined" class="text-xs text-mist mb-2">
            Sisa {{ room.available_units }} kamar
          </p>
          <p class="text-brass font-semibold">{{ formatPrice(room.price) }} <span class="text-ink/50 font-normal text-sm">/ malam</span></p>
        </div>
      </RouterLink>
    </div>
  </div>
</template>