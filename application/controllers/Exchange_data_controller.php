<?php
/**
 * @property CI_Loader $load
 * @property CI_Form_validation $form_validation
 * @property CI_Input $input
 * @property CI_Email $email
 * @property CI_DB_active_record $db
 * @property CI_DB_forge $dbforge
 */

class Exchange_data_controller extends CI_Controller {
    var $loan;
    var $data;

    function __construct() {
        parent::__construct();
        $this->load->helper('form');
        $this->load->helper('file');
        $this->load->helper('url');
        $this->output->enable_profiler(FALSE);
        $this->load->model('Exchange_model', 'exchangemodel');
        $this->load->model('Recordset_model', 'recordsetmodel');
        $this->data['title'] = 'MELISR | Exchange data';
        $this->load->library('Hispid5ToCsv');
    }
    
    function index() {
        $this->data['gifts'] = $this->exchangemodel->getGiftNumbers();
        $this->data['recordsets'] = $this->recordsetmodel->getCollectionObjectRecordSets();
        
        $this->load->model('Admin_model', 'adminmodel');
        $this->data['biocaseLastUpdated'] = $this->adminmodel->biocaseLastUpdated();
        
        $this->load->view('exchangedataview', $this->data);
    }
    
    function getdata() {
        $catalognumbers = FALSE;
        if($this->input->post('giftnumber')) {
            $catalognumbers = $this->exchangemodel->getCatalogNumbersExchange($this->input->post('giftnumber'));
            $filename = str_replace('/', '_', str_replace(' ', '_', trim($this->input->post('giftnumber'))));
        }
        elseif ($this->input->post('recordset')) {
            $catalognumbers = $this->exchangemodel->getCatalogNumbersRecordSet($this->input->post('recordset'));
            $filename = 'recordset_' . $this->input->post('recordset');
        }
        if ($catalognumbers) {
            $values = array();
            foreach ($catalognumbers as $unit)
                $values[] = '<value>' . $unit . '</value>';
            $values = implode('', $values);
            
            $url = 'http://biocase.rbg.vic.gov.au/biocase/pywrapper.cgi';
            $dsa = 'mel_avh';
            
            $query =<<<QUERY
<?xml version="1.0" encoding="UTF-8"?>
<request xmlns="http://www.biocase.org/schemas/protocol/1.3">
  <header><type>search</type></header>
  <search>
    <requestFormat>http://www.tdwg.org/schemas/abcd/2.06</requestFormat>
    <responseFormat start="0" limit="1000">http://www.tdwg.org/schemas/abcd/2.06</responseFormat>
      <filter>
        <in path="/DataSets/DataSet/Units/Unit/UnitID">$values</in>
      </filter>
      <count>false</count>
  </search>
</request>
QUERY;
            
            $command = "curl --data \"query=" . urlencode($query) . "\" https://biocase.rbg.vic.gov.au/biocase/pywrapper.cgi?dsa=mel_avh";

            $result = `$command`;
            
            $orgdoc = new DOMDocument('1.0', 'UTF-8');
            $orgdoc->loadXML($result);
            
            $content = $orgdoc->getElementsByTagName('content')->item(0);
            $recordCount = $content->getAttribute('recordCount');
            $recordDrop = $content->getAttribute('recordDropped');
            
            if ($recordCount > 0) {
                if ($this->input->post('format') == 'csv') {
                    $data = $this->hispid5tocsv->parseHISPID5($orgdoc);
                    $csv = $this->hispid5tocsv->outputToCsv($data, TRUE);
                    header('Content-type: text/csv');
                    header("Content-Disposition: attachment; filename=$filename.csv");
                    echo $csv;
                    return;
                }
                elseif ($this->input->post('format') == 'biocase') {
                    header('Content-type: text/xml');
                    echo $orgdoc->saveXML();
                    return;
                }
            
                $node = $orgdoc->getElementsByTagName('DataSets')->item(0);

                $newdoc = new DOMDocument('1.0', 'UTF-8');
                $node = $newdoc->importNode($node, TRUE);
                $newdoc->appendChild($node);

                $docstring = $newdoc->saveXML();
                $docstring = str_replace('hispid:', '', $docstring);

                $search = "<DataSets>";
                $repl = '<DataSets xmlns="http://www.tdwg.org/schemas/abcd/2.06" 
                    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" 
                    xsi:schemaLocation="http://www.tdwg.org/schemas/abcd/2.06 http://rs.tdwg.org/abcd/2.06/b/ABCD_2.06b.xsd">';


                $docstring = str_replace($search, $repl, $docstring);
                
                header('Content-type: text/xml');
                echo $docstring;
            }
            else {
                $this->session->set_flashdata('warning', 'No records returned by BioCASe provider');
                $this->index();
            }
//            
            
            
        }
        else {
            $this->session->set_flashdata('warning', 'Please select an exchange or a record set');
            $this->index();
        }
        
    }
    
    public function updateBiocase() {
        $updatefrom = $this->input->post('lastupdated');
        `php /home/melisr/biocase/update_biocase.php \"$updatefrom\" exchange`;
        redirect('exchangedata');
    }
    
}

?>