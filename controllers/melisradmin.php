<?php
/**
 * @property CI_Loader $load
 * @property CI_Form_validation $form_validation
 * @property CI_Input $input
 * @property CI_Email $email
 * @property CI_DB_active_record $db
 * @property CI_DB_forge $dbforge
 */

class MelisrAdmin extends Controller {
    var $data;

    function __construct() {
        parent::Controller();
        $this->load->helper('form');
        $this->load->helper('file');
        $this->load->helper('url');
        $this->output->enable_profiler(TRUE);
        $this->data['bannerimage'] = $this->banner();
        $this->data['title'] = 'MELISR | Admin.';
        $this->load->model('adminmodel');
    }

    function index() {
        $this->load->view('admin_view', $this->data);
    }

    function loggedin() {
        $this->data['ActiveLogins'] = $this->adminmodel->activeLogins();
        $this->load->view('admin_view', $this->data);
    }

    function logoff() {
        if ($this->input->post('spusers')) {
            $this->adminmodel->logOffUsers($this->input->post('spusers'));
        }
        $this->loggedin();
    }
    
    function locks() {
        $this->data['Locks'] = $this->adminmodel->showLocks();
        $this->load->view('admin_view', $this->data);
    }
    
    function releaselocks() {
        if ($this->input->post('tasks'))
            $this->adminmodel->releaseLocks($this->input->post('tasks'));
        $this->locks();
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
