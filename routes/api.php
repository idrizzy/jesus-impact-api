<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('register', 'UserController@register');
Route::post('login', 'UserController@authenticate');
Route::get('open', 'DataController@open');
Route::get('viewpermissions', 'RolePermissionController@permissions');
Route::get('viewroles', 'RolePermissionController@role');
Route::post('createrole', 'RolePermissionController@createRole');
Route::post('createpermission', 'RolePermissionController@createPermission');
Route::post('attachpermissiontorole', 'RolePermissionController@attachPermissionToRole');
Route::post('attachroletoUser', 'RolePermissionController@attachRoleToUser');
Route::post('attachpermissiontoUser', 'RolePermissionController@attachPermissionToUser');
Route::post('getuserrole', 'RolePermissionController@getUserRole');
Route::post('editpermission', 'RolePermissionController@editPermission');
Route::post('editrole', 'RolePermissionController@editRole');
Route::post('deleterole', 'RolePermissionController@deleteRole');
Route::post('deletepermission', 'RolePermissionController@deletePermission');

Route::group(['middleware' => ['auth']], function() {
    Route::get('user', 'UserController@getAuthenticatedUser');
    Route::get('closed', 'DataController@closed');
});