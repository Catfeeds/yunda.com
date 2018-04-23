<?php
/**
 * Created by PhpStorm.
 * User: wangsl
 * Date: 2018/04/20
 * Time: 10:41
 */

namespace App\Helper;

use Excel;

class ExcelHelper
{
    static public function excelToArray($file_url)
    {
        $filePath = iconv('UTF-8', 'GBK', $file_url);
        $a = Excel::load($filePath, function ($reader) {
            $reader->noHeading();
        })->get();
        return $a;
    }
}