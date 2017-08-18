<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
 * ==============================================================================
 * Author : Md. Safiqul Islam
 * Email : mdsir_diu@ymail.com
 * For : RITS Accounts with stock and sale.
 * Web : http://ritsbd.com
 * ==============================================================================
 */

class Site {

    public function __construct() {
        
    }

    public function __get($var) {
        return get_instance()->$var;
    }

    public function logged_in() {
        return (bool) $this->session->userdata('identity');
    }

    public function logged_user_id() {
        return $this->session->userdata('user_id');
    }

    public function logged_user_info_id() {
        return $this->ion_auth->user()->row()->info_id;
    }

    public function method_list() {
        $this->load->helper('file');

        $controllers = get_filenames(APPPATH . 'controllers/');

        foreach ($controllers as $k => $v) {
            if (strpos($v, '.php') === FALSE) {
                unset($controllers[$k]);
            }
        }

        echo '<ul>';

        foreach ($controllers as $controller) {
            echo '<li>' . $controller . '<ul>';

            include_once APPPATH . 'controllers/' . $controller;

            $methods = get_class_methods(str_replace('.php', '', $controller));

            foreach ($methods as $method) {
                echo '<li>' . $method . '</li>';
            }

            echo '</ul></li>';
        }

        echo '</ul>';
    }

    public function user_type($user_types = NULL, $user_id = NULL) {
        $user_id = $user_id ? $user_id : $this->logged_user_id();
        $group_name = $this->db->select('ia_groups.name')
                        ->join('ia_groups', 'ia_users_groups.group_id=ia_groups.id')
                        ->get_where('ia_users_groups', array(
                            'user_id' => $user_id
                        ))
                        ->row()->name;
        if ($user_types) {
            $types = explode('|', $user_types);
            return in_array($group_name, $types);
        } else {
            return $group_name;
        }
    }

    public function get_select_options($db_table_name, $field = NULL, $value_field = NULL, $selected_value = NULL, $where = NULL) {
        $field = $field ? $field : 'name';
        $value_field = $value_field ? $value_field : 'id';
        $options = '';

        if ($where) {
            $this->db->where($where, NULL, FALSE);
        }

        $this->db->order_by($field);

        foreach ($this->db->distinct()
                ->select("$value_field, $field")
                ->get($db_table_name)
                ->result() as $op) {
            $selected = $op->$value_field == $selected_value ? 'selected="selected"' : '';
            $options .= '<option value="' . $op->$value_field . '" ' . $selected . '>' . $op->$field . '</option>';
        }
        return $options;
    }

    public function delete_file($path = NULL) {
        if (is_file($path) && file_exists($path)) {
            unlink($path);
        }
    }

    public function echo_if($variable = NULL) {
        echo isset($variable) && $variable ? $variable : '';
    }

    public function send_email($to, $subject, $message, $from = NULL, $from_name = NULL, $attachment = NULL, $cc = NULL, $bcc = NULL) {
        $this->load->library('email');
        $config['useragent'] = "RITS Accounts";
        // $config['protocol'] = $this->Settings->protocol;
        $config['mailtype'] = "html";
        $config['crlf'] = "\r\n";
        $config['newline'] = "\r\n";
        /*
         * if ($this->Settings->protocol == 'sendmail') {
         * $config['mailpath'] = $this->Settings->mailpath;
         * } elseif ($this->Settings->protocol == 'smtp') {
         * $this->load->library('encrypt');
         * $config['smtp_host'] = $this->Settings->smtp_host;
         * $config['smtp_user'] = $this->Settings->smtp_user;
         * $config['smtp_pass'] = $this->encrypt->decode($this->Settings->smtp_pass);
         * $config['smtp_port'] = $this->Settings->smtp_port;
         * if (!empty($this->Settings->smtp_crypto)) {
         * $config['smtp_crypto'] = $this->Settings->smtp_crypto;
         * }
         * }
         */

        $this->email->initialize($config);

        if ($from && $from_name) {
            $this->email->from($from, $from_name);
        } /*
         * elseif ($from) {
         * $this->email->from($from, $this->Settings->site_name);
         * } else {
         * $this->email->from($this->Settings->default_email, $this->Settings->site_name);
         * }
         */

        $this->email->to($to);
        if ($cc) {
            $this->email->cc($cc);
        }
        if ($bcc) {
            $this->email->bcc($bcc);
        }
        $this->email->subject($subject);
        $this->email->message($message);
        if ($attachment) {
            if (is_array($attachment)) {
                foreach ($attachment as $file) {
                    $this->email->attach($file);
                }
            } else {
                $this->email->attach($attachment);
            }
        }

        if ($this->email->send()) {
            // echo $this->email->print_debugger(); die();
            return TRUE;
        } else {
            // echo $this->email->print_debugger(); die();
            return FALSE;
        }
    }

    public function encode($msg, $key = '') {
        $this->load->library('encrypt');
        return $this->encrypt->encode($msg, $key);
    }

    public function decode($msg, $key = '') {
        $this->load->library('encrypt');
        return $this->encrypt->decode($msg, $key);
    }

    // For jQuery validation REMOTE
    public function validation_is_exist($db_table_name, $db_table_column_name, $input_field_name = NULL) {
        $db_table_name_arr = explode('-', $db_table_name);
        $db_table_name = $db_table_name_arr[0];
        $input_field_name = $input_field_name ? $input_field_name : $db_table_column_name;
        $validity = !$this->site_model->db_is_exist($db_table_name, array(
                    $db_table_column_name => $this->input->get_post($input_field_name)
        ));

        if (isset($db_table_name_arr[1]) && $db_table_name_arr[1] == 'not') {
            $validity = !$validity;
        }
        echo json_encode($validity);
    }

    public function validation_is_exist_without_current($db_table_name, $db_table_column_name, $input_field_name = NULL, $id_input_field_name = 'generateid') {
        $input_field_name = $input_field_name ? $input_field_name : $db_table_column_name;
        $id = $this->decode($this->input->post($id_input_field_name));
        $given_code = $this->input->post($input_field_name);
        $current_code = $this->site_model->row($db_table_name, array(
            'id' => $id
                ), $db_table_column_name);
        $validity = !$this->site_model->db_is_exist($db_table_name, array(
                    $db_table_column_name => $given_code,
                    $db_table_column_name . ' !=' => $current_code
        ));

        return $validity;
    }

    public function save_barcode($text = NULL, $name = NULL, $height = 56, $drawText = false, $bcs = 'code128') {
        // $drawText = ($stext != 1) ? FALSE : TRUE;
        $this->load->library('zend');
        $this->zend->load('Zend/Barcode');
        $barcodeOptions = array(
            'text' => $text,
            'barHeight' => $height,
            'drawText' => $drawText
        );
        $rendererOptions = array(
            'imageType' => 'png',
            'horizontalPosition' => 'center',
            'verticalPosition' => 'middle'
        ); // ,'width' => $width
        $image = Zend_Barcode::draw($bcs, 'image', $barcodeOptions, $rendererOptions);
        if (imagepng($image, 'assets/uploads/barcodes/' . $name . '.png')) {
            imagedestroy($image);
            $bc = file_get_contents('assets/uploads/barcodes/' . $name . '.png');
            $bcimage = base64_encode($bc);
            return $bcimage;
        }
        return FALSE;
        // return $image;
    }

    public function get_sale_ref($sale_id) {
        return 'SALE/' . $sale_id;
    }

    public function generate_pdf($content, $name = NULL, $output_type = NULL, $footer = NULL, $margin_bottom = NULL, $header = NULL, $margin_top = NULL, $orientation = NULL, $page_size = NULL) {
        ini_set('memory_limit', '-1');

        if (!$output_type) {
            $output_type = 'D';
        }
        if (!$margin_bottom) {
            $margin_bottom = 10;
        }
        if (!$margin_top) {
            $margin_top = 10;
        }
        if (!$name) {
            $name = 'download';
        }
        $name = $name . '.pdf';

        $page_size = $page_size ?: 'A4';
        $orientation = $orientation ?: 'p';

        $this->load->library('pdf');
        $pdf = new mPDF('utf-8', $page_size . '-' . $orientation, '13', '', 10, 10, $margin_top, $margin_bottom, 9, 9);
        $pdf->debug = false;
        $pdf->autoScriptToLang = true;
        $pdf->autoLangToFont = true;
        // $pdf->SetProtection(array('print')); // You pass 2nd arg for user password (open) and 3rd for owner password (edit)
        // $pdf->SetProtection(array('print', 'copy')); // Comment above line and uncomment this to allow copying of content
        $pdf->SetTitle("RITS");
        $pdf->SetAuthor("RITS");
        $pdf->SetCreator("RITS");
        $pdf->SetDisplayMode('fullpage');
        $stylesheet = file_get_contents('assets/css/bootstrap.min.css');
        $pdf->WriteHTML($stylesheet, 1);
        $pdf->WriteHTML($content); // $pdf->WriteHTML($content, 2);
        if ($header != '') {
            $pdf->SetHTMLHeader('<p class="text-center">' . $header . '</p>', '', TRUE);
        }
        if ($footer != '') {
            $pdf->SetHTMLFooter('<p class="text-center">' . $footer . '</p>', '', TRUE);
        }
        // $pdf->SetHeader($this->Settings->site_name.'||{PAGENO}', '', TRUE); // For simple text header
        // $pdf->SetFooter($this->Settings->site_name.'||{PAGENO}', '', TRUE); // For simple text footer
        if ($output_type == 'F') {
            $pdf->Output('assets/uploads/' . $name, $output_type);
            return 'assets/uploads/' . $name;
        } else {
            $pdf->Output($name, $output_type);
        }
    }

    public function generate_excel($data_array, $file_name = NULL) {
        $file_name = $file_name ?: 'download';

        $this->load->library('excel');
        $this->excel->setActiveSheetIndex(0);
        $this->excel->getActiveSheet()->fromArray($data_array, null, 'A1');
        /*if (is_array($data_array)) {
            
        } else {
            // save $table inside temporary file that will be deleted later
             $tmpfile = tempnam(sys_get_temp_dir(), 'html');
              file_put_contents($tmpfile, $data_array);
              $excelHTMLReader = PHPExcel_IOFactory::createReader('HTML');
              $excelHTMLReader->loadIntoExisting($tmpfile, $this->excel);
              unlink($tmpfile); 
        }*/

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $file_name . '.xls"');
        header('Cache-Control: max-age=0');

        $writer = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');

        $writer->save('php://output');
    }

    public function my_print_r($array = array(), $is_pre = true) {
        if (!$is_pre) {
            print_r($array);
        } else {
            echo '<pre>';
            print_r($array);
            echo '</pre>';
        }
    }

    public function my_print($value = '', $text = '', $is_pre = true) {
        if ($text)
            echo '<br>@@ ' . $text . ' START @@<br>';
        if (!is_array($value) && !is_object($value))
            echo '<br>' . $value . '<br>';
        else {
            if (!$is_pre)
                print_r($value);
            else {
                echo '<pre>';
                print_r($value);
                echo '</pre> ------ <br>';
            }
        }
        if ($text)
            echo '@@ ' . $text . ' END @@<br>';
    }

    public function asset($uris, $is_raw = false, $is_return = false) {
        $output = '';
        $uriss = array();
        if (is_array($uris)) {
            $uriss = $uris;
        } else {
            $uriss[] = $uris;
        }

        foreach ($uriss as $uri) {
            $url = substr($uri, 0, 4) === 'http' ? $uri : site_url('assets/' . $uri);
            $type = pathinfo($url, PATHINFO_EXTENSION);

            if ($type == 'css') {
                $output .= $is_raw ? '<style type="text/css"> ' . file_get_contents($url) . ' </style>' . "\n" : '<link type="text/css" href="' . $url . '" rel="stylesheet">' . "\n";
            } else if ($type == 'js') {
                $output .= $is_raw ? '<script type="text/javascript"> ' . file_get_contents($url) . ' </script>' . "\n" : '<script type="text/javascript" src="' . $url . '"></script>' . "\n";
            }
        }

        if ($is_return)
            return $output;
        echo $output;
    }

    public function date_index($index, $convert = 'P2M') {
        $php_to_mysql_array = array(
            6,
            0,
            1,
            2,
            3,
            4,
            5
        );
        if ($convert === 'P2M') {
            return (int) $php_to_mysql_array[$index];
        } else if ($convert === 'M2P') {
            $mysql_to_php_array = array_flip($php_to_mysql_array);
            return (int) $mysql_to_php_array[$index];
        }

        return FALSE;
    }

    // Return the array of days(Y-m-d) in a month(Y-m)
    public function days($month = NULL, $without_day_index = array(), $without_days = array(), $up_to_today = FALSE) {
        $return = array();
        $month = $month ?: date('Y-m');
        $is_current_month = $month == date('Y-m');
        $without_day_index = is_null($without_day_index) ? array() : (array) $without_day_index;
        $without_days = is_null($without_days) ? array() : (array) $without_days;
        $month_arr = explode('-', $month);
        $days_in_month = $up_to_today && $is_current_month ? (int) date('j') : cal_days_in_month(CAL_GREGORIAN, (int) $month_arr[1], (int) $month_arr[0]);
        foreach (range(1, $days_in_month) as $value) {
            $day = $month . '-' . str_pad($value, 2, '0', STR_PAD_LEFT);
            $day_index = getMyDate('w', $day); //date("w", strtotime($day));
            if (!in_array($day, $without_days) && !in_array($day_index, $without_day_index)) {
                $return[] = $day;
            }
        }
        return $return;
    }

    public function days_from_range($start_date, $end_date, $format = NULL, $is_get_count = FALSE) {
        $format = $format ?: "Y-m-d";
        $begin = new DateTime($start_date);
        $end = new DateTime($end_date);
        $end = $end->modify('+1 day');

        $interval = new DateInterval('P1D');
        $daterange = new DatePeriod($begin, $interval, $end);

        $return_arr = array();
        foreach ($daterange as $date) {
            $return_arr[] = $date->format($format);
        }
        return $is_get_count === TRUE ? count($return_arr) : $return_arr;
    }

    public function ssop_sql_case($config_name, $dbt_field_name, $select_as = NULL) {
        $arr = $this->config->item($config_name, 'site_ssop');
        $case_string = "CASE {$dbt_field_name}";
        foreach ($arr as $op_val => $op_label) {
            $case_string .= " WHEN '{$op_val}' THEN '{$op_label}'";
        }
        return "(" . $case_string . " END) AS " . ($select_as ?: $config_name);
    }

    ////////////// Accounts //////////////

    public function acc_entry_number($numbering, $number = NULL) {
        switch ($numbering) {
            case 1:  // Auto
                if (empty($number)) {
                    $new = $this->db->select_max('number')->get('acc_entries')->row();
                    return empty($new->number) || $new->number < 1 ? 1 : $new->number + 1;
                } else {
                    return $number;
                }
                break;

            case 2:  // Manual (required)
                return $number;
                break;

            case 3:  // Manual (optional)
                return $number;
                break;
        }
    }

    ////////////// Accounts End //////////////
}
