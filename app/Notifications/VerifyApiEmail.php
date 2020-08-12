<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail as VerifyEmailBase;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\URL;

class VerifyApiEmail extends VerifyEmailBase
{


    public static $toMailCallback;

    /**
     * Get the verification URL for the given notifiable.
     *
     * @param mixed $notifiable
     * @return string
     */
    protected function verificationUrl($notifiable)
    {
        return URL::temporarySignedRoute(
            'verificationapi.verify', now()->addMinutes(1), ['id' => $notifiable->getKey()]
        ); // this will basically mimic the email endpoint with get request
    }


    public function toMail($notifiable)
    {
        $verificationUrl = $this->verificationUrl($notifiable);

        if (static::$toMailCallback) {
            return call_user_func(static::$toMailCallback, $notifiable, $verificationUrl);
        }

        return (new MailMessage)
            ->subject(Lang::get('ـأكيد حسابك البريدي'))
            ->line(Lang::get('شكرا لك على التسجيل في تطبيق أدرس الأن انت على بعد خطوة واحدة من تاكيد حسابك لدينا '))
            ->action(Lang::get('تأكيد حسابك '), $verificationUrl)
            ->line(Lang::get('أذا لم تكن انت الذي قام بعملية التسجيل فلا تضغط على أي زر . شكرا'));
    }

    public static function toMailUsing($callback)
    {
        static::$toMailCallback = $callback;
    }


}
