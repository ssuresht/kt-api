<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;

class AdminEmailContactUs extends Mailable
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function build()
    {
        return $this->subject(__('【Kotonaru】お問い合わせが届きました！'))
                ->from('mailform@motocle.com','Kotonaru')
                ->view('email-template.adminContactUs', [
                    'content' => (object) $this->data,
                ]);
    }
}
