<?php
/**
 * @property CI_Loader $load
 * @property CI_Form_validation $form_validation
 * @property CI_Input $input
 * @property CI_Email $email
 * @property CI_DB_active_record $db
 * @property CI_DB_forge $dbforge
 */

class Admin_controller extends CI_Controller {
    var $data;

    function __construct() {
        parent::__construct();
        $this->output->enable_profiler(false);
        $this->data['title'] = 'MELISR | Admin.';
        $this->load->model('Admin_model', 'adminmodel');
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

    public function version() {
        if ($this->input->post('change')) {
            $this->adminmodel->changeVersion($this->input->post('version'));
        }
        
        $this->data['SpVersion'] = $this->adminmodel->spVersion();
        $this->load->view('admin_view', $this->data);
    }
    
    public function biocase() {
        if ($this->input->post('update')) {
            $updatefrom = $this->input->post('lastupdated');
            `php /home/melisr/biocase/update_biocase.php \"$updatefrom\"`;
        }
        
        $this->data['lastUpdated'] = $this->adminmodel->biocaseLastUpdated();
        $this->load->view('admin_view', $this->data);
    }

}
?>
