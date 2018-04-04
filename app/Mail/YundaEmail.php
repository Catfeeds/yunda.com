<?php
/**
 * 
 * Author: mingyang <7789246@qq.com>
 * Date: 2018-04-03
 */

namespace App\Mail;

use DB;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;


class YundaEmail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */

    public $data;

    public function __construct($data)
    {
        //
        $this->data = $data;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('mail.yunda_email')
            ->subject('【天眼互联】-【韵达】- 交通意外理赔');
    }
}