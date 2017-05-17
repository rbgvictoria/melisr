<?php
/**
 * @property CI_Loader $load
 * @property CI_Form_validation $form_validation
 * @property CI_Input $input
 * @property CI_Email $email
 * @property CI_DB_active_record $db
 * @property CI_DB_forge $dbforge
 */

class Loan_sorter_controller extends CI_Controller {
    var $data;
    
    function __construct() {
        parent::__construct();
        $this->load->helper('form');
        $this->load->helper('file');
        $this->load->helper('url');
        $this->load->model('Loan_sorter_model', 'loansortermodel');
        $this->output->enable_profiler(false);
        $this->data['title'] = 'MELISR | Loan sorter';
        $this->data['js'][] = 'jquery.loansorter.js';
    }

    function index() {
        $this->load->view('loansorterview', $this->data);
    }
    
    function sort() {
        if ($this->input->post('melnumbers')) {
            $melnumbers = explode("\n", $this->input->post('melnumbers'));
            $numbers = array();
            $numbers_nonmel = array();
            foreach ($melnumbers as $number) {
                if (substr($number, 0, 4) == 'MEL ') {
                    $number = trim(str_replace('MEL', '', $number));
                    if (is_numeric($number))
                        $numbers[] = str_pad ($number, 7, '0', STR_PAD_LEFT) . 'A';
                }
                else {
                    $numbers_nonmel[] = $number;
                }
            }
            if ($numbers)
                $this->data['loans'] = $this->loansortermodel->sortByLoan($numbers);
            if ($numbers_nonmel)
                $this->data['nonmelloans'] = $this->loansortermodel->sortByLoan($numbers_nonmel, TRUE);
                
        }
        else {
            $this->session->set_flashdata('warning', 'Please enter some MEL numbers');
        }
        $this->index();
    }
    
    function send() {
        if ($this->input->post('loan_return'))
            redirect('loanreturn/prepare');
    }
    
}

?>