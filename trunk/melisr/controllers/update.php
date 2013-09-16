<?php
/**
 * @property CI_Loader $load
 * @property CI_Form_validation $form_validation
 * @property CI_Input $input
 * @property CI_Email $email
 * @property CI_DB_active_record $db
 * @property CI_DB_forge $dbforge
 */

class Update extends Controller {
    var $data;

    function __construct() {
        parent::Controller();
        $this->load->helper('form');
        $this->load->helper('file');
        $this->load->helper('url');
        $this->output->enable_profiler(TRUE);
        $this->data['bannerimage'] = $this->banner();
    }

    function index() {
        if ($this->input->post('submit1')) {
            $updateduntil = date('j F Y, H:i:s') . ' AEST';
            $interval = $this->input->post('interval1');
            $command = "php /home/melisr/ibra/update_ibra.php $interval";
            `$command`;
            $this->data['message1'] = 'updated until ' . $updateduntil;
        }
        
        if ($this->input->post('submit2')) {
            $updateduntil = date('j F Y, H:i:s') . ' AEST';
            $interval = $this->input->post('interval2');
            $command = "php /home/melisr/biocase/update_biocase.php $interval";
            `$command`;
            $this->data['message2'] = 'updated until ' . $updateduntil;
        }
        
        if ($this->input->post('submit3')) {
             $command = "php /home/melisr/biocase/update_biocase.php reindex loans";
            `$command`;
        }
        
        if ($this->input->post('submit4')) {
            $command = "php /home/melisr/biocase/update_biocase.php reindex exchange";
            `$command`;
        }

        $this->load->view('update_view', $this->data);
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
