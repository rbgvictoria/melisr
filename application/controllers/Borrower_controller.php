<?php

class Borrower_controller extends CI_Controller {
    public $data;
    
    public function __construct() {
        parent::__construct();
        $this->load->helper('form');
        $this->load->helper('file');
        $this->load->helper('url');
        $this->load->model('Borrower_model', 'borrowermodel');
        $this->output->enable_profiler(false);
        $this->data['js'][] = 'jquery.melisr.htmltableoptions.js';
        $this->data['js'][] = 'jquery.borrower.js';
        $this->data['title'] = 'MELISR | Non MEL loans';
    }
    
    public function index() {
        $this->data['melrefnos'] = $this->borrowermodel->getNonMelLoanNumbers();
        $this->data['preptypes'] = $this->borrowermodel->getPrepTypes();
        
        if ($this->input->post('add') && $this->input->post('melrefno') && $this->input->post('barcodes') &&
            $this->input->post('preptypeid') && $this->input->post('specifyuser')) {
            $this->borrowermodel->addLoanPreps($this->input->post('melrefno'), $this->input->post('specifyuser'), 
                $this->input->post('preptypeid'), $this->input->post('barcodes'));
        }
        
        if ($this->input->post('returnpreps') && $this->input->post('melrefno')  && $this->input->post('toreturn') && 
                $this->input->post('returningofficer') && $this->input->post('returndate')) {
            $this->borrowermodel->returnLoanPreps($this->input->post('melrefno'), $this->input->post('toreturn'), 
                    $this->input->post('returningofficer'), $this->input->post('returndate'), $this->input->post('remarks'));
        }
        
        if ($this->input->post('melrefno')) {
            $this->data['botanist'] = $this->borrowermodel->getBotanist($this->input->post('melrefno'));
            $this->data['taxa'] = $this->borrowermodel->getTaxa($this->input->post('melrefno'));
            $this->data['loansummary'] = $this->borrowermodel->getLoanSummary($this->input->post('melrefno'));
            $this->data['loanpreparations'] = $this->borrowermodel->getLoanPreparations($this->input->post('melrefno'));
        }
        
        $this->data['toreturn'] = array();
        if ($this->input->post('return') && $this->input->post('melrefno') && $this->input->post('barcodes')) {
            $this->data['toreturn'] = $this->borrowermodel->findLoanPrepsToReturn($this->input->post('melrefno'), $this->input->post('barcodes'));
        }
        if ($this->input->post('toreturn')) {
            $this->data['toreturn'] = array_unique(array_merge($this->data['toreturn'], $this->input->post('toreturn')));
        }
        if ($this->input->post('returnall')) {
            foreach($this->data['loanpreparations'] as $row)
                $this->data['toreturn'][] = $row['LoanPreparationID'];
        }
        if ($this->input->post('clear') || $this->input->post('returnpreps')) {
            $this->data['toreturn'] = array();
        }
        
        $this->load->view('borrowerview', $this->data);
    }
}
?>
