<script setup>
import { onMounted } from 'vue';
// import { useAuthStore } from './stores/auth';
import Navbar from './components/Navbar.vue';

// const authStore = useAuthStore();

// onMounted( async() => {
//   await authStore.fetchUserMenus();
//   // console.log("allowedMenus setelah fetch:", authStore.allowedMenus);
// });
</script>

<template>
  <div>
    <router-view />
  </div>
</template>