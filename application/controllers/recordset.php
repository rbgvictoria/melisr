<?php
/**
 * @property CI_Loader $load
 * @property CI_Form_validation $form_validation
 * @property CI_Input $input
 * @property CI_Email $email
 * @property CI_DB_active_record $db
 * @property CI_DB_forge $dbforge
 */

class RecordSet extends Controller {
    var $data;
    
    function __construct() {
        parent::Controller();
        $this->load->helper('form');
        $this->load->helper('file');
        $this->load->helper('url');
        $this->output->enable_profiler(TRUE);
        $this->data['bannerimage'] = $this->banner();
        $this->data['title'] = 'MELISR | Record set creator';
        $this->data['js'][] = 'jquery.recordset.js'; 
        $this->load->model('recordsetmodel');
    }

    function index() {
        $this->data['specifyusers'] = $this->recordsetmodel->getSpecifyUsers();
        $this->load->view('recordsetview', $this->data);
    }

    function banner() {
        $banners = get_dir_file_info('./images/banners', TRUE);
        $banners = array_values($banners);
        $count = count($banners);
        $i = rand(0, $count-1);
        return $banners[$i]['name'];
    }
    
    function check_names() {
        if (!$this->input->post('melnumbers')) {
            $this->data['message'] = 'This would work a lot better if you entered some MEL numbers';
            $this->load->view('message', $this->data);         
        }
        $this->data['specifyusers'] = $this->recordsetmodel->getSpecifyUsers();
        $melnumbers = $this->input->post('melnumbers');
        $melnumbers = str_replace("\n", ',', $melnumbers);
        $melnumbers = explode(',', $melnumbers);
        $numbers = array();
        foreach ($melnumbers as $number) {
            $number = trim(str_replace('MEL', '', $number));
            if (is_numeric($number))
                $numbers[] = $number;
        }
        if ($numbers) {
            $this->data['taxa'] = $this->recordsetmodel->getTaxa($numbers);
            $this->load->view('recordsetview', $this->data);       
        }
        else {
            $this->data['message'] = 'None of the MEL numbers added are valid';
            $this->load->view('message', $this->data);       
        }
    }
    
    function create() {
        $this->data['specifyusers'] = $this->recordsetmodel->getSpecifyUsers();
        $specifyuser = $this->input->post('specifyuser');
        $recordsetname = $this->input->post('recordsetname');
        $melnumbers = $this->input->post('melnumbers');
        if ($this->input->post('submit1')) {
            if ($specifyuser < 1 || $recordsetname == '' || $melnumbers == '') {
                $this->data['message'] = 'Not all fields have been filled in<br/>Record set cannot be created';
                $this->load->view('message', $this->data);
            } else {
                if ($this->recordsetmodel->findRecordSetName($specifyuser, $recordsetname) != FALSE) {
                    $this->data['message'] = 'A record set of this name already exists';
                    $this->load->view('message', $this->data);
                } else {
                    $this->recordsetmodel->createRecordSet($specifyuser, $recordsetname);
                    $recordsetid = $this->recordsetmodel->findRecordSetName($specifyuser, $recordsetname);
                    $melnumbers = str_replace("\t", '', $melnumbers);
                    $melnumbers = str_replace("\n", ',', $melnumbers);
                    $melnumbers = trim($melnumbers, ", \t\n");
                    
                    
                    if ($this->input->post('allparts')) $parts = TRUE;
                    else $parts = FALSE;
                    $this->recordsetmodel->createRecordSetItems($recordsetid, $melnumbers, $parts);
                    $this->data['specifyuser'] = $specifyuser;
                    $this->data['recordsetname'] = $recordsetname;
                    $this->data['melnumbers'] = $melnumbers;
                    $this->load->view('recordsetview', $this->data);
                }
            }
        } else {
            $melnumbers = $this->input->post('melnumbers');
            $melnumbers = str_replace("\t", '', $melnumbers);
            $melnumbers = str_replace("\n", ',', $melnumbers);
            $melnumbers = trim($melnumbers, ", \t\n");
            $melnumbers = str_replace('MEL ', '', $melnumbers);
            $melnumbers = 'MEL ' . str_replace(',', ',MEL ', $melnumbers);
            $this->data['specifyuser'] = $specifyuser;
            $this->data['recordsetname'] = $recordsetname;
            $this->data['melnumbers'] = $melnumbers;
            $this->load->view('recordsetview', $this->data);
        }
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