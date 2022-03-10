<?php
/**
 * @property CI_Loader $load
 * @property CI_Form_validation $form_validation
 * @property CI_Input $input
 * @property CI_Email $email
 * @property CI_DB_active_record $db
 * @property CI_DB_forge $dbforge
 */

class Vrs_controller extends CI_Controller {
    var $data;
    
    function __construct() {
        parent::__construct();
        $this->data['title'] = 'Vic. Ref. Set';
        $this->load->model('Vrs_model', 'vrsmodel');
    }

    function index() {
        if ($this->input->post('updatevrs')) {
            $recordsCreated = $this->vrsmodel->createVRSRecords($_POST);
            $this->session->set_flashdata('success', "$recordsCreated new records have been created in the VRS collection");
        }
        $this->data['records'] = $this->vrsmodel->getRecords();
        if (!$this->data['records']) {
            $this->data['message'] = 'There are no records to add to the Vic. Ref. Set collection at this time';
        }
        $this->load->view('vrsview', $this->data);
    }

    public function not_in_mel() {
        
        if ($this->input->post('delete') && $this->input->post('colobj')) {
            $this->vrsmodel->deleteVrsRecords($this->input->post('colobj'));
        }
        
        $this->data['records'] = $this->vrsmodel->getNotInMelRecords();
        $this->load->view('vrs_notinmelview', $this->data);
    }
    
    public function updated_determinations() {
        $this->data['records'] = $this->vrsmodel->getDifferentDets();
        $this->load->view('vrs_differentdetsview', $this->data);
    }
}

?>