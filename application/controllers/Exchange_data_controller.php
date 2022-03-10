<?php
/**
 * @property CI_Loader $load
 * @property CI_Form_validation $form_validation
 * @property CI_Input $input
 * @property CI_Email $email
 * @property CI_DB_active_record $db
 * @property CI_DB_forge $dbforge
 */

class Exchange_data_controller extends CI_Controller {
    var $loan;
    var $data;

    function __construct() {
        parent::__construct();
        $this->output->enable_profiler(false);
        $this->load->model('Recordset_model', 'recordsetmodel');
        $this->load->model('Exchange_model', 'exchangemodel');
        $this->load->model('Exchange_data_model', 'exchangedatamodel');
        $this->data['title'] = 'MELISR | Exchange data';
    }
    
    function index() {
        $this->load->library('dwca');
        $this->data['gifts'] = $this->exchangemodel->getGiftNumbers();
        $this->data['recordsets'] = $this->recordsetmodel->getCollectionObjectRecordSets();
        
        $this->data['giftnumber'] = '';
        $this->data['recordset'] = '';
        
        if ($this->input->post('submit_exchange') && $this->input->post('giftnumber')) {
            $this->data['giftnumber'] = $this->input->post('giftnumber');
            $records = $this->exchangedatamodel->getExchangeData($this->input->post('giftnumber'));
            $this->dwca->createDarwinCoreArchive($records, 'dwca-mel-exchange-' . $this->input->post('giftnumber') . '-' . date('Ymd_Hi'));
        }
        if ($this->input->post('submit_recordset') && $this->input->post('recordset')) {
            $this->data['recordset'] = $this->input->post('recordset');
            $records = $this->exchangedatamodel->getRecordSetData($this->input->post('recordset'));
            $this->dwca->createDarwinCoreArchive($records, 'dwca-mel-recordset-' . $this->input->post('recordset') . '-' . date('Ymd_Hi'));
        }
        
        $this->load->view('exchangedataview', $this->data);
    }
}

?>