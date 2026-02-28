<template>
  <div class="min-h-screen flex items-center justify-center bg-slate-300 px-6">
    <div class="bg-white p-8 rounded-lg shadow-lg w-full max-w-md mb-7">
      <div class="grid grid-cols-2 gap-1">
        <!-- Logo -->
        <div class="flex justify-center mb-8">
          <img src="/src/assets/images/logo.png" alt="Logo" />
        </div>
        <h2 class="text-3xl font-bold ml-8 mt-3 text-gray-800 text-center">LOGIN</h2>
      </div>

      <form @submit.prevent="login()" class="space-y-4">
        <!-- Email -->
        <div>
          <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
          <input
            id="email"
            name="email"
            class="mt-1 block w-full p-3 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500"
            placeholder="Masukkan email"
            v-model="auth.email"
          />
          <p v-if="validation.email" class="mt-1 text-sm text-red-500">{{ validation.email[0] }}</p>
        </div>

        <!-- Password -->
        <div>
          <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
          <input
            type="password"
            id="password"
            name="password"
            class="mt-1 block w-full p-3 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500"
            placeholder="Masukkan password"
            v-model="auth.password"
          />
          <p v-if="validation.password" class="mt-1 text-sm text-red-500">
            {{ validation.password[0] }}
          </p>
        </div>

        <!-- <div class="text-center text-sm text-gray-600">
          <p>Belum punya akun ? 
            <router-link :to="{ name: 'register'}" class="font-medium text-blue-500 hover:underline hover:text-blue-400">
                Daftar disini
            </router-link>
          </p>
        </div> -->

        <!-- Tombol Submit -->
        <div>
          <button
            type="submit"
            class="mt-5 w-full py-3 px-4 bg-blue-600 text-white rounded-lg hover:bg-blue-500 focus:ring-4 focus:ring-blue-300 flex items-center justify-center gap-2"
          >
            <span class="flex">
              <svg
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
                stroke-width="1.5"
                stroke="currentColor"
                class="size-6"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  d="M8.25 9V5.25A2.25 2.25 0 0 1 10.5 3h6a2.25 2.25 0 0 1 2.25 2.25v13.5A2.25 2.25 0 0 1 16.5 21h-6a2.25 2.25 0 0 1-2.25-2.25V15M12 9l3 3m0 0-3 3m3-3H2.25"
                />
              </svg>
              Masuk
            </span>
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
import { reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'
import swal from 'sweetalert'
// import { useAuthStore } from "@/stores/auth";

export default {
  setup() {
    const auth = reactive({
      email: '',
      password: '',
    })

    const router = useRouter()
    const validation = reactive({})
    // const user = ref(null);
    // const authStore = useAuthStore();

    const login = async () => {
      try {
        const response = await axios.post(`http://127.0.0.1:8000/api/user/login`, auth)

        const token = response.data.token
        const user = response.data.name
        const id = response.data.id
        const role = response.data.role
        // const permissions = response.data.permissions

        localStorage.setItem('authenticated', true)
        localStorage.setItem('token', token)
        localStorage.setItem('user', JSON.stringify(user))
        localStorage.setItem('user_id', id)
        localStorage.setItem('userRole', role)
        // authStore.setPermissions(permissions);
        // await authStore.fetchUserMenus();

        router.push({ name: 'dashboard' })
      } catch (err) {
        if (err.response && err.response.status === 429) {
          swal({
            title: 'Oops!',
            text: 'Silakan coba lagi nanti.',
            icon: 'warning',
            showConfirmButton: true,
            timer: 2000,
          })
        } else if (err.response && err.response.status === 422) {
          Object.assign(validation, err.response.data.data)
          swal({
            title: 'Oops!',
            text: 'Silahkan isi form tersebut dengan data anda !!!',
            icon: 'error',
            showConfirmButton: true,
            timer: 2000,
          })
        } else if (err.response && err.response.status === 401) {
          swal({
            title: 'Gagal',
            text: 'Email atau password salah !!',
            icon: 'error',
            showConfirmButton: true,
            timer: 2000,
          })
          auth.password = ''
        } else {
          swal({
            title: 'Error',
            text: err.response?.data?.message || 'Terjadi kesalahan.',
            icon: 'error',
            showConfirmButton: true,
            timer: 2000,
          })
        }
      }
    }

    return {
      auth,
      validation,
      router,
      login,
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
