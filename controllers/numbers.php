<?php
/**
 * @property CI_Loader $load
 * @property CI_Form_validation $form_validation
 * @property CI_Input $input
 * @property CI_Email $email
 * @property CI_DB_active_record $db
 * @property CI_DB_forge $dbforge
 */

class Numbers extends Controller {
    private $data;
    
    function __construct() {
        parent::Controller();
        $this->load->helper('form');
        $this->load->helper('file');
        $this->load->helper('url');
        $this->output->enable_profiler(FALSE);
        $this->data = array();
        $this->data['bannerimage'] = $this->banner();
        $this->data['title'] = 'MELISR | Numbers';
    }

    function index() {
        $this->load->view('numbers', $this->data);
    }

    function banner() {
        $banners = get_dir_file_info('./images/banners', TRUE);
        $banners = array_values($banners);
        $count = count($banners);
        $i = rand(0, $count-1);
        return $banners[$i]['name'];
    }

    function spirit() {
        $this->load->model('countmodel');
        $this->data['spiritnumber'] = $this->countmodel->getSpiritNumber();
        $this->load->view('numbers', $this->data);
    }

    function slide() {
        $this->load->model('countmodel');
        $this->data['slidenumber'] = $this->countmodel->getSlideNumber();
        $this->load->view('numbers', $this->data);
    }
    
    function silicagel() {
        $this->load->model('countmodel');
        $this->data['silicagelnumber'] = $this->countmodel->getSilicagelNumber();
        $this->load->view('numbers', $this->data);
    }

    function melnumber() {
        $howmany = $this->input->post('howmany');
        $this->load->model('countmodel');
        $last = $this->countmodel->getMelNumber();
        $this->data['startnumber'] = $last+1;
        $this->data['endnumber'] = $last+$howmany;
        $this->data['howmany'] = $howmany;
        $this->load->view('numbers', $this->data);
    }
    
    function melnumbers() {
        $this->load->model('countmodel');
        $this->data['melnumbers'] = $this->countmodel->MelNumbers();
        $this->load->view('melnumbers_view', $this->data);
    }

    function melnumber_insert() {
        $this->load->model('countmodel');
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
        $this->load->model('countmodel');
        $this->data['loannumber'] = $this->countmodel->getLoanNumber();
        $this->load->view('numbers', $this->data);
    }

    function exchange() {
        $this->load->model('countmodel');
        $this->data['exchangenumber'] = $this->countmodel->getExchangeNumber();
        $this->load->view('numbers', $this->data);
    }
    
    public function melnumbersusage($id) {
        if (!$id)
            $this->melnumbers();
        $this->load->model('countmodel');
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