<?php
/**
 * @property CI_Loader $load
 * @property CI_Form_validation $form_validation
 * @property CI_Input $input
 * @property CI_Email $email
 * @property CI_DB_active_record $db
 * @property CI_DB_forge $dbforge
 */
require_once('melisr.php');

class Gift extends Melisr {
    var $data;
    function __construct() {
        parent::__construct();
        $this->output->enable_profiler(TRUE);
        $this->load->model('giftmodel');
        $this->data['css'][] = 'gift.css';
        $this->data['js'][] = 'jquery.gift.js';
    }

    function index() {
        if ($this->input->post('reset'))
            redirect('gift');
        $this->data['years'] = $this->giftmodel->getYears();
        $this->data['institutions'] = $this->giftmodel->getInstitutions();
        $this->data['gifttypes'] = $this->giftmodel->getGiftTypes();
        $this->data['gifts'] = $this->giftmodel->getGifts(FALSE, $this->input->post('gifttype'), 
                $this->input->post('institution'), $this->input->post('year'));
        $this->load->view('gift/indexview', $this->data);
    }
    
    function edit($t=FALSE, $v=FALSE) {
        $this->data['gifttypes'] = $this->giftmodel->getGiftTypes();
        $this->data['giftagentroles'] = $this->giftmodel->getGiftAgentRoles();
        $this->data['shipmentmethods'] = $this->giftmodel->getShipmentMethods();
        if ($t == 'giftid' && $v) {
            $this->data['gift'] = $this->giftmodel->getGift($v);
            $this->data['giftagents'] = $this->giftmodel->getGiftAgents($v);
            $this->data['shipments'] = $this->giftmodel->getShipments($v);
            $this->data['giftpreparations'] = $this->giftmodel->getGiftPreparations($v);
            $this->load->view('gift/giftview', $this->data);
        }
    }
}

?>