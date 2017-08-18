<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Ajax extends MY_Controller {

    function __construct() {
        parent:: __construct();
        if (!$this->input->is_ajax_request()) {
            show_404();
        }
        $this->load->library('datatables');
    }

    public function index() {
        //$this->render_page('welcome_message', 'Dashboard', array(), 'dashboard');
    }

    // Jquery validation remote for check is exist
    // For jQuery validation REMOTE
    public function validation_is_exist($db_table_name, $db_table_column_name, $input_field_name = NULL) {
        $this->site->validation_is_exist($db_table_name, $db_table_column_name, $input_field_name);
    }

    public function ist_exist_wc($db_table_name, $db_table_column_name, $input_field_name = NULL, $id_input_field_name = 'generateid') {
        echo json_encode($this->site->validation_is_exist_without_current($db_table_name, $db_table_column_name, $input_field_name, $id_input_field_name));
    }

    public function select2_sugg($db_table, $text_column = "title", $id_column = "id") {
        $search_key = $this->input->get('term');
        $page_limit = $this->input->get('page_limit');

        $res = $this->db->select("{$id_column} AS id, {$text_column} AS text")
                ->like($text_column, $search_key)
                ->get($db_table, $page_limit);

        $ret['results'] = $res->result_array();
        echo json_encode($ret);
    }

    public function select2_parents() {
        $search_key = $this->input->get('term');
        $page_limit = $this->input->get('page_limit');

        $res = $this->db->select("id, CONCAT(father_name,' (',father_phone,') - ',mother_name,' (',mother_phone,')' ) AS text")
                ->like('father_name', $search_key)
                ->or_like('father_phone', $search_key)
                ->or_like('mother_name', $search_key)
                ->or_like('mother_phone', $search_key)
                ->get('parents', $page_limit);

        $ret['results'] = $res->result_array();
        echo json_encode($ret);
    }

    public function select_ops($db_table_name, $field=NULL, $value_field = NULL, $empty_option = "-- Select one option --") {
        $where = $this->input->get_post('where');
        $selected = $this->input->get_post('selected');
        $where = is_array($where) ? $where : NULL;
        $field = $this->input->get_post('field') ? : $field;
        $empty_option = $this->input->get_post('empty_option') ? : $empty_option;
        echo form_dropdown_db('', $empty_option, $selected, NULL, $db_table_name, $field, $value_field, $where);
    }

    public function dt_students() {
        $action_menus = anchor_lis(array(
            array('students/view/$1', icon_fa('television', 'View Details', 2), 'class=""')
        ));

        $action_menus .= anchor_lis(array(
            array('students/additional/official/$1', icon_fa('user', 'Official Info', 2), 'class="ajax-popup-link"'),
            array('students/additional/medical/$1', icon_fa('user', 'Medical Info', 2), 'class="ajax-popup-link"'),
            array('students/additional/academic/$1', icon_fa('user', 'Academic Info', 2), 'class="ajax-popup-link"'),
            array('students/edit/$1', icon_fa('pencil-square-o', 'Edit', 2)),
            array('students/delete/$1', icon_fa('trash-o', 'Delete', 2), 'class="confirm-link"'),
        ));

        $action_menus .= '<li class="divider"></li>';
        $action_menus .= anchor_lis(array(
            array('auth/users/add/$1?usertype=student', icon_fa('user-plus', 'Create User', 2), 'class="ajax-popup-link user-add" data-uid="$2"'),
            array('auth/password_reset_by_admin/$2/', icon_fa('key', 'New Password', 2), 'class="ajax-popup-link user-modify" data-uid="$2"'),
            array('auth/users/edit/$2/', icon_fa('pencil-square-o', 'Edit User', 2), 'class="ajax-popup-link user-modify" data-uid="$2"')
        ));

        $this->datatables->select("s.id, s.name, classes.name AS class, sections.name AS section, " . $this->site->ssop_sql_case('religions', 's.religion') . ", " . $this->site->ssop_sql_case('gender', 's.gender') . ", u.id AS user_id")
                ->from('students s')
                ->join('classes', 'classes.id=s.class_id', 'left')
                ->join('sections', 'sections.id=s.section_id', 'left')
                ->join('ia_users u', "u.info_id=s.id AND u.type='student'", 'left')
                ->add_column('action', $this->_dt_actions($action_menus), 'id, user_id');
        $this->output->set_content_type('application/json')->set_output($this->datatables->generate());
    }

    public function dt_parents() {

        $action_menus = anchor_lis(array(
            array('students/parents/guardian/$1', icon_fa('user', 'Guardian Info', 2), 'class="ajax-popup-link"'),
            array('students/parents/econtact/$1', icon_fa('phone', 'Emergency Contact', 2), 'class="ajax-popup-link"'),
            array('students/parents/edit/$1', icon_fa('pencil-square-o', 'Edit', 2)),
            array('students/parents/delete/$1', icon_fa('trash-o', 'Delete', 2), 'class="confirm-link"'),
        ));

        $action_menus .= '<li class="divider"></li>';
        $action_menus .= anchor_lis(array(
            array('auth/users/add/$1?usertype=parent', icon_fa('user-plus', 'Create User', 2), 'class="ajax-popup-link user-add" data-uid="$2"'),
            array('auth/password_reset_by_admin/$2/', icon_fa('key', 'New Password', 2), 'class="ajax-popup-link user-modify" data-uid="$2"'),
            array('auth/users/edit/$2/', icon_fa('pencil-square-o', 'Edit User', 2), 'class="ajax-popup-link user-modify" data-uid="$2"')
        ));

        $this->datatables->select("p.id, p.father_name, p.father_phone, p.mother_name, p.mother_phone, u.id AS user_id")
                ->from('parents p')
                ->join('ia_users u', "u.info_id=p.id AND u.type='parent'", 'left')
                ->add_column('action', $this->_dt_actions($action_menus), 'id, user_id');
        $this->output->set_content_type('application/json')->set_output($this->datatables->generate());
    }

    public function dt_teachers() {

        $action_menus = anchor_lis(array(
            array('teachers/view/$1', icon_fa('television', 'View Details', 2), 'class="ajax-popup-link"')
        ));

        $action_menus .= anchor_lis(array(
            array('teachers/edit/$1', icon_fa('pencil-square-o', 'Edit', 2), 'class="ajax-popup-link"'),
            array('teachers/delete/$1', icon_fa('trash-o', 'Delete', 2), 'class="confirm-link"'),
        ));

        $action_menus .= '<li class="divider"></li>';

        $action_menus .= anchor_lis(array(
            array('employees/profile/$3/personal', icon_fa('pencil-square-o', 'HR Profile', 2)),
        ));

        $action_menus .= '<li class="divider"></li>';
        $action_menus .= anchor_lis(array(
            array('auth/users/add/$1?usertype=teacher', icon_fa('user-plus', 'Create User', 2), 'class="ajax-popup-link user-add" data-uid="$2"'),
            array('auth/password_reset_by_admin/$2/', icon_fa('key', 'New Password', 2), 'class="ajax-popup-link user-modify" data-uid="$2"'),
            array('auth/users/edit/$2/', icon_fa('pencil-square-o', 'Edit User', 2), 'class="ajax-popup-link user-modify" data-uid="$2"')
        ));

        $this->datatables->select("t.id, t.name, t.phone, t.email, t.birthday, t.emp_pi_id, u.id AS user_id")
                ->from('teachers t')
                ->join('ia_users u', "u.info_id=t.id AND u.type='teacher'", 'left')
                ->add_column('action', $this->_dt_actions($action_menus), 'id, user_id, emp_pi_id');
        $this->output->set_content_type('application/json')->set_output($this->datatables->generate());
    }

    public function dt_exam_type() {

        $action_menus = anchor_lis(array(
            array('exams/exam_types/edit/$1', icon_fa('pencil-square-o', 'Edit', 2), 'class="ajax-popup-link"'),
            array('exams/exam_types/delete/$1', icon_fa('trash-o', 'Delete', 2), 'class="confirm-link"')
        ));

        $this->datatables->select("exam_types.id, (IF(exam_types.term_id = 1, 'Mid Term', 'Final Term' )) as term, exam_types.name , exam_types.base_mark, exam_types.created_date")
                ->from('exam_types')
                ->add_column('action', $this->_dt_actions($action_menus), 'id');
        $this->output->set_content_type('application/json')->set_output($this->datatables->generate());
    }

    public function dt_grades() {
        $action_menus = array(
            array('students/grade/edit/$1', icon_fa('pencil-square-o', 'Edit', 2), 'class="ajax-popup-link"'),
            array('students/grade/delete/$1', icon_fa('trash-o', 'Delete', 2), 'class="confirm-link"'),
        );

        $this->datatables->select("grade_id, name, grade_point, mark_from, mark_upto, comment, (IF(`grade`.`type` = 0, 'Pre-School', IF(`grade`.`type` = 1 , 'School','College'))) as type")
                ->from('grade')
                ->add_column('action', action_menu($action_menus), 'grade_id');
        $this->output->set_content_type('application/json')->set_output($this->datatables->generate());
    }

    public function dt_classes() {
        $action_menus = array(
            array('students/classes/edit/$1', icon_fa('pencil-square-o', 'Edit', 2), 'class="ajax-popup-link"'),
            array('students/classes/delete/$1', icon_fa('trash-o', 'Delete', 2), 'class="confirm-link"'),
        );

        $this->datatables->select("id, name, (IF(`type` = 0, 'Pre-School', IF(`type` = 1 , 'School','College'))) as type, created_date")
                ->from('classes')
                ->add_column('action', action_menu($action_menus), 'id');
        $this->output->set_content_type('application/json')->set_output($this->datatables->generate());
    }

    public function dt_subjects() {
        $action_menus = array(
            array('students/subjects/edit/$1', icon_fa('pencil-square-o', 'Edit', 2), 'class="ajax-popup-link"'),
            array('students/subjects/delete/$1', icon_fa('trash-o', 'Delete', 2), 'class="confirm-link"'),
        );

        $this->datatables->select("s.id, s.name, s.created_date")
                ->from('subjects s')
                ->add_column('action', action_menu($action_menus), 'id');
        $this->output->set_content_type('application/json')->set_output($this->datatables->generate());
    }

    public function dt_sections() {
        $action_menus = array(
            array('students/sections/edit/$1', icon_fa('pencil-square-o', 'Edit', 2), 'class="ajax-popup-link"'),
            array('students/sections/delete/$1', icon_fa('trash-o', 'Delete', 2), 'class="confirm-link"'),
        );

        $this->datatables->select("s.id, s.name, s.nick_name, teachers.name AS teacher, classes.name AS class")
                ->from('sections s')
                ->join('teachers', 'teachers.id=s.teacher_id', 'left')
                ->join('classes', 'classes.id=s.class_id', 'left')
                ->add_column('action', action_menu($action_menus), 'id');
        $this->output->set_content_type('application/json')->set_output($this->datatables->generate());
    }

    private function _dt_actions($action_menus) {
        return '<center><div class="btn-group">
<button type="button" class="btn btn-primary btn-xs dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
Action <span class="caret"></span>
</button>
<ul class="dropdown-menu dropdown-menu-right">' . $action_menus . '</ul></div></center>';
    }

    public function dt_customers() {
        $action_menus = anchor_lis(array(
            array('customers/view/$1', icon_fa('television', 'View Details', 2), 'class="ajax-popup-link"'),
            array('customers/view/$1?type=print', icon_fa('print', 'Print', 2), 'class="ajax-popup-link"'),
            array('customers/view/$1?type=pdf', icon_fa('file-pdf-o', 'Download as PDF', 2))
        ));

        $action_menus .= anchor_lis(array(
            array('customers/edit/$1', icon_fa('pencil-square-o', 'Edit', 2)),
            array('customers/delete/$1', icon_fa('trash-o', 'Delete', 2), 'class="confirm-link"'),
        ));

        $this->datatables->select("id, name, company, phone, email, mobile")
                ->from('customers')
                ->add_column('action', '<center><div class="btn-group">
<button type="button" class="btn btn-primary btn-xs dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
Action <span class="caret"></span>
</button>
<ul class="dropdown-menu dropdown-menu-right">' . $action_menus . '</ul></div></center>', 'id');

        $this->output->set_content_type('application/json')->set_output($this->datatables->generate());
    }

    public function dt_lc_list() {
        $action_menus = anchor_lis(array(
            array('orders/letter_credits/view/$1', icon_fa('television', 'View Details', 2), 'class="ajax-popup-link"'),
            array('orders/letter_credits/view/$1?type=print', icon_fa('print', 'Print', 2), 'class="ajax-popup-link"'),
            array('orders/letter_credits/view/$1?type=pdf', icon_fa('file-pdf-o', 'Download as PDF', 2))
        ));

        $action_menus .= anchor_lis(array(
            array('orders/letter_credits/edit/$1', icon_fa('pencil-square-o', 'Edit', 2), 'class="ajax-popup-link"'),
            array('orders/letter_credits/delete/$1', icon_fa('trash-o', 'Delete', 2), 'class="confirm-link"'),
        ));

        $this->datatables->select("lc.id, lc.name, customers.name AS customer, banks.name AS bank, lc.lc_number, lc.account_number, lc.credit_amount")
                ->from('letter_credits lc')
                ->join('customers', 'customers.id=lc.customer_id', 'left')
                ->join('banks', 'banks.id=lc.bank_id', 'left')
                ->add_column('action', '<center><div class="btn-group">
<button type="button" class="btn btn-primary btn-xs dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
Action <span class="caret"></span>
</button>
<ul class="dropdown-menu dropdown-menu-right">' . $action_menus . '</ul></div></center>', 'id');

        $this->output->set_content_type('application/json')->set_output($this->datatables->generate());
    }

    public function dt_products() {
        $action_menus = anchor_lis(array(
            array('products/view/$1', icon_fa('television', 'View Details', 2), 'class="ajax-popup-link"'),
            array('products/view/$1?type=print', icon_fa('print', 'Print', 2), 'class="ajax-popup-link"'),
            array('products/view/$1?type=pdf', icon_fa('file-pdf-o', 'Download as PDF', 2))
        ));

        $action_menus .= anchor_lis(array(
            array('products/edit/$1', icon_fa('pencil-square-o', 'Edit', 2), 'class="ajax-popup-link"'),
            array('products/delete/$1', icon_fa('trash-o', 'Delete', 2), 'class="confirm-link"'),
        ));

        $this->datatables->select("p.id, p.code, p.name, p.unit, p.price, cat.name AS category")
                ->from('products p')
                ->join('office_category cat', 'p.category_id=cat.id', 'left')
                ->add_column('action', '<center><div class="btn-group">
<button type="button" class="btn btn-primary btn-xs dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
Action <span class="caret"></span>
</button>
<ul class="dropdown-menu dropdown-menu-right">' . $action_menus . '</ul></div></center>', 'id');

        $this->output->set_content_type('application/json')->set_output($this->datatables->generate());
    }

    public function dt_student_invoices() {
        $sid = $this->input->post('sid');
        $session = $this->input->post('session');
        $type = $this->input->post('type');

        if (strlen($sid) > 0) {
            $this->datatables->where('student_sid', $sid);
        }

        if (strlen($session) > 0) {
            $this->datatables->where('academic_session_id', $session);
        }

        if (strlen($type) > 0) {
            if ($type == 'due') {
                $this->datatables->where('paid <', 'payable');
            } else if ($type == 'paid') {
                $this->datatables->where('paid >=', 'payable');
            }
        }

        $this->datatables->select($this->site->ssop_sql_case('invoice_items', 'invoice_item') . ", invoice_month, fee, vat, discount, late_fee, actual_fee, payable, paid")
                ->from('student_invoices');

        $this->output->set_content_type('application/json')->set_output($this->datatables->generate());
    }

    /* public function student_class_op() {
      $sid = $this->input->get_post('sid');
      $query = $this->db->select('classes.id, classes.name')
      ->join('classes', 'classes.id=student_sessions.class_id')
      ->get_where('student_sessions', array('student_sessions.student_sid'=>$sid));
      echo form_dropdown_db('', '-- Select Class --', NULL, NULL, $query, 'name');
      } */

    public function student_sessions_op() {
        $sid = $this->input->get_post('sid');
        $query = $this->db->select("academic_sessions.id, CONCAT(academic_sessions.title, ' (', classes.name, ')') name")
                ->join('academic_sessions', 'academic_sessions.id=student_sessions.academic_session_id', 'left')
                ->join('classes', 'classes.id=student_sessions.class_id', 'left')
                ->get_where('student_sessions', array('student_sessions.student_sid' => $sid));
        echo form_dropdown_db('', '-- Select Session --', NULL, NULL, $query, 'name');
    }

    public function class_fee_settings($class_id = NULL) {
        $current = $this->site_model->key_val_array('fee_settings.invoice_item.fee', array('class_id' => $class_id));
        $htmls = '';
        foreach ($this->config->item('invoice_items', 'site_ssop') as $val => $label) {
            if (in_array($val, array('store_fee', 'lib_fine'))) {
                continue;
            }

            $cv = isset($current[$val]) ? $current[$val] : '';
            $htmls .= '<div class="form-group">' . form_label($label, 'username') . form_hidden('invoice_item[]', $val) . form_input(array('name' => 'fees[]', 'type' => 'number', 'class' => 'form-control'), $cv) . '</div>';
        }
        echo $htmls;
    }

}
