<?php
/**
 * @property CI_Loader $load
 * @property CI_Form_validation $form_validation
 * @property CI_Input $input
 * @property CI_Email $email
 * @property CI_DB_active_record $db
 * @property CI_DB_forge $dbforge
 */

class Loan_return_controller extends CI_Controller {
    var $data;
    
    function __construct() {
        parent::__construct();
        $this->load->helper('form');
        $this->load->helper('file');
        $this->load->helper('url');
        $this->load->model('Loan_return_model', 'loanreturnmodel');
        $this->output->enable_profiler();
        $this->data['title'] = 'MELISR | Loan return';
        $this->data['js'][] = 'jquery.melisr.htmltableoptions.js';
        $this->data['js'][] = 'jquery.loans.js';
        $this->data['js'][] = 'jquery.transfer.js';
    }

    function index() {
        // re-use loans drop-down from transactions
        $this->load->model('Loan_model', 'loansmodel');
        $this->data['loans'] = $this->loansmodel->getLoanNumbers();
        
        // re-use specify users drop-down from recordset
        $this->load->model('Recordset_model', 'recordsetmodel');
        $this->data['specifyusers'] = $this->recordsetmodel->getSpecifyUsers();
        
        $this->load->view('loanreturnview', $this->data);
    }
    
    function prepare() {
        if ($this->input->post('clear')) {
            redirect('loanreturn');
        }
        
        if ($this->input->post('loannumber')) {
            $loannumber = $this->input->post('loannumber');
        }
        else {
            $loannumber = FALSE;
            if ($this->input->post('melnumber')) {
                $number = substr(trim($this->input->post('melnumber')), 4);
                if (is_numeric($number)){
                    $number = str_pad($number, 7, '0', STR_PAD_LEFT);
                    $loannumber = $this->loanreturnmodel->findLoan($number);
                }
            }
            elseif ($this->input->post('melnumbers')) {
                $numbers = explode("\n", $this->input->post('melnumbers'));
                $number = substr(trim($numbers[0]), 4);
                if (is_numeric($number)){
                    $number = str_pad($number, 7, '0', STR_PAD_LEFT);
                    $loannumber = $this->loanreturnmodel->findLoan($number);
                }
            }
        }
        if (!$loannumber) {
            $this->session->set_flashdata('warning', 'Please select a loan.');
            $this->index();
            return FALSE;
        }
        else {
            $this->data['loannumber'] = $loannumber;
        }
        
        $this->data['loanpreps'] = array();
        if ($this->input->post('loanpreparationid')) {
            $count = count($this->input->post('loanpreparationid'));
            for ($i = 0; $i < $count; $i++) {
                if ($_POST['quantity'][$i] > 0 && isset($_POST['returned'][$i])) {
                    // If the quantity returned is set to 0 the record
                    // will not be returned
                    $this->data['loanpreps'][] = array(
                        'LoanPreparationID' => $_POST['loanpreparationid'][$i],
                        'CatalogNumber' => $_POST['cataloguenumber'][$i],
                        'PrepType' => $_POST['preptype'][$i],
                        'Quantity' => $_POST['quantity'][$i],
                        'TaxonName' => $_POST['taxonname'][$i],
                        'Remarks' => $_POST['remarks'][$i],
                    );
                }
            }
        }
        
        // If the 'Return loan' button is clicked, we use the returnLoan function
        if ($this->input->post('return')) {
            $this->returnLoan();
        }
        
        $this->data['loaninfo'] = $this->loanreturnmodel->getLoanInfo($loannumber);
        
        if ($this->input->post('allpreps') > 0) {
            $allpreps = $this->loanreturnmodel->getAllPreparationsInLoan($loannumber, $this->input->post('allpreps'));
            $this->data['allpreps'] = $allpreps;
        }
        
        
        
        if ($this->input->post('melnumbers')) {
            $melnumbers = [];
            if ($this->input->post('melnumbers')) {
                $numbers = trim($this->input->post('melnumbers'));
                $numbers = explode("\n", $numbers);
                foreach ($numbers as $number) {
                    $number = trim(str_replace('MEL', '', $number));
                    if (is_numeric($number)) {
                        $melnumbers[] = str_pad($number, 7, '0', STR_PAD_LEFT);
                    }
                }
            }
            
            $newpreps = $this->loanreturnmodel->findLoanPreparations($loannumber, $melnumbers);
            foreach ($newpreps as $prep) {
                if (!$this->input->post('cataloguenumber') || !in_array($prep['CatalogNumber'], $this->input->post('cataloguenumber'))
                        || $this->input->post('allpreps'))
                    $this->data['loanpreps'][] = $prep;
            }
            
            if (count($this->data['loanpreps']) > count($newpreps)) {
                foreach ($this->data['loanpreps'] as $key => $row) 
                    $catalognumber[$key] = $row['CatalogNumber'];
                array_multisort($catalognumber, $this->data['loanpreps']);
            }
        }
        

        $this->index();
        
        
    }
    
    function returnLoan() {
        $warnings = [];
        if (!$this->input->post('loannumber'))
            $warnings[] = 'Please select a loan';
        if (!$this->input->post('loanpreparationid'))
            $warnings[] = 'Nothing to return';
        if (!$this->input->post('specifyuser'))
            $warnings[] = 'Who are you?';
        if (!$this->input->post('returndate'))
            $warnings[] = 'Please enter return date';
        if ($warnings){
            $this->session->set_flashdata('warning', $warnings);
            $this->index();
            return FALSE;
        }
        
        $this->loanreturnmodel->returnLoan($this->data['loannumber'], $this->data['loanpreps'], 
                $this->input->post('specifyuser'), $this->input->post('returndate'),
                $this->input->post('quarantinemessage'), $this->input->post('transferto'));
        $this->session->set_flashdata('success', 'Loan <b>' . $this->input->post('loannumber') . '</b> has been updated');

        $this->data['loaninfo'] = $this->loanreturnmodel->getLoanInfo($this->data['loannumber']);
        $this->data['loanpreps'] = array();
        
        if ($this->input->post('allpreps') > 0) {
            $allpreps = $this->loanreturnmodel->getAllPreparationsInLoan($this->data['loannumber'], $this->input->post('allpreps'));
            $this->data['allpreps'] = $allpreps;
        }
    }
    
    function startover($loannumber=FALSE) {
        $this->data['loannumber'] = $loannumber;
        $this->data['loanpreps'] = array();
        $this->index();
    }
    
    function loans() {
        $default = array('discipline');
        $uri = $this->uri->uri_to_assoc(3, $default);
        
        if ($this->input->post('discipline'))
            $discipline = $this->input->post('discipline');
        elseif ($uri['discipline'])
            $discipline = $uri['discipline'];
        else
            $discipline = 3;
        
        $this->data['title'] = 'MELISR | Find loan';
        $this->data['discipline'] = $discipline;
        $this->data['years'] = $this->loanreturnmodel->getYears($discipline);
        $this->data['institutions'] = $this->loanreturnmodel->getInstitutions($discipline);
        $this->data['loans'] = $this->loanreturnmodel->getLoans($discipline, $this->input->post('filter'), $this->input->post('institution'), $this->input->post('year'));
        $this->load->view('loanview', $this->data);
    }

    function transferTo($loannumber) {
        $this->output->enable_profiler(FALSE);
        $trans = $this->loanreturnmodel->getTransfers($loannumber);
        if ($trans) {
            $str = '<label>to </label><select name="transferto">';
            foreach ($trans as $row) {
                $str .= '<option value="' . $row['LoanID'] . '">' . $row['LoanNumber'] . '</option>';
            }
            $str .= '</select>';
            echo $str;
        }
        else
            echo '<span style="color:red">You need to create a new loan first</span>';
    }
    
    public function autocompleteBotanist($discipline=3) {
        if (empty($_GET['term'])) exit();
        $q = strtolower($_GET['term']);
        $items = $this->loanreturnmodel->autoBotanist($q, $discipline);
        echo json_encode($items);
    }

    
    


}

?>