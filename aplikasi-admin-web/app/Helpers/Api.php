<?php

namespace App\Helpers;

Class Api {

    public static function format($status, $data, $ErrorMessage) {
        $arr['status'] = !empty($status) ? $status : '';
        $arr['data'] = !empty($data) ? $data : '';
        $arr['error_msg'] = !empty($ErrorMessage) ? $ErrorMessage : '';

        return $arr;
    }
}