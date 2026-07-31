<?php
namespace App\Notifications;
use Illuminate\Auth\Notifications\ResetPassword; use Illuminate\Notifications\Messages\MailMessage;
class ResetPasswordNotification extends ResetPassword {
 public function toMail($notifiable):MailMessage {$url=rtrim((string)config('app.frontend_url'),'/').'/reset-password?token='.$this->token.'&email='.urlencode($notifiable->getEmailForPasswordReset());
  return (new MailMessage)->subject('إعادة تعيين كلمة المرور')->line('تلقينا طلباً لإعادة تعيين كلمة المرور.')->action('إعادة التعيين',$url)->line('إذا لم تطلب ذلك فتجاهل الرسالة.');}
}
