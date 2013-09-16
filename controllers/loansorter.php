<?php
/**
 * @property CI_Loader $load
 * @property CI_Form_validation $form_validation
 * @property CI_Input $input
 * @property CI_Email $email
 * @property CI_DB_active_record $db
 * @property CI_DB_forge $dbforge
 */

class LoanSorter extends Controller {
    var $data;
    
    function __construct() {
        parent::Controller();
        $this->load->helper('form');
        $this->load->helper('file');
        $this->load->helper('url');
        $this->load->model('loansortermodel');
        $this->output->enable_profiler(TRUE);
        $this->data['bannerimage'] = $this->banner();
        $this->data['js'][] = 'jquery.loansorter.js';
    }

    function index() {
        $this->load->view('loansorterview', $this->data);
    }
    
    function sort() {
        if ($this->input->post('melnumbers')) {
            $melnumbers = explode("\n", $this->input->post('melnumbers'));
            $numbers = array();
            foreach ($melnumbers as $number) {
                $number = trim(str_replace('MEL', '', $number));
                if (is_numeric($number))
                    $numbers[] = str_pad ($number, 7, '0', STR_PAD_LEFT) . 'A';
            }
            $this->data['loans'] = $this->loansortermodel->sortByLoan($numbers);
                
        }
        else {
            $this->data['message'] = 'Please enter some MEL numbers.';
        }
        $this->index();
    }
    
    function send() {
        if ($this->input->post('loan_return'))
            redirect('loanreturn/prepare');
    }
    
    function banner() {
        $banners = get_dir_file_info('./images/banners', TRUE);
        $banners = array_values($banners);
        $count = count($banners);
        $i = rand(0, $count-1);
        return $banners[$i]['name'];
    }


}

?>