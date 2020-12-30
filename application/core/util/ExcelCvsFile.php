<?php
namespace app\core\util;

class ExcelCvsFile{


    public function __construct(){


    }


    public static function export_csv($data, $header_data, $file_name = ''){
        set_time_limit(0);
        ini_set('memory_limit', '2048M');
        if(empty($file_name)) $file_name = date('Y-m-d H-i', time());
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="'.$file_name.'.csv"');
        header('Cache-Control: max-age=0');
        $fp = fopen('php://output', 'a');
        if(!is_array($data) && !is_array($header_data) && empty($header_data)){
            return false;
        }
        foreach($header_data as $key => $item){
            $header_data[$key] = iconv('UTF-8', 'GBK', $item);
        }

        fputcsv($fp, $header_data);

        $num = 0;
        $limit = 100000;
        $count = count($data);
        if($count >0) {
            for ($i = 0; $i < $count; $i++) {
                $num++;
                if ($num == $limit) {
                    ob_flush();
                    flush();
                    $num = 0;
                }
                $row = $data[$i];
                foreach ($row as $k => $v) {
                    $row[$k] = iconv('UTF-8', 'GBK', $v);
                }
                fputcsv($fp, $row);

            }
        }

        unset($data);
        fclose($fp);
        return;
    }






}