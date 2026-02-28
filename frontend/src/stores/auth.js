// import { defineStore } from 'pinia';
// import axios from 'axios';

// export const useAuthStore = defineStore('auth', {
//     state: () => ({
//         allowedMenus: [],
//         permissions: JSON.parse(localStorage.getItem("permissions")) || {}
//     }),

//     // getters: {
//     //     canCreate: (state) => state.permissions?.['Mahasiswa']?.create || false
//     // },

//     actions: {
//         async fetchUserMenus() {
//             try {
//                 const token = localStorage.getItem('token');
//                 const userId = localStorage.getItem('user_id');

//                 if (!userId || !token) return;

//                 const response = await axios.get(`http://127.0.0.1:8000/api/menus/${userId}`, {
//                     headers: {
//                         Authorization: `Bearer ${token}`
//                     }
//                 });

//                 if (response.data.success && Array.isArray(response.data.menus)) {

//                     this.allowedMenus = response.data.menus;

//                     localStorage.setItem('allowedMenus', JSON.stringify(this.allowedMenus));
//                 } else {
//                     console.warn("Format data menus tidak valid:", response.data);
//                 }
//             } catch (error) {
//                 console.error('Gagal mengambil izin menu:', error);
//             }
//         },

//         async fetchUserPermissions() { 
//             try {
//                 const token = localStorage.getItem('token');
//                 const userId = localStorage.getItem('user_id');

//                 if (!userId || !token) return;

//                 const response = await axios.get(`http://127.0.0.1:8000/api/user-permission/${userId}`, {
//                     headers: { Authorization: `Bearer ${token}` }
//                 });

//                 if (response.data.menus && Array.isArray(response.data.menus)) {
//                     let formattedPermissions = {};
//                     response.data.menus.forEach(menu => {
//                         formattedPermissions[menu.name] = {
//                             create: menu.permissions.create || false,
//                             read: menu.permissions.read || false,
//                             update: menu.permissions.update || false,
//                             delete: menu.permissions.delete || false,
//                         };
//                     });
        
//                     this.permissions = formattedPermissions;
//                     localStorage.setItem("permissions", JSON.stringify(formattedPermissions));
//                 } else {
//                     console.warn("Format data permissions tidak valid:", response.data);
//                 }
//             } catch (error) {
//                 console.error('Gagal mengambil permissions:', error);
//             }
//         },
        
//         setPermissions(newPermissions) {
//             this.permissions = newPermissions;
//             localStorage.setItem("permissions", JSON.stringify(newPermissions));
//         },
//     }
// });
// stores/auth.js
import { defineStore } from 'pinia'

export const useAuthStore = defineStore('auth', {
    state: () => ({
        user: null
    }),
    actions: {
        setUser(userData) {
        this.user = userData
        }
    }
})
