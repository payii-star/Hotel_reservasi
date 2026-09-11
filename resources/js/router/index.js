import { createRouter, createWebHistory } from 'vue-router'

import Home from '../views/Home.vue'
import Rooms from '../views/Rooms.vue'
import RoomDetail from '../views/RoomDetail.vue'
import Gallery from '../views/Gallery.vue'
import BookingStatus from '../views/BookingStatus.vue'
import GuestLogin from '../views/Login.vue'
import GuestRegister from '../views/Register.vue'
import Profile from '../views/Profile.vue'
import AdminLogin from '../views/admin/Login.vue'
import AdminDashboard from '../views/admin/Dashboard.vue'
import AdminRooms from '../views/admin/Rooms.vue'
import AdminBookings from '../views/admin/Bookings.vue'
import AdminGallery from '../views/admin/GalleryManager.vue'
import AdminHotelProfile from '../views/admin/HotelProfile.vue'

const routes = [
  { path: '/', name: 'home', component: Home },
  { path: '/kamar', name: 'rooms', component: Rooms },
  { path: '/kamar/:id', name: 'room-detail', component: RoomDetail, props: true },
  { path: '/galeri', name: 'gallery', component: Gallery },
  { path: '/cek-booking/:code?', name: 'booking-status', component: BookingStatus, props: true },
  { path: '/login', name: 'guest-login', component: GuestLogin },
  { path: '/register', name: 'guest-register', component: GuestRegister },
  { path: '/profil', name: 'guest-profile', component: Profile },

  { path: '/admin/login', name: 'admin-login', component: AdminLogin },
  {
    path: '/admin',
    component: AdminDashboard,
    meta: { requiresAuth: true },
    children: [
      { path: '', redirect: { name: 'admin-bookings' } },
      { path: 'kamar', name: 'admin-rooms', component: AdminRooms },
      { path: 'booking', name: 'admin-bookings', component: AdminBookings },
      { path: 'galeri', name: 'admin-gallery', component: AdminGallery },
      { path: 'profil', name: 'admin-hotel-profile', component: AdminHotelProfile },
    ],
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

router.beforeEach((to) => {
  if (to.meta.requiresAuth && !localStorage.getItem('admin_token')) {
    return { name: 'admin-login' }
  }
  if (to.name === 'guest-profile' && !localStorage.getItem('guest_token')) {
    return { name: 'guest-login', query: { redirect: to.fullPath } }
  }
})

export default router