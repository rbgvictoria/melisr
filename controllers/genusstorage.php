<?php
/**
 * @property CI_Loader $load
 * @property CI_Form_validation $form_validation
 * @property CI_Input $input
 * @property CI_Email $email
 * @property CI_DB_active_record $db
 * @property CI_DB_forge $dbforge
 */

class GenusStorage extends Controller {
    function __construct() {
        parent::Controller();
        $this->load->helper('form');
        $this->load->helper('file');
        $this->load->helper('url');
        $this->output->enable_profiler(TRUE);
    }

    function index() {
        $this->load->model('genusstoragemodel');
        $data['bannerimage'] = $this->banner();
        $data['taxa'] = $this->genusstoragemodel->getTaxa();
        $this->load->view('genusstorageview', $data);
    }

    function banner() {
        $banners = get_dir_file_info('./images/banners', TRUE);
        $banners = array_values($banners);
        $count = count($banners);
        $i = rand(0, $count-1);
        return $banners[$i]['name'];
    }
    
    function edit($t) {
        $this->load->model('genusstoragemodel');
        $data['bannerimage'] = $this->banner();
        $data['taxonid'] = $t;
        $data['name'] = $this->genusstoragemodel->getName($t);
        $data['classification'] = $this->genusstoragemodel->getClassification($t);
        $data['options'] = $this->genusstoragemodel->getStorageDropDown();
        
        $this->load->view('genusstorageeditview', $data);
    }

    function insert() {
        if ($this->input->post('storedunder')) {
            $this->load->model('genusstoragemodel');
            $this->genusstoragemodel->insertTaxon($this->input->post('taxonid'), $this->input->post('name'), $this->input->post('storedunder'));
            redirect('/genusstorage/');
        }
        else redirect ('/genusstorage/edit/' . $this->input->post('taxonid'));
    }

}

?>