<?php
/**
 * @property CI_Loader $load
 * @property CI_Form_validation $form_validation
 * @property CI_Input $input
 * @property CI_Email $email
 * @property CI_DB_active_record $db
 * @property CI_DB_forge $dbforge
 */

class Destructor extends Controller {
    var $data;
    
    function __construct() {
        parent::Controller();
        $this->load->helper('form');
        $this->load->helper('file');
        $this->load->helper('url');
        $this->load->model('destructormodel');
        $this->output->enable_profiler(TRUE);
        $this->data['bannerimage'] = $this->banner();
    }

    function index() {
        $this->load->model('recordsetmodel');
        $this->data['recordsets'] = $this->recordsetmodel->getCollectionObjectRecordSets();
        $this->data['agents'] = $this->destructormodel->getAgents();
        if($this->input->post('submit_destr')) {
            if ($this->input->post('recordset') && $this->input->post('destructive_sampling')
                    && $this->input->post('agent')) {
                $destruct = $this->destructormodel->batchDestruct($this->input->post('recordset'), 
                        $this->input->post('destructive_sampling'), $this->input->post('agent'),
                        $this->input->post('override'));
                $this->data['already_destructed'] = ($destruct) ? $destruct : FALSE;
            }
            else {
                $this->data['message'] = "You forgot something...";
                $this->load->view('message', $this->data);
            }
        }
        $this->load->view('destructorview', $this->data);
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