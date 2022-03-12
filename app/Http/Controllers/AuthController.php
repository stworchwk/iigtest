<?php

namespace App\Http\Controllers;

use App\Http\Controllers\libs\ImageManagement as ImgMgmt;
use App\Http\Controllers\libs\ResponseStructure as ResSt;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\PasswordRequest;
use App\Http\Requests\Auth\ProfileRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use App\Models\UserProfile;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class AuthController extends Controller
{

    public function register(RegisterRequest $request)
    {
        DB::beginTransaction();
        try {
            $user = new User();
            $user->username = $request->username;
            $user->password = bcrypt($request->password);
            if ($user->save()) {
                $userProfile = new UserProfile();
                $userProfile->user_id = $user->id;
                $userProfile->first_name = $request->first_name;
                $userProfile->last_name = $request->last_name;

                if ($request->hasFile('image') and $request->file('image')->isValid()) {
                    $extension = $request->image->extension();
                    $imageName = sha1(Carbon::now() . microtime()) . "." . $extension;
                    $sub_path = 'users/';
                    $request->file('image')->storeAs('/public/' . $sub_path, $imageName);
                    $url = Storage::url($sub_path . $imageName);

                    $path = storage_path('app/public/' . $sub_path . '/' . $imageName);

                    ImgMgmt::resize($path);

                    $userProfile->image_path = $url;
                }

                if ($userProfile->save()) {
                    DB::commit();
                    $token_access = $user->createToken('web', ['system:management'])->plainTextToken;
                    return ResSt::response(true, 201, __('auth.register.success'), [
                        'token_access' => $token_access,
                        'user' => $user
                    ]);
                } else {
                    DB::rollBack();
                    return ResSt::fail();
                }
            } else {
                DB::rollBack();
                return ResSt::fail();
            }
        } catch (Exception $e) {
            DB::rollBack();
            return ResSt::fail();
        }
    }

    public function login(LoginRequest $request)
    {
        $user = User::where('username', $request->username)->first();

        if (!$user) {
            return ResSt::response(false, 400, __('auth.login.username_not_match'));
        }

        if (!Hash::check($request->password, $user->password)) {
            return ResSt::response(false, 400, __('auth.login.password_incorrect'));
        }

        $token_access = $user->createToken('web', ['system:management'])->plainTextToken;
        return ResSt::response(true, 200, __('auth.login.success'), [
            'token_access' => $token_access,
            'user' => $user
        ]);
    }

    public function logout(Request $request)
    {
        if ($request->user()->currentAccessToken()->delete()) {
            return ResSt::response(true, 200, __('auth.logout.success'));
        } else {
            return ResSt::fail();
        }
    }

    //Profile Manage

    public function myProfile(Request $request)
    {
        try {
            $user = $request->user();
            $user->push($user->profile);
            return ResSt::response(true, 200, null, [
                'user' => $user
            ]);
        } catch (Exception $e) {
            return ResSt::fail();
        }
    }

    public function changeProfile(ProfileRequest $request)
    {
        try {
            $userProfile = $request->user()->profile;
            $userProfile->first_name = $request->first_name;
            $userProfile->last_name = $request->last_name;

            if ($request->hasFile('image') and $request->file('image')->isValid()) {
                //Remove old image
                if ($userProfile->image != null) {
                    $rm_image_path = str_replace('/storage', '', $userProfile->image);
                    Storage::delete('/public' . $rm_image_path);
                }
                //End remove old image

                $extension = $request->image->extension();
                $imageName = sha1(Carbon::now() . microtime()) . "." . $extension;
                $sub_path = 'users/';
                $request->file('image')->storeAs('/public/' . $sub_path, $imageName);
                $url = Storage::url($sub_path . $imageName);

                $path = storage_path('app/public/' . $sub_path . '/' . $imageName);

                ImgMgmt::resize($path);

                $userProfile->image_path = $url;
            }

            if ($userProfile->save()) {
                return ResSt::response(true, 200, __('auth.profile.update.success'), [
                    'profile' => $userProfile
                ]);
            } else {
                return ResSt::fail();
            }
        } catch (Exception $e) {
            return ResSt::fail();
        }
    }

    public function changePassword(PasswordRequest $request)
    {
        try {
            $user = $request->user();
            if (Hash::check($request->old_password, $user->password)) {

                $user->password = Hash::make($request->password);
                if ($user->save()) {
                    // Revoke all tokens
                    $user->tokens()->delete();

                    return ResSt::response(true, 200, __('auth.change_password.success'));
                } else {
                    return ResSt::fail();
                }
            } else {
                return ResSt::response(false, 200, __('auth.change_password.current_incorrect_failed'));
            }
        } catch (Exception $e) {
            return ResSt::fail();
        }
    }

    //End Profile Manage
}
