<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Lang;

/**
 * Custom Reset Password Notification — SKJ Japan Shipping
 * ใช้ HTML template แบรนด์ SKJ แทน Laravel default template
 */
class CustomerResetPasswordNotification extends Notification
{
    use Queueable;

    /** @var string $token  ลิงก์ token สำหรับ reset */
    public string $token;

    public function __construct(string $token)
    {
        $this->token = $token;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $expireMinutes = Config::get('auth.passwords.' . Config::get('auth.defaults.passwords') . '.expire', 60);

        // สร้าง URL พร้อม email + token (ตรงกับ structure ของ Laravel built-in)
        $resetUrl = url(route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));

        $support = \App\Models\SystemSetting::support();

        return (new MailMessage)
            ->subject('🔐 รีเซ็ตรหัสผ่าน — SKJ Japan Shipping')
            ->view('emails.reset-password', [
                'user'          => $notifiable,
                'resetUrl'      => $resetUrl,
                'expireMinutes' => $expireMinutes,
                'support'       => $support,
                'loginUrl'      => url('/login'),
            ]);
    }

    public function toArray($notifiable)
    {
        return [];
    }
}
