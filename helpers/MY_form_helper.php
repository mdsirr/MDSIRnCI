<?php

defined('BASEPATH') OR exit('No direct script access allowed');

function form_dropdown_db($name = '', $options = array(), $selected = array(), $extra = '', $db_table_name, $field = NULL, $value_field = NULL, $where = NULL) {
    $field = $field ? $field : 'name';
    $value_field = $value_field ? $value_field : 'id';
    $CI = &get_instance();
    
    if(!is_array($options)){
        $options = array(''=>$options);
    }

    if ($where) {
        if (is_array($where)) {
            $CI->db->where($where);
        } else {
            $CI->db->where($where, NULL, FALSE); // Only cusotm string
        }
    }

    $CI->db->order_by($field);

    foreach ($CI->db->distinct()->select("$value_field, $field")->get($db_table_name)->result() as $op) {
        $options[$op->$value_field] = $op->$field;
    }

    return form_dropdown($name, $options, $selected, $extra);
}

// Form Dropdown with a empty option
function form_dropdown_eo($name = '', $options = array(), $selected = array(), $extra = '', $empty_option_text = '-- Select One --') {
    $empty_option = array('' => $empty_option_text);

    // Get Data from config array like 'site_ssop.sale_paid_by'
    if (!is_array($options)) {
        $CI = &get_instance();
        sscanf($options, '%[^.].%[^.]', $config_array, $config_item);
        $options = $CI->config->item($config_item, $config_array);
    }

    return form_dropdown($name, ($empty_option + $options), $selected, $extra);
}
