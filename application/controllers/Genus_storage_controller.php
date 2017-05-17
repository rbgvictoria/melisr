<?php
/**
 * @property CI_Loader $load
 * @property CI_Form_validation $form_validation
 * @property CI_Input $input
 * @property CI_Email $email
 * @property CI_DB_active_record $db
 * @property CI_DB_forge $dbforge
 */

class Genus_storage_controller extends CI_Controller {
    private $data;
    
    function __construct() {
        parent::__construct();
        $this->load->helper('form');
        $this->load->helper('file');
        $this->load->helper('url');
        $this->output->enable_profiler(false);
        $this->load->model('Genus_storage_model', 'genusstoragemodel');
        $this->data = array();
        $this->data['title'] = 'MELISR | Genus storage';
    }

    function index() {
        $this->data['taxa'] = $this->genusstoragemodel->getTaxa();
        $this->load->view('genusstorageview', $this->data);
    }

    function edit($t) {
        $this->data['taxonid'] = $t;
        $this->data['name'] = $this->genusstoragemodel->getName($t);
        $this->data['classification'] = $this->genusstoragemodel->getClassification($t);
        $this->data['options'] = $this->genusstoragemodel->getStorageDropDown();
        $this->data['colobjects'] = $this->genusstoragemodel->getCollectionObjects($t);
        $this->load->view('genusstorageeditview', $this->data);
    }

    function insert() {
        if ($this->input->post('storedunder')) {
            $this->genusstoragemodel->insertTaxon($this->input->post('taxonid'), $this->input->post('name'), $this->input->post('storedunder'));
            $this->genusstoragemodel->updateCollectionObjectStorage($this->input->post('colobj'), $this->input->post('storagetype'), $this->input->post('storedunder'));
            redirect('/genusstorage/');
        }
        else redirect ('/genusstorage/edit/' . $this->input->post('taxonid'));
    }
}

?>