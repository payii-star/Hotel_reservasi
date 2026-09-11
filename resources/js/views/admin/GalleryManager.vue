<script setup>
import { ref, onMounted } from 'vue'
import api from '../../services/api'

const photos = ref([])
const title = ref('')
const category = ref('')
const file = ref(null)
const saving = ref(false)
const editingId = ref(null)
const selectedPhoto = ref(null)

const STORAGE_URL = 'http://127.0.0.1:8000/storage/'

function imageUrl(path) {
  return STORAGE_URL + path
}

async function load() {
  const res = await api.get('/gallery')
  photos.value = res.data
}

function onFileChange(e) {
  file.value = e.target.files[0]
}

function resetForm() {
  editingId.value = null
  title.value = ''
  category.value = ''
  file.value = null
}

function startEdit(photo) {
  editingId.value = photo.id
  title.value = photo.title
  category.value = photo.category || ''
  file.value = null
}

function cancelEdit() {
  resetForm()
}

async function submit() {
  saving.value = true
  const formData = new FormData()
  formData.append('title', title.value)
  formData.append('category', category.value)
  if (file.value) formData.append('image', file.value)

  try {
    if (editingId.value) {
      formData.append('_method', 'PUT')
      await api.post(`/admin/gallery/${editingId.value}`, formData, { headers: { 'Content-Type': 'multipart/form-data' } })
    } else {
      await api.post('/admin/gallery', formData, { headers: { 'Content-Type': 'multipart/form-data' } })
    }
    resetForm()
    await load()
  } finally {
    saving.value = false
  }
}

async function remove(photo) {
  if (!confirm('Hapus foto ini?')) return
  await api.delete(`/admin/gallery/${photo.id}`)
  await load()
}

function openPhoto(photo) {
  selectedPhoto.value = photo
}

function closePhoto() {
  selectedPhoto.value = null
}

onMounted(load)
</script>

<template>
  <div>
    <h1 class="font-display text-2xl text-ink mb-6">Kelola Galeri</h1>

    <form @submit.prevent="submit" class="bg-white border border-mist/30 rounded-2xl p-6 mb-8 grid sm:grid-cols-3 gap-4 items-end">
      <p v-if="editingId" class="sm:col-span-3 text-sm text-ink/60 -mb-2">Mengedit foto</p>
      <div>
        <label class="block text-sm text-ink/60 mb-1">Judul Foto</label>
        <input v-model="title" required class="w-full border border-mist/40 rounded-lg px-3 py-2" />
      </div>
      <div>
        <label class="block text-sm text-ink/60 mb-1">Kategori</label>
        <input v-model="category" placeholder="Lobby, Kolam Renang, dll" class="w-full border border-mist/40 rounded-lg px-3 py-2" />
      </div>
      <div>
        <label class="block text-sm text-ink/60 mb-1">
          Foto {{ editingId ? '(kosongkan jika tidak ganti)' : '' }}
        </label>
        <input type="file" accept="image/*" @change="onFileChange" :required="!editingId" class="w-full text-sm" />
      </div>
      <div class="sm:col-span-3 flex gap-3">
        <button type="submit" :disabled="saving" class="btn-primary flex-1 disabled:opacity-50">
          {{ saving ? 'Menyimpan...' : editingId ? 'Simpan Perubahan' : 'Tambah Foto' }}
        </button>
        <button v-if="editingId" type="button" @click="cancelEdit" class="px-5 py-2 rounded-full border border-mist/40 text-ink/60 text-sm hover:bg-mist/10">
          Batal
        </button>
      </div>
    </form>

    <div class="grid sm:grid-cols-3 md:grid-cols-4 gap-4">
      <div v-for="photo in photos" :key="photo.id" class="bg-white border border-mist/30 rounded-xl overflow-hidden">
        <button
          type="button"
          @click="openPhoto(photo)"
          class="aspect-square bg-pine/10 overflow-hidden block w-full cursor-zoom-in"
        >
          <img
            :src="imageUrl(photo.image_path)"
            :alt="photo.title"
            class="w-full h-full object-cover"
          />
        </button>
        <div class="p-3">
          <p class="text-sm text-ink mb-1 truncate">{{ photo.title }}</p>
          <div class="flex items-center justify-between">
            <span class="text-xs text-ink/50">{{ photo.category }}</span>
            <div class="flex gap-3">
              <button @click="startEdit(photo)" class="text-pine text-xs hover:underline">Edit</button>
              <button @click="remove(photo)" class="text-clay text-xs hover:underline">Hapus</button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Lightbox -->
    <div
      v-if="selectedPhoto"
      @click.self="closePhoto"
      class="fixed inset-0 bg-ink/90 z-50 flex items-center justify-center p-6"
    >
      <button
        @click="closePhoto"
        class="absolute top-6 right-6 text-sand text-3xl leading-none hover:text-brass"
        aria-label="Tutup"
      >
        &times;
      </button>
      <img
        :src="imageUrl(selectedPhoto.image_path)"
        :alt="selectedPhoto.title"
        class="max-w-full max-h-[85vh] rounded-xl"
      />
    </div>
  </div>
</template>