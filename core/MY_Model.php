<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class MY_Model extends CI_Model {

    function __construct() {
        parent::__construct();
    }

    ////////////////MDSIR Start////////////////

    public function db_is_exist($table, $where = NULL, $where_in_array = NULL) {
        if ($where)
            $this->db->where($where);
        if ($where_in_array)
            $this->db->where_in($where_in_array[0], $where_in_array[1]);
        return $this->db->count_all_results($table) > 0 ? true : false;
    }

    public function db_get_single_row($table, $where, $columns = '*') {
        $this->db->select($columns);
        $this->db->where($where);
        $this->db->limit(1);
        $row = $this->db->get($table)->row();
        return empty($row) ? false : $row;
    }

    public function entries($table_name, $id = NULL, $columns = NULL, $where = NULL) {
        $columns = $columns ? $columns : '*';
        //$table_name = $this->site->dbt_com($table_name, NULL, TRUE); // FOR SAS
        if ($id) {
            $result = $this->db_get_single_row($table_name, array('id' => $id), $columns);
            $result_arr = (array) $result;
            return count($result_arr) === 1 ? $result_arr[$columns] : $result;
        } else {
            if ($where) {
                $this->db->where($where);
            }
            $result = $this->db->select($columns)->get($table_name)->result();
            return empty($result) ? FALSE : $result;
        }
    }

//    public function delete_entry($table_name, $id) {
//        return $this->db->delete($table_name, array('id' => $id));
//    } 
    
    //$delete_attachment = attachment_column_name>>path (applicable for $where_id = integer)
    public function del($table_name, $where_id = NULL, $delete_attachment = NULL) {


        if (is_null($where_id)) {
            return FALSE;
        } else if (is_numeric($where_id)) {
            if ($delete_attachment) {
                $del_att_arr = explode('>>', $delete_attachment);
                if(!isset($del_att_arr[1])){
                    $del_att_arr[1] = '';
                }
                $attachment_name = $this->entries($table_name, $where_id, $del_att_arr[0]);
                $this->site->delete_file(FCPATH . '/assets/uploads/'.$del_att_arr[1].'/'.$attachment_name);
                $this->site->delete_file(FCPATH . '/assets/uploads/'.$del_att_arr[1].'/thumb_'.$attachment_name);
            }            
            $this->db->where('id', (int) $where_id);
        } else {
            $this->db->where($where_id);
        }
        
        $this->db->delete($table_name);
        $deleted = (int) $this->db->affected_rows();
        return $deleted > 0;
    }

    ///////////////MDSIR End//////////////////
}