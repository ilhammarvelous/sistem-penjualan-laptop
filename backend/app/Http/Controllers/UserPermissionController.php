<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Models\UserPermission;
use Illuminate\Http\Request;

class UserPermissionController extends Controller
{
    public function index()
    {
        $userPermissions = UserPermission::with(['user', 'menu'])->get();

        return response()->json([
            'success' => true,
            'message' => 'List User Permissions',
            'data' => $userPermissions
        ]);
        
    }

    public function getUserPermissions($userId) {
        $permissions = UserPermission::where('user_id', $userId)->get();
    
        $filteredMenus = $permissions->map(function ($perm) {
            return [
                'name' => $perm->menu->menu,
                'permissions' => [
                    'create' => (bool) $perm->can_create,
                    'read' => (bool) $perm->can_read,
                    'update' => (bool) $perm->can_update,
                    'delete' => (bool) $perm->can_delete,
                ]
            ];
        })->filter(function ($menu) {
            return  $menu['permissions']['create'] || $menu['permissions']['read'] || 
                    $menu['permissions']['update'] || $menu['permissions']['delete'];
        });
    
        return response()->json(['menus' => $filteredMenus->values()]);
    }
    

    // public function getUserPermissions($userId) {
    //     $allMenus = Menu::all();
    //     $permissions = UserPermission::where('user_id', $userId)->get();
    
    //     return response()->json([
    //         'menus' => $allMenus->map(function ($menu) use ($permissions) {
    //             $perm = $permissions->firstWhere('menu_id', $menu->id);
    //             return [
    //                 'name' => $menu->menu,
    //                 'permissions' => [
    //                     'create' => $perm ? (bool) $perm->can_create : false,
    //                     'read' => $perm ? (bool) $perm->can_read : false,
    //                     'update' => $perm ? (bool) $perm->can_update : false,
    //                     'delete' => $perm ? (bool) $perm->can_delete : false,
    //                 ]
    //             ];
    //         })
    //     ]);
    // }

    // public function getUserPermissions($userId) {
    //     $permissions = UserPermission::where('user_id', $userId)->get();
    
    //     return response()->json([
    //         'menus' => $permissions->map(function ($perm) {
    //             return [
    //                 'name' => $perm->menu->menu,
    //                 'permissions' => [
    //                     'create' => (bool) $perm->can_create,
    //                     'read' => (bool) $perm->can_read,
    //                     'update' => (bool) $perm->can_update,
    //                     'delete' => (bool) $perm->can_delete
    //                 ]
    //             ];
    //         })
    //     ]);
    // }

    public function updateUserPermissions(Request $request, $userId) {
        // foreach ($request->menus as $menu) {
        //     UserPermission::updateOrCreate(
        //         ['user_id' => $userId, 'menu_id' => Menu::where('menu', $menu['name'])->first()->id],
        //         [
        //             'can_create' => $menu['permissions']['create'] ?? false,
        //             'can_read' => $menu['permissions']['read'] ?? false,
        //             'can_update' => $menu['permissions']['update'] ?? false,
        //             'can_delete' => $menu['permissions']['delete'] ?? false
        //         ]
        //     );
        // }
        // return response()->json(['message' => 'Permissions updated successfully']);
        foreach ($request->menus as $menu) {
            $menuId = Menu::where('menu', $menu['name'])->first()->id;
            
            $isChecked = $menu['checked'] ?? false;
            $canCreate = $menu['permissions']['create'] ?? false;
            $canRead = $menu['permissions']['read'] ?? false;
            $canUpdate = $menu['permissions']['update'] ?? false;
            $canDelete = $menu['permissions']['delete'] ?? false;

            if (!$isChecked) {
                UserPermission::where('user_id', $userId)->where('menu_id', $menuId)->delete();
            }
        
            if ($canCreate || $canRead || $canUpdate || $canDelete) {
                UserPermission::updateOrCreate(
                    ['user_id' => $userId, 'menu_id' => $menuId],
                    [
                        'can_create' => $canCreate,
                        'can_read' => $canRead,
                        'can_update' => $canUpdate,
                        'can_delete' => $canDelete
                    ]
                );
            } else {
                UserPermission::where('user_id', $userId)->where('menu_id', $menuId)->delete();
            }
        }
        
    }    
}
