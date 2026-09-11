<script setup>
defineOptions({ name: 'Home' })

import { ref, onMounted } from 'vue'
import api from '../services/api'

const hotelInfo = ref(null)
const rooms = ref([])

const STORAGE_URL = 'http://127.0.0.1:8000/storage/'

function imageUrl(path) {
  return STORAGE_URL + path
}

function formatPrice(price) {
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(price)
}

function observeReveal() {
  const observer = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          entry.target.classList.add('reveal-visible')
          observer.unobserve(entry.target)
        }
      })
    },
    { threshold: 0.15 }
  )
  document.querySelectorAll('.reveal').forEach((el) => observer.observe(el))
}

onMounted(async () => {
  if (!hotelInfo.value) {
    try {
      const [infoRes, roomsRes] = await Promise.all([
        api.get('/hotel-info'),
        api.get('/rooms'),
      ])
      hotelInfo.value = infoRes.data
      rooms.value = roomsRes.data.slice(0, 3)
    } catch (e) {
      console.error(e)
    }
  }

  observeReveal()
})
</script>

<template>
  <div>
    <!-- Hero -->
    <section class="relative h-screen min-h-[600px] flex items-center bg-pine text-sand overflow-hidden">
      <div class="absolute inset-0">
        <img
          v-if="hotelInfo?.main_photo"
          :src="imageUrl(hotelInfo.main_photo)"
          alt="Foto Utama Hotel"
          class="w-full h-full object-cover"
        />
        <div class="absolute inset-0 bg-ink/50"></div>
        <div class="absolute inset-x-0 bottom-0 h-40 bg-gradient-to-b from-transparent to-sand"></div>
      </div>

      <div class="relative max-w-3xl mx-auto px-6 py-24 text-center">
        <p class="uppercase tracking-widest text-brass text-sm mb-4">{{ hotelInfo?.welcome_text || 'Selamat Datang' }}</p>
        <h1 class="font-display text-5xl leading-tight mb-6">
          {{ hotelInfo?.hotel_name || 'Grand Nusantara Hotel' }}
        </h1>
        <p class="text-sand/90 text-lg mb-8">
          {{ hotelInfo?.description || 'Tempat menginap nyaman untuk liburan maupun perjalanan bisnis Anda.' }}
        </p>
        <RouterLink to="/kamar" class="inline-block bg-brass text-ink px-7 py-3 rounded-full font-medium hover:bg-sand transition-colors">
          Lihat Kamar & Pesan
        </RouterLink>
      </div>
    </section>

    <!-- Kamar unggulan -->
    <section class="max-w-6xl mx-auto px-6 py-20 reveal">
      <div class="flex items-end justify-between mb-10">
        <h2 class="font-display text-3xl text-ink">Kamar Pilihan</h2>
        <RouterLink to="/kamar" class="text-pine font-medium hover:underline">Lihat semua &rarr;</RouterLink>
      </div>
      <div class="grid sm:grid-cols-2 md:grid-cols-3 gap-6">
        <RouterLink
          v-for="room in rooms"
          :key="room.id"
          :to="{ name: 'room-detail', params: { id: room.id } }"
          class="border border-mist/30 rounded-2xl overflow-hidden hover:shadow-lg transition-shadow group"
        >
          <div class="aspect-[4/3] bg-pine/10 overflow-hidden">
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
          <div class="p-5">
            <p class="uppercase text-xs tracking-widest text-mist mb-1">{{ room.type }}</p>
            <h3 class="font-display text-xl mb-2 group-hover:text-pine">{{ room.name }}</h3>
            <p class="text-brass font-semibold">{{ formatPrice(room.price) }} <span class="text-ink/50 font-normal text-sm">/ malam</span></p>
          </div>
        </RouterLink>
      </div>
    </section>

    <!-- Kenapa pilih kami -->
    <section class="bg-sand border-y border-mist/30 reveal">
      <div class="max-w-6xl mx-auto px-6 py-20 grid sm:grid-cols-3 gap-8 text-center">
        <div>
          <p class="font-display text-2xl text-pine mb-2">Lokasi Strategis</p>
          <p class="text-ink/70 text-sm">{{ hotelInfo?.address || 'Alamat hotel belum diisi.' }}</p>
        </div>
        <div>
          <p class="font-display text-2xl text-pine mb-2">Fasilitas Lengkap</p>
          <p class="text-ink/70 text-sm">{{ hotelInfo?.facilities_text || 'WiFi, sarapan, kolam renang, dan lainnya di setiap kelas kamar.' }}</p>
        </div>
        <div>
          <p class="font-display text-2xl text-pine mb-2">Booking Mudah</p>
          <p class="text-ink/70 text-sm">{{ hotelInfo?.booking_text || 'Reservasi online tanpa ribet, konfirmasi cepat dari tim kami.' }}</p>
        </div>
      </div>
    </section>
  </div>
</template>

<style scoped>
.reveal {
  opacity: 0;
  transform: translateY(24px);
  transition: opacity 0.7s ease, transform 0.7s ease;
}
.reveal-visible {
  opacity: 1;
  transform: translateY(0);
}
</style>