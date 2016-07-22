<?php
/**
 * @property CI_Loader $load
 * @property CI_Form_validation $form_validation
 * @property CI_Input $input
 * @property CI_Email $email
 * @property CI_DB_active_record $db
 * @property CI_DB_forge $dbforge
 */

require_once('/var/www/lib/tcpdf/config/lang/eng.php');
require_once('/var/www/lib/tcpdf/tcpdf.php');

class MelisrLabels extends Controller {
    private $data;
    
    function __construct() {
        parent::Controller();
        $this->load->helper('form');
        $this->load->helper('file');
        $this->load->helper('url');
        $this->load->helper('xml');
        $this->output->enable_profiler(TRUE);
        $this->data = array();
        $this->data['bannerimage'] = $this->banner();
        $this->data['title'] = 'MELISR | Labels';
    }

    function index() {
        $this->load->model('recordsetmodel');
        $this->data['recordsets'] = $this->recordsetmodel->getCollectionObjectRecordSets();
        $this->load->view('welcome', $this->data);
    }

    function banner() {
        $banners = get_dir_file_info('./images/banners', TRUE);
        $banners = array_values($banners);
        $count = count($banners);
        $i = rand(0, $count-1);
        return $banners[$i]['name'];
    }

    function label() {
        $this->load->model('recordsetmodel');
        $this->load->model('labeldatamodel');
        $this->load->model('fqcmmodel');
        $get = $this->uri->uri_to_assoc();
        $type = $this->input->post('labeltype');
        $part = $this->input->post('part');
        $start = $this->input->post('start');
        
        $config = $this->labelConfig(0, $type-1);
        if (!$config) {
            $this->data['message'] = 'Labels have not been configured for this printer yet.';
            $this->load->view('message', $this->data);
            return FALSE;
        }

        if(isset($get['recordset'])) {
            $recordset = $get['recordset'];
            $records = FALSE;
        }
        elseif($this->input->post('recordset')) {
            $recordset = $this->input->post('recordset');
            $records = FALSE;
        }
        elseif ($type == 11 && $this->input->post('melno_start')) {
            $barcode_start = $this->input->post('melno_start');
            if ($this->input->post('melno_count'))
                $barcode_count = $this->input->post('melno_count');
            else
                $barcode_count = $this->input->post('melno_end')-$this->input->post('melno_start')+1;
            $this->printBarcodeLabel($config, $barcode_start, $barcode_count, $start-1);
        }
        elseif($this->input->post('melno_start')) {
            if ($this->input->post('melno_count') || $this->input->post('melno_end')) {
                $recordset = FALSE;
                $records = $this->recordsetmodel->getRecordSetFromRange($this->input->post('melno_start'),
                        $this->input->post('melno_count'), $this->input->post('melno_end'));
            } else {
                $this->data['message'] = 'Please enter the number of labels or the last MEL number to print';
                $this->load->view('message', $this->data);
            }
        }
        elseif($this->input->post('vrsno_start')) {
            if ($this->input->post('vrsno_count') || $this->input->post('vrsno_end')) {
                $recordset = FALSE;
                $records = $this->recordsetmodel->getRecordsFromVrsNos($this->input->post('vrsno_start'),
                        $this->input->post('vrsno_count'), $this->input->post('vrsno_end'));
            } else {
                $this->data['message'] = 'Please enter the number of labels or the last VRS number to print';
                $this->load->view('message', $this->data);
            }
        }
        else {
            $recordset = false;
        }

        if($recordset || $records) {
            $records = ($records) ? ($records) : $this->recordsetmodel->getRecordSetItems($recordset);
            
            $passedqc = $this->qc($records);
            
            if ($passedqc) {
            
                switch ($type) {
                    case $type < 6: // our (more or less) standard labels
                    case 17:
                    case 23:
                    case 19:
                    case 21:
                        $labeldata = $this->labeldatamodel->getLabelDataNew($records, $part, FALSE, $type);
                        //print_r($labeldata);

                        $storagemissing = FALSE;
                        foreach ($labeldata as $rec) {
                            if (!$rec['StoredUnder'])
                                $storagemissing = TRUE;
                        }
                        if ($storagemissing) {
                            $this->data['bannerimage'] = $this->banner();
                            $this->data['message'] = 'One or more of your records do not have storage information.<br/>
                                Please check the genus storage.';
                            $this->load->view('message', $this->data);
                            break;
                        }
                        else {
                            $this->printLabelNew($labeldata, $config, $start-1);
                            break;
                        }
                    case 6:
                    case 7:
                    case 13:
                    case 14: // various duplicate labels
                        $labeldata = $this->labeldatamodel->getLabelDataNew($records, $part, TRUE, $type);
                        if (count($labeldata) < 1) {
                            $this->data['message'] = 'Nothing to print';
                            $this->load->view('message', $this->data);
                            break;
                        }
                        //print_r($labeldata);
                        $this->printLabelNew($labeldata, $config, $start-1);
                        break;
                    case 8: // spirit jar labels
                        $labeldata = $this->labeldatamodel->getSpiritJarLabelData($records);
                        if (count($labeldata) > 0){
                            $labeldata = $this->spiritLabelHtml($labeldata);
                            $this->printAveryLabel($labeldata, $config, $start-1);
                        } else {
                            $this->data['message'] = 'Your record set does not contain records for spirit collections.';
                            $this->load->view('message', $this->data);
                        }
                        break;
                    case 9: // multisheet labels
                        $labeldata = $this->labeldatamodel->getMultisheetLabelData($records);
                        if (count($labeldata) > 0){
                            $labeldata = $this->multisheetLabelHtml($labeldata);
                            $this->printAveryLabel($labeldata, $config, $start-1);
                        } else {
                            $this->data['message'] = 'Your record set does not contain multisheets.';
                            $this->load->view('message', $this->data);
                        }
                        break;
                    case 10: // type folder labels
                        $labeldata = $this->labeldatamodel->getTypeFolderLabelData($records);
                        //print_r($labeldata);
                        if ($labeldata){
                            $storagemissing = FALSE;
                            $protologuemissing = FALSE;
                            $table = '<table>';
                            $table .= '<tr><th>MEL number</th><th>Storage family</th><th>Protologue</th>';
                            foreach ($labeldata as $rec) {
                                if (!$rec['Family'] || !$rec['Protologue']) {
                                    $table .= '<tr><td>';
                                    $table .= $rec['MELNumber'];
                                    $table .= '</td><td>';
                                    $table .= ($rec['Family']) ? $rec['Family'] : '&nbsp;';
                                    $table .= '</td><td>';
                                    $table .= ($rec['Protologue']) ? $rec['Protologue'] : '&nbsp;';
                                    $table .= '</td></tr>';
                                }
                                if (!$rec['Family'])
                                    $storagemissing = TRUE;
                                if (!$rec['Protologue'])
                                    $protologuemissing = TRUE;
                            }
                            $table .= '</table>';
                            if ($storagemissing || $protologuemissing) {
                                $this->data['message'] = 'One or more of your records do not have all required information.';
                                $this->data['message_table'] = $table;
                                $this->load->view('message', $this->data);
                                break;
                            }
                            else {
                                //$labeldata = $this->typeFolderLabelHtml($labeldata);
                                //$this->printAveryLabel($labeldata, $config);
                                $this->printTypeFolderLabel($labeldata, $config);
                            }
                        } else {
                            $this->data['message'] = 'Your record set does not contain types.';
                            $this->load->view('message', $this->data);
                        }
                        break;
                    case 11: // barcode stickers
                        if ($this->input->post('recordset')) {
                            $barcodes = $this->labeldatamodel->getBarcodeLabelData($records);
                            //print_r($barcodes);
                            $this->printBarcodeLabelRecordSet($config, $barcodes, $start-1);
                        }
                        elseif ($this->input->post('melno_start')) {
                            $barcode_start = $this->input->post('melno_start');
                            if ($this->input->post('melno_count'))
                                $barcode_count = $this->input->post('melno_count');
                            else
                                $barcode_count = $this->input->post('melno_end')-$this->input->post('melno_start')+1;
                            $this->printBarcodeLabel($config, $barcode_start, $barcode_count, $start-1);
                        }
                        break;
                    case 20:
                        $barcodes = $this->labeldatamodel->getVrsBarcodeLabelData($records);
                        //print_r($barcodes);
                        $this->printVrsBarcodeLabelRecordSet($config, $barcodes, $start-1);
                        break;
                    case 12: // spirit cards
                    case 22: // spirit cards
                        $labeldata = $this->labeldatamodel->getLabelDataNew($records, $part, FALSE, $type);
                        //$labeldata = $this->spiritCardHtml($labeldata, $type);
                        $this->printLabelNew($labeldata, $config, $start-1);
                        break;

                    case 15:
                        $labeldata = $this->labeldatamodel->getAnnotationSlipData($records, $part);
                        if ($labeldata){
                            //print_r($labeldata);
                            $this->printAnnotationSlip($labeldata, $config, TRUE, $start-1);
                        }
                        else {
                            $this->data['message'] = 'Nothing to print';
                            $this->load->view('message', $this->data);
                        }
                        break;
                    case 16: // det. slips
                        $labeldata = $this->labeldatamodel->getAnnotationSlipData($records);
                        $this->printAnnotationSlip($labeldata, $config, TRUE);
                        break;
                    case 18: // silicagel sample labels
                        $labeldata = $this->labeldatamodel->getSpiritJarLabelData($records, 7);
                        if (count($labeldata) > 0){
                            $labeldata = $this->spiritLabelHtml($labeldata);
                            $this->printAveryLabel($labeldata, $config, $start-1);
                        } else {
                            $this->data['message'] = 'Your record set does not contain records for silica gel samples.';
                            $this->load->view('message', $this->data);
                        }
                        break;
                }
            }
        }
    }

    function labelConfig($printer, $type) {
        require_once('/var/www/dev/melisr/config/printerconfig.php');
        if ($printer >= count($config))
            return FALSE;
        $config = $config[$printer][$type];
        $config['dimensions'] = $this->labelDimensions($config, $type);
        $config['type'] = $type;
        return $config;
    }
    
    function qc($records) {
        $this->load->model('fqcmmodel');
        $this->data = array();
        $this->data['MissingGeography'] = $this->fqcmmodel->missingGeography(FALSE, FALSE, FALSE, $records);
        $this->data['MissingTaxonName'] = $this->fqcmmodel->missingTaxonName(FALSE, FALSE, FALSE, $records);
        $this->data['MissingPreparation'] = $this->fqcmmodel->missingPreparation(FALSE, FALSE, FALSE, $records);
        $this->data['MissingStorage'] = $this->fqcmmodel->missingPreparation(FALSE, FALSE, FALSE, $records);

        $error = FALSE;
        foreach (array_values($this->data) AS $value) {
            if ($value)
                $error = TRUE;
        }
        if ($error) {
            $this->data['Users'] = $this->fqcmmodel->getUsers();
            $this->load->view('fqcmview', $this->data);
            return FALSE;
        }
        else
            return TRUE;
    }

    function spiritLabelHtml($labeldata) {
        $labels = array();
        for ($i = 0; $i < count($labeldata); $i++) {
            $ldata = $labeldata[$i];
            $html = array();
            $html[] = "<style>td#tname {padding-bottom: 40px;}</style>";
            $html[] = '<table>';
            $html[] = "<tr><td id=\"tname\" colspan=\"2\">$ldata[FormattedName]</td></tr>";
            $collector = FALSE;
            if ($ldata['Collector']) {
                $arr = explode(';', $ldata['Collector']);
                switch (count($arr)) {
                    case 1:
                        $collector = $arr[0];
                        break;

                    case 2:
                        $collector = $arr[0] . ' & ' . $arr[1];
                        break;

                    default:
                        $collector = $arr[0] . ' & al.';
                        break;
                }
            }
            $html[] = "<tr><td>$collector $ldata[CollectingNumber]</td>";
            $html[] = "<td align=\"right\">" . $ldata['State'] . "</td></tr>";
            $html[] = "<tr><td>MEL $ldata[MelNumber]</td>";
            $html[] = "<td align=\"right\">$ldata[SpiritNumber] $ldata[JarSize]</td></tr>";
            $html[] = '</table>';
            $labels[] = implode('', $html);
        }
        return $labels;
    }

    function MultisheetLabelHtml($labeldata) {
        $labels = array();
        for ($i = 0; $i < count($labeldata); $i++) {
            $ldata = $labeldata[$i];
            $html = array();
            $html[] = '<table>';
            $html[] = "<tr><td>$ldata[Multisheet]</td></tr>";
            $html[] = '</table>';
            $labels[] = implode('', $html);
        }
        return $labels;
    }

    function typeFolderLabelHtml($labeldata) {
        $labels = array();
        for ($i = 0; $i < count($labeldata); $i++) {
            $ldata = $labeldata[$i];
            $html = array();
            $html[] = '<table>';
            $rowspan = ($ldata['Multisheet']) ? 3 : 2;

            if ($ldata['DoubtfulFlag']) {
                $doubtfulflag = $ldata['DoubtfulFlag'];
                if ($doubtfulflag != '?') $doubtfulflag .= ' ';
            }
            else
                $doubtfulflag = '';

            $html[] = '<tr>';
                $html[] = '<td width="93%" colspan="2"><span style="font-size: 12pt"><b>' . $doubtfulflag . strtoupper($ldata['Status']) . '</b> of ' . $ldata['Basionym'] . '</span><span style="font-size: 12pt;">' . $ldata['Protologue'] . '</span></td>';
                $html[] = "<td width=\"7%\" rowspan=\"$rowspan\" align=\"right\"><span style=\"font-size: 30pt; font-weight: bold\">" . $ldata['AuOrForeign'] . '</span></td>';
            $html[] = '</tr>';
            $html[] = '<tr>';
               $html[] = '<td width="43%"><span style="font-size: 12pt; font-weight: bold">' . $ldata['MELNumber'] . '</span></td>';
               $html[] = '<td width="50%"><span style="font-size: 12pt;">' . strtoupper($ldata['Family']) . '</span></td>';
            $html[] = '</tr>';
            if ($ldata['Multisheet']) {
                $html[] = '<tr>';
                   $html[] = '<td colspan="2"><span style="font-size: 9pt;">' . $ldata['Multisheet'] . '</span></td>';
                $html[] = '</tr>';
            }
            $html[] = '</table>';
            $labels[] = implode('', $html);
        }
        return $labels;
    }
    
    function printTypeFolderLabel($labeldata, $props, $start=0) {
        $numx = $props['numx'];
        $numy = $props['numy'];
        $labelheight = $props['dimensions']['labelheight'];
        $labelwidht = $props['dimensions']['labelwidth'];
        $labelheader_pos = $props['dimensions']['labelheader_pos'];
        $labelbody_pos = $props['dimensions']['labelbody_pos'];
        $numlabels = $numx*$numy;

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
        
        set_time_limit (600);
        // create new PDF document
        $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);

        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Niels Klazenga');
        $pdf->SetTitle('MEL Label');
        $pdf->SetSubject('MEL Label');

        //set margins
        $pdf->SetMargins(5, 7.5, 5);

        //set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, 3);

        // remove default header/footer
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        // ---------------------------------------------------------

        // set font
        $pdf->SetFont('times', '', 12);
        // set cell padding
        $pdf->setCellPaddings(0, 0, 0, 0);
        // set cell margins
        $pdf->setCellMargins(0, 0, 0, 0);

        if ($labelheader_pos)
            $labelheader='<p style="font-weight: bold"><span style="font-size:20.5px;">NATIONAL HERBARIUM OF VICTORIA (MEL)</span></p>';

        if ($start > 0) $pdf->AddPage();
        for($i=0; $i<count($labeldata); $i++) {
            $j = $i+$start;
            $offset = $j%($numx*$numy);
            $x = $offset%$numx;
            $y = floor($offset/$numx);

            if($j%$numlabels == 0) $pdf->AddPage();
            
            if ($labeldata[$i]['DoubtfulFlag']) {
                $doubtfulflag = $labeldata[$i]['DoubtfulFlag'];
                if ($doubtfulflag != '?') $doubtfulflag .= ' ';
            }
            else
                $doubtfulflag = '';

            $typestatus = '<b>' . $doubtfulflag . strtoupper($labeldata[$i]['Status']) . '</b> of ' . $labeldata[$i]['Basionym'] . $labeldata[$i]['Protologue'];
            $pdf->MultiCell(185, 5, $typestatus, 0, 'L', 0, 1, $labelbody_pos['x'][$x], $labelbody_pos['y'][$y], true, 0, true, true, 0, 'T', false);
            $posy = $pdf->getY();
            $pdf->MultiCell(28, 5, '<b>' . $labeldata[$i]['MELNumber'] . '</b>', 0, 'L', 0, 1, $labelbody_pos['x'][$x], $posy, true, 0, true, true, 0, 'T', false);       
            $pdf->write1DBarcode($labeldata[$i]['MELNumber'], 'C39', $labelbody_pos['x'][$x]+28, $posy, 55, 6, 0.1, $barcodestyle, 'N');
            $pdf->MultiCell(94, 5, strtoupper($labeldata[$i]['Family']), 0, 'R', 0, 1, $labelbody_pos['x'][$x]+91, $posy, true, 0, true, true, 0, 'T', false); 
            if ($labeldata[$i]['Multisheet'])
            $pdf->MultiCell(185, 5, '<span style="font-size: 9pt;">' . $labeldata[$i]['Multisheet'] . '</span>', 0, 'L', 0, 1, $labelbody_pos['x'][$x], $pdf->getY(), true, 0, true, true, 0, 'T', false);       
            $pdf->MultiCell(16, 5, '<span style="font-size: 30pt; font-weight: bold">' . $labeldata[$i]['AuOrForeign'] . '</span>', 0, 'L', 0, 1, $labelbody_pos['x'][$x]+190, $labelbody_pos['y'][$y]-1, true, 0, true, true, 0, 'T', false);       
        }   
        // move pointer to last page
        $pdf->lastPage();

        // ---------------------------------------------------------

        //Close and output PDF document
        $pdf->Output('mellabel.pdf', 'I');
	}

    
    function spiritCardHtml($labeldata, $type) {
        $dup = (in_array($type, array(6, 7, 13, 14))) ? true : false;
        $labels = array();
        for ($i = 0; $i < count($labeldata); $i++) {
            $ldata = $labeldata[$i];
            $label['melnumber'] = 'MEL ' . $ldata['MelNumber'];

            $determination = array();
            $determination[] = '<table>';
            $formattedname = '';
            if($ldata['Introduced']) $formattedname .= '*';
            $formattedname .= $ldata['FormattedName'];
            $determination[] = "<tr><td colspan=\"2\" style=\"font-size: 11pt;\">$formattedname</td></tr>";
            if($ldata['ExtraInfo'])
                $determination[] = "<tr><td colspan=\"2\">$ldata[ExtraInfo]</td></tr>";
            if(($ldata['DetType'] == 'Det.' || $ldata['DetType'] == 'Conf.') && $ldata['DeterminedBy']) {
                $determination[] = '<tr>';
                $determination[] = '<td colspan="2" align="right">';
                $determination[] = '<span style="font-size: 7pt">';
                $determination[] = $ldata['DetType'] . ': ';
                $determination[] = $ldata['DeterminedBy'];
                if ($ldata['DeterminedDate']) $determination[] = ', ' . $ldata['DeterminedDate'];
                $determination[] = '</span>';
                $determination[] = '</td>';
                $determination[] = '</tr>';
                $determination[] = '</table>';
            }
            $label['determination'] = implode('', $determination);

            if($ldata['TypeInfo'])
                $label['typeinfo'] = "<table><tr><td colspan=\"2\">$ldata[TypeInfo]</td></tr></table>";
            else $label['typeinfo'] = FALSE;

            $collectinginfo = array();
            $collectinginfo[] = '<table>';
            $collectinginfo[] = "<tr><td colspan=\"2\"><span>Coll.: $ldata[Collector] $ldata[CollectingNumber]</span>";
            if ($ldata['CollectingDate'])
                $collectinginfo[] = "<span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Date: $ldata[CollectingDate]</span>";
            $collectinginfo[] = "</td></tr>";
            if($ldata['AdditionalCollectors'])
                $collectinginfo[] = "<tr><td colspan=\"2\">Addit. coll.: $ldata[AdditionalCollectors]</td></tr>";
            $collectinginfo[] = '</table>';
            $label['collectinginfo'] = implode('', $collectinginfo);

            $loc = array();
            $loc[] = '<table>';
            $loc[] = "<tr><td style=\"font-weight: bold\">$ldata[Geography]</td></tr>";
            $localitystring = trim($ldata['Locality']);
            if (($ldata['Latitude'] && $ldata['Longitude']) || $ldata['Altitude'] || $ldata['Depth']) {
                $localitystring .= '<br/>';
                if($ldata['Latitude'] && $ldata['Longitude']) {
                        $localitystring .= $ldata['Latitude'] . '&nbsp;&nbsp;' . $ldata['Longitude'] . '. ';
                }
                if($ldata['Altitude']) {
                    $localitystring .= 'Alt.: ' . $ldata['Altitude'] . '. ';
                }
                if ($ldata['Depth']) {
                    $localitystring .= 'Depth: ' . $ldata['Depth'] . '. ';
                }
            }
            $loc[] = "<tr><td>Loc.: $localitystring</td></tr>";
            $loc[] = "</table>";
            $label['locality'] = implode('', $loc);
            
            if($ldata['Habitat'] || $ldata['DescriptiveNotes'] || $ldata['CollectingNotes']) {
                $note = array();
                $note[] = '<table>';
                $note[] = '<tr><td>';
                $notes = array();
                if($ldata['Habitat']) $notes[] = $ldata['Habitat'];
                if($ldata['DescriptiveNotes']) $notes[] = $ldata['DescriptiveNotes'];
                if($ldata['Provenance']) $notes[] = $ldata['Provenance'];
                if ($ldata['CollectingNotes'] || $ldata['Ethnobotany'] || $ldata['Toxicity'] || $ldata['CollectingTrip']) $notes[] = 'Notes:';
                if($ldata['CollectingTrip']) $notes[] = $ldata['CollectingTrip'];
                if($ldata['Ethnobotany']) $notes[] = $ldata['Ethnobotany'];
                if($ldata['Toxicity']) $notes[] = $ldata['Toxicity'];
                if($ldata['CollectingNotes']) $notes[] = $ldata['CollectingNotes'];
                $note[] = implode(' ', $notes);
                $note[] = '</td></tr>';
                $note[] = '</table>';
                $label['notes'] = implode('', $note);
            } else $label['notes'] = FALSE;

            
            if($ldata['Multisheet']) {
                $multisheet = array();
                $multisheet[] = '<table>';
                $multisheet[] = '<tr><td>' . $ldata['Multisheet'] . '</td></tr>';
                $multisheet[] = '</table>';
                $label['multisheet'] = implode('', $multisheet);
            } else $label['multisheet'] = FALSE;

            if($ldata['MixedInfo']) {
                $mixed = array();
                $mixed[] = '<table>';
                $mixed[] = '<tr><td>';
                $mixed[] = '<b>This is a mixed collection. The components are:</b><br/>';
                if ($type == 4 || $type == 5)
                    $ldata['MixedInfo'] = str_replace ('<br/>', '; ', $ldata['MixedInfo']);
                $mixed[] = "<span style=\"font-size: 8pt\">$ldata[MixedInfo]</span>";
                $mixed[] = '</td></tr>';
                $mixed[] = '</table>';
                $label['mixed'] = implode('', $mixed);
            } else $label['mixed'] = FALSE;

            $storedunder = $ldata['StoredUnder'];
            $storedunder .= ($ldata['Continent']) ? ' (' . $ldata['Continent'] . ')' : '';
            $label['footer'] = "<table width=\"100%\"><tr>
                <td width=\"70%\" style=\"font-size: 7pt;\">Storage: $storedunder</td><td style=\"font-size:7pt;text-align: right\">Printed: ".date('d M. Y').'</td></tr></table>';

            if (isset($ldata['SpiritInfo'])) {
                $number = $ldata['SpiritInfo']['Number'];
                $jarsize = $ldata['SpiritInfo']['JarSize'];
                $label['spiritinfo'] = "Spirit jar: <span style=\"font-size: 11pt\">$number $jarsize</span>";
            }

            $labels[] = $label;
        }
        return $labels;
    }
    

    function labelHtml($labeldata, $type) {
        $dup = (in_array($type, array(6, 7, 13, 14))) ? true : false;
        $labels = array();
        for ($i = 0; $i < count($labeldata); $i++) {
            $ldata = $labeldata[$i];
            $label['melnumber'] = 'MEL ' . $ldata['MelNumber'];
            $html = array();
            $html[] = '<table>';

            $formattedname = '';
            if($ldata['Introduced'] == 'Naturalised') $formattedname .= '*';
            $formattedname .= $ldata['FormattedName'];
            $html[] = "<tr><td colspan=\"2\" style=\"font-size: 11pt;\">$formattedname</td></tr>";
            if($ldata['ExtraInfo'])
                $html[] = "<tr><td colspan=\"2\">$ldata[ExtraInfo]</td></tr>";
            if(($ldata['DetType'] == 'Det.' || $ldata['DetType'] == 'Conf.') && $ldata['DeterminedBy']) {
                $html[] = '<tr>';
                $html[] = '<td colspan="2" align="right">';
                $html[] = '<span style="font-size: 7pt">';
                $html[] = $ldata['DetType'] . ': ';
                $html[] = $ldata['DeterminedBy'];
                if ($ldata['DeterminedDate']) $html[] = ', ' . $ldata['DeterminedDate'];
                $html[] = '</span>';
                $html[] = '</td>';
                $html[] = '</tr>';
            }
            if($ldata['TypeInfo'])
                $html[] = "<tr><td colspan=\"2\">$ldata[TypeInfo]</td></tr>";
            $html[] = "<tr><td colspan=\"2\">&nbsp;</td></tr>";
            $html[] = "<tr><td colspan=\"2\"><span>Coll.: $ldata[Collector] $ldata[CollectingNumber]</span>";
            if ($ldata['CollectingDate'])
                $html[] = "<span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Date: $ldata[CollectingDate]</span>";
            $html[] = "</td></tr>";
            if($ldata['AdditionalCollectors'])
                $html[] = "<tr><td colspan=\"2\">Addit. coll.: $ldata[AdditionalCollectors]</td></tr>";
            if($ldata['CollectingTrip'])
                $html[] = "<tr><td colspan=\"2\">" . $ldata['CollectingTrip'] . "</td></tr>";
            $html[] = "<tr><td colspan=\"2\">&nbsp;</td></tr>";
            $html[] = "<tr><td colspan=2 style=\"font-weight: bold\">$ldata[Geography]</td></tr>";
            $localitystring = trim($ldata['Locality']);
            if (($ldata['Latitude'] && $ldata['Longitude']) || $ldata['Altitude'] || $ldata['Depth']) {
                $localitystring .= '<br/>';
                if($ldata['Latitude'] && $ldata['Longitude']) {
                        $localitystring .= $ldata['Latitude'] . '&nbsp;&nbsp;' . $ldata['Longitude'] . '. ';
                }
                if($ldata['Altitude']) {
                    $localitystring .= 'Alt.: ' . $ldata['Altitude'] . '. ';
                }
                if ($ldata['Depth']) {
                    $localitystring .= 'Depth: ' . $ldata['Depth'] . '. ';
                }
            }
            $html[] = "<tr><td colspan=\"2\">Loc.: $localitystring</td></tr>";
            $html[] = "<tr><td colspan=\"2\">&nbsp;</td></tr>";
            
            if($ldata['Cultivated'] && $ldata['Geography'] != 'Cultivated') {
                $html[] = "<tr><td colspan=\"2\"><b>$ldata[Cultivated]</b></td></tr>";
                $html[] = "<tr><td colspan=\"2\">&nbsp;</td></tr>";
            }
            
            if($ldata['Habitat'] || $ldata['DescriptiveNotes'] || $ldata['CollectingNotes']) {
                $html[] = '<tr><td colspan="2">';
                $notes = array();
                if($ldata['Habitat']) $notes[] = $ldata['Habitat'];
                if($ldata['DescriptiveNotes']) $notes[] = $ldata['DescriptiveNotes'];
                if($ldata['Provenance']) $notes[] = $ldata['Provenance'];
                if ($ldata['CollectingNotes'] || $ldata['Ethnobotany'] || $ldata['Toxicity'] || $ldata['CollectingTrip']) $notes[] = 'Notes:';
                if($ldata['Ethnobotany']) $notes[] = $ldata['Ethnobotany'];
                if($ldata['Toxicity']) $notes[] = $ldata['Toxicity'];
                if($ldata['CollectingNotes']) $notes[] = $ldata['CollectingNotes'];
                $html[] = implode(' ', $notes);
                $html[] = '</td></tr>';
            }
            if($ldata['Multisheet'] && !$dup) {
                $html[] = '<tr><td colspan="2">&nbsp;</td></tr>';
                $html[] = '<tr><td colspan="2">' . $ldata['Multisheet'] . '</td></tr>';
            }
            if($ldata['MixedInfo']) {
                $html[] = '<tr><td colspan="2">&nbsp;</td></tr>';
                $html[] = '<tr><td colspan="2">';
                $html[] = '<b>This is a mixed collection. The components are:</b><br/>';
                if ($type == 4 || $type == 5)
                    $ldata['MixedInfo'] = str_replace ('<br/>', '; ', $ldata['MixedInfo']);
                $html[] = "<span style=\"font-size: 8pt\">$ldata[MixedInfo]</span>";
                $html[] = '</td></tr>';
            }
            if ($dup)
                $html[] = "<tr><td colspan=\"2\">&nbsp;<br/><b>This specimen is a duplicate of MEL $ldata[MelNumber].</b></td></tr>";
            if ($ldata['DuplicateInfo']) {
                $html[] = '<tr><td colspan="2">&nbsp;</td></tr>';
                $html[] = '<tr><td colspan="2">Dupl.: ' . $ldata['DuplicateInfo'] . '</td></tr>';
            }

            $html[] = '</table>';
            $label['html'] = implode('', $html);
            if(!$dup) {
                $storedunder = $ldata['StoredUnder'];
                $storedunder .= ($ldata['Continent']) ? ' (' . $ldata['Continent'] . ')' : '';
                $label['footer'] = "<span style=\"font-size: 7pt;\">Storage: $storedunder</span><br /><span style=\"font-size: 7pt;\">Printed: ".date('d M. Y').'</span>';
            } else
                $label['footer'] = "<span style=\"font-size: 7pt;\">&nbsp;<br /><span style=\"font-size: 7pt;\">Printed: ".date('d M. Y').'</span>';

            if (isset($ldata['SpiritInfo'])) {
                $number = $ldata['SpiritInfo']['Number'];
                $jarsize = $ldata['SpiritInfo']['JarSize'];
                $label['spiritinfo'] = "$jarsize $number";
            }

            $labels[] = $label;
        }
        return $labels;
    }
    
    function labelDimensions($config, $type) {
        $numx = $config['numx'];
        $numy = $config['numy'];
        $labeldimensions = array();
        if (isset($config['height']) && isset($config['width'])) {
            $labeldimensions['labelheight'] = $config['height'];
            $labeldimensions['labelwidth'] = $config['width'];
        }
        elseif (isset($config['orientation']) && $config['orientation'] = 'L') {
            $labeldimensions['labelheight'] = 105;
            $labeldimensions['labelwidth'] = 148.5;
        } else {
            $labeldimensions['labelheight'] = (isset($config['labelheight'])) ? $config['labelheight'] : 297/$numy;
            $labeldimensions['labelwidth'] = (isset($config['labelwidth'])) ? $config['labelwidth'] : 210/$numx;
        }
        $xpos = (isset($config['xpos']) && $config['xpos']) ? $config['xpos'] : 7.5;

        $yheader = (isset($config['yheader']) && $config['yheader']) ? $config['yheader'] : 7.5;

        if ($config['wheader']) {
            $labeldimensions['labelheader_pos'] = array();
            $labeldimensions['labelheader_pos']['x'] = array();
            for ($i = 0; $i<$numx; $i++)
            $labeldimensions['labelheader_pos']['x'][] = $xpos + $i*$labeldimensions['labelwidth'];

            $labeldimensions['labelheader_pos']['y'] = array();
            for ($i = 0; $i<$numy; $i++)
            $labeldimensions['labelheader_pos']['y'][] = $yheader + $i*$labeldimensions['labelheight'];
        } else $labeldimensions['labelheader_pos'] = FALSE;


        $labeldimensions['labelbody_pos'] = array();
        $labeldimensions['labelbody_pos']['x'] = array();
        for ($i = 0; $i<$numx; $i++){
            $labeldimensions['labelbody_pos']['x'][] = $xpos + $i*$labeldimensions['labelwidth'];
            }
        $labeldimensions['labelbody_pos']['y'] = array();
        for ($i = 0; $i<$numy; $i++)
        $labeldimensions['labelbody_pos']['y'][] = $config['yhtml'] + $i*$labeldimensions['labelheight'];

        if ((($type < 7 || $type > 9) && $type < 14) || in_array($type, array(16, 18, 19, 20, 21, 22))) {
            $labeldimensions['barcode_pos'] = array();
            $labeldimensions['barcode_pos']['x'] = array();
            for ($i = 0; $i<$numx; $i++)
            $labeldimensions['barcode_pos']['x'][] = $config['xbarcode'] + $i*$labeldimensions['labelwidth'];
            $labeldimensions['barcode_pos']['y'] = array();
            for ($i = 0; $i<$numy; $i++)
            $labeldimensions['barcode_pos']['y'][] = $config['ybarcode'] + $i*$labeldimensions['labelheight'];

            $labeldimensions['barcodetext_pos'] = array();
            $labeldimensions['barcodetext_pos']['x'] = array();
            for ($i = 0; $i<$numx; $i++)
            $labeldimensions['barcodetext_pos']['x'][] = $config['xbarcode'] + $i*$labeldimensions['labelwidth'];
            $labeldimensions['barcodetext_pos']['y'] = array();
            for ($i = 0; $i<$numy; $i++)
            $labeldimensions['barcodetext_pos']['y'][] = $config['ybarcodetext'] + $i*$labeldimensions['labelheight'];

            if (isset($config['footeroffsety'])) {
                $offset = $config['footeroffsety'];
            }
            else {
                $offset = 15;
            }
            
            $labeldimensions['labelfooter_pos'] = array();
            $labeldimensions['labelfooter_pos']['x'] = array();
            for ($i = 0; $i<$numx; $i++)
            $labeldimensions['labelfooter_pos']['x'][] = $xpos + $i*$labeldimensions['labelwidth'];
            $labeldimensions['labelfooter_pos']['y'] = array();
            for ($i = 0; $i<$numy; $i++)
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

        if ($this->input->post('labeltype') == 6 || $this->input->post('labeltype') == 7
            || $this->input->post('labeltype') == 13 || $this->input->post('labeltype') == 14)
            $dup = true;
        else $dup = false;

        set_time_limit (600);
        // create new PDF document
        $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);

        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Niels Klazenga');
        $pdf->SetTitle('MEL Label');
        $pdf->SetSubject('MEL Label');

        //set margins
        $pdf->SetMargins(5, 7.5, 5);

        //set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, 7.5);

        // remove default header/footer
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        // ---------------------------------------------------------

        // set font
        $pdf->SetFont('times', '', 9);
        // set cell padding
        $pdf->setCellPaddings(0, 0, 0, 0);
        // set cell margins
        $pdf->setCellMargins(0, 0, 0, 0);

        if (!$dup)
            $labelheader='<p style="font-weight: bold"><span style="font-size: 11pt;">NATIONAL HERBARIUM OF VICTORIA (MEL)</span><br />
                <span style="font-size: 11pt;">MELBOURNE, AUSTRALIA</span></p>';
        else
            $labelheader='<p style="font-weight: bold"><span style="font-size: 30px;">Ex NATIONAL HERBARIUM OF VICTORIA (MEL)</span><br />
                <span style="font-size: 11pt;">MELBOURNE, AUSTRALIA</span></p>';
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

            if($j%$numlabels == 0) $pdf->AddPage();

            $pdf->MultiCell($props['wheader'], 7.5, $labelheader, 0, 'C', 0, 1, $labelheader_pos['x'][$x], $labelheader_pos['y'][$y], true, false, true);
            if(!$dup) {
                $pdf->write1DBarcode($labeldata[$i]['melnumber'], 'C39', $barcode_pos['x'][$x], $barcode_pos['y'][$y], 55, 12, 0.1, $barcodestyle, 'N');
                $pdf->MultiCell(55, 5, '<b>'.$labeldata[$i]['melnumber'].'</b>', 0, 'C', 0, 1, $barcodetext_pos['x'][$x], $barcodetext_pos['y'][$y], true, false, true);
            }
            $pdf->MultiCell($props['whtml'], 5, $labeldata[$i]['html'], 0, 'L', 0, 1, $labelbody_pos['x'][$x], $labelbody_pos['y'][$y], true, 0, true, true, 0, 'T', false);

            if($props['footerpositionabsolute']) {
                if ($dup) {
                    $pdf->MultiCell(90, 5, $labeldata[$i]['footer'], 0, 'L', 0, 1, $labelfooter_pos['x'][$x], $labelfooter_pos['y'][$y], true, 0, true, true, 0, 'T', false);
                    $pdf->write1DBarcode($labeldata[$i]['melnumber'], 'C39', $barcode_pos['x'][$x], $barcode_pos['y'][$y], 55, 12, 0.1, $barcodestyle, 'N');
                    $pdf->MultiCell(55, 5, '<b>'.$labeldata[$i]['melnumber'].'</b>', 0, 'C', 0, 1, $barcodetext_pos['x'][$x], $barcodetext_pos['y'][$y], true, false, true);
                }
                else {
                    $pdf->MultiCell(90, 5, $labeldata[$i]['footer'], 0, 'L', 0, 1, $labelfooter_pos['x'][$x], $labelfooter_pos['y'][$y]-10, true, 0, true, true, 0, 'T', false);
                }
            }
            else {
                $y = $pdf->getY()+5;
                if ($dup) {
                    $pdf->write1DBarcode($labeldata[$i]['melnumber'], 'C39', $barcode_pos['x'][$x], $y, 55, 12, 0.1, $barcodestyle, 'N');
                    $pdf->MultiCell(55, 5, '<b>'.$labeldata[$i]['melnumber'].'</b>', 0, 'C', 0, 1, $barcodetext_pos['x'][$x], $y+11, true, false, true);
                    $pdf->MultiCell(90, 5, $labeldata[$i]['footer'], 0, 'L', 0, 1, $labelfooter_pos['x'][$x], $y+8.5, true, 0, true, true, 0, 'T', false);
                }
                else 
                    $pdf->MultiCell(90, 5, $labeldata[$i]['footer'], 0, 'L', 0, 1, $labelfooter_pos['x'][$x], $y, true, 0, true, true, 0, 'T', false);
            }
        }
        // move pointer to last page
        $pdf->lastPage();

        // ---------------------------------------------------------

        //Close and output PDF document
        $pdf->Output('mellabel.pdf', 'I');
	}

    function printLabelNew($labeldata, $props, $start=0) {
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

        if ($this->input->post('labeltype') == 6 || $this->input->post('labeltype') == 7
            || $this->input->post('labeltype') == 13 || $this->input->post('labeltype') == 14)
            $dup = true;
        else $dup = false;

        set_time_limit (600);
        // create new PDF document
        
        $format = 'A4';
        if (isset($props['format'])) {
            $format = $props['format'];
        }
        
        $orientation = 'P';
        if (isset($props['orientation'])) {
            $orientation = $props['orientation'];
        }
        
        $pdf = new TCPDF($orientation, 'mm', $format, true, 'UTF-8', false);
        
        
        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Niels Klazenga');
        $pdf->SetTitle('MEL Label');
        $pdf->SetSubject('MEL Label');

        //set margins
        $pdf->SetMargins(5, 7.5, 5);

        //set auto page breaks
        //if($props['footerpositionabsolute'])
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

        if (!$dup)
            $labelheader='<p style="font-weight: bold"><span style="font-size: 11.5pt;">NATIONAL HERBARIUM OF VICTORIA (MEL)</span><br />
                <span style="font-size: 11pt;">MELBOURNE, AUSTRALIA</span></p>';
        else
            $labelheader='<p style="font-weight: bold"><span style="font-size: 30px;">Ex NATIONAL HERBARIUM OF VICTORIA (MEL)</span><br />
                <span style="font-size: 11pt;">MELBOURNE, AUSTRALIA</span></p>';
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

            if($j%$numlabels == 0) $pdf->AddPage();
            
            $pdf->MultiCell($props['wheader'], 7.5, $labelheader, 0, 'C', 0, 1, $labelheader_pos['x'][$x], $labelheader_pos['y'][$y], true, false, true);
            if ($this->input->post('labeltype') == 19 || $this->input->post('labeltype') == 21) {
                $pdf->MultiCell($props['wheader'], 5, '<b>Victorian Reference Set</b>', 0, 'C', 0, 1, $labelheader_pos['x'][$x], $pdf->GetY(), true, false, true);
            }
            elseif($labeldata[$i]['HortRefSet']) {
                $pdf->MultiCell($props['wheader'], 5, '<b>Horticultural Reference Set</b>', 0, 'C', 0, 1, $labelheader_pos['x'][$x], $pdf->GetY(), true, false, true);
                $barcode_pos_y += 3;
                $barcodeheight -= 3;
            }
            
            if(!$dup) {
                if ($this->input->post('labeltype') == 19 || $this->input->post('labeltype') == 21) {
                    $vrsnumber = 'VRS ' . $labeldata[$i]['VRSNumber'];
                    $pdf->write1DBarcode($vrsnumber, 'C39', $barcode_pos['x'][$x], $barcode_pos_y, 55, $barcodeheight, 0.1, $barcodestyle, 'N');
                    $pdf->MultiCell(55, 5, '<b>'.$vrsnumber.'</b>', 0, 'C', 0, 1, $barcodetext_pos['x'][$x], $barcodetext_pos['y'][$y], true, false, true);
                }
                else {
                    $melnumber = 'MEL ' . $labeldata[$i]['MelNumber'];
                    $pdf->write1DBarcode($melnumber, 'C39', $barcode_pos['x'][$x], $barcode_pos_y, 55, $barcodeheight, 0.1, $barcodestyle, 'N');
                    $pdf->MultiCell(55, 5, '<b>'.$melnumber.'</b>', 0, 'C', 0, 1, $barcodetext_pos['x'][$x], $barcodetext_pos['y'][$y], true, false, true);
                }
            }
            
            if (isset($labeldata[$i]['SpiritInfo']) && $labeldata[$i]['SpiritInfo']) {
                $spirit = 'Spirit jar: ' . $labeldata[$i]['SpiritInfo']['Number'] . $labeldata[$i]['SpiritInfo']['JarSize'];
                $pdf->MultiCell(51, 5, $spirit, 0, 'R', 0, 1, $barcodetext_pos['x'][$x], $barcode_pos_y-6, true, false, true);
            }
            
            $pdf->SetY($labelbody_pos['y'][$y]);
            
            
            if ($this->input->post('labeltype') == 19 || $this->input->post('labeltype') == 21) {
                $pdf->MultiCell($props['whtml'], 5, '<b>' . strtoupper($labeldata[$i]['Family']) . '</b>', 0, 'L', 0, 1, $labelbody_pos['x'][$x], $pdf->GetY(), true, 0, true, true, 0, 'T', false);
                $pdf->SetY($pdf->GetY()-1);
            }

            $formattedname = '';
            if($labeldata[$i]['Introduced'] == 'Not native') $formattedname .= '*';
            $formattedname .= $labeldata[$i]['FormattedName'];
            $formattedname = "<div style=\"font-size: 11pt;\">$formattedname</div>";
            $pdf->MultiCell($props['whtml'], 5, $formattedname, 0, 'L', 0, 1, $labelbody_pos['x'][$x], $pdf->GetY(), true, 0, true, true, 0, 'T', false);
            
            if ($labeldata[$i]['ExtraInfo'])
                $pdf->MultiCell($props['whtml'], 5, $labeldata[$i]['ExtraInfo'], 0, 'L', 0, 1, $labelbody_pos['x'][$x], $pdf->GetY()-1, true, 0, true, true, 0, 'T', false);

            if (($labeldata[$i]['DetType'] == 'Det.' || $labeldata[$i]['DetType'] == 'Conf.') && $labeldata[$i]['DeterminedBy']) {
                $det = '<b>' . $labeldata[$i]['DetType'] . ':</b> ' . $labeldata[$i]['DeterminedBy'];
                if ($labeldata[$i]['DeterminedDate']) $det .= ', ' . $labeldata[$i]['DeterminedDate'];
                $pdf->MultiCell($props['whtml'], 5, $det, 0, 'L', 0, 1, $labelbody_pos['x'][$x], $pdf->GetY(), true, 0, true, true, 0, 'T', false);
            }
            
            if ($labeldata[$i]['TypeInfo']) {
                if ($this->input->post('labeltype') == 6 || $this->input->post('labeltype') == 7)
                    $labeldata[$i]['TypeInfo'] = str_replace ('HOLOTYPE', 'ISOTYPE', $labeldata[$i]['TypeInfo']);
                $pdf->MultiCell($props['whtml'], 5, $labeldata[$i]['TypeInfo'], 0, 'L', 0, 1, $labelbody_pos['x'][$x], $pdf->GetY()-1, true, 0, true, true, 0, 'T', false);
            }
            
            $collinfo = '<b>Coll.:</b> '. $labeldata[$i]['Collector'] . ' ' . $labeldata[$i]['CollectingNumber'];
            if ($labeldata[$i]['CollectingDate'])
                $collinfo .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>Date:</b> ' . $labeldata[$i]['CollectingDate'];
            $pdf->MultiCell($props['whtml'], 5, $collinfo, 0, 'L', 0, 1, $labelbody_pos['x'][$x], $pdf->GetY()+2, true, 0, true, true, 0, 'T', false);
            
            if ($labeldata[$i]['AdditionalCollectors'])
                $pdf->MultiCell($props['whtml'], 5, '<b>Addit. Coll.:</b> ' .$labeldata[$i]['AdditionalCollectors'], 0, 'L', 0, 1, $labelbody_pos['x'][$x], $pdf->GetY()-1, true, 0, true, true, 0, 'T', false);
                
            if ($labeldata[$i]['CollectingTrip'])
                $pdf->MultiCell($props['whtml'], 5, '<b>Collecting trip:</b> ' . $labeldata[$i]['CollectingTrip'], 0, 'L', 0, 1, $labelbody_pos['x'][$x], $pdf->GetY()-1, true, 0, true, true, 0, 'T', false);
            
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
            if ($labeldata[$i]['Habitat']) $hab['Habitat'] = '<b>Habitat:</b> ' . xml_convert($labeldata[$i]['Habitat']);
            if ($labeldata[$i]['AssociatedTaxa']) $hab['AssociatedTaxa'] = '<b>Associated taxa:</b> ' . xml_convert($labeldata[$i]['AssociatedTaxa']);
            if ($labeldata[$i]['Substrate']) $hab['Substrate'] = '<b>Substrate:</b> ' . xml_convert($labeldata[$i]['Substrate']);
            if ($labeldata[$i]['Host']) $hab['Host'] = '<b>Host:</b> ' . xml_convert($labeldata[$i]['Host']);
            if ($hab)
                $pdf->MultiCell($props['whtml'], 5, implode('<br/>', $hab), 0, 'L', 0, 1, $labelbody_pos['x'][$x], $pdf->GetY()+1, true, 0, true, true, 0, 'T', false);
            
            if ($labeldata[$i]['Provenance'])
                $pdf->MultiCell($props['whtml'], 5, '<b>Provenance:</b> '. $labeldata[$i]['Provenance'], 0, 'L', 0, 1, $labelbody_pos['x'][$x], $pdf->GetY()+1, true, 0, true, true, 0, 'T', false);
            
            
            $not = array();
            if ($labeldata[$i]['DescriptiveNotes']) $not['DescriptiveNotes'] = '<b>Descriptive notes:</b> ' . xml_convert($labeldata[$i]['DescriptiveNotes']);
            if ($labeldata[$i]['Introduced']) $not['Introduced'] = '<b>Natural occurrence:</b> ' . $labeldata[$i]['Introduced'];
            if ($labeldata[$i]['Cultivated']) $not['Cultivated'] = '<b>Cultivated occurrence:</b> ' . $labeldata[$i]['Cultivated'];
            if ($labeldata[$i]['CollectingNotes']) $not['CollectingNotes'] = '<b>Collecting notes:</b> ' . xml_convert($labeldata[$i]['CollectingNotes']);
            if ($labeldata[$i]['Ethnobotany']) $not['Ethnobotany'] = '<b>Ethnobotany notes:</b> ' . $labeldata[$i]['Ethnobotany'];
            if ($labeldata[$i]['Toxicity']) $not['Toxicity'] = '<b>Toxicity notes:</b> ' . $labeldata[$i]['Toxicity'];
            if ($labeldata[$i]['MiscellaneousNotes']) $not['MiscellaneousNotes'] = '<b>Misc. notes:</b> ' . xml_convert($labeldata[$i]['MiscellaneousNotes']);
            
            if ($not) {
                $pdf->SetY($pdf->GetY() + 1);
                $pdf->MultiCell($props['whtml'], 5, implode('<br/>', $not), 0, 'L', 0, 1, $labelbody_pos['x'][$x], $pdf->GetY()-1, true, 0, true, true, 0, 'T', false);
            }
            
            if ($labeldata[$i]['Multisheet'] && !$dup && !in_array($this->input->post('labeltype'), array(19, 21))) {
                $pdf->SetY($pdf->GetY() + 2);
                $pdf->MultiCell($props['whtml'], 5, $labeldata[$i]['Multisheet'], 0, 'L', 0, 1, $labelbody_pos['x'][$x], $pdf->GetY()+1, true, 0, true, true, 0, 'T', false);
            }
            
            if (isset($labeldata[$i]['VRSMultisheets']) && $labeldata[$i]['VRSMultisheets']) {
                $pdf->SetY($pdf->GetY() + 2);
                $pdf->MultiCell($props['whtml'], 5, $labeldata[$i]['VRSMultisheets'], 0, 'L', 0, 1, $labelbody_pos['x'][$x], $pdf->GetY()+1, true, 0, true, true, 0, 'T', false);
            }
            
            if ($labeldata[$i]['MixedInfo']) {
                $mixed = '<b>This is a mixed collection. The components are:</b><br/>';
                if ($props['type'] == 3 || $props['type'] == 4)
                    $labeldata[$i]['MixedInfo'] = str_replace ('<br/>', ' &ndash; ', $labeldata[$i]['MixedInfo']);
                //echo '<pre>' . str_replace('<', '&lt;', $labeldata[$i]['MixedInfo']) . '</pre>';
                $mixed .= $labeldata[$i]['MixedInfo'];
                $pdf->MultiCell($props['whtml'], 5, $mixed, 0, 'L', 0, 1, $labelbody_pos['x'][$x], $pdf->GetY()+1, true, 0, true, true, 0, 'T', false);
            }
            
            if ($dup || $this->input->post('labeltype')==19 || $this->input->post('labeltype')==21) {
                $dupl = '<b>This specimen is a duplicate of MEL ' . $labeldata[$i]['MelNumber'] . '.</b>';
                $pdf->MultiCell($props['whtml'], 5, $dupl, 0, 'L', 0, 1, $labelbody_pos['x'][$x], $pdf->GetY()+1, true, 0, true, true, 0, 'T', false);
            }
            
            if ($labeldata[$i]['DuplicateInfo']) 
                $pdf->MultiCell($props['whtml'], 5, '<b>Dupl.:</b> ' . $labeldata[$i]['DuplicateInfo'], 0, 'L', 0, 1, $labelbody_pos['x'][$x], $pdf->GetY()+1, true, 0, true, true, 0, 'T', false);
            
            if ($labeldata[$i]['VicRefSet']) 
                $pdf->MultiCell($props['whtml'], 5, '<b>Vic. Ref. Set:</b> ' . $labeldata[$i]['VicRefSet'], 0, 'L', 0, 1, $labelbody_pos['x'][$x], $pdf->GetY()+2, true, 0, true, true, 0, 'T', false);
            
            $storedunder = $labeldata[$i]['StoredUnder'];
            if ($labeldata[$i]['HortRefSet'])
                $storedunder = str_replace ('Main collection', 'Hort. Ref. Set', $storedunder);
            if($props['footerpositionabsolute']) {
                if ($dup) {
                    
                    $footer = '<div style="font-size: 7pt">Printed from MELISR, ' . date('d M. Y') . '</div>';
                    $melnumber = 'MEL ' . $labeldata[$i]['MelNumber'];
                    $pdf->MultiCell(40, 5, '<div style="font-size: 7pt">MEL specimen stored under:<br/>'.$storedunder . '</div>', 0, 'L', 0, 1, $labelfooter_pos['x'][$x], $barcode_pos['y'][$y]+1, true, false, true);
                    $pdf->MultiCell(90, 5, $footer, 0, 'L', 0, 1, $labelfooter_pos['x'][$x], $labelfooter_pos['y'][$y]+3, true, 0, true, true, 0, 'T', false);
                    $pdf->write1DBarcode($melnumber, 'C39', $barcode_pos['x'][$x], $barcode_pos['y'][$y]-1, 55, 12, 0.1, $barcodestyle, 'N');
                    $pdf->MultiCell(55, 5, '<b>'.$melnumber.'</b>', 0, 'C', 0, 1, $barcodetext_pos['x'][$x], $labelfooter_pos['y'][$y]+2, true, false, true);
                }
                else {
                    if ($labeldata[$i]['Continent'])
                        $storedunder .= ' (' . $labeldata[$i]['Continent'] . ')';
                    
                    if ($this->input->post('labeltype') != 19 && $this->input->post('labeltype') != 21) {
                        $storage = '<div style="font-size: 7pt">' . $storedunder . '</div>';
                        $pdf->MultiCell(90, 5, $storage, 0, 'L', 0, 1, $labelfooter_pos['x'][$x], $labelfooter_pos['y'][$y]-3.5, true, 0, true, true, 0, 'T', false);
                    }
                    $footer = '<div style="font-size: 7pt">Printed from MELISR, ' . date('d M. Y') . '</div>';
                    $pdf->MultiCell(90, 5, $footer, 0, 'L', 0, 1, $labelfooter_pos['x'][$x], $labelfooter_pos['y'][$y], true, 0, true, true, 0, 'T', false);
                }
            }
            else {
                $yy = $pdf->GetY()+5;
                if ($dup) {
                    $footer = '<div style="font-size: 7pt">Printed from MELISR, ' . date('d M. Y') . '</div>';
                    $melnumber = 'MEL ' . $labeldata[$i]['MelNumber'];
                    $pdf->MultiCell(40, 5, '<div style="font-size: 7pt">MEL specimen stored under:<br/>'.$storedunder, 0, 'L', 0, 1, $labelfooter_pos['x'][$x], $yy+1, true, false, true);
                    $pdf->write1DBarcode($melnumber, 'C39', $barcode_pos['x'][$x], $yy, 55, 12, 0.1, $barcodestyle, 'N');
                    $pdf->MultiCell(55, 5, '<b>'.$melnumber.'</b>', 0, 'C', 0, 1, $barcodetext_pos['x'][$x], $yy+11, true, false, true);
                    $pdf->MultiCell(90, 5, $footer, 0, 'L', 0, 1, $labelfooter_pos['x'][$x], $yy+11.5, true, 0, true, true, 0, 'T', false);
                }
                else {
                    if ($labeldata[$i]['Continent'])
                        $storedunder .= ' (' . $labeldata[$i]['Continent'] . ')';
                    
                    if ($this->input->post('labeltype') != 19 && $this->input->post('labeltype') != 21) {
                        $storage = '<div style="font-size: 7pt">' . $storedunder . '</div>';
                        $pdf->MultiCell(90, 5, $storage, 0, 'L', 0, 1, $labelfooter_pos['x'][$x], $yy, true, 0, true, true, 0, 'T', false);
                        $yy = $pdf->GetY()-2;
                    }
                    $footer = '<div style="font-size: 7pt">Printed from MELISR, ' . date('d M. Y') . '</div>';
                    $pdf->MultiCell(90, 5, $footer, 0, 'L', 0, 1, $labelfooter_pos['x'][$x], $yy, true, 0, true, true, 0, 'T', false);
                }
            }
            
        }
        // move pointer to last page
        $pdf->lastPage();

        // ---------------------------------------------------------

        //Close and output PDF document
        $pdf->Output('mellabel.pdf', 'I');
	}

    function printSpiritCard($labeldata, $props, $start=0) {
        set_time_limit (600);
        // create new PDF document
        $pageformat = array(
            'format' => 'A4',
            'Rotate' => 0
        );

        $pdf = new TCPDF('L', 'mm', $pageformat, true, 'UTF-8', false);

        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Niels Klazenga');
        $pdf->SetTitle('MEL Label');
        $pdf->SetSubject('MEL Label');

        //set margins
        $pdf->SetTopMargin(7.5);

        //set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, 0);

        // remove default header/footer
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        // ---------------------------------------------------------

        // set font
        $pdf->SetFont('times', '', 9);
        // set cell padding
        $pdf->setCellPaddings(0, 0, 0, 0);
        // set cell margins
        $pdf->setCellMargins(0, 0, 0, 0);

        $labelheader='<p style="font-weight: bold"><span style="font-size: 12pt;">NATIONAL HERBARIUM OF VICTORIA (MEL)</span><br />
            <span style="font-size: 11pt;">MELBOURNE, AUSTRALIA</span></p>';
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

        $left = 149.5;

        for($i=0; $i<count($labeldata); $i++) {
            $pdf->AddPage();
            $pdf->MultiCell(94, 5, $labelheader, 0, 'C', 0, 1, $left+5, $props['yheader'], true, false, true);
            $pdf->MultiCell(39.5, 5, $labeldata[$i]['spiritinfo'], 0, 'R', 0, 0, $left+99.5, $props['yheader'], true, false, true);
            $pdf->write1DBarcode($labeldata[$i]['melnumber'], 'C39', $left+96.5, $props['ybarcode'], 45, 9, 0.1, $barcodestyle, 'N');
            $pdf->MultiCell(45, 5, '<b>'.$labeldata[$i]['melnumber'].'</b>', 0, 'C', 0, 1, $left+96.5, $props['ybarcodetext'], true, false, true);
            $pdf->MultiCell($props['whtml'], 5, $labeldata[$i]['determination'], 0, 'L', 0, 1, $left+5, $pdf->GetY(), true, 0, true, true, 0, 'T', false);
            if ($labeldata[$i]['typeinfo'])
                $pdf->MultiCell($props['whtml'], 5, $labeldata[$i]['typeinfo'], 0, 'L', 0, 1, $left+5, $pdf->GetY(), true, 0, true, true, 0, 'T', false);
            $pdf->MultiCell($props['whtml'], 5, $labeldata[$i]['collectinginfo'], 0, 'L', 0, 1, $left+5, $pdf->GetY()+1, true, 0, true, true, 0, 'T', false);
            $pdf->MultiCell($props['whtml'], 5, $labeldata[$i]['locality'], 0, 'L', 0, 1, $left+5, $pdf->GetY()+1, true, 0, true, true, 0, 'T', false);
            if($labeldata[$i]['notes'])
                $pdf->MultiCell($props['whtml'], 5, $labeldata[$i]['notes'], 0, 'L', 0, 1, $left+5, $pdf->GetY()+1, true, 0, true, true, 0, 'T', false);
            if($labeldata[$i]['multisheet'])
                $pdf->MultiCell($props['whtml'], 5, $labeldata[$i]['multisheet'], 0, 'L', 0, 1, $left+5, $pdf->GetY()+1, true, 0, true, true, 0, 'T', false);
            if($labeldata[$i]['mixed'])
                $pdf->MultiCell($props['whtml'], 5, $labeldata[$i]['mixed'], 0, 'L', 0, 1, $left+5, $pdf->GetY()+1, true, 0, true, true, 0, 'T', false);
            if ($pdf->GetY() > 97) $pdf->AddPage();
            $footer = str_replace('Main collection', 'Spirit collection', $labeldata[$i]['footer']);
            $pdf->MultiCell(111.5, 5, $footer, 0, 'L', 0, 1, $left+5, 95, true, 0, true, true, 0, 'T', false);

        }
        // move pointer to last page
        $pdf->lastPage();

        // ---------------------------------------------------------

        //Close and output PDF document
        $pdf->Output('mellabel.pdf', 'I');
	}

    function printAnnotationSlip($labeldata, $props, $detnotes, $start=0) {
        $numx = $props['numx'];
        $numy = $props['numy'];
        $labelheight = $props['dimensions']['labelheight'];
        $labelwidht = $props['dimensions']['labelwidth'];
        $labelheader_pos = $props['dimensions']['labelheader_pos'];
        $labelbody_pos = $props['dimensions']['labelbody_pos'];
        $numlabels = $numx*$numy;

        set_time_limit(600);
        $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);

        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Niels Klazenga');
        $pdf->SetTitle('MEL Label');
        $pdf->SetSubject('MEL Label');

        //set margins
        $pdf->SetMargins(5, 7.5, 5);

        //set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, 3);

        // remove default header/footer
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        // ---------------------------------------------------------

        // set font
        $pdf->SetFont('times', '', 8);
        // set cell padding
        $pdf->setCellPaddings(0, 0, 0, 0);
        // set cell margins
        $pdf->setCellMargins(0, 0, 0, 0);

        $labelheader='<p style="font-weight: bold"><span style="font-size:20px;">NATIONAL HERBARIUM OF VICTORIA (MEL)</span></p>';
        if ($start > 0) $pdf->AddPage();
        for($i=0; $i<count($labeldata); $i++) {
            $j = $i+$start;
            $offset = $j%($numx*$numy);
            $x = $offset%$numx;
            $y = floor($offset/$numx);

            if($j%$numlabels == 0) $pdf->AddPage();
            $pdf->MultiCell($props['wheader'], 1, $labelheader, 0, 'C', 0, 1, $labelheader_pos['x'][$x], $labelheader_pos['y'][$y], true, false, true);
            $melno = '<p>MEL ';
            $melno .= (integer) substr($labeldata[$i]['CatalogNumber'], 0, 7) . substr($labeldata[$i]['CatalogNumber'], 7);
            $melno .= '</p>';
            $pdf->MultiCell($props['whtml'], 5, $melno, 0, 'L', 0, 1, $labelbody_pos['x'][$x], $labelbody_pos['y'][$y]-1, true, 0, true, true, 0, 'T', false);
            $pdf->MultiCell($props['whtml'], 5, $labeldata[$i]['FormattedName'], 0, 'C', 0, 1, $labelbody_pos['x'][$x], $pdf->GetY(), true, 0, true, true, 0, 'T', false);

            if ($detnotes) {
                $notes = '<p style="line-height:80%">' . $labeldata[$i]['DeterminationNotes'] . '</p>';
                $pdf->MultiCell($props['whtml'], 5, $notes, 0, 'L', 0, 1, $labelbody_pos['x'][$x], $pdf->GetY(), true, 0, true, true, 0, 'T', false);
            }
            if (!$labeldata[$i]['Role']) $labeldata[$i]['Role'] = 'Det.';
            if ($labeldata[$i]['Role'] == 'Acc. name change') $by = 'Accepted name change';
            else $by = $labeldata[$i]['Role'] . ' ' . $labeldata[$i]['Determiner'];
            $pdf->MultiCell($props['whtml'], 5, $by, 0, 'L', 0, 1, $labelbody_pos['x'][$x], $labelbody_pos['y'][$y]+15, true, 0, true, true, 0, 'T', false);
            $pdf->MultiCell($props['whtml'], 5, $labeldata[$i]['DeterminedDate'], 0, 'R', 0, 1, $labelbody_pos['x'][$x], $labelbody_pos['y'][$y]+15, true, 0, true, true, 0, 'T', false);
        }

        $pdf->lastPage();
        $pdf->Output('mellabel.pdf', 'I');
    }

    function printAveryLabel($labeldata, $props, $start=0) {
        $numx = $props['numx'];
        $numy = $props['numy'];
        $labelheight = $props['dimensions']['labelheight'];
        $labelwidht = $props['dimensions']['labelwidth'];
        $labelheader_pos = $props['dimensions']['labelheader_pos'];
        //$barcode_pos = $props['dimensions']['barcode_pos'];
        //$barcodetext_pos = $props['dimensions']['barcodetext_pos'];
        $labelbody_pos = $props['dimensions']['labelbody_pos'];
        //$labelfooter_pos = $props['dimensions']['labelfooter_pos'];
        $numlabels = $numx*$numy;

        set_time_limit (600);
        // create new PDF document
        $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);

        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Niels Klazenga');
        $pdf->SetTitle('MEL Label');
        $pdf->SetSubject('MEL Label');

        //set margins
        $pdf->SetMargins(5, 7.5, 5);

        //set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, 3);

        // remove default header/footer
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        // ---------------------------------------------------------

        // set font
        $pdf->SetFont('times', '', 8);
        // set cell padding
        $pdf->setCellPaddings(0, 0, 0, 0);
        // set cell margins
        $pdf->setCellMargins(0, 0, 0, 0);

        if ($labelheader_pos)
            $labelheader='<p style="font-weight: bold"><span style="font-size:20.5px;">NATIONAL HERBARIUM OF VICTORIA (MEL)</span></p>';

        if ($start > 0) $pdf->AddPage();
        for($i=0; $i<count($labeldata); $i++) {
            $j = $i+$start;
            $offset = $j%($numx*$numy);
            $x = $offset%$numx;
            $y = floor($offset/$numx);

            if($j%$numlabels == 0) $pdf->AddPage();

            if ($labelheader_pos)
                $pdf->MultiCell($props['wheader'], 1, $labelheader, 0, 'C', 0, 1, $labelheader_pos['x'][$x], $labelheader_pos['y'][$y], true, false, true);
            $pdf->MultiCell($props['whtml'], 5, $labeldata[$i], 0, 'L', 0, 1, $labelbody_pos['x'][$x], $labelbody_pos['y'][$y], true, 0, true, true, 0, 'T', false);
        }   
        // move pointer to last page
        $pdf->lastPage();

        // ---------------------------------------------------------

        //Close and output PDF document
        $pdf->Output('mellabel.pdf', 'I');
	}

    function printBarcodeLabel($props, $barcode_start, $barcode_count=30, $start=0) {
        $numx = $props['numx'];
        $numy = $props['numy'];
        $labelheight = $props['dimensions']['labelheight'];
        $labelwidht = $props['dimensions']['labelwidth'];
        $labelheader_pos = $props['dimensions']['labelheader_pos'];
        $labelbody_pos = $props['dimensions']['labelbody_pos'];
        $numlabels = $numx*$numy;
        $barcode_pos = $props['dimensions']['barcode_pos'];
        $barcodetext_pos = $props['dimensions']['barcodetext_pos'];

        set_time_limit (600);
        // create new PDF document
        $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);

        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Niels Klazenga');
        $pdf->SetTitle('MEL Label');
        $pdf->SetSubject('MEL Label');

        //set margins
        $pdf->SetMargins(5, 7.5, 5);

        //set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, 3);

        // remove default header/footer
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        // ---------------------------------------------------------

        // set font
        $pdf->SetFont('times', '', 10);
        // set cell padding
        $pdf->setCellPaddings(0, 0, 0, 0);
        // set cell margins
        $pdf->setCellMargins(0, 0, 0, 0);


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
        for($i=0; $i<$barcode_count; $i++) {
            $j = $i+$start;
            $offset = $j%($numx*$numy);
            $x = $offset%$numx;
            $y = floor($offset/$numx);

            if($j%$numlabels == 0) $pdf->AddPage();

            $first = (int) $barcode_start;
            $first+=$i;
            $melnumber = 'MEL ' . $first;

            $pdf->write1DBarcode($melnumber, 'C39', $barcode_pos['x'][$x], $barcode_pos['y'][$y], 55, 12, 0.1, $barcodestyle, 'N');
            $pdf->MultiCell(55 , 5, '<b>'.$melnumber.'</b>', 0, 'C', 0, 1, $barcodetext_pos['x'][$x], $barcodetext_pos['y'][$y], true, false, true);
        }
        // move pointer to last page
        $pdf->lastPage();

        // ---------------------------------------------------------

        //Close and output PDF document
        $pdf->Output('mellabel.pdf', 'I');
	}
        
    function printBarcodeLabelRecordSet($props, $barcodes, $start=0) {
        $numx = $props['numx'];
        $numy = $props['numy'];
        $labelheight = $props['dimensions']['labelheight'];
        $labelwidht = $props['dimensions']['labelwidth'];
        $labelheader_pos = $props['dimensions']['labelheader_pos'];
        $labelbody_pos = $props['dimensions']['labelbody_pos'];
        $numlabels = $numx*$numy;
        $barcode_pos = $props['dimensions']['barcode_pos'];
        $barcodetext_pos = $props['dimensions']['barcodetext_pos'];

        set_time_limit (600);
        // create new PDF document
        $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);

        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Niels Klazenga');
        $pdf->SetTitle('MEL Label');
        $pdf->SetSubject('MEL Label');

        //set margins
        $pdf->SetMargins(5, 7.5, 5);

        //set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, 3);

        // remove default header/footer
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        // ---------------------------------------------------------

        // set font
        $pdf->SetFont('times', '', 10);
        // set cell padding
        $pdf->setCellPaddings(0, 0, 0, 0);
        // set cell margins
        $pdf->setCellMargins(0, 0, 0, 0);


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
        for($i=0; $i<count($barcodes); $i++) {
            $j = $i+$start;
            $offset = $j%($numx*$numy);
            $x = $offset%$numx;
            $y = floor($offset/$numx);

            if($j%$numlabels == 0) $pdf->AddPage();

            $melnumber = 'MEL ' . $barcodes[$i]['Barcode'];

            $pdf->write1DBarcode($melnumber, 'C39', $barcode_pos['x'][$x], $barcode_pos['y'][$y], 55, 12, 0.1, $barcodestyle, 'N');
            $pdf->MultiCell(55 , 5, '<b>'.$melnumber.'</b>', 0, 'C', 0, 1, $barcodetext_pos['x'][$x], $barcodetext_pos['y'][$y], true, false, true);
        }
        // move pointer to last page
        $pdf->lastPage();

        // ---------------------------------------------------------

        // Close and output PDF document
        $pdf->Output('mellabel.pdf', 'I');
    }

    function printVrsBarcodeLabelRecordSet($props, $barcodes, $start=0) {
        $numx = $props['numx'];
        $numy = $props['numy'];
        $labelheight = $props['dimensions']['labelheight'];
        $labelwidht = $props['dimensions']['labelwidth'];
        $labelheader_pos = $props['dimensions']['labelheader_pos'];
        $labelbody_pos = $props['dimensions']['labelbody_pos'];
        $numlabels = $numx*$numy;
        $barcode_pos = $props['dimensions']['barcode_pos'];
        $barcodetext_pos = $props['dimensions']['barcodetext_pos'];

        set_time_limit (600);
        // create new PDF document
        $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);

        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Niels Klazenga');
        $pdf->SetTitle('MEL Label');
        $pdf->SetSubject('MEL Label');

        //set margins
        $pdf->SetMargins(5, 7.5, 5);

        //set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, 3);

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
        for($i=0; $i<count($barcodes); $i++) {
            $j = $i+$start;
            $offset = $j%($numx*$numy);
            $x = $offset%$numx;
            $y = floor($offset/$numx);

            if($j%$numlabels == 0) $pdf->AddPage();

            $pdf->MultiCell(55 , 5, '<b>Victorian Reference Set</b>', 0, 'C', 0, 1, $barcodetext_pos['x'][$x], $barcode_pos['y'][$y]-3, true, false, true);
            
            $vrsnumber = $barcodes[$i]['Barcode'];

            $pdf->write1DBarcode($vrsnumber, 'C39', $barcode_pos['x'][$x], $barcode_pos['y'][$y], 55, 8, 0.1, $barcodestyle, 'N');
            $pdf->MultiCell(55 , 5, '<b>'.$vrsnumber.'</b>', 0, 'C', 0, 1, $barcodetext_pos['x'][$x], $barcodetext_pos['y'][$y]-4, true, false, true);
            $pdf->MultiCell(55 , 5, 'Duplicate of ' . $barcodes[$i]['MELNumber'], 0, 'C', 0, 1, $barcodetext_pos['x'][$x], $barcodetext_pos['y'][$y], true, false, true);
        }
        // move pointer to last page
        $pdf->lastPage();

        // ---------------------------------------------------------

        // Close and output PDF document
        $pdf->Output('mellabel.pdf', 'I');
    }
    
}

?>