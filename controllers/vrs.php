<?php
/**
 * @property CI_Loader $load
 * @property CI_Form_validation $form_validation
 * @property CI_Input $input
 * @property CI_Email $email
 * @property CI_DB_active_record $db
 * @property CI_DB_forge $dbforge
 */

class Vrs extends Controller {
    var $data;
    
    function __construct() {
        parent::Controller();
        $this->load->helper('form');
        $this->load->helper('file');
        $this->load->helper('url');
        $this->output->enable_profiler(TRUE);
        $this->data['bannerimage'] = $this->banner();
        $this->data['title'] = 'Vic. Ref. Set';
        $this->load->model('vrsmodel');
    }

    function index() {
        if ($this->input->post('updatevrs'))
            $this->vrsmodel->createVRSRecords($_POST);
        
        $this->data['records'] = $this->vrsmodel->getRecords();
        if (!$this->data['records'])
            $this->data['message'] = 'There are no records to add to the Vic. Ref. Set collection at this time';
        $this->load->view('vrsview', $this->data);
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