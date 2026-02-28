<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\User;
use App\Models\UserPermission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email',
            'no_wa' => 'required|numeric|regex:/^\d{10,14}$/',
            'password' => 'required',
            'confirm_password' => 'required|same:password'
        ], [
            'name.required' => 'nama wajib diisi!',
            'name.string' => 'nama harus berupa teks!',
            'email.required' => 'email wajib diisi!',
            'email.email' => 'masukkan email yang benar!',
            'no_wa.required' => 'Nomor WhatsApp wajib diisi!',
            'no_wa.numeric' => 'Nomor WhatsApp harus berupa angka.',
            'no_wa.regex' => 'Nomor WhatsApp harus terdiri dari 10 hingga 13 digit.',
            'password.required' => 'password wajib diisi',
            'confirm_password.required' => 'konfirmasi password wajib diisi!',
            'confirm_password.same' => 'konfirmasi password harus sama dengan password sebelumnya!'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Ada kesalahan',
                'data' => $validator->errors()
            ], 422);
        }

        $no_wa = ltrim($request->no_wa, '0');
        $no_wa = "62" . $no_wa;

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'no_wa' => $no_wa,
            'password' => Hash::make($request->password),
        ]);

        $menus = ['Dashboard', 'Pelanggan', 'Barang', 
                    'Transaksi' ,'Manage User', 
                    'Otorisasi Menu', 'Menu'
        ];
        foreach ($menus as $menu) {
            Menu::create(['menu' => $menu]);
        }

        $defaultMenus = Menu::whereIn('menu', [
                'Dashboard', 'Pelanggan', 'Barang', 'Transaksi', 
                'Manage User','Otorisasi Menu', 'Menu'
            ])->get();

        // $defaultMenus = Menu::whereIn('menu', [
        //     'Pelanggan', 'Barang', 'Transaksi', 
        //     'Manage User'
        // ])->get();

        foreach ($defaultMenus as $menu){
            $permissions = [
                'can_create' => true,
                'can_read'   => true,
                'can_update' => true,
                'can_delete' => true,
            ];
            
            $user->menus()->attach($menu->id, $permissions);
        };

        // $otorisasiMenu = Menu::where('menu', 'Otorisasi Menu')->first();

        // if ($otorisasiMenu) {
        //     $user->menus()->attach($otorisasiMenu->id, [
        //         'can_create' => true,
        //         'can_read'   => true,
        //         'can_update' => true,
        //         'can_delete' => true,
        //     ]);
        // }
        
        return response()->json([
            'success' => true,
            'message' => 'Registrasi sukses',
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'no_wa' => $user->no_wa,
                'allowedMenus' => $user->menus()->pluck('menu')
            ]
        ], 201);
    }


    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|max:255|regex:/^[\w\.-]+@[\w\.-]+\.\w{2,4}$/',
            'password' => 'required|string|max:64',
        ], [
            'email.required' => 'Email wajib diisi!!',
            'email.email' => 'Masukkan email yang benar!!',
            'email.max' => 'Email tidak boleh lebih dari 255 karakter!!',
            'email.regex' => 'Format email tidak valid!!',
            'password.required' => 'Password wajib diisi!!',
            'password.max' => 'Password maksimal 64 karakter!!',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Ada kesalahan',
                'data' => $validator->errors()
            ], 422);
        }

        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            $token = $request->user()->createToken('auth_token')->plainTextToken;

            // $permissions = UserPermission::where('user_id', $user->id)
            //     ->with('menu')
            //     ->get()
            //     ->mapWithKeys(function ($perm) {
            //         return [
            //             $perm->menu->menu => [
            //                 'create' => (bool) $perm->can_create,
            //                 'read'   => (bool) $perm->can_read,
            //                 'update' => (bool) $perm->can_update,
            //                 'delete' => (bool) $perm->can_delete
            //             ]
            //         ];
            //     });

            return response()->json([
                'success' => true,
                'message' => 'login berhasil',
                'token' => $token,
                'id' => $user->id,
                'name' => $user->name,
                'whatsapp' => $user->no_wa,
                'role' => $user->role,
                // 'permissions' => $permissions
            ], 200);
        }

        return response()->json([
            'success' => false,
            'message' => 'Email atau password salah',
        ], 401);
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();
        return response()->json([
            'status' => true,
            'message' => 'token berhasil dihapus'
        ], 200);
    }
}
