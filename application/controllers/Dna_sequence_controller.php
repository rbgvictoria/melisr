<?php
/**
 * @property CI_Loader $load
 * @property CI_Form_validation $form_validation
 * @property CI_Input $input
 * @property CI_Email $email
 * @property CI_DB_active_record $db
 * @property CI_DB_forge $dbforge
 */

class Dna_sequence_controller extends CI_Controller {
    private $data;
    
    function __construct() {
        parent::__construct();
        $this->output->enable_profiler(false);
        $this->data = array();
        $this->data['title'] = 'MELISR | DNA sequence';
        
        $this->load->model('Dna_sequence_model', 'dnasequencemodel');
        $this->data['js'][] = 'jquery.fileupload.js';
        $this->data['specify_user'] = $this->dnasequencemodel->getSpecifyUsers();
    }

    function index() {
        $this->data['messages'] = array();
        
        if ($this->input->post('submit')) {
            if ($_FILES['upload']['error'] == 0 && $this->input->post('specify_user')) {
                $this->parseHeaderRow();
            }
            else {
                if ($_FILES['upload']['error'] != 0) {
                    $this->data['messages'][] = "No sequence file selected...";
                }
                if (!$this->input->post('specify_user')) {
                    $this->data['messages'][] = "Please select a Specify username";
                }
            }
        }
        
        elseif ($this->input->post('submit_2')) {
            if ($this->input->post('specify_user')) {
                $this->loadData();
            }
            else {
                $this->data['messages'][] = "Please select a Specify username";
            }
        }
        
        elseif ($this->input->post('cancel')) {
            redirect('dnasequence');
        }
        
        if (!($this->input->post('submit')) || $this->data['messages']) {
            $baseurl = base_url();
            $this->data['intro'] = <<<EOT
<p>You can add sequences to MELISR here by uploading a CSV file with catalogue numbers and Genbank
    accession numbers. A link to an example CSV file is provided below. Only the <i>CatalogNumber</i> 
    column is required. <i>SampleNumber</i>, <i>PreparedBy</i>, <i>PreparedDate</i>, <i>Sequencer</i>, <i>Project</i>, 
    <i>BOLDSampleID</i>, <i>BOLDBarcodeID</i> and <i>TaxonName</i> are optional. Column headers have to match
    exactly the headers given above; columns with any other headers will be considered markers. The taxon name
    is for your own use only and is not uploaded. If a Sample number is given, a Preparation record with prep. type
    &apos;Molecular isolate&apos; will be created, which you can query on in MELISR. Molecular isolate preparations
    may be uploaded without sequences and sequences may be uploaded without preparations. DNA Sequence and Preparation
    records will only be created once, so you can keep using the same spreadsheet.</p>
                    
<p>You can upload sequences for both MEL and non-MEL collections here. Sequences for collections that are not from MEL 
    will be loaded into the non-MEL collection, which is the collection we use to record incoming loans. If you don&apos;t
    have access to this collection you can ask one of the MELISR administrators for access.</p>
                    
<p>The upload process comprises two steps. on this page you can select your Specify username from the drop-down list and
    select the file you want to upload. On the next page you can review the columns you are uploading and set default values
    for some of the columns.</p>
EOT;
        }
        
        $this->load->view('dnasequence_view', $this->data);
        
    }
    
    public function example()
    {
        $this->output->enable_profiler(false);
        $example = file_get_contents(VIEWPATH . 'media/dnasequences_example.csv');
        header("Content-Disposition: attachment; filename=\"melisr_dna_sequences_example.csv\"");
        header("Content-type: text/csv");
        echo $example;
    }
    
    private function parseHeaderRow() {
        $file = file_get_contents($_FILES['upload']['tmp_name']);
        $this->data['tempfile'] = 'dna.' . uniqid() . '.csv';
        file_put_contents('tempfiles/' . $this->data['tempfile'], $file);
        
        $this->data['header_row'] = array(
            'catalog_number_column' => FALSE,
            'sample_number_column' => FALSE,
            'prepared_by_column' => FALSE,
            'prepared_date_column' => FALSE,
            'sequencer_column' => FALSE,
            'project_column' => FALSE,
            'bold_barcode_id_column' => FALSE,
            'bold_sample_id_column' => FALSE,
            'marker_columns' => array()
        );
        
        $this->data['markers'] = $this->dnasequencemodel->getMarkers();
        
        $this->data['intro'] = <<<EOT
<p>The table below shows the mapping of the columns in the uploaded CSV file, based on the information in the header row. 
    If you find anything out of order please make the necessary changes in the CSV file and upload it again.</p>
<p>You can fill in defaults for certain fields. Defaults will not overwrite values in the CSV file, but will only be applied
    for empty cells or when a column is missing.</p>
<p>Markers that are not already in the pick list will be indicated. You can safely upload sequences from markers that are not
    yet in the pick list, once you are assured that it is indeed a marker. You can add new markers to the pick list 
    <a href="dnasequence/markers" target="_blank">here</a>, either before or after you load the data.</p>
<p>New projects do need to be added before loading the data. You can do that <a href="dnasequence/projects" target="_blank">here</a>.</p>
EOT;
        
        $handle = fopen('tempfiles/' . $this->data['tempfile'], 'r');
        $headerrow = fgetcsv($handle);
        foreach ($headerrow as $index => $value) {
            switch ($value) {
                case 'CatalogNumber':
                    $this->data['header_row']['catalog_number_column'] = $index;
                    break;

                case 'SampleNumber':
                    $this->data['header_row']['sample_number_column'] = $index;
                    break;
                
                case 'PreparedBy':
                    $this->data['header_row']['prepared_by_column'] = $index;
                    break;
                
                case 'PreparedDate':
                    $this->data['header_row']['prepared_date_column'] = $index;
                    break;

                case 'Sequencer':
                    $this->data['header_row']['sequencer_column'] = $index;
                    break;

                case 'Project':
                    $this->data['header_row']['project_column'] = $index;
                    break;
                
                case 'BOLDBarcodeID':
                    $this->data['header_row']['bold_barcode_id_column'] = $index;
                    break;
                
                case 'BOLDSampleID':
                    $this->data['header_row']['bold_sample_id_column'] = $index;
                    break;
                
                case 'TaxonName':
                    break;
                
                default:
                    $this->data['header_row']['marker_columns'][$value] = $index;
                    break;
            }
        }
        
        fclose($handle);
    }
    
    private function loadData() {
        $infile = fopen('tempfiles/' . $this->input->post('temp_file'), 'r');
        $firstline = fgetcsv($infile);
        $marker_columns = array();
        foreach ($firstline as $index => $value) {
            if (!in_array($value, array('CatalogNumber', 'SampleNumber', 'PreparedBy', 'PreparedDate', 'Project', 'Sequencer', 'TaxonName')))
                $marker_columns[] = $index;
        }
        
        $data = array();
        while (!feof($infile)) {
            $line = fgetcsv($infile);
            $rec = array();
            if ($line) {
                foreach ($line as $index => $value) {
                    if (in_array($firstline[$index], array('CatalogNumber', 'SampleNumber', 'PreparedBy', 
                        'PreparedDate', 'Project', 'Sequencer', 'TaxonName', 'BOLDBarcodeID', 'BOLDSampleID'))) {
                        $rec[$firstline[$index]] = $value;
                    }
                    else {
                        if ($value)
                            $rec['sequences'][$firstline[$index]] = $value;
                    }
                }
                $data[] = $rec;
            }
        }
        
        $this->data['data'] = $data;
        
        $report = array();
        
        foreach ($data as $row) {
            if (isset($row['SampleNumber']) || isset($row['sequences'])) {
                $reportRow = array();
                
                if (substr($row['CatalogNumber'], 0, 4) == 'MEL ') {
                    $colobjid = $this->dnasequencemodel->getCollectionObjectIdMel(trim(substr($row['CatalogNumber'], 4)));
                    if (!$colobjid) {
                        $reportRow[] = array (
                            'catalogNumber' => $row['CatalogNumber'],
                            'type' => 'error',
                            'note' => 'Catalogue number not in MELISR.'
                        );
                        $report[] = $reportRow;
                        continue;
                    }
                    $collectionid = 4;
                    $prep_type_id = 27;
                }
                else {
                    $colobjid = $this->dnasequencemodel->getCollectionObjectIdNonMel($row['CatalogNumber']);
                    if (!$colobjid) {
                        $colobjid = $this->dnasequencemodel->createCollectionObjectNonMel($row['CatalogNumber']);
                        $reportRow[] = array(
                            'catalogNumber' => $row['CatalogNumber'],
                            'type' => 'warning',
                            'note' => "Catalogue number was not found. A new Collection Object record was created."
                        );
                    }
                    $collectionid = 32769;
                    $prep_type_id = 28;
                }
                
                if (isset($row['SampleNumber'])) {
                    $preparedby = FALSE;
                    if (isset($row['PreparedBy']) && $row['PreparedBy']) {
                        $preparedby = $row['PreparedBy'];
                    }
                    elseif ($this->input->post('prepared_by_default')) {
                        $preparedby = $this->input->post('prepared_by_default');
                    }
                    
                    $prepared_by_id = NULL;
                    if ($preparedby) {
                        $prepared_by_id = $this->dnasequencemodel->getPreparedByID($preparedby);
                        if (!$prepared_by_id) {
                            $reportRow[] = array(
                                'catalogNumber' => $row['CatalogNumber'],
                                'type' => 'warning',
                                'note' => "Prepared by Agent '$preparedby' is not in Agent table."
                            );
                        }
                    }
                    
                    $prepdate = FALSE;
                    $prepared_date = NULL;
                    $prepared_date_precision = NULL;
                    
                    if (isset($row['PreparedDate']) && $row['PreparedDate']) {
                        $prepdate = $row['PreparedDate'];
                    }
                    elseif ($this->input->post('prepared_date')) {
                        $prepdate = $this->input->post('prepared_date');
                    }
                    
                    if ($prepdate) {
                        $bits = explode('-', $prepdate);
                        switch (count($bits)) {
                            case 3:
                                $prepared_date = $bits[0] . '-' . $bits[1] . '-' . $bits[2];
                                $prepared_date_precision = 1;
                                break;

                            case 3:
                                $prepared_date = $bits[0] . '-' . $bits[1] . '-01';
                                $prepared_date_precision = 2;
                                break;

                            case 3:
                                $prepared_date = $bits[0] . '-01-01';
                                $prepared_date_precision = 3;
                                break;

                            default:
                                break;
                        }
                    }
                    
                    $prep = $this->dnasequencemodel->findPreparation($colobjid, $prep_type_id, $row['SampleNumber']);
                    if ($prep) {
                        $reportRow[] = array(
                            'catalogNumber' => $row['CatalogNumber'],
                            'type' => 'info',
                            'note' => "Molecular isolate Preparation record $row[SampleNumber] already exists."
                        );
                        if ((($prep->PreparedByID || $prepared_by_id) && $prep->PreparedByID != $prepared_by_id) ||
                                ($prep->PreparedDate || $prepared_date) && $prep->PreparedDate != $prepared_date) {
                            $this->dnasequencemodel->updatePreparation($prep->PreparationID, $prepared_by_id, $prepared_date, $prepared_date_precision, $prep->Version + 1);
                            $reportRow[] = array(
                                'catalogNumber' => $row['CatalogNumber'],
                                'type' => 'info',
                                'note' => "Molecular isolate Preparation record $row[SampleNumber] has been updated."
                            );
                        }
                        
                    }
                    else {
                        $this->dnasequencemodel->insertPreparation($colobjid, $collectionid, $prep_type_id, $row['SampleNumber'], $prepared_by_id, $prepared_date, $prepared_date_precision);
                        $reportRow[] = array(
                            'catalogNumber' => $row['CatalogNumber'],
                            'type' => 'info',
                            'note' => "A new Molecular isolate Preparation record was created: $row[SampleNumber]."
                        );
                    }
                }
                
                $sequencer_id = NULL;
                if ($sequencer) {
                    $sequencer_id = $this->dnasequencemodel->getPreparedByID($sequencer);
                }

                $bold_barcode_id = (isset($row['BOLDBarcodeID']) && $row['BOLDBarcodeID']) ? $row['BOLDBarcodeID'] : NULL;
                $bold_sample_id = (isset($row['BOLDSampleID']) && $row['BOLDSampleID']) ? $row['BOLDSampleID'] : NULL;
                    
                if (isset($row['sequences']) && $row['sequences']) {
                    $sequencer = FALSE;
                    if (isset($row['Sequencer']) && $row['Sequencer']) {
                        $sequencer = $row['Sequencer'];
                    }
                    elseif ($this->input->post('sequencer_default')) {
                        $sequencer = $this->input->post('sequencer_default');
                    }
                    
                    $seqinfo = $this->sequences($row['CatalogNumber'], $colobjid, $collectionid, $row['SampleNumber'], $row['sequences'], $sequencer_id, $bold_barcode_id, $bold_sample_id);
                    $reportRow = array_merge($reportRow, $seqinfo);
                    
                }
                elseif (isset($row['BOLDBarcodeID']) && $row['BOLDBarcodeID']) {
                    $seqinfo = $this->bold($row['CatalogNumber'], $colobjid, $row['SampleNumber'], $sequencer_id, $row['BOLDBarcodeID'], $bold_sample_id);
                    $reportRow = array_merge($reportRow, $seqinfo);
                }
                
                
                $project = FALSE;
                if (isset($row['Project']) && $row['Project']) {
                    $project = $row['Project'];
                }
                elseif ($this->input->post('project_default')) {
                    $project = $this->input->post('project_default');
                }
                
                if ($project) {
                    $projectid = $this->dnasequencemodel->getProjectID($project, $collectionid);
                    
                    if ($projectid) {
                        $this->dnasequencemodel->insertProjectColObj($colobjid, $projectid, $collectionid);
                        $reportRow[] = array(
                            'catalogNumber' => $row['CatalogNumber'],
                            'type' => 'info',
                            'note' => "$row[CatalogNumber] has been added to project '$project'."
                        );
                    }
                    else {
                        $reportRow[] = array(
                            'catalogNumber' => $row['CatalogNumber'],
                            'type' => 'warning',
                            'note' => "Project '$project' is not in MELISR."
                        );
                    }
                }
            }
            $report[] = $reportRow;    
        }
        $this->data['report'] = $report;
    }
    
    
    private function doCurl($url) {   
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        //curl_setopt($ch,CURLOPT_POST, TRUE);
        //curl_setopt($ch,CURLOPT_POSTFIELDS, $postfields);    
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
        //curl_setopt($ch, CURLOPT_PROXY, 'http://proxy.rbg.vic.gov.au:8080'); 
        //curl_setopt($ch, CURLOPT_PROXYPORT, 8080); 
        //curl_setopt ($ch, CURLOPT_PROXYUSERPWD, 'helpdesk:glass3d');
        //curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, 300);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }

    private function sequences($catno, $colobjid, $collection_id, $sample_number, $sequences, $sequencer, $bold_barcode_id, $bold_sample_id) {
        $seqinfo = array();
        $sequencer_id = NULL;
        if ($sequencer) {
            $sequencer_id = $this->dnasequencemodel->getPreparedByID($sequencer);
        }
        
        foreach ($sequences as $marker => $accessionno) {
            $dnaSequence = $this->dnasequencemodel->findGenBankAccessionNumber($colobjid, $accessionno);
            if ($dnaSequence) {
                $seqinfo[] = array(
                    'catalogNumber' => $catno,
                    'type' => 'info',
                    'note' => "DNA Sequence record $marker: $accessionno already exists."
                );
                if (($bold_barcode_id && (!$row->BOLDBarcodeID || $row->BOLDBarcodeID != $bold_barcode_id)) || 
                        ($bold_sample_id && (!$row->BOLDSampleID || $row->BOLDSampleID != $bold_sample_id))) {
                    $this->dnasequencemodel->updateBOLD($dnaSequence->DnaSequenceID, $bold_barcode_id, $bold_sample_id, $dnaSequence->Version);
                    $seqinfo[] = array(
                        'catalogNumber' => $catno,
                        'type' => 'info',
                        'note' => "BoL barcode and/or sample number updated for $accessionno."
                    );
                }
                if (($sequencer) && (!$dnaSequence->AgentID || $dnaSequence->AgentID != $sequencer)) {
                    $this->dnasequencemodel->updateSequencer($dnaSequence->DnaSequenceID, $sequencer, $dnaSequence->Version);
                    $seqinfo[] = array(
                        'catalogNumber' => $catno,
                        'type' => 'info',
                        'note' => "Sequencer updated for $accessionno."
                    );
                }
            }
            else {
                $seq = array(
                    'Sequence' => NULL,
                    'compA' => NULL,
                    'compT' => NULL,
                    'compC' => NULL,
                    'compG' => NULL,
                    'ambiguous' => NULL,
                    'total' => NULL
                );
                
                switch ($marker) {
                    case 'ITS':
                    case 'ETS':
                        $seq['Genome'] = 'nuclear';
                        break;

                    case 'rbcL':
                    case 'matK':
                    case 'rpL32-trnL':
                        $seq['Genome'] = 'chloroplast';
                        break;

                    default:
                        break;
                }
                        
                $url =  "http://eutils.ncbi.nlm.nih.gov/entrez/eutils/efetch.fcgi?db=nuccore&id={$accessionno}&rettype=gb&retmode=xml";
                $result = $this->doCurl($url);
                file_put_contents("tempfiles/gbxml/gb.{$accessionno}.xml", $result);
                if (substr($result, 0, 2) == '<?') {
                    $doc = new DOMDocument();
                    $doc->loadXML($result);
                    $list = $doc->getElementsByTagName('GBSeq_sequence');
                    if ($list->length > 0) {
                        $sequence = $list->item(0)->nodeValue;
                        $seq = array();
                        $seq['Sequence'] = $sequence;
                        $seq['compA'] = substr_count($sequence, 'a');
                        $seq['compT'] = substr_count($sequence, 't');
                        $seq['compC'] = substr_count($sequence, 'c');
                        $seq['compG'] = substr_count($sequence, 'g');
                        $seq['ambiguous'] = strlen($sequence) - ($seq['compA'] + $seq['compT'] + $seq['compC'] + $seq['compG']);
                        $seq['total'] = strlen($sequence);

                    }
                }
                else {
                    $seqinfo[] = array(
                        'catalogNumber' => $catno,
                        'type' => 'info',
                        'note' => "New DNA Sequence record created for $marker: $accessionno."
                    );
                }
                
                $seqmarker = ($marker != 'unknown') ? $marker : NULL;
                $this->dnasequencemodel->insertSequence($colobjid, $collection_id, $sample_number, $accessionno, $seqmarker, $seq, $sequencer, $bold_barcode_id, $bold_sample_id);
                $seqinfo[] = array(
                    'catalogNumber' => $catno,
                    'type' => 'info',
                    'note' => "New DNA Sequence record $marker: $accessionno created."
                );
            }
        }
        return $seqinfo;
    }
    
    private function bold($catno, $collobjid, $sample_number, $sequencer, $bold_barcode_id, $bold_sample_id) {
        $seqinfo = array();
        $bold = $this->dnasequencemodel->findBOLDBarcodeID($collobjid, $bold_barcode_id);
        if ($bold) {
            $seqinfo[] = array(
                'catalogNumber' => $catno,
                'type' => 'info',
                'note' => "DNA Sequence record BOLD barcode only, $bold_barcode_id, already exists."
            );
            if (($sample_number && $sample_number != $bold['SampleNumber']) ||
                    ($sequencer_id && $sequencer_id != $bold['AgentID']) ||
                    ($bold_sample_id && $bold_sample_id != $bold['BOLDSampleID'])) {
                $this->dnasequencemodel->updateBOLD2($bold['DNASequenceID'], $sample_number, $sequencer, $bold_sample_id, $bold['Version']+1);
                $seqinfo[] = array(
                    'catalogNumber' => $catno,
                    'type' => 'info',
                    'note' => "DNA Sequence record BOLD barcode only, $bold_barcode_id, updated."
                );
            }
        }
        else {
            $this->dnasequencemodel->insertBOLD($collobjid, $bold_barcode_id, $bold_sample_id, $sample_number, $sequencer);
            $seqinfo[] = array(
                'catalogNumber' => $catno,
                'type' => 'info',
                'note' => "New DNA Sequence record BOLD barcode only, $bold_barcode_id, created."
            );
        }
        return $seqinfo;
    }    
    
    public function markers() {
        $this->data['markers'] = $this->dnasequencemodel->getMarkerDetails();
        $this->load->view('sequencemarker_view', $this->data);
    }
    
    public function new_marker() {
        if ($this->input->post('new_marker') && $this->input->post('specify_user')) {
            $this->dnasequencemodel->addNewMarker($this->input->post('new_marker'));
        }
        redirect('dnasequence/markers');
    }


    public function projects() {
        $this->data['projects'] = $this->dnasequencemodel->getProjects();
        $this->load->view('sequenceproject_view', $this->data);
    }
    
    public function new_project() {
        if ($this->input->post('new_project') && $this->input->post('specify_user')) {
            $this->dnasequencemodel->addNewProject($this->input->post('new_project'), 4);
            $this->dnasequencemodel->addNewProject($this->input->post('new_project'), 32769);
        }
        redirect('dnasequence/projects');
    }

    private function banner() {
        $banners = get_dir_file_info('./images/banners', TRUE);
        $banners = array_values($banners);
        $count = count($banners);
        $i = rand(0, $count-1);
        return $banners[$i]['name'];
    }
    
    

    

}


