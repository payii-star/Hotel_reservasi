<script setup>
import { ref, onMounted } from 'vue'
import api from '../../services/api'

const rooms = ref([])
const showForm = ref(false)
const editingId = ref(null)
const form = ref({ name: '', type: '', description: '', capacity: 2, price: 0, total_unit: 1, facilities: '', main_image: null })
const saving = ref(false)
const uploadingGallery = ref({})

const STORAGE_URL = 'http://127.0.0.1:8000/storage/'

function imageUrl(path) {
  return STORAGE_URL + path
}

async function load() {
  const res = await api.get('/admin/rooms')
  rooms.value = res.data
}

function onMainImageChange(e) {
  form.value.main_image = e.target.files[0] || null
}

function resetForm() {
  editingId.value = null
  form.value = { name: '', type: '', description: '', capacity: 2, price: 0, total_unit: 1, facilities: '', main_image: null }
}

function startAdd() {
  resetForm()
  showForm.value = true
}

function startEdit(room) {
  editingId.value = room.id
  form.value = {
    name: room.name,
    type: room.type,
    description: room.description || '',
    capacity: room.capacity,
    price: room.price,
    total_unit: room.total_unit,
    facilities: (room.facilities || []).map((f) => (typeof f === 'string' ? f : f.name)).join(', '),
    main_image: null,
  }
  showForm.value = true
}

function cancelForm() {
  resetForm()
  showForm.value = false
}

async function submit() {
  saving.value = true
  try {
    const formData = new FormData()
    formData.append('name', form.value.name)
    formData.append('type', form.value.type)
    formData.append('description', form.value.description)
    formData.append('capacity', form.value.capacity)
    formData.append('price', form.value.price)
    formData.append('total_unit', form.value.total_unit)

    const facilitiesArr = form.value.facilities.split(',').map((f) => f.trim()).filter(Boolean)
    facilitiesArr.forEach((f) => formData.append('facilities[]', f))

    if (form.value.main_image) {
      formData.append('main_image', form.value.main_image)
    }

    if (editingId.value) {
      formData.append('_method', 'PUT')
      await api.post(`/admin/rooms/${editingId.value}`, formData, { headers: { 'Content-Type': 'multipart/form-data' } })
    } else {
      await api.post('/admin/rooms', formData, { headers: { 'Content-Type': 'multipart/form-data' } })
    }

    showForm.value = false
    resetForm()
    await load()
  } finally {
    saving.value = false
  }
}

async function remove(room) {
  if (!confirm(`Hapus kamar "${room.name}"?`)) return
  await api.delete(`/admin/rooms/${room.id}`)
  await load()
}

async function onGalleryFileChange(e, room) {
  const file = e.target.files[0]
  if (!file) return

  uploadingGallery.value[room.id] = true
  try {
    const formData = new FormData()
    formData.append('image', file)
    await api.post(`/admin/rooms/${room.id}/images`, formData, { headers: { 'Content-Type': 'multipart/form-data' } })
    await load()
  } finally {
    uploadingGallery.value[room.id] = false
    e.target.value = ''
  }
}

function formatPrice(price) {
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(price)
}

onMounted(load)
</script>

<template>
  <div>
    <div class="flex items-center justify-between mb-6">
      <h1 class="font-display text-2xl text-ink">Kelola Kamar</h1>
      <button @click="showForm ? cancelForm() : startAdd()" class="btn-primary !py-2 text-sm">
        {{ showForm ? 'Tutup' : '+ Tambah Kamar' }}
      </button>
    </div>

    <form v-if="showForm" @submit.prevent="submit" class="bg-white border border-mist/30 rounded-2xl p-6 mb-8 grid sm:grid-cols-2 gap-4">
      <p class="sm:col-span-2 text-sm text-ink/60 -mb-2">
        {{ editingId ? 'Mengedit kamar' : 'Menambah kamar baru' }}
      </p>
      <div>
        <label class="block text-sm text-ink/60 mb-1">Nama Kamar</label>
        <input v-model="form.name" required class="w-full border border-mist/40 rounded-lg px-3 py-2" />
      </div>
      <div>
        <label class="block text-sm text-ink/60 mb-1">Tipe</label>
        <input v-model="form.type" required class="w-full border border-mist/40 rounded-lg px-3 py-2" />
      </div>
      <div>
        <label class="block text-sm text-ink/60 mb-1">Harga / malam</label>
        <input v-model.number="form.price" type="number" required class="w-full border border-mist/40 rounded-lg px-3 py-2" />
      </div>
      <div>
        <label class="block text-sm text-ink/60 mb-1">Kapasitas</label>
        <input v-model.number="form.capacity" type="number" required class="w-full border border-mist/40 rounded-lg px-3 py-2" />
      </div>
      <div>
        <label class="block text-sm text-ink/60 mb-1">Jumlah Unit</label>
        <input v-model.number="form.total_unit" type="number" required class="w-full border border-mist/40 rounded-lg px-3 py-2" />
      </div>
      <div>
        <label class="block text-sm text-ink/60 mb-1">Fasilitas (pisah koma)</label>
        <input v-model="form.facilities" placeholder="AC, WiFi, TV" class="w-full border border-mist/40 rounded-lg px-3 py-2" />
      </div>
      <div class="sm:col-span-2">
        <label class="block text-sm text-ink/60 mb-1">
          Foto Utama {{ editingId ? '(kosongkan jika tidak ingin ganti)' : '' }}
        </label>
        <input type="file" accept="image/*" @change="onMainImageChange" class="w-full text-sm" />
      </div>
      <div class="sm:col-span-2">
        <label class="block text-sm text-ink/60 mb-1">Deskripsi</label>
        <textarea v-model="form.description" rows="2" class="w-full border border-mist/40 rounded-lg px-3 py-2"></textarea>
      </div>
      <div class="sm:col-span-2 flex gap-3">
        <button type="submit" :disabled="saving" class="btn-primary flex-1 disabled:opacity-50">
          {{ saving ? 'Menyimpan...' : editingId ? 'Simpan Perubahan' : 'Simpan Kamar' }}
        </button>
        <button type="button" @click="cancelForm" class="px-5 py-2 rounded-full border border-mist/40 text-ink/60 text-sm hover:bg-mist/10">
          Batal
        </button>
      </div>
    </form>

    <div class="grid sm:grid-cols-2 md:grid-cols-3 gap-5">
      <div v-for="room in rooms" :key="room.id" class="bg-white border border-mist/30 rounded-2xl overflow-hidden">
        <div class="aspect-[4/3] bg-pine/10 overflow-hidden">
          <img
            v-if="room.main_image"
            :src="imageUrl(room.main_image)"
            :alt="room.name"
            class="w-full h-full object-cover"
          />
          <div v-else class="w-full h-full flex items-center justify-center text-mist text-xs">
            Belum ada foto utama
          </div>
        </div>

        <div class="p-5">
          <p class="uppercase text-xs tracking-widest text-mist mb-1">{{ room.type }}</p>
          <h3 class="font-display text-lg mb-1">{{ room.name }}</h3>
          <p class="text-brass font-semibold mb-1">{{ formatPrice(room.price) }} / malam</p>
          <p class="text-xs text-ink/50 mb-3">{{ room.total_unit }} unit &middot; kapasitas {{ room.capacity }} orang</p>

          <div v-if="room.images && room.images.length" class="flex gap-2 mb-3 overflow-x-auto">
            <img
              v-for="img in room.images"
              :key="img.id"
              :src="imageUrl(img.image_path)"
              class="w-14 h-14 object-cover rounded-lg border border-mist/30 flex-shrink-0"
            />
          </div>

          <div class="flex items-center justify-between mb-2">
            <label class="text-pine text-sm hover:underline cursor-pointer">
              {{ uploadingGallery[room.id] ? 'Mengunggah...' : '+ Tambah Foto Galeri' }}
              <input type="file" accept="image/*" class="hidden" @change="onGalleryFileChange($event, room)" />
            </label>
          </div>

          <div class="flex items-center justify-between">
            <button @click="startEdit(room)" class="text-pine text-sm hover:underline">Edit</button>
            <button @click="remove(room)" class="text-clay text-sm hover:underline">Hapus</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>