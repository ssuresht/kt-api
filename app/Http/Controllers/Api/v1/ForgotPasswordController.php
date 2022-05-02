<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PasswordResetRequest;
use Illuminate\Support\Str;
use App\Models\Admin;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;

class ForgotPasswordController extends Controller {
  public function forgot (Request $request) {

    $email = $request->validate([
      'email' => 'required|email',
    ]);


    $admin = Admin::where('email', $email)->first();
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
      'adminUserName'=> $adminUserName,
      'passwordResetUrl' => "http://kotonaru.s3-website-ap-northeast-1.amazonaws.com/update-password?token=".$token,
    );
   
    Mail::send(['html'=>'mail'], $data, function($message ) use ($email) {
       $message->to($email['email'])->subject('【Kotonaru】 管理者用パスワード再設定');
       $message->from('admin@motocle5.sakura.ne.jp','Kotonaru 管理者サイト');
    });

    return response()->json(['token' => $token], 200);
  }


}