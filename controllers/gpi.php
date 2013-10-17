<?php
/**
 * @property CI_Loader $load
 * @property CI_Form_validation $form_validation
 * @property CI_Input $input
 * @property CI_Email $email
 * @property CI_DB_active_record $db
 * @property CI_DB_forge $dbforge
 */

/**
 * MELISR Gpi Class
 * 
 * Controller for GPI application. Application loads a CSV file with MEL numbers,
 * then grabs the required metadata from MELISR and stores it in the right format
 * in the gpi database. Some quality quality checks are done on whether a basionym
 * is really a basionym, if every unit has a current determination as well as a
 * type status designation and on presence of authors for scientific names. There
 * is an option to fix the errors in MELISR and then reload the corresponding records
 * into the gpi database. Once all errors in a batch have been fixed the BioCASe
 * provider can be queried for the records in the batch. The BioCASe provider will
 * then spit out the XML, validated against the African types schema. Post-processing 
 * takes off the BioCASe wrapper, removes the namespace aliases and puts a no-namespace
 * declaration in the opening tag for the root element. This post-processing will
 * not take place if the BioCASe provider dropped any records. Instead a message
 * will be sent back to the browser. This will generally not happen if all the 
 * reported errors have been fixed.
 * 
 * @package     GPI
 * @author      Niels Klazenga
 * @copyright   copyright (c) 2011-2012, Royal Botanic Gardens, Melbourne
 * @license     http://creativecommons.org/licenses/by/3.0/au/ CC BY 3.0
 */
class Gpi extends Controller {
    var $data;

    function __construct() {
        parent::Controller();
        $this->load->helper('form');
        $this->load->helper('file');
        $this->load->helper('url');
        $this->output->enable_profiler(TRUE);
        $this->data['bannerimage'] = $this->banner();
        $this->load->model('gpimodel');
        $this->load->library('Hispid5ToCsv');
    }

    function index() {
        $this->data['DataSets'] = $this->gpimodel->showDataSets();
        $this->load->view('gpi_view', $this->data);
    }

    function upload() {
        $filename = $_FILES['upload']['name'];
        $handle = fopen($_FILES['upload']['tmp_name'], 'r');
        $n = 0;
        $units = array();
        while (!feof($handle)) {
            $row = fgetcsv($handle);
            $n++;
            if ($row[0] && $n > 1) {
                $catno = trim(substr($row[0], 4));
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

    function banner() {
        $banners = get_dir_file_info('./images/banners', TRUE);
        $banners = array_values($banners);
        $count = count($banners);
        $i = rand(0, $count-1);
        return $banners[$i]['name'];
    }
    
    function get_xml($batchno, $format=FALSE) {
        $this->output->enable_profiler(FALSE);
        if ($batchno) {
            $units = $this->gpimodel->getUnitIds($batchno);
            $numunits = count($units);
            
            $units = implode('', $units);
            
            $this->gpimodel->updateMetadata(date('Y-m-d'));
            
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
            $command = "curl --data \"query=" . urlencode($query) . "\" http://203.55.15.78/biocase/pywrapper.cgi?dsa=mel_gpi";

            $result = `$command`;
            
            $orgdoc = new DOMDocument('1.0', 'UTF-8');
            $orgdoc->loadXML($result);
            
            if ($format == 'biocase') {
                header('Content-type: text/xml');
                header("Content-Disposition: attachment; filename=gpi_batch$batchno.xml");
                echo $orgdoc->saveXML();
                return;
            }
            elseif ($format == 'csv') {
                $data = $this->hispid5tocsv->parseHISPID5($orgdoc);
                $csv = $this->hispid5tocsv->outputToCsv($data);
                header('Content-type: text/csv');
                header("Content-Disposition: attachment; filename=gpi_batch$batchno.csv");
                echo $csv;
                return;
            }
            
            $content = $orgdoc->getElementsByTagName('content')->item(0);
            $recordCount = $content->getAttribute('recordCount');
            $recordDrop = $content->getAttribute('recordDropped');
            
            if (!$recordDrop || $recordDrop == 0) {
                $node = $orgdoc->getElementsByTagName('DataSet')->item(0);

                $newdoc = new DOMDocument('1.0', 'UTF-8');
                $node = $newdoc->importNode($node, TRUE);
                $newdoc->appendChild($node);

                $docstring = $newdoc->saveXML();
                $docstring = str_replace('ns0:', '', $docstring);

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
    
    public function delete_batch($batch) {
        $this->gpimodel->deleteBatch($batch);
        $this->index();
    }
    
    function recordsxml () {
        if ($this->input->post('submit') && $this->input->post('melnos')) {
            $this->output->enable_profiler(FALSE);
            $melnos = explode("\n", $this->input->post('melnos'));
            $units = array();
            foreach ($melnos as $index => $value) {
                $melnos[$index] = trim(str_replace('MEL', '', $value));
                if ($melnos[$index]) $units[] = '<value>MEL' . $melnos[$index] . '</value>';
            }
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
            $command = "curl --data \"query=" . urlencode($query) . "\" http://203.55.15.78/biocase/pywrapper.cgi?dsa=mel_gpi";

            $result = `$command`;
            
            $orgdoc = new DOMDocument('1.0', 'UTF-8');
            $orgdoc->loadXML($result);
            
            $batchno = date("Ymd");
            
            if ($this->input->post('format') == 'biocase') {
                header('Content-type: text/xml');
                header("Content-Disposition: attachment; filename=gpi_$batchno.xml");
                echo $orgdoc->saveXML();
                return;
            }
            elseif ($this->input->post('format') == 'csv') {
                $data = $this->hispid5tocsv->parseHISPID5($orgdoc);
                $csv = $this->hispid5tocsv->outputToCsv($data);
                header('Content-type: text/csv');
                header("Content-Disposition: attachment; filename=gpi_$batchno.csv");
                echo $csv;
                return;
            }
            
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
        elseif ($this->input->post('update') && $this->input->post('melnos')) {
            $melnos = explode("\n", $this->input->post('melnos'));
            $units = array();
            foreach ($melnos as $index => $value) {
                $melnos[$index] = trim(str_replace('MEL', '', $value));
                if ($melnos[$index]) $units[] = 'MEL' . $melnos[$index];
            }
            
            $this->data['Units'] = $this->gpimodel->updateUnits($units);
            
            
            
            $this->load->view('recordsxml_view', $this->data);
        }
        else {
            $this->load->view('recordsxml_view', $this->data);
        }
    }
    
    function mark_in_melisr($batch) {
        $this->gpimodel->markInMelisr($batch);
        $this->index();
    }

}
?>
