<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PasswordResetRequest;
use Illuminate\Support\Str;
use App\Models\Students;
use App\Models\Admin;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;

class ForgotPasswordController extends Controller
{
  public function adminForgot(Request $request)
  {

    $email = $request->validate([
      'email' => 'required|email',
    ]);

    $admin = Admin::where('email', $email)->first();
    if ($admin) {

      $adminUserName = $admin->name;
      $expiration = Carbon::now()->addMinute(20);

      $token = Str::random(60);
      $resetToken = new PasswordResetRequest;
      $resetToken->email = $email['email'];
      $resetToken->user_type = 'admin';
      $resetToken->user_id = $admin->id;
      $resetToken->token = $token;
      $resetToken->expired_at = $expiration;
      $resetToken->save();

      $data = array(
        'adminUserName' => $adminUserName,
        'passwordResetUrl' => "https://stg.admin.kotonaru.co.jp/update-password?token=" . $token,
      );

      Mail::send(['html' => 'admin-mail'], $data, function ($message) use ($email) {
        $message->to($email['email'])->subject('【Kotonaru】 管理者用パスワード再設定');
        $message->from('admin@motocle5.sakura.ne.jp', 'Kotonaru 管理者サイト');
      });
      return response()->json(['token' => $token], 200);
    } else {

      return response()->json(['error' => __('messages.This_e_mail_address_is_not_registered')], 404);
    }
  }


  public function studentForgot(Request $request)
  {

    $email = $request->validate([
      'email' => 'required|email',
    ]);


    $student = Students::where('email_valid', $email)->first();
    $studentUserName = $student->family_name . ' ' . $student->first_name;
    $expiration = Carbon::now()->addMinute(20);

    $userPasswordResetToken = Str::random(60);
    $resetToken = new PasswordResetRequest;
    $resetToken->email = $email['email'];
    $resetToken->user_type = 'student';
    $resetToken->user_id = $student->id;
    $resetToken->token = $userPasswordResetToken;
    $resetToken->expired_at = $expiration;
    $resetToken->save();

    $data = array(
      'adminUserName' => $studentUserName,
      'passwordResetUrl' => "https://stg.kotonaru.co.jp/top?userPasswordResetToken=" . $userPasswordResetToken,
    );

    Mail::send(['html' => 'student-mail'], $data, function ($message) use ($email) {
      $message->to($email['email'])->subject('【Kotonaru】 パスワード再設定');
      $message->from('admin@motocle5.sakura.ne.jp', 'Kotonaru事務局');
    });

    return response()->json(['token' => $userPasswordResetToken], 200);
  }
}
