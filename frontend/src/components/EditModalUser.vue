<template>
  <div class="fixed inset-0 bg-black bg-opacity-70 flex items-center justify-center z-50">
    <div class="relative bg-white z-50 rounded-lg shadow-xl w-full max-w-3xl overflow-hidden">
      <div class="flex items-center justify-between bg-blue-500 px-8 py-5">
        <h2 class="text-2xl font-bold text-white">Edit user</h2>
        <div class="absolute top-0 right-0 p-3">
          <button
            @click="$emit('close')"
            class="text-gray-500 hover:text-gray-800 bg-gray-300 hover:bg-gray-300 rounded-full p-2"
          >
            <svg
              xmlns="http://www.w3.org/2000/svg"
              fill="none"
              viewBox="0 0 24 24"
              stroke-width="1.5"
              stroke="currentColor"
              class="size-7"
            >
              <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
      </div>
      <form @submit.prevent="update()" class="grid grid-cols-2 gap-4 px-8 py-6">
        <!-- Nama -->
        <div>
          <label for="name" class="block text-sm font-medium text-gray-700">Nama</label>
          <input
            type="text"
            id="name"
            name="name"
            class="mt-1 block w-full p-3 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500"
            v-model="user.name"
          />
          <p v-if="validation.name" class="mt-1 text-sm text-red-500">{{ validation.name[0] }}</p>
        </div>

        <!-- email -->
        <div>
          <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
          <input
            type="text"
            id="email"
            name="email"
            class="mt-1 block w-full p-3 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500"
            v-model="user.email"
          />
          <p v-if="validation.email" class="mt-1 text-sm text-red-500">{{ validation.email[0] }}</p>
        </div>

        <!-- password -->
        <div>
          <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
          <input
            type="password"
            id="password"
            name="password"
            placeholder="Masukkan password baru (opsional)"
            class="mt-1 block w-full p-3 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500"
            v-model="user.password"
          />
          <p v-if="validation.password" class="mt-1 text-sm text-red-500">
            {{ validation.password[0] }}
          </p>
        </div>

        <!-- No hp -->
        <div>
          <label for="no_wa" class="block text-sm font-medium text-gray-700">Nomor WhatsApp</label>
          <input
            type="text"
            id="no_wa"
            name="no_wa"
            class="mt-1 block w-full p-3 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500"
            v-model="user.no_wa"
          />
          <p v-if="validation.no_wa" class="mt-1 text-sm text-red-500">{{ validation.no_wa[0] }}</p>
        </div>


        <div>
                    <label for="role" class="block text-sm font-medium text-gray-700">Role<span class="text-red-600">*</span></label>
                    <select 
                        class="mt-1 block w-full p-3 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500"
                        name="role"
                        id="role"
                        v-model="user.role">
                        <option value="" disabled>-- Pilih Role--</option>
                        <option value="super admin">Super Admin</option>
                        <option value="admin">Admin</option>
                    </select>
            </div>

        <!-- Tombol Submit -->
        <div class="col-span-2">
          <button
            type="submit"
            class="w-50 py-3 px-4 bg-blue-600 text-white font-bold rounded-lg hover:bg-blue-500 focus:ring-4 focus:ring-blue-300"
          >
            Update
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref, watch } from 'vue'
import axios from 'axios'
import swal from 'sweetalert'

const emit = defineEmits(['close', 'updateData'])
const loading = ref(true);
const error = ref(null);
const validation = reactive({})

const props = defineProps({
    userId: Number
})

let user = reactive({
    name: '',
    email: '',
    no_wa: '',
    password: '',
    role: '',
})

    function editUser(id){
            if(!id) return
                
            loading.value = true
            error.value = null

            const token = localStorage.getItem('token');
            if(!token){
                swal('Error', 'Token tidak ditemukan. Silakan login ulang!', 'error');
                router.push('/');
                return;
            }

            axios.get(`http://127.0.0.1:8000/api/user/${id}`, {
                headers: {
                    Authorization: `Bearer ${token}`,
                },
            })
            .then((result) => {
                user.name = result.data.data.name;
                user.email = result.data.data.email;
                user.no_wa = result.data.data.no_wa;
                user.role = result.data.data.role;
            }).catch((err) => {
                swal('Error', err.response.data, 'error');
            });
    }

    function update() {
            const token = localStorage.getItem('token');

            if(!token){
                swal('Error', 'Token tidak ditemukan. Silakan login ulang!', 'error');
                router.push('/');
                return;
            }
            
            axios.put(`http://127.0.0.1:8000/api/user/${props.userId}`, user, {
                headers: {
                    Authorization: `Bearer ${token}`,
                },
            })
            .then(() => {
                swal({
                    title: 'Berhasil!',
                    text: 'Data user berhasil diperbarui.',
                    icon: 'success',
                    showConfirmButton: true,
                    timer: 2000
                })
                emit('updateData')
                emit('close')
            })
            .catch((err) => {
                if (err.response && err.response.status === 422) {
                    Object.assign(validation, err.response.data.errors)
                    swal({
                        title: 'Gagal!',
                        text: 'Terjadi kesalahan saat memperbarui data !!',
                        icon: 'error',
                        showConfirmButton: true,
                        timer: 2000
                    });
                } else {
                    swal('Error', 'Terjadi kesalahan pada server.', 'error');
                }
            })
    }

    watch(() => props.userId, (newId) => {
        editUser(newId)
    }, {
        immediate: true
    })

</script>
