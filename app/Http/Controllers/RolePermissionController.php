<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;

use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionController extends Controller
{
    /**
     * this display all the list of availabe permissions
     *
     * @return \Illuminate\Http\Response
     */
   
    public function permissions()
    {
        //this function returns all Available permissions
        $permission = Permission::all();
        return response()->json(['data'=>$permission], 200);
    }


    /**
     * this shows the list of all availale roles
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function role()
    {
        //this function returns all Available Roles
        $permission = Role::all();
        return response()->json(['data'=>$permission], 200);
    }

    /**
     * this creates a new role in the system
     *
     * @return \Illuminate\Http\Response
     */
    public function createRole(Request $request)
    {   
        // auth()->user();
        $validatedData = $request->validate([
            'name' => 'required|unique:roles|max:255',
        ]);
        Role::create(['name'=>$request->name]);
        return   response()->json(['message'=>'Role Successfully Created'], 200);
    }

    /**
     * this creates a new Permission in the system
     *
     * @return \Illuminate\Http\Response
     */
    public function createPermission(Request $request)
    {
        //this creates permission
        $validatedData = $request->validate([
            'name' => 'required|unique:permissions|max:255',
        ]);
        Permission::create(['name'=>$request->name]);
        return response()->json(['message'=>'Permission Successfully Created'], 200);
    }

    /**
     * this attach permission to a role
     *
     * @return \Illuminate\Http\Response
     */
    public function attachPermissionToRole(Request $request)
    {
        //this attach permission to a Particular role
        $validatedData = $request->validate([
            'roleid' => 'required',
            'permid' => 'required',
        ]);

        $permission =  Permission::findById($request->permid);
        $role = Role::findById($request->roleid);
        $role->givePermissionTo($permission->name);
        return response()->json(['message'=>$role->name.' Permission Successfully Attached To Role '.$permission->name], 200);
    }


    /**
     * this is used to edit existing permission
     *
     * @return \Illuminate\Http\Response
     */
    public function editPermission(Request $request)
    {
    
        //this is used to edit permission
        $validatedData = $request->validate([
            'name' => 'required',
            'permid' => 'required'
            ]);
        $permission =  Permission::findById($request->permid);
        $permission->update(['name'=>$permission->name]);
        return response()->json(['message'=>'Operation Successful'], 200);

    }

    /**
     * this is used to edit role
     *
     * @return \Illuminate\Http\Response
     */
    public function editRole(Request $request)
    {
        //this is used to edit role
        $validatedData = $request->validate([
            'name' => 'required',
            'roleid' => 'required'
            ]);
        $role =  Role::findById($request->roleid);
        $role->update(['name'=>$request->name]);
        return response()->json(['message'=>'Operation Successful'], 200);
    }

    /**
     * this is used to remove permission from role
     *
     * @return \Illuminate\Http\Response
     */
    public function removePermission(Request $request)
    {
        //this is used to edit role
        $validatedData = $request->validate([
            'permid' => 'required',
            'roleid' => 'required'
            ]);
        $permission =  Permission::findById($request->permid);
        $role =  Role::findById($request->roleid);
        $permission->removeRole($role);
        
        return response()->json(['message'=>'Operation Successful'], 200);
    }

    /**
     * this is used to delete role
     *
     * @return \Illuminate\Http\Response
     */
    public function deleteRole(Request $request)
    {
        //this is used to edit role
        $validatedData = $request->validate([
            'roleid' => 'required'
            ]);
        $role =  Role::findById($request->roleid);
        $role->delete();
        
        return response()->json(['message'=>'Operation Successful'], 200);
    }

    /**
     * Rthis is uswed to delete permission
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function deletePermission(Request $request)
    {
        $validatedData = $request->validate([
            'permid' => 'required'
            ]);
        $permission =  Permission::findById($request->permid);
        $permission->delete();
        
        return response()->json(['message'=>'Operation Successful'], 200);
    }

    /**
     * this is used to attach role to a user
     *
     * @return \Illuminate\Http\Response
     */
    public function attachRoleToUser(Request $request){
        // this is used to assign Role to user
        $validatedData = $request->validate([
            'user_id' => 'required',
            'roleid' => 'required'
            ]);
        $user = User::where('id', $request->user_id)->first();
        $role =  Role::findById($request->roleid);
        $user->assignRole($role->name);
        return response()->json(['message'=>"Role Successfully Attached to ".$user->username], 200);
    }

    /**
     * this is used to assign permission to user
     *
     * @return \Illuminate\Http\Response
     */
    public function attachPermissionToUser(Request $request){
        // this is used to assign permission directly to user
         $validatedData = $request->validate([
            'user_id' => 'required',
            'permid' => 'required'
            ]);
        $user = User::where('id', $request->user_id)->first();
        $permission =  Permission::where('id',$request->permid)->first();
        $user->givePermissionTo($permission->name);
        return response()->json(['message'=>"Permission Successfully Attached to ".$user->username], 200);
    }

    /**
     * this is used to get specific role for a user
     *
     * @return \Illuminate\Http\Response
     */
    public function getUserRole (Request $request){
        // this function is used to get role for a particular user
        $validatedData = $request->validate([
            'user_id' => 'required'
            ]);
        $user = User::where('id', $request->user_id)->first();
        $user->getPermissionsViaRoles();
        return response()->json(['data'=>$user], 200);
    }

    /**
     * this is used to get specific permissions for a user
     *
     * @return \Illuminate\Http\Response
     */
    public function getUserPermission(Request $request){
         // this function is used to get role for a particular user
         $validatedData = $request->validate([
            'user_id' => 'required'
            ]);
        $user = User::where('id', $request->user_id)->first();
        $user->getDirectPermissions();
        return response()->json(['data'=>$user], 200);
    }

     /**
     * this is used to remove permissioin from a user
     *
     * @return \Illuminate\Http\Response
     */
    public function removeRoleFromUser(Request $request){
        // this is used to get user permissions
        $validatedData = $request->validate([
            'user_id' => 'required',
            'roleid' => 'required'
            ]);
        $user = User::where('id', $request->user_id)->first();
        $role =  Role::findById($request->roleid);
        $user->removeRole($role->name);
        return response()->json(['data'=>"Operation Successful"], 200);
    }

    /**
     * this is used to remove permissions directly assigned to a user
     *
     * @return \Illuminate\Http\Response
     */
    public function removePermissionFromUser(Request $request){
        // this is used to get user permissions
        $validatedData = $request->validate([
            'user_id' => 'required'
            ]);
        $user = User::where('id', $request->user_id)->first();
        $permission =  Permission::where('id',$request->permid)->first();
        $user->revokePermissionTo($permission->name);
        return response()->json(['data'=>"Operation Successful"], 200);
    }
}
