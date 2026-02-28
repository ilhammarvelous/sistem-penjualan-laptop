import { createRouter, createWebHistory } from 'vue-router'
import Register from '../views/login/register.vue';
import Login from '../views/login/login.vue';
import { useAuthStore } from '../stores/auth'
import { storeToRefs } from 'pinia'

const routes = [
  {
    path: '/',
    name: 'login',
    component: Login,
  },

  {
    path: '/register',
    name: 'register',
    component: Register,
  },

  {
    path: '/verifikasi-otp',
    name: 'verifikasi-otp',
    component: () => import('../views/login/Verifikasi_OTP.vue'),
  },

  {
    path: '/dashboard',
    name: 'dashboard',
    component: () => import('../views/dashboard/index.vue'),
  },

  {
    path: '/pelanggan',
    name: 'pelanggan',
    component: () => import('../views/pelanggan/Index.vue'),
    meta: { requiredMenu: 'Pelanggan'}
  },

  {
    path: '/barang',
    name: 'barang',
    component: () => import('../views/barang/Index.vue'),
    meta: { requiredMenu: 'Barang' }
  },

  {
    path: '/transaksi',
    name: 'transaksi',
    component: () => import('../views/transaksi/index.vue'),
    meta: { requiredMenu: 'Transaksi' }
  },

  {
    path: '/manage_user',
    name: 'manage_user.index',
    component: () => import('../views/manage_user/index.vue'),
    // meta: { requiredMenu: 'Manage User' },
    meta: { requiredRole: 'super admin' }
  },

  // {
  //   path: '/otorisasi-menu',
  //   name: 'otorisasi.index',
  //   component: () => import('../views/otorisasi_menu/index.vue'),
  //   meta: { requiredMenu: 'Otorisasi Menu' }
  // },

  // {
  //   path: '/menu',
  //   name: 'menu.index',
  //   component: () => import('../views/menu/index.vue'),
  //   meta: { requiredMenu: 'Menu' }
  // },

]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

router.beforeEach((to, from, next) => {
  const publicPages = ['login', 'register'];
  const isAuthenticated = JSON.parse(localStorage.getItem('authenticated'));
  const userRole = localStorage.getItem('userRole');
  // const allowedMenus = JSON.parse(localStorage.getItem('allowedMenus')) || [];


  // if (to.meta.requiredMenu && !allowedMenus.includes(to.meta.requiredMenu)) {
  //   console.warn(`Akses ditolak ke: ${to.path} - Tidak ada izin menu`);
  //   return next({ name: 'login' });
  // }

  if(!publicPages.includes(to.name) && !isAuthenticated){
    return next({ name: 'login'});
  } 

  if (to.meta.requiredRole && userRole !== to.meta.requiredRole) {
    return next({ name: 'dashboard' }) // atau tampilkan halaman akses ditolak
  }

  // Jika sudah verifikasi OTP, jangan boleh ke halaman verifikasi-otp lagi
  // if (to.name === 'verifikasi-otp') {
  //   const needOTP = localStorage.getItem('OTP');
  //   if (!needOTP) {
  //     return next({ name: 'login' });
  //   }
  // }

  // Jika login tapi belum verifikasi OTP, blok akses halaman lain
  // if (isAuthenticated && localStorage.getItem('OTP') === 'true' && to.name !== 'verifikasi-otp') {
  //   return next({ name: 'verifikasi-otp' });
  // }

  if (publicPages.includes(to.name) && isAuthenticated) {
    return next({ name: 'dashboard' })
  }
  
  // Jika sudah login, jangan ke halaman login/register/verifikasi lagi
  // if (publicPages.includes(to.name) && isAuthenticated) {
  //   const allowedMenus = JSON.parse(localStorage.getItem('allowedMenus')) || [];
  //   const matchedRoute = router.getRoutes().find(route =>
  //     route.meta?.requiredMenu && allowedMenus.includes(route.meta.requiredMenu)
  //   );
  
  //   if (matchedRoute) {
  //     return next({ name: matchedRoute.name });
  //   } else {
  //     console.warn("User tidak punya akses ke route manapun.");
  //     localStorage.removeItem('authenticated');
  //     localStorage.removeItem('allowedMenus');
  //     return next({ name: 'login' });
  //   }
  // }

  next();

})

export default router
