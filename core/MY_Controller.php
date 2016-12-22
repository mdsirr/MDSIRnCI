<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class MY_Controller extends CI_Controller {

    function __construct() {
        parent::__construct();
        date_default_timezone_set('Asia/Dhaka');

        $this->loggedIn = $this->site->logged_in();
        if (!in_array($this->uri->segment(1), array('login', 'supplier_registration'))) {
            if (!$this->loggedIn)
                redirect('login');
        }

        $this->logged = $this->site_model->get_logged();
    }

    // Database insert and update
    public function in_up($db_table_name, $data, $validation = NULL, $redirect = NULL, $msg_success = NULL, $msg_failed = NULL, $set_created = TRUE, $attachment = NULL) {
        $is_validate = TRUE;
        if (is_array($db_table_name)) {
            $db_table = $db_table_name[0];
            $update_where = $db_table_name[1];
            $is_insert = FALSE;
        } else {
            $db_table_arr = explode('^', $db_table_name);
            $is_insert = count($db_table_arr) == 2 ? FALSE : TRUE;
            $db_table = $is_insert ? $db_table_name : $db_table_arr[0];
            $update_id = $is_insert ? NULL : $db_table_arr[1];
            $update_where = array('id' => $update_id);
        }
        $msg_success = $msg_success ? $msg_success : ($is_insert ? 'Saved Successfully' : 'Updated Successfully.');
        $msg_failed = $msg_failed ? $msg_failed : ($is_insert ? 'Save Failed' : 'Update Failed.');
        $result = FALSE;

        $data = $data ? $data : $this->input->post();

        if ($set_created) {
            if ($is_insert) {
                $data['created_date'] = getMyDate();
                $data['created_by'] = $this->site->logged_user_id();
            } else {
                $data['updated_date'] = getMyDate();
                $data['updated_by'] = $this->site->logged_user_id();
            }
        }

        // Remove the generated(used for edit/update) field form data.
        if (isset($data['generateid'])) {
            unset($data['generateid']);
        }

        if ($validation) {
            $this->load->library('form_validation');
            $is_validate = $this->form_validation->runs($validation);
        }

        if ($is_validate == FALSE) {
            msg_success($this->form_validation->error_string(), FALSE);
        } else {
            //Upload File
            $file = $thumb = $upload_data = NULL;
            if ($attachment === TRUE) {
                $attachment_column = 'attachment';
            } else if (is_array($attachment)) {
                $attachment_column = key($attachment);
                $upload_params = reset($attachment);
                if (is_array($upload_params)) {
                    $file = isset($upload_params[0]) ? $upload_params[0] : NULL;
                    $thumb = isset($upload_params[1]) ? $upload_params[1] : NULL;
                    $upload_data = isset($upload_params[2]) ? $upload_params[2] : NULL;
                } else {
                    $file = $upload_params;
                }
            } else if (is_string($attachment)) {
                $attachment_column = $attachment;
            }

            $upload = $this->upload($file, $thumb, $upload_data);
            if ($upload->success) {
                $data[$attachment_column] = $upload->data;
            }
            //X Upload File

            if ($is_insert) {
                $result = $this->db->insert($db_table, $data);
                $result ? msg_success($msg_success) : msg_success($msg_failed, FALSE);
                if ($result && is_null($redirect)) {
                    $insert_id = $this->db->insert_id();
                    return $insert_id;
                }
            } else {
                $result = $this->db->update($db_table, $data, $update_where);
                $result ? msg_success($msg_success) : msg_success($msg_failed, FALSE);
            }
        }

        if ($this->input->is_ajax_request()) {
            die();
        } else if ($redirect) {
            redirect($redirect);
        } else {
            return $result;
        }
    }

    public function upload($file = NULL, $thumb = NULL, $upload_data = NULL) {
        $file = $file ? : 'attachment';
        $thumb = $thumb ? : FALSE;
        $upload_data = $upload_data ? : 'file_name';
        $return = new stdClass();
        $return->success = $return->errors = $return->data = NULL;

        $config = array('upload_path' => FCPATH . '/assets/uploads/', 'allowed_types' => 'jpg|png|jpeg|doc|docx|pdf', 'max_size' => '1024', 'encrypt_name' => TRUE);
        if (is_array($file)) {
            if (!isset($file['input_name'])) {
                return $return;
            }

            $input_name = $file['input_name'];
            unset($file['input_name']);
            $config = array_replace($config, $file);
        } else {
            $input_name = $file;
        }

        $add_update_arr = explode('^', $input_name);
        $is_update = count($add_update_arr) == 2 ? TRUE : FALSE;
        $input_name = $is_update ? $add_update_arr[0] : $input_name;

        if (!empty($_FILES[$input_name]['tmp_name'])) {
            $this->load->library('upload', $config);
            $return->success = $this->upload->do_upload($input_name);

            if (!$return->success) {
                $return->errors = $this->upload->display_errors();
            } else {
                $prop = $this->upload->data();
                $return->data = $upload_data ? $prop[$upload_data] : $prop;

                if ($thumb) {
                    $this->load->library('image_lib');
                    $config_img_process_thumb = array('maintain_ratio' => TRUE, 'width' => 100, 'height' => 100);
                    $config_img_process_thumb = is_array($thumb) ? array_replace($config_img_process_thumb, $thumb) : $config_img_process_thumb;
                    $config_img_process_thumb['source_image'] = $prop['full_path'];
                    $config_img_process_thumb['new_image'] = $prop['file_path'] . 'thumb_' . $prop['file_name'];
                    $this->image_lib->initialize($config_img_process_thumb);
                    $this->image_lib->resize();
                    $this->image_lib->clear();
                }

                if ($is_update) {
                    $this->site->delete_file($config['upload_path'] . $add_update_arr[1]);
                    $this->site->delete_file($config['upload_path'] . 'thumb_' . $add_update_arr[1]);
                }
            }
        } else {
            $return->success = FALSE;
        }

        return $return;
    }

    

    public function render_page($menu_active = '', $title = '', $breadcrumb = array(), $content_view = '', $content_data = array(), $main_data = array()) {
        $data = array('menu_active' => $menu_active, 'title' => $title, 'breadcrumb' => $breadcrumb, 'content' => $this->load->view($content_view, $content_data, true));
        $this->load->view('layout', array_merge($data, $main_data));
    }
    
    public function logged_type($user_types = NULL) {
        if ($user_types) {
            $types = explode('|', str_replace(' ', '', $user_types));
            return in_array($this->logged->type, $types);
        } else {
            return $this->logged->type;
        }
    }

    // Validation method for is not exist without current value.
    public function ist_exist_wc($field_val = '', $param) {
        $param = preg_split('/,/', $param);
        $db_table_name = $param[0];
        $db_table_column_name = $param[1];
        $this->form_validation->set_message(__FUNCTION__, '{field} already taken.');
        return $this->site->validation_is_exist_without_current($db_table_name, $db_table_column_name);
    }
    
    public function is_post(){
        return $this->input->method() === 'post';
    }

}
