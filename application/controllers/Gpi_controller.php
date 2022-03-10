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
 * in the gpi database. Some quality checks are done on whether a basionym
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
class Gpi_controller extends CI_Controller {
    var $data;

    function __construct() {
        parent::__construct();
        $this->output->enable_profiler(false);
        $this->data['title'] = 'MELISR | GPI';
        $this->load->model('Gpi_model', 'gpimodel');
        $this->load->library('Hispid5ToCsv');
        $this->session->unset_userdata(['error', 'warning', 'success']);
        $this->data['js'][] = 'jquery.fileupload.js';
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
                //$catno = str_pad($catno, 7, '0', STR_PAD_LEFT) . 'A';
                $units[] = $catno;
            }
        }
        fclose($handle);
        if ($units) {
            $colobjs = $this->gpimodel->getCollectionObjects($units);
            $batchno = substr($filename, 6, strpos($filename, '.')-strpos($filename, '_')-1);
            $this->gpimodel->createOrUpdateProject($batchno);
            $this->gpimodel->insertCollectionObjects($batchno, $colobjs);
        }
        redirect('gpi');
    }

    function show_errors($t=false, $batchno=false) {
        $this->data['BatchNo'] = $batchno;
        $this->data['Errors'] = $this->gpimodel->showErrors($batchno);
        $this->data['SpecifyUsers'] = $this->gpimodel->getSpecifyUsers();
        $this->load->view('gpi_errors', $this->data);
    }
    
    function show_parts($t, $batchno) {
        $this->data['BatchNo'] = $batchno;
        $this->data['parts'] = $this->gpimodel->getParts($batchno);
        $this->load->view('gpi_parts', $this->data);
    }

    function create_error_record_set($t=false, $batchno=false) {
        if($this->input->post('spuser') && $this->input->post('spuser') != 0  && $this->input->post('recsetitems')) {
            $recsetitems = array_unique($this->input->post('recsetitems'));
            sort($recsetitems);
            $recordset = $this->gpimodel->createErrorRecordSet($batchno, $this->input->post('recsetname'), $this->input->post('spuser'), $recsetitems);
            $recordsetLink = "https://specify.rbg.vic.gov.au/specify/view/collectionobject/" . $recsetitems[0] . "/?recordsetid=" . $recordset;
            $this->session->set_flashdata('success', "Record set <b>" . $this->input->post('recsetname') . 
                    '</b> has been created<br/><a href="' . $recordsetLink . '" target="_blank">' . $recordsetLink . '</a>');
        }
        else {
            $errors = [];
            if (!$this->input->post('spuser')) {
                $errors[] = 'Please select a Specify user...';
            }
            if (!$this->input->post('recsetitems')) {
                $errors[] = 'Please select some records...';
            }
            $this->session->set_flashdata('error', $errors);
        }
        $this->show_errors($t, $batchno);
    }
    
    public function dwca($batch)
    {
        $this->load->library('dwca');
        $data = $this->gpimodel->getDarwinCoreData($batch);
        $this->dwca->createDarwinCoreArchive($data, 'dwca-gpi-batch-' . $batch . '-' . date('Ymd_Hi'));
    }
    
    public function jstor($batch)
    {
        $dataset = $this->gpimodel->getDataset($batch);
        $units = $this->gpimodel->getUnits($batch);
        $identifications = $this->gpimodel->getIdentifications($batch);
        $params = array(
            'dataset' => $dataset,
            'units' => $units,
            'identifications' => $identifications
        );
        $this->load->library('jstor', $params);
        $this->jstor->createXml();
    }

    public function delete_batch($batch) {
        $this->gpimodel->deleteBatch($batch);
        redirect('gpi');
    }
    
    function mark_in_melisr($batch) {
        $this->gpimodel->markInMelisr($batch);
        $this->index();
    }
}

/*
 * /application/controllers/Gpi_controller.php
 */
