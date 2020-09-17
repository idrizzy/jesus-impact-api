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
Route::get('test', 'UserController@test');
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

Route::group([
    'namespace' => 'Auth',
    'middleware' => 'api',
    'prefix' => 'password'
], function () {
    Route::post('create', 'PasswordResetController@create');
    Route::get('find/{token}', 'PasswordResetController@find');
    Route::post('reset', 'PasswordResetController@reset');
});


//  Route::group(['middleware' => 'auth:api'], function () {
//  Route::post('change_password', 'Api\AuthController@change_password');
// });

Route::group(['middleware' => ['auth']], function() {
    Route::get('getAuthUser', 'UserController@getAuthenticatedUser');
    Route::post('changepassword', 'UserController@changePassword');
    Route::post('updateuser', 'UserController@updateUser');
    Route::post('updateprofilepicture', 'UserController@UpdateProfilePicture');

    //User Feed
    Route::get('/delete/feed/{feedid}', 'FeedController@destroy');
    Route::post('/create/feed', 'FeedController@store');
    Route::get('/user/feeds', 'FeedController@index');
    Route::get('/user/feed/{id}', 'FeedController@show');
    Route::get('/my/feeds', 'FeedController@myFeeds');
    Route::get('closed', 'DataController@closed');
    Route::get('user/{id}', 'FeedController@userFeeds');

    //user comment
    Route::post('/feed/comment', 'CommentController@store');
    Route::post('/feed/reply', 'CommentController@replyStore');

    //feed like and dislike
    Route::post('/feed/like', 'FeedController@like');
    Route::post('/feed/unlike', 'FeedController@unLike');

    //FOLLOW AND UNFOLLOW
    Route::post('/users/follow', 'UserController@follow');
    Route::post('/users/unfollow', 'UserController@unFollow');
    Route::get('/users/followers', 'UserController@followers');
    Route::get('/users/followings', 'UserController@followings');

    // BAN & UNBAN USER
    Route::post('/users/ban', 'UserController@banUser');
    Route::post('/users/unban', 'UserController@unBanUser');
    Route::get('/users/active', 'UserController@activeUsers');
    Route::get('/users/inactive', 'UserController@inActiveUsers');
    Route::get('/users/all', 'UserController@allUsers');


    //lIST USERS
    Route::get('/users/list', 'UserController@users');
    Route::post('/users/search', 'UserController@users');

    // categories
    Route::get('/categories', 'CategoryController@index');
    Route::get('/categories/{id}', 'CategoryController@show');
    Route::post('/categories', 'CategoryController@create');
    Route::post('/categories/{id}/update', 'CategoryController@update');
    Route::get('/categories/{id}/delete', 'CategoryController@destroy');

    // tags
    Route::get('/tags', 'TagsController@index');
    Route::get('/tags/{id}', 'TagsController@show');
    Route::post('/tags', 'TagsController@create');
    Route::post('/tags/{id}/update', 'TagsController@update');
    Route::post('/tags/{id}/delete', 'TagsController@destroy');


    // blog Posts
    // tags
    Route::get('/blogPost', 'BlogPostController@index');
    Route::get('/blogPost/{id}', 'BlogPostController@show');
    Route::post('/blogpost', 'BlogPostController@create');
    Route::post('/blogPost/{id}/update', 'BlogPostController@update');
    Route::post('/blogPost/{id}/delete', 'BlogPostController@destroy');

    // Community
    Route::post('/community/add', 'CommunityController@store');
    Route::get('/community/all', 'CommunityController@index');
    Route::get('/usercommunities', 'CommunityController@userCommunities');
    Route::post('/community/search', 'CommunityController@search');
    Route::get('/community/members/{id}', 'CommunityController@communityMember');
    Route::get('/community/feeds/{id}', 'CommunityController@communityFeed');
    Route::get('/community/join/{id}', 'CommunityController@joinCommunity');
    Route::get('/community/unjoin/{id}', 'CommunityController@unjoinCommunity');
    Route::get('/community/approveCommunityUser', 'CommunityController@approveCommunityUser');
    // Route::get('/community/details/{id}', 'CommunityController@communityDetails');

    // Logout Route for the Application
    Route::get('/logout', 'UserController@logout');
    Route::get('/today/notifications', 'NotificationController@today');
    Route::get('/yesterday/notifications', 'NotificationController@yesterday');
});
