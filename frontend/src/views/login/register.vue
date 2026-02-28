<template>
  <div class="min-h-screen flex items-center justify-center bg-slate-300 px-6">
    <div class="bg-white p-8 rounded-lg shadow-lg w-full max-w-md mb-7">
      <h2 class="text-2xl font-bold mb-6 text-gray-800 text-center">Registrasi</h2>
      <form @submit.prevent="register()" class="space-y-4">
        <!-- Nama -->
        <div>
          <label for="nama" class="block text-sm font-medium text-gray-700">Nama</label>
          <input
            type="text"
            id="nama"
            name="nama"
            class="mt-1 block w-full p-3 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500"
            placeholder="Masukkan nama"
            v-model="formData.name"
          />
          <p v-if="validation.name" class="mt-1 text-sm text-red-500">{{ validation.name[0] }}</p>
        </div>

        <!-- Email -->
        <div>
          <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
          <input
            id="email"
            name="email"
            class="mt-1 block w-full p-3 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500"
            placeholder="Masukkan email"
            v-model="formData.email"
          />
          <p v-if="validation.email" class="mt-1 text-sm text-red-500">{{ validation.email[0] }}</p>
        </div>

        <!-- No Wa -->
        <div>
          <label for="no_wa" class="block text-sm font-medium text-gray-700">Nomor WhatsApp</label>
          <input
            type="text"
            id="no_wa"
            name="no_wa"
            class="mt-1 block w-full p-3 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500"
            placeholder="Masukkan nomor whatsapp"
            v-model="formData.no_wa"
          />
          <p v-if="validation.no_wa" class="mt-1 text-sm text-red-500">{{ validation.no_wa[0] }}</p>
          <p class="text-xs mt-1 text-gray-400">Contoh: 0812345678910</p>
        </div>

        <!-- Password -->
        <div>
          <label for="password1" class="block text-sm font-medium text-gray-700">Password</label>
          <input
            type="password"
            id="password1"
            name="password1"
            class="mt-1 block w-full p-3 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500"
            placeholder="Masukkan password"
            v-model="formData.password"
          />
          <p v-if="validation.password" class="mt-1 text-sm text-red-500">{{ validation.password[0] }}</p>
        </div>

        <!-- Konfirmasi Password -->
        <div>
          <label for="password2" class="block text-sm font-medium text-gray-700"
            >Konfirmasi password</label>
          <input
            type="password"
            id="password2"
            name="password2"
            class="mt-1 block w-full p-3 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500"
            placeholder="Masukkan konfirmasi password"
            v-model="formData.confirm_password"
          />
          <p v-if="validation.confirm_password" class="mt-1 text-sm text-red-500">{{ validation.confirm_password[0] }}</p>
        </div>

        <div class="text-center text-sm text-gray-600">
          <p>
            Sudah punya akun ?
            <router-link
              :to="{ name: 'login' }"
              class="font-medium text-blue-500 hover:underline hover:text-blue-400">
              Login disini
            </router-link>
          </p>
        </div>

        <!-- Tombol Submit -->
        <div>
          <button
            type="submit"
            class="w-full py-3 px-4 bg-blue-600 text-white font-bold rounded-lg hover:bg-blue-500 focus:ring-4 focus:ring-blue-300">
            Daftar
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
import { reactive } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'
import swal from 'sweetalert'

export default {
  setup() {
    const formData = reactive({
      name: '',
      email: '',
      no_wa: '',
      password: '',
      confirm_password: '',
    })

    const validation = reactive({})

    const router = useRouter()

    function register() {
      axios
        .post(`http://127.0.0.1:8000/api/user/register`, formData)
        .then(() => {
          // const userData = response.data.data

          // localStorage.setItem('authenticated', true);
          // localStorage.setItem('token', userData.token);
          // localStorage.setItem('user', JSON.stringify(userData.name));
          // localStorage.setItem('user_id', userData.id); 
          // localStorage.setItem("allowedMenus", JSON.stringify(userData.allowedMenus));
          swal({
            title: 'Berhasil!',
            text: 'Berhasil mendaftar. Silahkan login',
            icon: 'success',
            showConfirmButton: true,
            timer: 2000
          });

          router.push({
            name: 'login',
          });

        })
        .catch((err) => {
          if (err.response && err.response.status === 422) {
            Object.assign(validation, err.response.data.data)
            swal({
                title: 'Gagal!',
                icon: 'error',
                text: 'Terjadi kesalahan, Silahkan cek inputan data anda !!!',
                showConfirmButton: true,
                timer: 1500
              });
          } else {
            swal({
                title: 'Gagal!',
                icon: 'error',
                text: 'Terjadi kesalahan pada server.',
                showConfirmButton: true,
                timer: 1500
              });
          }
        })
    }

    return{
      register,
      formData,
      validation,
      router,
    }
  },
}
</script>

<!-- <style>
  .bg-gng{
    background-image: url('@/assets/images/2.jpg');
    background-size: cover;
    background-position: center;
    height: 100vh;
    width: 100vw;
  }
</style> -->