<script setup>
defineOptions({ name: 'Gallery' })

import { ref, onMounted } from 'vue'
import api from '../services/api'

const photos = ref([])
const selectedPhoto = ref(null)

const STORAGE_URL = 'http://127.0.0.1:8000/storage/'

function imageUrl(path) {
  return STORAGE_URL + path
}

function openPhoto(photo) {
  selectedPhoto.value = photo
}

function closePhoto() {
  selectedPhoto.value = null
}

onMounted(async () => {
  if (photos.value.length === 0) {
    const res = await api.get('/gallery')
    photos.value = res.data
  }
})
</script>

<template>
  <div class="max-w-6xl mx-auto px-6 pt-28 pb-14">
    <h1 class="font-display text-4xl mb-2 text-ink">Galeri Hotel</h1>
    <p class="text-ink/60 mb-10">Lihat suasana dan fasilitas Grand Nusantara Hotel.</p>

    <p v-if="photos.length === 0" class="text-ink/50">Belum ada foto galeri.</p>

    <div v-else class="columns-2 md:columns-3 gap-4 space-y-4">
      <button
        v-for="photo in photos"
        :key="photo.id"
        type="button"
        @click="openPhoto(photo)"
        class="break-inside-avoid block w-full rounded-2xl overflow-hidden border border-mist/30 cursor-zoom-in relative group"
      >
        <img
          :src="imageUrl(photo.image_path)"
          :alt="photo.title"
          class="w-full h-auto block"
        />
        <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-ink/70 to-transparent px-4 py-3 opacity-0 group-hover:opacity-100 transition-opacity">
          <p class="text-sand text-sm font-medium text-left">{{ photo.title }}</p>
          <p v-if="photo.category" class="text-sand/70 text-xs text-left">{{ photo.category }}</p>
        </div>
      </button>
    </div>

    <!-- Lightbox -->
    <Transition name="lightbox-fade">
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
        <Transition name="lightbox-zoom" appear>
          <div :key="selectedPhoto.id" class="max-w-full max-h-[85vh] flex flex-col items-center">
            <img
              :src="imageUrl(selectedPhoto.image_path)"
              :alt="selectedPhoto.title"
              class="max-w-full max-h-[75vh] rounded-xl"
            />
            <div class="mt-4 text-center">
              <p class="text-sand font-medium">{{ selectedPhoto.title }}</p>
              <p v-if="selectedPhoto.category" class="text-sand/60 text-sm">{{ selectedPhoto.category }}</p>
            </div>
          </div>
        </Transition>
      </div>
    </Transition>
  </div>
</template>

<style scoped>
.lightbox-fade-enter-active,
.lightbox-fade-leave-active {
  transition: opacity 0.25s ease;
}
.lightbox-fade-enter-from,
.lightbox-fade-leave-to {
  opacity: 0;
}

.lightbox-zoom-enter-active {
  transition: transform 0.25s ease, opacity 0.25s ease;
}
.lightbox-zoom-leave-active {
  transition: transform 0.15s ease, opacity 0.15s ease;
}
.lightbox-zoom-enter-from,
.lightbox-zoom-leave-to {
  transform: scale(0.9);
  opacity: 0;
}
</style>