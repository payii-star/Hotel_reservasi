<script setup>
import { useRouter } from 'vue-router'
import api from '../../services/api'

const router = useRouter()

async function logout() {
  try {
    await api.post('/logout')
  } catch (e) {}
  localStorage.removeItem('admin_token')
  localStorage.removeItem('admin_name')
  router.push({ name: 'admin-login' })
}
</script>


<template>
  <div class="min-h-screen bg-sand">
    <aside class="fixed inset-y-0 left-0 w-64 bg-ink text-sand flex flex-col p-6 z-10">
      <h1 class="font-display text-xl mb-1">Grand Nusantara</h1>
      <p class="text-sand/50 text-xs mb-8">Panel Admin</p>

      <nav class="flex flex-col gap-2 flex-1 overflow-y-auto">
        <RouterLink to="/admin/booking" class="px-3 py-2 rounded-lg hover:bg-pine" active-class="bg-pine">
          Reservasi
        </RouterLink>
        <RouterLink to="/admin/kamar" class="px-3 py-2 rounded-lg hover:bg-pine" active-class="bg-pine">
          Kelola Kamar
        </RouterLink>
        <RouterLink to="/admin/galeri" class="px-3 py-2 rounded-lg hover:bg-pine" active-class="bg-pine">
          Galeri
        </RouterLink>
        <RouterLink to="/admin/profil" class="px-3 py-2 rounded-lg hover:bg-pine" active-class="bg-pine">
          Profil Hotel
        </RouterLink>
      </nav>

      <button @click="logout" class="text-sand/70 hover:text-brass text-sm text-left pt-4 border-t border-sand/10">
        Logout
      </button>
    </aside>

    <main class="ml-64 p-8">
      <RouterView />
    </main>
  </div>
</template>