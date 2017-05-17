<?php
/**
 * @property CI_Loader $load
 * @property CI_Form_validation $form_validation
 * @property CI_Input $input
 * @property CI_Email $email
 * @property CI_DB_active_record $db
 * @property CI_DB_forge $dbforge
 */

class Recordset_controller extends CI_Controller {
    var $data;
    
    function __construct() {
        parent::__construct();
        $this->output->enable_profiler(false);
        $this->data['title'] = 'MELISR | Record set creator';
        $this->load->model('Recordset_model', 'recordsetmodel');
    }

    function index() {
        $this->session->unset_userdata(['error', 'warning', 'success']);
        $this->data['specifyusers'] = $this->recordsetmodel->getSpecifyUsers();
        $this->load->view('recordsetview', $this->data);
    }

    function check_names() {
        if (!$this->input->post('melnumbers')) {
            $this->session->set_flashdata('warning', 'This would work a lot better if you entered some MEL numbers');
        }
        $this->data['specifyusers'] = $this->recordsetmodel->getSpecifyUsers();
        $barcodes = $this->barcodeString($this->input->post('melnumbers'));
        $numbers = array();
        foreach (explode(',', $barcodes) as $number) {
            $number = trim(str_replace('MEL', '', $number));
            if (is_numeric($number))
                $numbers[] = $number;
        }
        if ($numbers) {
            $this->data['taxa'] = $this->recordsetmodel->getTaxa($numbers);
        }
        else {
            $this->session->set_flashdata('warning', 'None of the MEL numbers added are valid');
        }
        $this->data['specifyuser'] = $this->input->post('specifyuser');
        $this->data['recordsetname'] = $this->input->post('recordsetname');
        $this->data['melnumbers'] = $this->input->post('melnumbers');
        $this->load->view('recordsetview', $this->data);       
    }
    
    function create() {
        $this->data['specifyusers'] = $this->recordsetmodel->getSpecifyUsers();
        $specifyuser = $this->input->post('specifyuser');
        $recordsetname = $this->input->post('recordsetname');
        $melnumbers = $this->input->post('melnumbers');
        if ($this->input->post('submit1')) {
            if ($specifyuser < 1 || $recordsetname == '' || $melnumbers == '') {
                $this->session->set_flashdata('error', 'Not all fields have been filled in<br/>Record set cannot be created');
            } else {
                if ($this->recordsetmodel->findRecordSetName($specifyuser, $recordsetname) != false) {
                    $this->session->set_flashdata('error', 'A record set of this name already exists');
                } else {
                    $this->recordsetmodel->createRecordSet($specifyuser, $recordsetname);
                    $recordsetid = $this->recordsetmodel->findRecordSetName($specifyuser, $recordsetname);
                    $melnumbers = str_replace("\t", '', $melnumbers);
                    $melnumbers = preg_replace('/\r\n|\r|\n/', ',', $melnumbers);
                    $melnumbers = trim($melnumbers, ", \t\r\n");
                    
                    
                    if ($this->input->post('allparts')) $parts = true;
                    else $parts = false;
                    $this->recordsetmodel->createRecordSetItems($recordsetid, $melnumbers, $parts);
                    $this->session->set_flashdata('success', "Recordset <b>$recordsetname</b> has been created");
                }
            }
        } else {
            $melnumbers = $this->input->post('melnumbers');
            $melnumbers = str_replace("\t", '', $melnumbers);
            $melnumbers = preg_replace('/\r\n|\r|\n/', ',', $melnumbers);
            $melnumbers = trim($melnumbers, ", \t\r\n");
        }
        $this->data['specifyuser'] = $specifyuser;
        $this->data['recordsetname'] = $recordsetname;
        $this->data['melnumbers'] = $melnumbers;
        $this->load->view('recordsetview', $this->data);
    }
    
    private function barcodeString($melnumbers) 
    {
        $barcodes = str_replace("\t", '', $melnumbers);
        $barcodes = preg_replace('/\r\n|\r|\n/', ',', $barcodes);
        $barcodes = trim($barcodes, ", \t\r\n");
        return $barcodes;
    }
    
    function manage() {
        $this->data['recordSetUsers'] = $this->recordsetmodel->getRecordSetUsers();
        $this->load->view('managerecordsetview', $this->data);
    }
    
    function delete_users_recordsets($userID) {
        $sets = $this->recordsetmodel->getRecordSetsForUser($userID);
        if ($sets) {
            foreach ($sets as $set) {
                $this->recordsetmodel->deleteRecordSet($set->RecordSetID);
            }
        }
        redirect('recordset/manage');
    }
}

?>