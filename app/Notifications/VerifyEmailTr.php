<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;

/**
 * Laravel'in e-posta dogrulama bildiriminin Turkce surumu.
 */
class VerifyEmailTr extends VerifyEmail
{
    protected function buildMailMessage($url): MailMessage
    {
        return (new MailMessage())
            ->subject('E-posta adresinizi doğrulayın — '.config('app.name'))
            ->greeting('Merhaba!')
            ->line('Hesabınızı güvence altına almak için lütfen e-posta adresinizi doğrulayın.')
            ->action('E-postamı Doğrula', $url)
            ->line('Bu hesabı siz oluşturmadıysanız bu e-postayı yok sayabilirsiniz.')
            ->salutation('Sevgiler, '.config('app.name').' ekibi');
    }
}
