<?php

defined('BASEPATH') OR exit('No direct script access allowed');

// return anchor tag with li tag
function anchor_li($uri = '', $title = '', $attributes = '', $li_attributes = '') {
    $return = '<li ' . $li_attributes . '>';
    $return .= anchor($uri, $title, $attributes);
    $return .= '</li>';
    return $return;
}

function anchor_lis($array_li = array(), $ul_attributes = NULL) {
    $return = '';
    foreach ($array_li as $li) {
        $attributes = isset($li[2]) ? $li[2] : '';
        $li_attributes = isset($li[3]) ? $li[3] : '';
        $return .= anchor_li($li[0], $li[1], $attributes, $li_attributes);
    }
    $ul_attr = is_bool($ul_attributes) === TRUE ? '' : ' ' . $ul_attributes;
    return $ul_attributes ? '<ul' . $ul_attr . '>' . $return . '</ul>' : $return;
}

function redirect_msg($uri, $msg, $is_success = true, $redirect_method = 'auto', $http_response_code = NULL) {
    msg_success($msg, $is_success);
    redirect($uri, $redirect_method, $http_response_code);
}

function upload_url($uri='', $protocol='') {
    if (strlen($uri) > 0 && file_exists(FCPATH . '/assets/uploads/' . $uri)) {
        return site_url('/assets/uploads/' . $uri, $protocol);
    }
    return FALSE;
}

function anchor_upload($uri='', $title = '', $attributes = '') {
    $upload_url = upload_url($uri);
    return $upload_url ? anchor($upload_url, $title, $attributes) : FALSE;
}
