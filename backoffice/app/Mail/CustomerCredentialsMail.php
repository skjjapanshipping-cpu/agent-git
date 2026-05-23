<?php

namespace App\Mail;

use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CustomerCredentialsMail extends Mailable
{
    use Queueable, SerializesModels;

    public User $user;
    public string $plainPassword;
    public string $customerno;
    public array $warehouses;   // ['sea'=>[...], 'air'=>[...]]
    public ?string $contactNote;
    public array $support;
    public string $loginUrl;

    public function __construct(User $user, string $plainPassword, array $warehouses, array $support, ?string $contactNote = null)
    {
        $this->user = $user;
        $this->plainPassword = $plainPassword;
        $this->customerno = strtoupper($user->customerno);
        $this->warehouses = $warehouses;
        $this->contactNote = $contactNote;
        $this->support = $support;
        $this->loginUrl = url('/login');
    }

    public function build()
    {
        return $this->subject('ยินดีต้อนรับสู่ SKJ Japan Shipping — ข้อมูลบัญชีของคุณ ' . $this->customerno)
            ->view('emails.customer-credentials')
            ->with([
                'user'          => $this->user,
                'plainPassword' => $this->plainPassword,
                'customerno'    => $this->customerno,
                'warehouses'    => $this->warehouses,
                'contactNote'   => $this->contactNote,
                'support'       => $this->support,
                'loginUrl'      => $this->loginUrl,
            ]);
    }
}
