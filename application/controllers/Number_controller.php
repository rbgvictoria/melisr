<?php
/**
 * @property CI_Loader $load
 * @property CI_Form_validation $form_validation
 * @property CI_Input $input
 * @property CI_Email $email
 * @property CI_DB_active_record $db
 * @property CI_DB_forge $dbforge
 */

class Number_controller extends CI_Controller {
    private $data;
    
    function __construct() {
        parent::__construct();
        $this->load->model('Count_model', 'countmodel');
        $this->output->enable_profiler(FALSE);
        $this->data = array();
        $this->data['title'] = 'MELISR | Numbers';
    }

    function index() {
        $this->session->unset_userdata(['error', 'warning', 'success']);
        $this->load->view('numbers', $this->data);
    }

    function spirit() {
        $this->data['spiritnumber'] = $this->countmodel->getSpiritNumber();
        $this->load->view('numbers', $this->data);
    }

    function slide() {
        $this->data['slidenumber'] = $this->countmodel->getSlideNumber();
        $this->load->view('numbers', $this->data);
    }
    
    function silicagel() {
        $this->data['silicagelnumber'] = $this->countmodel->getSilicagelNumber();
        $this->load->view('numbers', $this->data);
    }

    function melnumber() {
        $howmany = $this->input->post('howmany');
        $last = $this->countmodel->getMelNumber();
        $this->data['startnumber'] = $last+1;
        $this->data['endnumber'] = $last+$howmany;
        $this->data['howmany'] = $howmany;
        $this->load->view('numbers', $this->data);
    }
    
    function melnumbers() {
        $this->data['melnumbers'] = $this->countmodel->MelNumbers();
        $this->load->view('melnumbers_view', $this->data);
    }

    function melnumber_insert() {
        $this->data['startnumber'] = $this->input->post('startnumber');
        $this->data['endnumber'] = $this->input->post('endnumber');
        if ($this->input->post('username')){
            $this->data['username'] = $this->input->post('username');
            $this->data['print'] = TRUE;
            if ($this->countmodel->insertMELNumbers($this->input->post('username'), $this->input->post('startnumber'), $this->input->post('endnumber')))
                $this->load->view('numbers', $this->data);
            else {
                $this->data['message'] = 'This range of numbers can not be used';
                $this->load->view('message', $this->data);
            }
        } else {
                $this->data['message'] = 'Please type in a name';
                $this->load->view('message', $this->data);
        }
    }

    function loan() {
        $this->data['loannumber'] = $this->countmodel->getLoanNumber();
        $this->load->view('numbers', $this->data);
    }

    function exchange() {
        $this->data['exchangenumber'] = $this->countmodel->getExchangeNumber();
        $this->load->view('numbers', $this->data);
    }
    
    public function melnumbersusage($id) {
        if (!$id)
            $this->melnumbers();
        $this->data['usage'] = $this->countmodel->checkUsage($id);
        $this->load->view('melnumbersusage_view', $this->data);
    }

    function printcsv() {
        $uriarray = $this->uri->uri_to_assoc(3);
        $start = $uriarray['start'];
        $end = $uriarray['end'];

        $csv = array();
        for ($i = $start; $i <= $end; $i+=7) {
            $line = array();
            for ($j = 0; $j < 7; $j++) {
                if($i+$j<=$end)
                    $line[] = $i+$j;
            }
            $csv[] = implode(',', $line);
        }
        $path = '/var/www/melisr/tempfiles/';
        $filename = 'csv_' . time() . '.csv';
        $file = fopen($path.$filename, 'w');
        //echo $filename;
        fwrite($file, implode("\n", $csv));
        fclose($file);
        header('Content-type: text/csv; charset=UTF-8');
        header('Location: ' . base_url() . 'tempfiles/'  . $filename);
    }
    

}

?>