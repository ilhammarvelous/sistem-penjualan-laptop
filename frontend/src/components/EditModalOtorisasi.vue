<template>
    <div class="fixed inset-0 bg-black bg-opacity-70 flex items-center justify-center z-50">
        <div class="relative bg-white z-50 rounded-lg shadow-xl w-full max-w-3xl overflow-hidden">
            <div class="flex items-center justify-between bg-blue-500 px-8 py-5">
                <h2 class="text-2xl font-bold text-white">Edit otorisasi user</h2>
                <div class="absolute top-0 right-0 p-3">
                    <button
                        @click="$emit('close')"
                        class="text-gray-500 hover:text-gray-800 bg-gray-300 hover:bg-gray-300 rounded-full p-2">
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke-width="1.5"
                            stroke="currentColor"
                            class="size-7">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            <div class="modal space-y-2 px-8 py-6">
                <div v-if="loading" class="flex justify-center items-center">
                    <div>Memuat....<div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div></div>
                </div>

                <table v-else class="w-full border-collapse border border-gray-200">
                    <thead>
                        <tr class="bg-gray-300">
                            <th class="px-4 py-2 text-left">Izin Menu</th>
                            <th v-for="action in actions" :key="action" class="px-4 py-2 text-center">
                                {{ action }}
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(menu, index) in menus" :key="index">
                            <td class="border px-4 py-2">
                                <input 
                                    type="checkbox" 
                                    v-model="menu.checked" 
                                    @change="toggleAllPermissions(menu)"
                                    class="w-5 h-5 rounded-md border-gray-300 focus:ring-blue-500"
                                />
                                <span class="text-base ml-1">{{ menu.name }}</span>
                            </td>

                            <td v-for="action in actions" :key="action" class="border px-4 py-2 text-center">
                                <input
                                    type="checkbox"
                                    v-model="menu.permissions[action]"
                                    @change="syncMenuChecked(menu)"
                                    class="w-5 h-5"
                                />
                            </td>

                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="col-span-2 px-8 py-4 mb-1">
                <button
                    @click="simpanMenu"
                    type="submit"
                    class="w-50 py-2 px-3 bg-blue-600 text-white font-bold rounded-lg hover:bg-blue-500 focus:ring-4 focus:ring-blue-300">
                    Simpan
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
    import { ref, onMounted, defineProps, defineEmits } from 'vue';
    import axios from 'axios';

    const props = defineProps({
        user: Number,
    });

    const emit = defineEmits(['close']);
    const menus = ref([]);
    const loading = ref(true);
    const selectedMenus = ref([]);
    const actions = ref(["create", "read", "update", "delete"]);

    // const fetchMenus = async () => {
    //     try {
    //         const token = localStorage.getItem('token');

    //         const response = await axios.get(`http://127.0.0.1:8000/api/menus`, {
    //             headers: { Authorization: `Bearer ${token}` },
    //         });

    //         menus.value = response.data.map(menu => ({
    //             id: menu.id,
    //             name: menu.menu,
    //             checked: false,
    //             permissions: actions.value.reduce((acc, action) => {
    //                 acc[action] = false;
    //                 return acc;
    //             }, {})
    //         }));
    //     } catch (err) {
    //         console.error('Gagal mengambil data menu:', err.response ? err.response.data : err.message);
    //     }
    // };

    const fetchUserMenus = async () => {
        try {
            const token = localStorage.getItem('token');

            const [menuResponse, permissionResponse] = await Promise.all([
                axios.get(`http://127.0.0.1:8000/api/menus`, {
                    headers: { Authorization: `Bearer ${token}` },
                }),
                axios.get(`http://127.0.0.1:8000/api/user-permission/${props.user}`, {
                    headers: { Authorization: `Bearer ${token}` },
                })
            ]);

            // const menuResponse = await axios.get(`http://127.0.0.1:8000/api/menus`, {
            //     headers: { Authorization: `Bearer ${token}` },
            // });

            // const permissionResponse = await axios.get(`http://127.0.0.1:8000/api/user-permission/${props.user}`, {
            //     headers: { Authorization: `Bearer ${token}` },
            // });

            const allMenus = menuResponse.data.map(menu => ({
                id: menu.id,
                name: menu.menu,
                checked: false,
                permissions: {
                    create: false,
                    read: false,
                    update: false,
                    delete: false
                }
            }));

            const userPermissions = permissionResponse.data.menus || [];

            menus.value = allMenus.map(menu => {
                const userMenu = userPermissions.find(m => m.name === menu.name);
                return {
                    ...menu,
                    checked: !!userMenu,
                    permissions: {
                        create: userMenu ? userMenu.permissions.create : false,
                        read: userMenu ? userMenu.permissions.read : false,
                        update: userMenu ? userMenu.permissions.update : false,
                        delete: userMenu ? userMenu.permissions.delete : false
                    }
                };
            });
        } catch (err) {
            console.error('Gagal mengambil data user:', err.response ? err.response.data : err.message);
        } finally {
            loading.value = false;
        }
    };

    const toggleAllPermissions = (menu) => {
        actions.value.forEach(action => {
            menu.permissions[action] = menu.checked
        });
    };

    const syncMenuChecked = (menu) => {
        menu.checked = actions.value.some(action => menu.permissions[action]);
    };

    const simpanMenu = async () => {
        try {
            const token = localStorage.getItem('token');

            const selectedPermissions = menus.value.map(menu => ({
                name: menu.name,
                checked: menu.checked, // Kirim status checked
                permissions: { ...menu.permissions }
            }));
                // .filter(menu => menu.checked)
                // .map(menu => ({
                //     name: menu.name,
                //     // permissions: {
                //     //     create: menu.permissions.create || false,
                //     //     read: menu.permissions.read || false,
                //     //     update: menu.permissions.update || false,
                //     //     delete: menu.permissions.delete || false
                //     // }
                //     permissions: { ...menu.permissions }
                // }));

            await axios.put(`http://127.0.0.1:8000/api/user-permission-update/${props.user}`,
                { menus: selectedPermissions }, 
                { headers: { Authorization: `Bearer ${token}` } }
            );

            swal({
                title: 'Berhasil!',
                text: 'Otorisasi menu berhasil diperbarui',
                icon: 'success',
                showConfirmButton: true,
                timer: 2000
            });

            emit('close');
        } catch (err) {
            console.error('Gagal menyimpan:', err.response ? err.response.data : err.message);
        }
    };

    onMounted(fetchUserMenus);

    // onMounted(async () => {
    //     await fetchMenus();
    //     await fetchUserMenus();
    // })
</script>
