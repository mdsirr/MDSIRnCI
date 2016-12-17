<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

if (!function_exists('getMyDate')) {

    function getMyDate($format = "Y-m-d H:i:s", $dateString = '') {
        return $dateString == '' ? date($format) : date($format, strtotime($dateString));
    }

}

if (!function_exists('isDateString')) {

    function isDateString($dateString = '') {
        return strtotime($dateString) > 0;
    }

}

/* if(!function_exists('get_image_thumb')){
  function get_image_thumb($main_image_name_with_ext='')
  {
  return 'thumb_'.$main_image_name_with_ext;
  }
  } */

if (!function_exists('menu_group_active')) {

    function menu_group_active($menu_uri_array, $active_uri) {
        echo in_array($active_uri, $menu_uri_array) ? 'active' : '';
    }

}

if (!function_exists('menu_active')) {

    function menu_active($my_menu, $active_menu) {
        echo $my_menu == $active_menu ? 'class="active"' : '';
    }

}

/* if(!function_exists('is_admin')){
  function is_admin(){
  $CI		= &get_instance();
  return $CI->session->userdata('logged')->type=='admin' ? true : false;
  }
  } */

/* if(!function_exists('is_logged')){
  function is_logged(){
  $CI		= &get_instance();
  return $CI->session->userdata('logged') ? true : false;
  }
  } */

/* if(!function_exists('only_for')){
  function only_for($user_types = '', $redirect_uri='dashboard'){
  $ut_arr	= explode('|', $user_types);
  $CI		= &get_instance();

  if(!in_array($CI->session->userdata('logged')->type,$ut_arr)){
  redirect($redirect_uri);
  exit();
  }
  }
  } */


/* if(!function_exists('get_settings_list_name')){
  function get_settings_list_name($list_id = ''){
  $CI		= &get_instance();
  return $CI->db->get_where('settings_list_items',array('id'=>$list_id))->row()->list_item_name;
  }
  } */



/* if ( ! function_exists('msg_success'))
  {
  function msg_success($message='',$success=true)
  {
  $CI		= &get_instance();
  if($message==''){
  $msg	= $CI->session->flashdata('msg_success');
  if($CI->session->flashdata('is_success')){
  $alert_type	= 'success';
  //$alert_icon	= 'entypo-thumbs-up';
  }
  else{
  $alert_type	= 'danger';
  //$alert_icon	= 'entypo-attention';
  }
  return $msg ? '<div class="alert alert-dismissable alert-'.$alert_type.'"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>'.$msg.'</div>' : '';
  }
  else{
  $CI->session->set_flashdata(array('msg_success'=> $message,'is_success'=>$success));
  }
  }
  } */

if (!function_exists('msg_success')) {

    function msg_success($message = '', $success = true) {
        $CI = &get_instance();
        if ($message == '') {
            return get_msg_success($CI->session->flashdata('msg_success'), $CI->session->flashdata('is_success'));
        } else {
            if ($CI->input->is_ajax_request()) {
                echo get_msg_success($message, $success);
            } else {
                $CI->session->set_flashdata(array('msg_success' => $message, 'is_success' => $success));
            }
        }
    }

}

if (!function_exists('get_msg_success')) {

    function get_msg_success($message = '', $success = true) {
        if ($success) {
            $alert_type = 'success';
            //$alert_icon	= 'entypo-thumbs-up';
        } else {
            $alert_type = 'danger';
            //$alert_icon	= 'entypo-attention';
        }
        return $message ? '<div class="alert alert-dismissable alert-' . $alert_type . '"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>' . $message . '</div>' : '';
    }

}

if (!function_exists('msg')) {

    function msg($is_success, $success_msg = 'Success', $falied_msg = 'Failed.', $redirect_uri=NULL) {
        $is_success ? msg_success($success_msg) : msg_success($falied_msg, FALSE);
        if($redirect_uri){
            redirect($redirect_uri);
        }
    }

}

if (!function_exists('icon_fa')) {

    function icon_fa($icon_name = '', $text_after = NULL, $nbs = NULL, $class = NULL, $attr = NULL) {
        return '<i class="fa fa-' . $icon_name . ($class ? ' ' . $class : '') . '" ' . ($attr ? $attr . ' ' : '') . 'aria-hidden="true"></i>' .
                ($text_after ? str_repeat('&nbsp;', ($nbs ? $nbs : 1)) . $text_after : '');
    }

}

if (!function_exists('mdsir_encode')) {

    function mdsir_encode($str = '') {
        return strtr(base64_encode($str), '+=/', '.-~');
    }

}

if (!function_exists('mdsir_decode')) {

    function mdsir_decode($str = '') {
        return base64_decode(strtr($str, '.-~', '+=/'));
    }

}

if (!function_exists('btn')) {

    function btn($btn_items = 'c|s') {
        $return = '';
        $items = is_array($btn_items) ? $btn_items : explode("|", str_replace(' ', '', $btn_items));
        foreach ($items as $item) {
            switch ($item) {
                case 'c':
                    $return .= '<button type="button" class="btn btn-default text-danger" onclick="$.magnificPopup.close()">' . icon_fa('times', 'Close', 2) . '</button>';
                    break;
                case 's':
                    $return .= '<button class="btn btn-primary" type="submit">' . icon_fa('floppy-o', 'Save', 2) . '</button>';
                    break;
                case 'u':
                    $return .= '<button class="btn btn-primary" type="submit">' . icon_fa('paper-plane-o', 'Update', 2) . '</button>';
                    break;
            }
        }
        return $return;
    }

}
