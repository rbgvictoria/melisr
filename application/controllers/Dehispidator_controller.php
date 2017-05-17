<?php
/**
 * @property CI_Loader $load
 * @property CI_Form_validation $form_validation
 * @property CI_Input $input
 * @property CI_Email $email
 * @property CI_DB_active_record $db
 * @property CI_DB_forge $dbforge
 */

class Dehispidator_controller extends CI_Controller {
    var $data;

    function __construct() {
        parent::__construct();
        $this->output->enable_profiler(FALSE);
        $this->data['title'] = 'MELISR | Dehispidator';
        $this->load->library('Hispid3ToCsv');
        $this->load->library('Hispid5ToCsv');
        $this->data['js'][] = 'jquery.fileupload.js';
    }

    function index() {
        $this->load->view('dehispidator_view', $this->data);
    }
    
    function hispid3_convert() {
        $filename = $_FILES['upload']['name'];
        $hispid = file_get_contents($_FILES['upload']['tmp_name']);
        
        $parsed = $this->hispid3tocsv->parseHispid3($hispid);
        if ($this->input->post('outputfields')) {
            $parsed = $this->hispid3tocsv->massageData($parsed);
            //$this->load->model('dehispidatormodel');
            //$parsed = $this->dehispidatormodel->addCurationOfficer($parsed);
            $csv = $this->hispid3tocsv->outputToCsv($parsed, $this->input->post('outputfields'));
        }
        else
            $csv = $this->hispid3tocsv->outputToCsv($parsed);
        
        if (strpos($filename, '.')){
            $outputfilename = substr($filename, 0, strpos($filename, '.')) . '.csv';
        }
        else {
            $outputfilename = $filename . '.csv';
        }

        header("Content-Disposition: attachment; filename=$outputfilename");
        header('Content-type: text/csv');
        echo $csv;
    }
    
    function hispid5_convert() {
        $filename = $_FILES['upload2']['name'];
        $hispid = file_get_contents($_FILES['upload2']['tmp_name']);
        
        if ($this->input->post('ad')) {
            $hispid = substr($hispid, strpos($hispid, '<DataSet>'));
            $hispid = substr($hispid, 0, strpos($hispid, '</DataSet>')+  strlen('</DataSets>'));
        }
        
        
        if (strpos($filename, '.'))
            $outputfilename = substr($filename, 0, strpos($filename, '.')) . '.csv';
        else 
            $outputfilename = $filename . '.csv';
        header("Content-Disposition: attachment; filename=$outputfilename");
        header('Content-type: text/csv');
        $doc = new DOMDocument('1.0', 'UTF-8');
        $doc->loadXML($hispid);
        
        
        $parsed = $this->hispid5tocsv->parseHISPID5($doc);
        
        $friendlycolumnnames = ($this->input->post('friendlycolumnnames')) ? TRUE : FALSE;
        $csv = $this->hispid5tocsv->outputToCsv($parsed, $friendlycolumnnames);
        echo $csv;
        
        //print_r($csv);
        
        /*if (strpos($filename, '.'))
            $outputfilename = substr($filename, 0, strpos($filename, '.')) . '.csv';
        else 
            $outputfilename = $filename . '.csv';

        header("Content-Disposition: attachment; filename=$outputfilename");
        header('Content-type: text/csv');
        echo $csv;*/
    }
    
    

    /*function upload() {
        $filename = $_FILES['upload']['name'];
        $handle = fopen($_FILES['upload']['tmp_name'], 'r');
        $n = 0;
        $units = array();
        while (!feof($handle)) {
            $row = fgetcsv($handle);
            $n++;
            if ($row[0] && $n > 1) {
                $catno = substr($row[0], 4);
                $catno = str_pad($catno, 7, '0', STR_PAD_LEFT) . 'A';
                $units[] = $catno;
            }
        }
        fclose($handle);
        if ($units) {
            //header('Content-type: text/xml; charset=UTF-8');
            //echo $this->lapimodel->getMetadata($units);
            $batchno = substr($filename, 6, strpos($filename, '.')-strpos($filename, '_')-1);
            $this->gpimodel->insertDataSet($batchno);
            $this->gpimodel->insertUnits($units, $batchno);
            $this->gpimodel->insertIdentifications($units);
        }
        $this->index();
    }

    function show_errors($t, $batchno) {
        $this->data['BatchNo'] = $batchno;
        $this->data['Errors'] = $this->gpimodel->showErrors($batchno);
        $this->data['SpecifyUsers'] = $this->gpimodel->getSpecifyUsers();
        $this->load->view('gpi_errors', $this->data);
    }

    function create_error_record_set($t, $batchno) {
        if($this->input->post('spuser') && $this->input->post('spuser') != 0  && $this->input->post('recsetitems')) {
            $this->gpimodel->createErrorRecordSet($batchno, $this->input->post('recsetname'), $this->input->post('spuser'), $this->input->post('recsetitems'));
        }
        else
            $this->data['message'] = 'Please select a Specify user.';
        $this->show_errors($t, $batchno);
    }

    function create_error_csv($t, $batchno) {
        $errors = $this->gpimodel->showErrors($batchno);
        $csv = array();
        $melnumbers = array();
        foreach ($errors as $type=>$group) {
            foreach ($group as $error) {
                $melnumbers[] = $error['MELNumber'];
                $csv[] = implode(',', $error) . ',' . $type;
            }
        }
        array_multisort($melnumbers, $csv);
        $path = '/var/www/dev/melisr/tempfiles/';
        $filename = 'batch_' . $batchno . '_errors.csv';
        $file = fopen($path.$filename, 'w');
        //echo $filename;
        fwrite($file, 'MEL number,Taxon name,Author,Type status,Type of error' . "\n");
        fwrite($file, implode("\n", $csv));
        fclose($file);
        header('Content-type: text/csv; charset=UTF-8');
        header('Location: ' . base_url() . 'tempfiles/'  . $filename);

    }

    function fix_errors($t, $batchno) {
        if ($batchno) {
            $this->gpimodel->fixErrors($batchno);
            $this->show_errors($t, $batchno);
        }
        else
            $this->index();
    }

    function delete_hybrid_dets($t, $batchno) {
        if ($batchno) {
            $this->gpimodel->deleteHybridDets($batchno);
            $this->show_errors($t, $batchno);
        }
        else
            $this->index();
    }

    function get_xml($t, $batchno) {
        if ($batchno) {
            $units = $this->gpimodel->getUnitIds($batchno);
            $numunits = count($units);
            
            $units = implode('', $units);
            
            $query =<<<QUERY
<?xml version="1.0" encoding="UTF-8"?>
<request xmlns="http://www.biocase.org/schemas/protocol/1.3">
  <header><type>search</type></header>
  <search>
    <requestFormat>http://plants.jstor.org/XSD/AfricanTypesv2.xsd</requestFormat>
    <responseFormat start="0" limit="1000">http://plants.jstor.org/XSD/AfricanTypesv2.xsd</responseFormat>
      <filter>
        <in path="/DataSet/Unit/UnitID">$units</in>
      </filter>
      <count>false</count>
  </search>
</request>
QUERY;
            $command = "curl --data \"query=" . urlencode($query) . "\" http://avh.rbg.vic.gov.au/biocase/pywrapper.cgi?dsa=mel_gpi";

            $result = `$command`;
            
            $orgdoc = new DOMDocument('1.0', 'UTF-8');
            $orgdoc->loadXML($result);
            
            $content = $orgdoc->getElementsByTagName('content')->item(0);
            $recordCount = $content->getAttribute('recordCount');
            $recordDrop = $content->getAttribute('recordDropped');
            
            if (!$recordDrop || $recordDrop == 0) {
                $node = $orgdoc->getElementsByTagName('DataSet')->item(0);

                $newdoc = new DOMDocument('1.0', 'UTF-8');
                $node = $newdoc->importNode($node, TRUE);
                $newdoc->appendChild($node);

                $docstring = $newdoc->saveXML();
                $docstring = str_replace('african:', '', $docstring);

                $search = "<DataSet xmlns:african=\"http://plants.jstor.org/XSD/AfricanTypesv2.xsd\">";
                $repl = "<DataSet xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xsi:noNamespaceSchemaLocation=\"http://plants.jstor.org/XSD/AfricanTypesv2.xsd\">";

                $docstring = str_replace($search, $repl, $docstring);

                $newdoc = new DOMDocument('1.0', 'UTF-8');
                $newdoc->loadXML($docstring);
                $newdoc->formatOutput = TRUE;

                header('Content-type: text/xml');
                echo $newdoc->saveXML();
            }
            else {
                $this->data['message'] = "There were $recordDrop invalid records.\nPlease check your data.";
                //$this->data['message'] .= "<pre>$query</pre>";
                $this->load->view('message', $this->data);
            }
        }
        else
            $this->index();
    }
*/
}
?>
