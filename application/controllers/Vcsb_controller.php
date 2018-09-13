<?php
/**
 * @property CI_Loader $load
 * @property CI_Form_validation $form_validation
 * @property CI_Input $input
 * @property CI_Email $email
 * @property CI_DB_active_record $db
 * @property CI_DB_forge $dbforge
 */

class Vcsb_controller extends CI_Controller {
    var $data;
    var $collectionId;
    
    function __construct() 
    {
        parent::__construct();
        $this->collectionId = 294912;
        $this->output->enable_profiler(false);
        $this->data['title'] = 'VCSB';
        $this->load->model('Vcsb_model', 'vcsbmodel');
    }

    function index() 
    {
        if ($this->input->post('updatevcsb')) {
            $this->vcsbmodel->createVcsbRecords($_POST);
            $this->session->set_flashdata('success', 'VCSB collection has been updated');
        }
        $this->data['records'] = $this->vcsbmodel->getRecords();
        if (!$this->data['records']) {
            $this->data['message'] = 'There are no records to add to the Victorian Conservation Seed Bank collection at this time';
        }
        $this->load->view('vcsb_view', $this->data);
    }

    public function not_in_mel() 
    {
        
        if ($this->input->post('delete') && $this->input->post('colobj')) {
            $this->vcsbmodel->deleteVcsbRecords($this->input->post('colobj'));
        }
        
        $this->data['records'] = $this->vcsbmodel->getNotInMelRecords();
        $this->load->view('vcsb_not_in_mel_view', $this->data);
    }
    
    public function updated_determinations() {
        $this->data['records'] = $this->vcsbmodel->getDifferentDets();
        $this->load->view('vcsb_different_dets_view', $this->data);
    }
    
    public function labels()
    {
        $this->load->model('Recordset_model', 'recordset_model');
        $this->load->model('Label_data_model', 'labeldata_model');
        
        $this->data['labeldata'] = [];
        if ($this->input->post('submit')) {
            $records = false;
            if ($this->input->post('recordset')) {
                $records = $this->recordset_model->getRecordSetItems($this->input->post('recordset'));
            }
            elseif ($this->input->post('vcsbno_start')) {
                if ($this->input->post('vcsbno_count') || $this->input->post('vcsbno_end')) {
                    $records = $this->vcsbmodel->getRecordSetFromRange($this->input->post('vcsbno_start'),
                            $this->input->post('vcsbno_count'), $this->input->post('vcsbno_end'));
                } else {
                    $this->session->set_flashdata('error', 'Please enter the number of labels or the last VCSB number to print');
                }
            }
            if ($records) {
                $config = $this->labelConfig();
                $labelData = $this->vcsbmodel->getVcsbLabelData($records, $this->input->post('labelType'));
                if ($labelData) {
                    $start = 0;
                    if ($this->input->post('start')) {
                        $start = $this->input->post('start') - 1;
                    }
                    $this->printLabel($labelData, $config, $start);
                }
            }
            else {
                $this->session->set_flashdata('error', 'Please select a record set or enter a range of VCSB numbers');
            }
        }
        $this->data['recordsets'] = $this->recordset_model->getCollectionObjectRecordSets($this->collectionId);
        $this->load->view('vcsb_label_view', $this->data);
    }
    
    protected function labelConfig() {
        $config = [
            'numx'  =>  2,
            'numy'  =>  2,
            'barcode'   =>  true,
            'footerpositionabsolute'    =>  true,
            'footeroffsety' => 25,
            'wheader' => 87,
            'xbarcode'  =>  44.5,
            'ybarcode'  =>  20,
            'ybarcodetext'  =>  31,
            'whtml' =>  86.5,
            'yhtml' =>  28,
        ];
        $config['dimensions'] = $this->labelDimensions($config);
        return $config;
    }
    
    function labelDimensions($config) {
        $numx = $config['numx'];
        $numy = $config['numy'];
        $labeldimensions = array();
        $labeldimensions['labelheight'] = (isset($config['labelheight'])) ? $config['labelheight'] : 290/$numy;
        $labeldimensions['labelwidth'] = (isset($config['labelwidth'])) ? $config['labelwidth'] : 210/$numx;
        $xpos = 7.5;
        $yheader = 7.5;
        $labeldimensions['labelheader_pos'] = array();
        $labeldimensions['labelheader_pos']['x'] = [];
        for ($i = 0; $i<$numx; $i++) {
            $labeldimensions['labelheader_pos']['x'][] = $xpos + $i*$labeldimensions['labelwidth'];
        }
        $labeldimensions['labelheader_pos']['y'] = [];
        for ($i = 0; $i<$numy; $i++) {
            $labeldimensions['labelheader_pos']['y'][] = $yheader + $i*$labeldimensions['labelheight'];
        }
        $labeldimensions['labelbody_pos'] = array();
        $labeldimensions['labelbody_pos']['x'] = array();
        for ($i = 0; $i<$numx; $i++){
            $labeldimensions['labelbody_pos']['x'][] = $xpos + $i*$labeldimensions['labelwidth'];
        }
        $labeldimensions['labelbody_pos']['y'] = array();
        for ($i = 0; $i<$numy; $i++) {
            $labeldimensions['labelbody_pos']['y'][] = $config['yhtml'] + $i*$labeldimensions['labelheight'];
        }
        $labeldimensions['barcode_pos'] = array();
        $labeldimensions['barcode_pos']['x'] = array();
        for ($i = 0; $i<$numx; $i++) {
            $labeldimensions['barcode_pos']['x'][] = $config['xbarcode'] + $i*$labeldimensions['labelwidth'];
        }
        $labeldimensions['barcode_pos']['y'] = array();
        for ($i = 0; $i<$numy; $i++) {
            $labeldimensions['barcode_pos']['y'][] = $config['ybarcode'] + $i*$labeldimensions['labelheight'];
        }
        $labeldimensions['barcodetext_pos'] = array();
        $labeldimensions['barcodetext_pos']['x'] = array();
        for ($i = 0; $i<$numx; $i++) {
            $labeldimensions['barcodetext_pos']['x'][] = $config['xbarcode'] + $i*$labeldimensions['labelwidth'];
        }
        $labeldimensions['barcodetext_pos']['y'] = array();
        for ($i = 0; $i<$numy; $i++) {
            $labeldimensions['barcodetext_pos']['y'][] = $config['ybarcodetext'] + $i*$labeldimensions['labelheight'];
        }
        if (isset($config['footeroffsety'])) {
            $offset = $config['footeroffsety'];
        }
        else {
            $offset = 15;
        }
        $labeldimensions['labelfooter_pos'] = array();
        $labeldimensions['labelfooter_pos']['x'] = array();
        for ($i = 0; $i<$numx; $i++) {
            $labeldimensions['labelfooter_pos']['x'][] = $xpos + $i*$labeldimensions['labelwidth'];
        }
        $labeldimensions['labelfooter_pos']['y'] = array();
        for ($i = 0; $i<$numy; $i++) {
            $labeldimensions['labelfooter_pos']['y'][] = ($labeldimensions['labelheight']) - $offset + $i*$labeldimensions['labelheight'];
        }
        return $labeldimensions;
    }

    function printLabel($labeldata, $props, $start=0) {
        $numx = $props['numx'];
        $numy = $props['numy'];
        $labelheight = $props['dimensions']['labelheight'];
        $labelwidht = $props['dimensions']['labelwidth'];
        $labelheader_pos = $props['dimensions']['labelheader_pos'];
        $barcode_pos = $props['dimensions']['barcode_pos'];
        $barcodetext_pos = $props['dimensions']['barcodetext_pos'];
        $labelbody_pos = $props['dimensions']['labelbody_pos'];
        $labelfooter_pos = $props['dimensions']['labelfooter_pos'];
        $numlabels = $numx*$numy;


        set_time_limit (600);
        // create new PDF document
        
        $format = 'A4';
        $orientation = 'P';
        $pdf = new TCPDF($orientation, 'mm', $format, true, 'UTF-8', false);
        
        
        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Niels Klazenga');
        $pdf->SetTitle('VCSB Label');
        $pdf->SetSubject('VCSB Label');

        //set margins
        $pdf->SetMargins(5, 7.5, 5);

        //set auto page breaks
        $pdf->SetAutoPageBreak(FALSE, 7.5);

        // remove default header/footer
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        // ---------------------------------------------------------

        // set font
        $pdf->SetFont('helvetica', '', 9);
        // set cell padding
        $pdf->setCellPaddings(0, 0, 0, 0);
        // set cell margins
        $pdf->setCellMargins(0, 0, 0, 0);

        $labelheader='<p style="font-weight: bold"><span style="font-size: 11.5pt;">VICTORIAN CONSERVATION SEED BANK</span><br />
                <span style="font-size: 9pt;">MELBOURNE, AUSTRALIA</span></p>';
        
        $barcodestyle = array(
            'position' => '',
            'padding' => 0,
            'align' => 'C',
            'stretch' => true,
            'cellfitalign' => '',
            'border' => false,
            'hpadding' => 'auto',
            'vpadding' => 'auto',
            'fgcolor' => array(0,0,0),
            'bgcolor' => false, //array(255,255,255),
            'text' => false,
        );

        if ($start > 0) $pdf->AddPage();
        for($i=0; $i<count($labeldata); $i++) {
            $j = $i+$start;
            $offset = $j%($numx*$numy);
            $x = $offset%$numx;
            $y = floor($offset/$numx);
            $barcodeheight = 12;
            $barcode_pos_y = $barcode_pos['y'][$y];

            if($j%$numlabels == 0) {
                $pdf->AddPage();
            }
            $pdf->MultiCell($props['wheader'], 7.5, $labelheader, 0, 'C', 0, 1, $labelheader_pos['x'][$x], $labelheader_pos['y'][$y], true, false, true);
            
            $melnumber = 'VCSB ' . $labeldata[$i]['CatalogNumber'];
            $pdf->MultiCell($props['whtml'], 5, '<span style="font-size:10pt;font-weight:bold;">'.$melnumber.'</span>', 0, 'L', 0, 1, $labelbody_pos['x'][$x], $pdf->GetY()+6, true, 0, true, true, 0, 'T', 0);
            
            $pdf->SetY($labelbody_pos['y'][$y]);
            
            
            
            $pdf->MultiCell($props['whtml'], 5, '<b>' . strtoupper($labeldata[$i]['Family']) . '</b>', 0, 'L', 0, 1, $labelbody_pos['x'][$x], $pdf->GetY(), true, 0, true, true, 0, 'T', false);
            $pdf->SetY($pdf->GetY()-1);

            $formattedname = '';
            $formattedname .= $labeldata[$i]['FormattedName'];
            $formattedname = "<div style=\"font-size: 11pt;\">$formattedname</div>";
            $pdf->MultiCell($props['whtml'], 5, $formattedname, 0, 'L', 0, 1, $labelbody_pos['x'][$x], $pdf->GetY(), true, 0, true, true, 0, 'T', false);
            
            /*if (($labeldata[$i]['DetType'] == 'Det.' || $labeldata[$i]['DetType'] == 'Conf.') && $labeldata[$i]['DeterminedBy']) {
                $det = '<b>' . $labeldata[$i]['DetType'] . ':</b> ' . $labeldata[$i]['DeterminedBy'];
                if ($labeldata[$i]['DeterminedDate']) $det .= ', ' . $labeldata[$i]['DeterminedDate'];
                $pdf->MultiCell($props['whtml'], 5, $det, 0, 'L', 0, 1, $labelbody_pos['x'][$x], $pdf->GetY(), true, 0, true, true, 0, 'T', false);
            }*/
            
            /*if ($labeldata[$i]['TypeInfo']) {
                if ($this->input->post('labeltype') == 6 || $this->input->post('labeltype') == 7)
                    $labeldata[$i]['TypeInfo'] = str_replace ('HOLOTYPE', 'ISOTYPE', $labeldata[$i]['TypeInfo']);
                $pdf->MultiCell($props['whtml'], 5, $labeldata[$i]['TypeInfo'], 0, 'L', 0, 1, $labelbody_pos['x'][$x], $pdf->GetY()-1, true, 0, true, true, 0, 'T', false);
            }*/
            
            $collinfo = '<b>Coll.:</b> '. $labeldata[$i]['Collector'] . ' ' . $labeldata[$i]['CollectingNumber'];
            if ($labeldata[$i]['CollectingDate'])
                $collinfo .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>Date:</b> ' . $labeldata[$i]['CollectingDate'];
            $pdf->MultiCell($props['whtml'], 5, $collinfo, 0, 'L', 0, 1, $labelbody_pos['x'][$x], $pdf->GetY()+2, true, 0, true, true, 0, 'T', false);
            
            if ($labeldata[$i]['AdditionalCollectors'])
                $pdf->MultiCell($props['whtml'], 5, '<b>Addit. Coll.:</b> ' .$labeldata[$i]['AdditionalCollectors'], 0, 'L', 0, 1, $labelbody_pos['x'][$x], $pdf->GetY()-1, true, 0, true, true, 0, 'T', false);
                
            if ($labeldata[$i]['CollectingTrip']) {
                $pdf->MultiCell($props['whtml'], 5, '<b>Collecting trip:</b> ' . $labeldata[$i]['CollectingTrip'], 0, 'L', 0, 1, $labelbody_pos['x'][$x], $pdf->GetY()-1, true, 0, true, true, 0, 'T', false);
            }
            $pdf->MultiCell($props['whtml'], 5, '<b>' . $labeldata[$i]['Geography'] . '</b>', 0, 'L', 0, 1, $labelbody_pos['x'][$x], $pdf->GetY()+2, true, 0, true, true, 0, 'T', false);
            
            $loc = '<b>Loc.:</b> '. trim($labeldata[$i]['Locality']);
            if (($labeldata[$i]['Latitude'] && $labeldata[$i]['Longitude']) || $labeldata[$i]['Altitude'] || $labeldata[$i]['Depth']) {
                $loc .= '<br/>';
                if ($labeldata[$i]['Latitude'] || $labeldata[$i]['Latitude'])
                    $loc .= $labeldata[$i]['Latitude'] . '&nbsp;&nbsp;' . $labeldata[$i]['Longitude'] . '. ';
                if ($labeldata[$i]['Altitude'])
                    $loc .= 'Alt.: ' . $labeldata[$i]['Altitude'] . '. ';
                if ($labeldata[$i]['Depth'])
                    $loc .= 'Depth: ' . $labeldata[$i]['Depth'] . '. ';
            }
            $pdf->MultiCell($props['whtml'], 5, $loc, 0, 'L', 0, 1, $labelbody_pos['x'][$x], $pdf->GetY()-1, true, 0, true, true, 0, 'T', false);
            
            $hab = array();
            if ($labeldata[$i]['Habitat']) $hab['Habitat'] = '<b>Habitat:</b> ' . $labeldata[$i]['Habitat'];
            if ($labeldata[$i]['AssociatedTaxa']) $hab['AssociatedTaxa'] = '<b>Associated taxa:</b> ' . $labeldata[$i]['AssociatedTaxa'];
            if ($labeldata[$i]['Substrate']) $hab['Substrate'] = '<b>Substrate:</b> ' . $labeldata[$i]['Substrate'];
            if ($labeldata[$i]['Host']) $hab['Host'] = '<b>Host:</b> ' . $labeldata[$i]['Host'];
            if ($hab)
                $pdf->MultiCell($props['whtml'], 5, implode('<br/>', $hab), 0, 'L', 0, 1, $labelbody_pos['x'][$x], $pdf->GetY()+1, true, 0, true, true, 0, 'T', false);
            
            if ($labeldata[$i]['Provenance'])
                $pdf->MultiCell($props['whtml'], 5, '<b>Provenance:</b> '. $labeldata[$i]['Provenance'], 0, 'L', 0, 1, $labelbody_pos['x'][$x], $pdf->GetY()+1, true, 0, true, true, 0, 'T', false);
            
            
            $not = array();
            if ($labeldata[$i]['DescriptiveNotes']) $not['DescriptiveNotes'] = '<b>Descriptive notes:</b> ' . $labeldata[$i]['DescriptiveNotes'];
            if ($labeldata[$i]['Introduced']) $not['Introduced'] = '<b>Natural occurrence:</b> ' . $labeldata[$i]['Introduced'];
            if ($labeldata[$i]['Cultivated']) $not['Cultivated'] = '<b>Cultivated occurrence:</b> ' . $labeldata[$i]['Cultivated'];
            if ($labeldata[$i]['CollectingNotes']) $not['CollectingNotes'] = '<b>Collecting notes:</b> ' . $labeldata[$i]['CollectingNotes'];
            if ($labeldata[$i]['Ethnobotany']) $not['Ethnobotany'] = '<b>Ethnobotany notes:</b> ' . $labeldata[$i]['Ethnobotany'];
            if ($labeldata[$i]['Toxicity']) $not['Toxicity'] = '<b>Toxicity notes:</b> ' . $labeldata[$i]['Toxicity'];
            if ($labeldata[$i]['MiscellaneousNotes']) $not['MiscellaneousNotes'] = '<b>Misc. notes:</b> ' . $labeldata[$i]['MiscellaneousNotes'];
            
            if ($not) {
                $pdf->SetY($pdf->GetY() + 1);
                $pdf->MultiCell($props['whtml'], 5, implode('<br/>', $not), 0, 'L', 0, 1, $labelbody_pos['x'][$x], $pdf->GetY()-1, true, 0, true, true, 0, 'T', false);
            }
            
            
            $dupl = '<b>National Herbarium of Victoria (MEL) voucher: ' . $labeldata[$i]['MelVoucher'] . '.</b>';
            $pdf->MultiCell($props['whtml'], 5, $dupl, 0, 'L', 0, 1, $labelbody_pos['x'][$x], $pdf->GetY()+1, true, 0, true, true, 0, 'T', false);
            
            if ($this->input->post('labelType') == 'seedling') {
                $pdf->MultiCell($props['whtml'], 5, "<b>Seedling collection # "
                        . "{$labeldata[$i]['SampleNumber']}</b>", 0, 'L', 0, 1, 
                        $labelbody_pos['x'][$x], $pdf->GetY()+1, true, 0, true, 
                        true, 0, 'T', 0);
                $pdf->MultiCell($props['whtml'], 5, '<b>Prepared:</b> ' . 
                        $labeldata[$i]['PreparedDate'], 0, 'L', 0, 1, 
                        $labelbody_pos['x'][$x], $pdf->GetY()-1, true, 0, 
                        true, true, 0, 'T', false);
            }

            
            $yy = $pdf->GetY()+5;

            $footer = '<div style="font-size: 7pt">Printed from MELISR, ' . date('d M. Y') . '</div>';
            $pdf->MultiCell(90, 5, $footer, 0, 'L', 0, 1, $labelfooter_pos['x'][$x], $yy, true, 0, true, true, 0, 'T', false);
            
            
        }
        // move pointer to last page
        $pdf->lastPage();

        // ---------------------------------------------------------

        //Close and output PDF document
        $pdf->Output('vcsblabel.pdf', 'I');
    }
}