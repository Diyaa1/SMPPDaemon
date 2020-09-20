<?php

class Helpers{

    public static function wh_log($log_msg)
    {

        $now = '[' . date("Y-m-d H:i:s") . ']';

        $log_filename = "log";
        if (!file_exists($log_filename))
        {
            // create directory/folder uploads.
            mkdir($log_filename, 0777, true);
        }
        $log_file_data = $log_filename.'/log_' . date('d-M-Y') . '.log';
        // if you don't add `FILE_APPEND`, the file will be erased each time you add a log
        file_put_contents($log_file_data, $now . ' ' . $log_msg . "\n", FILE_APPEND);
    }

}
