<script setup>
import { computed, ref, onMounted, onUnmounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'

const route = useRoute()
const router = useRouter()
const isHome = computed(() => route.path === '/')

const scrolled = ref(false)
const hidden = ref(false)
let lastScrollY = 0

function onScroll() {
  const currentY = window.scrollY
  scrolled.value = currentY > 60

  if (currentY > lastScrollY && currentY > 120) {
    hidden.value = true
  } else {
    hidden.value = false
  }

  lastScrollY = currentY
}

onMounted(() => {
  window.addEventListener('scroll', onScroll)
  onScroll()
})

onUnmounted(() => {
  window.removeEventListener('scroll', onScroll)
})

const showDark = computed(() => !isHome.value || scrolled.value)

function isActive(path) {
  if (path === '/') return route.path === '/'
  return route.path.startsWith(path)
}

const guestUser = ref(JSON.parse(localStorage.getItem('guest_user') || 'null'))
const isLoggedIn = ref(!!localStorage.getItem('guest_token'))
const showProfileMenu = ref(false)
const showMobileMenu = ref(false)

function logout() {
  localStorage.removeItem('guest_token')
  localStorage.removeItem('guest_user')
  guestUser.value = null
  isLoggedIn.value = false
  showProfileMenu.value = false
  showMobileMenu.value = false
  router.push('/')
}

router.afterEach(() => {
  guestUser.value = JSON.parse(localStorage.getItem('guest_user') || 'null')
  isLoggedIn.value = !!localStorage.getItem('guest_token')
  showMobileMenu.value = false
})
</script>

<template>
  <div class="min-h-screen flex flex-col">
    <header
      v-if="!route.path.startsWith('/admin')"
      class="fixed top-0 left-0 right-0 z-30 transition-all duration-300"
      :class="[showDark || showMobileMenu ? 'bg-sand/95 backdrop-blur-sm shadow-sm' : 'bg-transparent', hidden && !showMobileMenu ? '-translate-y-full' : 'translate-y-0']"
    >
      <nav class="max-w-6xl mx-auto flex sm:grid sm:grid-cols-[1fr_auto_1fr] justify-between items-center gap-4 px-6 transition-all duration-300" :class="scrolled ? 'py-3' : 'py-5'">
        <!-- KIRI: logo -->
        <RouterLink
          to="/"
          class="font-display text-2xl font-semibold transition-colors duration-300 justify-self-start"
          :class="showDark || showMobileMenu ? 'text-pine' : 'text-white drop-shadow-md'"
        >
          Grand Nusantara
        </RouterLink>

        <!-- TENGAH: menu desktop, selalu center -->
        <div
          class="hidden sm:flex items-center gap-1 font-medium transition-colors duration-300 justify-self-center"
          :class="showDark ? 'text-ink/80' : 'text-white drop-shadow-md'"
        >
          <RouterLink
            to="/"
            class="px-3 py-1.5 rounded-full transition-colors whitespace-nowrap"
            :class="isActive('/') ? (showDark ? 'bg-pine/10 text-pine font-semibold' : 'bg-white/20 text-brass font-semibold') : (showDark ? 'hover:bg-pine/10 hover:text-pine' : 'hover:bg-white/15 hover:text-brass')"
          >
            Beranda
          </RouterLink>
          <RouterLink
            to="/kamar"
            class="px-3 py-1.5 rounded-full transition-colors whitespace-nowrap"
            :class="isActive('/kamar') ? (showDark ? 'bg-pine/10 text-pine font-semibold' : 'bg-white/20 text-brass font-semibold') : (showDark ? 'hover:bg-pine/10 hover:text-pine' : 'hover:bg-white/15 hover:text-brass')"
          >
            Kamar
          </RouterLink>
          <RouterLink
            to="/galeri"
            class="px-3 py-1.5 rounded-full transition-colors whitespace-nowrap"
            :class="isActive('/galeri') ? (showDark ? 'bg-pine/10 text-pine font-semibold' : 'bg-white/20 text-brass font-semibold') : (showDark ? 'hover:bg-pine/10 hover:text-pine' : 'hover:bg-white/15 hover:text-brass')"
          >
            Galeri
          </RouterLink>
          <RouterLink
            to="/cek-booking"
            class="px-3 py-1.5 rounded-full transition-colors whitespace-nowrap"
            :class="isActive('/cek-booking') ? (showDark ? 'bg-pine/10 text-pine font-semibold' : 'bg-white/20 text-brass font-semibold') : (showDark ? 'hover:bg-pine/10 hover:text-pine' : 'hover:bg-white/15 hover:text-brass')"
          >
            Cek Booking
          </RouterLink>
        </div>

        <!-- KANAN: tombol pesan/profil (desktop) + hamburger (mobile) -->
        <div class="justify-self-end flex items-center gap-3">
          <!-- Desktop: belum login -->
          <RouterLink v-if="!isLoggedIn" to="/login" class="hidden sm:inline-block btn-primary text-sm !py-2 !px-5 whitespace-nowrap">Masuk</RouterLink>

          <!-- Desktop: sudah login -->
          <div v-else class="hidden sm:block relative">
            <button
              @click="showProfileMenu = !showProfileMenu"
              class="w-10 h-10 rounded-full bg-pine text-sand flex items-center justify-center hover:bg-ink transition-colors"
            >
              <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M20 21a8 8 0 0 0-16 0" />
                <circle cx="12" cy="7" r="5" />
              </svg>
            </button>

            <Transition name="menu-fade">
              <div v-if="showProfileMenu" @click="showProfileMenu = false" class="fixed inset-0 z-10"></div>
            </Transition>
            <Transition name="menu-fade">
              <div v-if="showProfileMenu" class="absolute right-0 mt-3 w-48 bg-white rounded-xl shadow-lg border border-mist/20 py-2 z-20">
                <p class="px-4 py-2 text-sm text-ink/50 truncate border-b border-mist/20">{{ guestUser?.email }}</p>
                <RouterLink to="/profil" @click="showProfileMenu = false" class="block px-4 py-2 text-sm text-ink hover:bg-sand">Profil Saya</RouterLink>
                <RouterLink to="/kamar" @click="showProfileMenu = false" class="block px-4 py-2 text-sm text-ink hover:bg-sand">Pesan Kamar</RouterLink>
                <button @click="logout" class="block w-full text-left px-4 py-2 text-sm text-clay hover:bg-sand">Keluar</button>
              </div>
            </Transition>
          </div>

          <!-- Hamburger: cuma muncul di mobile -->
          <button
            @click="showMobileMenu = !showMobileMenu"
            class="sm:hidden w-10 h-10 flex items-center justify-center rounded-full transition-colors"
            :class="showDark || showMobileMenu ? 'text-ink hover:bg-ink/10' : 'text-white hover:bg-white/10'"
          >
            <svg v-if="!showMobileMenu" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <line x1="3" y1="6" x2="21" y2="6" />
              <line x1="3" y1="12" x2="21" y2="12" />
              <line x1="3" y1="18" x2="21" y2="18" />
            </svg>
            <svg v-else xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <line x1="18" y1="6" x2="6" y2="18" />
              <line x1="6" y1="6" x2="18" y2="18" />
            </svg>
          </button>
        </div>
      </nav>

      <!-- PANEL MENU MOBILE -->
      <Transition name="mobile-menu">
        <div v-if="showMobileMenu" class="sm:hidden bg-sand border-t border-mist/20 px-6 py-4">
          <div class="flex flex-col gap-1 text-ink font-medium mb-4">
            <RouterLink to="/" @click="showMobileMenu = false" class="py-2.5 border-b border-mist/15" :class="isActive('/') ? 'text-pine font-semibold' : ''">Beranda</RouterLink>
            <RouterLink to="/kamar" @click="showMobileMenu = false" class="py-2.5 border-b border-mist/15" :class="isActive('/kamar') ? 'text-pine font-semibold' : ''">Kamar</RouterLink>
            <RouterLink to="/galeri" @click="showMobileMenu = false" class="py-2.5 border-b border-mist/15" :class="isActive('/galeri') ? 'text-pine font-semibold' : ''">Galeri</RouterLink>
            <RouterLink to="/cek-booking" @click="showMobileMenu = false" class="py-2.5 border-b border-mist/15" :class="isActive('/cek-booking') ? 'text-pine font-semibold' : ''">Cek Booking</RouterLink>
            <RouterLink v-if="isLoggedIn" to="/profil" @click="showMobileMenu = false" class="py-2.5 border-b border-mist/15" :class="isActive('/profil') ? 'text-pine font-semibold' : ''">Profil Saya</RouterLink>
          </div>

          <RouterLink v-if="!isLoggedIn" to="/login" @click="showMobileMenu = false" class="btn-primary w-full text-center block text-sm !py-2.5">
            Masuk
          </RouterLink>
          <button v-else @click="logout" class="w-full text-center text-clay font-medium py-2.5 border border-clay/30 rounded-full">
            Keluar
          </button>
        </div>
      </Transition>
    </header>

    <main class="flex-1">
      <RouterView v-slot="{ Component }">
        <keep-alive :include="['Home', 'Gallery']">
          <component :is="Component" />
        </keep-alive>
      </RouterView>
    </main>

    <footer v-if="!route.path.startsWith('/admin')" class="bg-ink text-sand/80">
      <div class="max-w-6xl mx-auto px-6 py-14 grid gap-10 sm:grid-cols-3">
        <!-- Tentang -->
        <div class="md:col-span-1">
          <p class="font-display text-2xl text-sand mb-3">Grand Nusantara</p>
          <p class="text-sm leading-relaxed">
            Resort tepi pantai di Bali dengan kolam renang, spa, dan bar untuk liburan maupun perjalanan bisnis Anda.
          </p>
        </div>

        <!-- Kontak -->
        <div>
          <p class="font-display text-lg text-sand mb-3">Kontak</p>
          <ul class="text-sm space-y-2">
            <li>Jalan Pantai Berawa No. 100XX, Tibubeneng, North Kuta - Badung - Bali, Indonesia, 80361</li>
            <li>+62 812 3456 7890</li>
            <li>info@grandnusantara.com</li>
          </ul>
        </div>

        <!-- Sosial media -->
        <div>
          <p class="font-display text-lg text-sand mb-3">Ikuti Kami</p>
          <div class="flex gap-3">
            <a href="#" aria-label="Instagram" class="w-9 h-9 rounded-full border border-sand/30 flex items-center justify-center hover:bg-sand/10 transition-colors">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="2" y="2" width="20" height="20" rx="5" />
                <path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z" />
                <line x1="17.5" y1="6.5" x2="17.5" y2="6.5" />
              </svg>
            </a>
            <a href="#" aria-label="WhatsApp" class="w-9 h-9 rounded-full border border-sand/30 flex items-center justify-center hover:bg-sand/10 transition-colors">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z" />
              </svg>
            </a>
            <a href="#" aria-label="Facebook" class="w-9 h-9 rounded-full border border-sand/30 flex items-center justify-center hover:bg-sand/10 transition-colors">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z" />
              </svg>
            </a>
          </div>
        </div>
      </div>

      <div class="border-t border-sand/10">
        <div class="max-w-6xl mx-auto px-6 py-5 text-xs text-center text-sand/60">
          &copy; 2026 Grand Nusantara Hotel. Semua hak dilindungi.
        </div>
      </div>
    </footer>
  </div>
</template>

<style scoped>
.menu-fade-enter-active,
.menu-fade-leave-active {
  transition: opacity 0.15s ease, transform 0.15s ease;
}
.menu-fade-enter-from,
.menu-fade-leave-to {
  opacity: 0;
  transform: translateY(-4px);
}

.mobile-menu-enter-active,
.mobile-menu-leave-active {
  transition: opacity 0.2s ease, max-height 0.2s ease;
  overflow: hidden;
}
.mobile-menu-enter-from,
.mobile-menu-leave-to {
  opacity: 0;
  max-height: 0;
}
.mobile-menu-enter-to,
.mobile-menu-leave-from {
  opacity: 1;
  max-height: 400px;
}
</style>