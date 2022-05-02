<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;

class UserEmailContactUs extends Mailable
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function build()
    {
        return $this->subject(__('【Kotonaru】 お問い合わせを受け付けました！'))
                ->from('mailform@motocle.com','Kotonaru')
                ->view('email-template.userContactUs', [
                    'content' => (object) $this->data,
                ]);
    }
}
