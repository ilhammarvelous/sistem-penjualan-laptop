<template>
    <nav class="absolute top-0 left-0 w-full z-10 bg-blue-500 md:flex-row md:flex-nowrap md:justify-start flex items-center p-3">
        <div class="w-full mx-auto items-center flex md:flex-nowrap flex-wrap md:px-10 px-4">
            <router-link 
                :to="{ name: 'dashboard' }" 
                class="text-white hover:text-blue-200 ml-3 text-sm tracking-wide lg:inline-block relative"
                active-class="after:absolute after:bottom-[-4px] after:left-[-4px] after:right-0 after:mx-auto after:w-[90px] after:h-[1.5px] after:bg-white">
                Dashboard
            </router-link>
            <router-link 
                :to="{ name: 'pelanggan' }" 
                class="text-white hover:text-blue-200 ml-8 text-sm tracking-wide lg:inline-block relative"
                active-class="after:absolute after:bottom-[-4px] after:left-[-3px] after:right-0 after:mx-auto after:w-[85px] after:h-[1.5px] after:bg-white">
                Pelanggan
            </router-link>
            <router-link 
                :to="{ name: 'barang'}" 
                class="text-white hover:text-blue-200 ml-8 text-sm tracking-wide lg:inline-block relative"
                active-class="after:absolute after:bottom-[-4px] after:left-[-3px] after:right-0 after:mx-auto after:w-[60px] after:h-[1.5px] after:bg-white">
                Barang
            </router-link>
            <router-link 
                :to="{ name: 'transaksi'}" 
                class="text-white hover:text-blue-200 ml-8 text-sm tracking-wide lg:inline-block relative"
                active-class="after:absolute after:bottom-[-4px] after:left-[-4px] after:right-0 after:mx-auto after:w-[78px] after:h-[1.5px] after:bg-white">
                Transaksi
            </router-link>
            <router-link 
                v-if="userRole === 'super admin'"
                :to="{ name: 'manage_user.index'}" 
                class="text-white hover:text-blue-200 ml-8 text-sm tracking-wide lg:inline-block relative"
                active-class="after:absolute after:bottom-[-4px] after:left-[-3px] after:right-0 after:mx-auto after:w-[103px] after:h-[1.5px] after:bg-white">
                Manage User
            </router-link>
            <!-- <router-link 

                :to="{ name: 'otorisasi.index'}" 
                class="text-white hover:text-blue-200 ml-8 text-sm tracking-wide lg:inline-block relative"
                active-class="after:absolute after:bottom-[-4px] after:left-[-3px] after:right-0 after:mx-auto after:w-[115px] after:h-[1.5px] after:bg-white">
                Otorisasi Menu
            </router-link>
            <router-link 
                
                :to="{ name: 'menu.index'}" 
                class="text-white hover:text-blue-200 ml-8 text-sm tracking-wide lg:inline-block relative"
                active-class="after:absolute after:bottom-[-4px] after:left-[-2px] after:right-0 after:mx-auto after:w-[45px] after:h-[1.5px] after:bg-white">
                Daftar Menu
            </router-link> -->
        </div>
        
        <div ref="dropdownRef" class="flex justify-end mr-11 text-sm space-x-2">
            <button @click="toggleDropdown" class="flex items-center space-x-2">
                    <span class="text-white">Hi,</span>
                    <span class="text-white">{{ user }}</span>
                    <img src="@/assets/images/aku.jpg" class="w-10 h-10 rounded-full object-cover" />
            </button>
            <div v-if="dropdown" class="absolute right-7 mt-11 bg-white hover:bg-slate-300 text-black rounded-lg shadow-lg">
                <button @click.prevent="logout()" class="flex p-2 px-3 text-md font-medium">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 9V5.25A2.25 2.25 0 0 1 10.5 3h6a2.25 2.25 0 0 1 2.25 2.25v13.5A2.25 2.25 0 0 1 16.5 21h-6a2.25 2.25 0 0 1-2.25-2.25V15m-3 0-3-3m0 0 3-3m-3 3H15" />
                        </svg>
                        Logout
                </button>
            </div>
        </div>
    </nav>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import swal from 'sweetalert';
// import { useAuthStore } from '@/stores/auth';
// import { storeToRefs } from 'pinia';

const router = useRouter();
const user = ref(null);
const dropdown = ref(false);
const dropdownRef = ref(null);
const userRole = ref(null);

// const authStore = useAuthStore();
// const { allowedMenus } = storeToRefs(authStore);

const toggleDropdown = (event) => {
    event.stopPropagation();
    dropdown.value = !dropdown.value;
}

const closeDropdown = (event) => {
    if (dropdownRef.value && !dropdownRef.value.contains(event.target)) {
        dropdown.value = false;
    }
};

onMounted(() => {
    const simpanUser = localStorage.getItem('user');
    try {
        user.value = JSON.parse(simpanUser);
    } catch (error) {
        console.error("Gagal parsing user data:", error);
        user.value = null;
    }

    userRole.value = localStorage.getItem('userRole'); 

    window.addEventListener("click", closeDropdown);
});

onUnmounted(() => {
    window.removeEventListener("click", closeDropdown);
});

const logout = () => {
    const token = localStorage.getItem('token');

        axios
        .get(`http://127.0.0.1:8000/api/user/logout`, {
            headers: {
                Authorization: `Bearer ${token}`,
            },
        })
        .then(() => {
            localStorage.removeItem('token');
            localStorage.removeItem('authenticated');
            localStorage.removeItem('user');
            localStorage.removeItem('user_id');
            localStorage.removeItem('userRole');
            // localStorage.removeItem('allowedMenus');
            // localStorage.removeItem('permissions');
            
            router.push({
                name: 'login'
            })
            swal({
                title:'Berhasil!',
                text: 'Berhasil logout',
                icon: 'success',
                showConfirmButton: true,
                timer: 1500
            })
        })
        .catch(() => {
            swal('Error', 'Terjadi kesalahan', 'error');
        });
}
</script>