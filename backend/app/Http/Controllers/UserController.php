<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use PhpParser\Node\Stmt\TryCatch;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::query();

        if ($request->has('search')) {
            $search = $request->input('search');
            $users->where('name', 'LIKE', "%{$search}%")
                ->orWhere('email', 'LIKE', "%{$search}%")
                ->orWhere('no_wa', 'LIKE', "%{$search}%")
                ->orWhere('role', 'LIKE', "%{$search}%");
        }

        $users = $users->paginate(10);

        if ($users->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Belum ada data pengguna',
                'data' => []
            ], 200);
        }

        // $transformedUsers = $users->getCollection()->map(function ($user) {
        //     return [
        //         'id' => Crypt::encryptString($user->id),
        //         'name' => $user->name,
        //         'email' => $user->email,
        //         'no_wa' => $user->no_wa,
        //         'created_at' => $user->created_at,
        //         'updated_at' => $user->updated_at,
        //     ];
        // });

        // $users->setCollection($transformedUsers);

        return response()->json([
            'success' => true,
            'message' => 'Data pengguna ditemukan',
            'data' => $users
        ], 200);
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required',
                'email' => 'required|email',
                'no_wa' => 'required|numeric|regex:/^\d{10,14}$/',
                'password' => 'required',
                'role' => 'required',
            ], [
                'name.required' => 'Nama wajib diisi!',
                'name.string' => 'Nama harus berupa teks!',
                'email.required' => 'Email wajib diisi!',
                'email.email' => 'Masukkan email yang benar!',
                'no_wa.required' => 'Nomor WhatsApp wajib diisi!',
                'no_wa.numeric' => 'Nomor WhatsApp harus berupa angka.',
                'no_wa.regex' => 'Nomor WhatsApp harus terdiri dari 10 hingga 14 digit.',
                'password.required' => 'Password wajib diisi',
                'role.required' => 'Role wajib diisi',
            ]);

            $no_wa = ltrim($request->no_wa, '0');
            $no_wa = "62" . $no_wa;

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'no_wa' => $no_wa,
                'password' => Hash::make($request->password),
                'role' => $request->role,
            ]);

            // $defaultMenus = Menu::whereIn('menu', [
            //     'Pelanggan', 'Barang', 'Transaksi', 
            //     'Manage User'
            // ])->get();

            // foreach ($defaultMenus as $menu){
            //     $permissions = [
            //         'can_create' => true,
            //         'can_read'   => true,
            //         'can_update' => true,
            //         'can_delete' => true,
            //     ];
            //     $user->menus()->attach($menu->id, $permissions);
            // };

            return response()->json([
                'success' => true,
                'message' => 'User baru berhasil ditambahkan',
                'data' => $user
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan',
                'errors' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        // $decrypt = Crypt::decryptString($id);

        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Data pengguna tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Data pengguna ditemukan',
            'data' => $user
        ], 200);
    }

    public function update(Request $request, $id)
    {
        // $decrypt = Crypt::decryptString($id);

        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Data pengguna tidak ditemukan',
            ], 404);
        }

        $request->validate([
            'name' => 'string',
            'email' => 'required|email',
            'no_wa' => 'numeric|regex:/^\d{10,14}$/',
        ], [
            'name.string' => 'Nama harus berupa teks!',
            'email.required' => 'Email wajib diisi!',
            'email.email' => 'Masukkan email yang benar!',
            'no_wa.numeric' => 'Nomor WhatsApp harus berupa angka!',
            'no_wa.regex' => 'Nomor WhatsApp harus terdiri dari 10 hingga 14 digit!',
        ]);

        if ($request->password == "") {
            $user->update([
                'name' => $request->name,
                'email' => $request->email,
                'no_wa' => $request->no_wa,
                'role' => $request->role,
            ]);
        } else {
            $user->update([
                'name' => $request->name,
                'email' => $request->email,
                'no_wa' => $request->no_wa,
                'password' => Hash::make($request->password),
                'role' => $request->role,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Berhasil mengupdate data pengguna',
            'data' => $user
        ], 200);
    }

    public function destroy($id)
    {
        // try {

        //     try {
        //         $decrypt = Crypt::decryptString($id);
        //     } catch (\Exception $e) {
        //         return response()->json([
        //             'success' => false,
        //             'message' => 'ID tidak valid.',
        //         ], 400);
        //     }

        //     if (auth()->id() === (int) $decrypt) {
        //         return response()->json([
        //             'success' => false,
        //             'message' => 'Tidak bisa menghapus akun anda sendiri.'
        //         ], 403);
        //     }

        //     $user = User::find($decrypt);

        //     if (!$user) {
        //         return response()->json([
        //             'success' => false,
        //             'message' => 'Data pengguna tidak ditemukan.',
        //         ], 404);
        //     }

        //     $user->delete();

        //     return response()->json([
        //         'success' => true,
        //         'message' => 'Data pengguna berhasil dihapus.',
        //     ], 200);

        // } catch (\Exception $e) {
        //     return response()->json([
        //         'success' => false,
        //         'message' => 'Terjadi kesalahan.',
        //         'error' => $e->getMessage(),
        //     ], 500);
        // }

        $authUser = auth()->user();

        // $decrypt = Crypt::decryptString($id);

        $user = User::find($id);

        if ($authUser->id == $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak bisa menghapus akun Anda sendiri.'
            ], 403);
        }

        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'User berhasil dihapus.'
        ], 200);
    }
}
