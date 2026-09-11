<script setup>
import { ref, onMounted } from 'vue'
import api from '../../services/api'

const form = ref({
  hotel_name: '',
  welcome_text: '',
  description: '',
  address: '',
  phone: '',
  email: '',
  facilities_text: '',
  booking_text: '',
})
const logoFile = ref(null)
const mainPhotoFile = ref(null)
const saving = ref(false)
const savedMsg = ref('')

async function load() {
  const res = await api.get('/hotel-info')
  if (res.data) {
    form.value = {
      hotel_name: res.data.hotel_name,
      welcome_text: res.data.welcome_text || '',
      description: res.data.description,
      address: res.data.address,
      phone: res.data.phone,
      email: res.data.email,
      facilities_text: res.data.facilities_text || '',
      booking_text: res.data.booking_text || '',
    }
  }
}

function onLogoChange(e) {
  logoFile.value = e.target.files[0]
}
function onMainPhotoChange(e) {
  mainPhotoFile.value = e.target.files[0]
}

async function submit() {
  saving.value = true
  savedMsg.value = ''
  const formData = new FormData()
  Object.entries(form.value).forEach(([key, val]) => formData.append(key, val))
  if (logoFile.value) formData.append('logo', logoFile.value)
  if (mainPhotoFile.value) formData.append('main_photo', mainPhotoFile.value)
  formData.append('_method', 'PUT')

  try {
    await api.post('/admin/hotel-info', formData, { headers: { 'Content-Type': 'multipart/form-data' } })
    savedMsg.value = 'Profil hotel berhasil disimpan.'
  } finally {
    saving.value = false
  }
}

onMounted(load)
</script>

<template>
  <div class="max-w-2xl">
    <h1 class="font-display text-2xl text-ink mb-6">Profil Hotel</h1>
    <p class="text-ink/60 text-sm mb-6">Informasi ini tampil di halaman Beranda situs.</p>

    <p v-if="savedMsg" class="text-pine bg-pine/10 rounded-lg px-4 py-2 mb-6 text-sm">{{ savedMsg }}</p>

    <form @submit.prevent="submit" class="bg-white border border-mist/30 rounded-2xl p-6 space-y-5">
      <div>
        <label class="block text-sm text-ink/60 mb-1">Nama Hotel</label>
        <input v-model="form.hotel_name" required class="w-full border border-mist/40 rounded-lg px-3 py-2" />
      </div>
      <div>
        <label class="block text-sm text-ink/60 mb-1">Teks "Selamat Datang" (di Hero)</label>
        <input v-model="form.welcome_text" placeholder="Selamat Datang" class="w-full border border-mist/40 rounded-lg px-3 py-2" />
      </div>
      <div>
        <label class="block text-sm text-ink/60 mb-1">Deskripsi Singkat (di Hero)</label>
        <textarea v-model="form.description" rows="3" required class="w-full border border-mist/40 rounded-lg px-3 py-2"></textarea>
      </div>
      <div>
        <label class="block text-sm text-ink/60 mb-1">Alamat</label>
        <input v-model="form.address" required class="w-full border border-mist/40 rounded-lg px-3 py-2" />
        <p class="text-xs text-ink/40 mt-1">Muncul di kartu "Lokasi Strategis" di Beranda</p>
      </div>
      <div class="grid sm:grid-cols-2 gap-5">
        <div>
          <label class="block text-sm text-ink/60 mb-1">No. Telepon</label>
          <input v-model="form.phone" required class="w-full border border-mist/40 rounded-lg px-3 py-2" />
        </div>
        <div>
          <label class="block text-sm text-ink/60 mb-1">Email</label>
          <input v-model="form.email" type="email" required class="w-full border border-mist/40 rounded-lg px-3 py-2" />
        </div>
      </div>

      <hr class="border-mist/20" />
      <p class="text-sm font-medium text-ink">Bagian "Kenapa Pilih Kami" di Beranda</p>

      <div>
        <label class="block text-sm text-ink/60 mb-1">Teks Fasilitas Lengkap</label>
        <textarea v-model="form.facilities_text" rows="2" placeholder="WiFi, sarapan, kolam renang, dll..." class="w-full border border-mist/40 rounded-lg px-3 py-2"></textarea>
      </div>
      <div>
        <label class="block text-sm text-ink/60 mb-1">Teks Booking Mudah</label>
        <textarea v-model="form.booking_text" rows="2" placeholder="Reservasi online tanpa ribet..." class="w-full border border-mist/40 rounded-lg px-3 py-2"></textarea>
      </div>

      <hr class="border-mist/20" />

      <div>
        <label class="block text-sm text-ink/60 mb-1">Logo (opsional)</label>
        <input type="file" accept="image/*" @change="onLogoChange" class="w-full text-sm" />
      </div>
      <div>
        <label class="block text-sm text-ink/60 mb-1">Foto Utama (Hero Beranda)</label>
        <input type="file" accept="image/*" @change="onMainPhotoChange" class="w-full text-sm" />
      </div>

      <button type="submit" :disabled="saving" class="btn-primary disabled:opacity-50">
        {{ saving ? 'Menyimpan...' : 'Simpan Profil' }}
      </button>
    </form>
  </div>
</template>