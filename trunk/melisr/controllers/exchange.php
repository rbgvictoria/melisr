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
require_once('/var/www/melisr/libraries/LoanPDF.php');

class Loans extends Controller {
    var $loan;

    function __construct() {
        parent::Controller();
        $this->load->helper('form');
        $this->load->helper('file');
        $this->load->helper('url');
        $this->output->enable_profiler(TRUE);

    }

    function index() {
        $data['bannerimage'] = $this->banner();
        $this->load->model('loansmodel');
        $data['loans'] = $this->loansmodel->getLoanNumbers();
        $this->load->view('loansview', $data);
    }

    /*function banner() {
        $banners = get_dir_file_info('./images/banners', TRUE);
        $banners = array_values($banners);
        $count = count($banners);
        $i = rand(0, $count-1);
        return $banners[$i]['name'];
    }*/

    function loanpaperwork() {
        $data['bannerimage'] = $this->banner();
        $this->load->model('loansmodel');

        if ($this->input->post('loannumber')) {
            if(!$this->loaninfo = $this->loansmodel->getLoanInfo($this->input->post('loannumber'))) {
                $data['message'] = 'You are way ahead of yourself';
                $this->load->view('message', $data);
            }
            $this->loanpreparationsummary = $this->loansmodel->getLoanPreparationSummary($this->input->post('loannumber'));
            
            switch ($this->input->post('output')) {
                case 1:
                    $this->loanpreparations = $this->loansmodel->getLoanPreparations($this->input->post('loannumber'));
                    $this->loanPaperWorkPDF();
                    break;
                case 2:
                    $this->loanpreparations = $this->loansmodel->getLoanPreparations($this->input->post('loannumber'));
                    if ($this->loanpreparations)
                        $this->preparationListPDF();
                    else {
                        $data['message'] = 'This loan has no preparations';
                        $this->load->view('message', $data);
                    }
                    break;
                case 3:
                case 4:
                    $this->addressLabelPDF($this->input->post('output')); 
                    break;
                case 5:
                    $newurl = base_url() . 'pdf/MEL_Conditions_of_Loan.pdf';
                    redirect($newurl);
                    break;
            }   
        }
        else {
            $data['message'] = 'Please select a loan.';
            $this->load->view('message', $data);
        }
    }
    
    function loanPaperWorkPDF() {
        $this->loan->LoanNumber = $this->loaninfo['LoanNumber'];
        $this->Address();
        $this->LoanAgentString();

        $this->preptypes = array(
            'Sheet' => 'sheet',
            'Packet' => 'packet',
            'Microscope slide' => 'microscope slide',
            'Spirit' => 'spirit jar',
            'Cibachrome' => 'cibachrome',
            'Photograph of specimen' => 'photograph',
            'Type' => 'type'
        );

        $this->LoanSummaryString();
        $this->loanPrepHeader = array(
            'LoanNumber' => $this->loan->LoanNumber,
            'LoanAgent' => $this->LoanAgents,
            'ShipmentDate' => $this->loaninfo['ShipmentDate'],
            'LoanPrepSummary' => $this->LoanSummaryString()
        );

        $pdf = new TCPDF();
        
        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Niels Klazenga');
        $pdf->SetTitle('MEL Label');
        $pdf->SetSubject('MEL Label');

        //set margins
        $pdf->SetMargins(25, 37, 25);

        //set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, 15);

        // remove default header/footer
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        $pdf->setViewerPreferences(array('FitWindow' => true));


        // set font
        $pdf->SetFont('helvetica', '', 10);
        // set cell padding
        $pdf->setCellPaddings(0, 0, 0, 0);
        // set cell margins
        $pdf->setCellMargins(0, 0, 0, 0);


        // start loan cover letter
        $pdf->AddPage();

        $image_file = base_url() . 'images/loans_paperwork_background.png';
        $pdf->Image($image_file, 163, 0, 30, 0, 'PNG', '', 'T', true, 300, '', false, false, 0, false, false, false);


        $pdf->MultiCell(120, 5, $this->loaninfo['ShipmentDate'], 0, 'L', 0, 1, 25, 15, true, false, true);
        $pdf->MultiCell(120, 5, $this->loan->ShippedTo, 0, 'L', 0, 1, 25, 22, true, false, true);

        $pdf->MultiCell(125, 5, '<span style="font-size:16pt;font-weight:bold">MEL loan ' . $this->loan->LoanNumber . '</span>',
                0, 'L', 0, 1, 25, 65, true, false, true);
        $pdf->MultiCell(130, 1, '<hr/>', 0, 'L', 0, 1, 22.5, $pdf->GetY()+1, true, false, true);

        $w = 30;
        $y = $pdf->GetY()-2;
        $pdf->MultiCell($w, 5, 'For study by:', 0, 'L', 0, 1, 25, $y, true, false, true);
        $pdf->MultiCell(120-$w, 5, $this->LoanAgents, 0, 'L', 0, 1, 30+$w, $y, true, false, true);

        $y = $pdf->GetY()+1;
        $pdf->MultiCell($w, 5, 'Description:', 0, 'L', 0, 1, 25, $y, true, false, true);
        $description = $this->loaninfo['Description'];
        if (strpos($description, '||'))
            $description = substr($description, 0, strpos($description, '||'));
        $description = trim($description);
        $pdf->MultiCell(120-$w, 5, $description, 0, 'L', 0, 1, 30+$w, $y, true, false, true);

        $y = $pdf->GetY()+1;
        $pdf->MultiCell($w, 5, 'Quantity:', 0, 'L', 0, 1, 25, $y, true, false, true);
        $pdf->MultiCell(120-$w, 5, $this->Quantity(), 0, 'L', 0, 1, 30+$w, $y, true, false, true);

        $y = $pdf->GetY()+1;
        $pdf->MultiCell($w, 5, 'Due date:', 0, 'L', 0, 1, 25, $y, true, false, true);
        $pdf->MultiCell(120-$w, 5, $this->loaninfo['CurrentDueDate'], 0, 'L', 0, 1, 30+$w, $y, true, false, true);

        $y = $pdf->GetY()+1;
        $pdf->MultiCell($w, 5, 'Shipment details:', 0, 'L', 0, 1, 25, $y, true, false, true);
        $pdf->MultiCell(120-$w, 5, $this->loaninfo['ShipmentMethod'], 0, 'L', 0, 1, 30+$w, $y, true, false, true);

        if ($this->loaninfo['TrackingLabels']) {
            $y = $pdf->GetY()+1;
            $pdf->MultiCell($w, 5, 'Tracking label(s):', 0, 'L', 0, 1, 25, $y, true, false, true);
            $pdf->MultiCell(120-$w, 5, $this->loaninfo['TrackingLabels'], 0, 'L', 0, 1, 30+$w, $y, true, false, true);
        }

        $y = $pdf->GetY()+1;
        $pdf->MultiCell($w, 5, 'Loan conditions:', 0, 'L', 0, 1, 25, $y, true, false, true);
        $pdf->MultiCell(120-$w, 5, 'See attached. ' . $this->loaninfo['SpecialConditions'], 0, 'L', 0, 1, 30+$w, $y, true, false, true);

        $pdf->MultiCell(130, 5, '<hr/>', 0, 'L', 0, 1, 22.5, $pdf->GetY()+1, true, false, true);

        $paragraphs = array();
        $paragraphs[] = <<<EOD
Please verify the contents of the loan against the attached specimen list and acknowledge
receipt by returning the yellow copy of this form. Any damage in transit should be noted
on the yellow form.
EOD;
        $paragraphs[] = <<<EOD
This loan should be returned to MEL by the date shown above. An extension may be granted
on request.
EOD;

        if (strstr($this->LoanAgents, ' and ')) $c = 'comply';
        else $c = 'complies';

        $paragraphs[] = <<<EOD
Please ensure that $this->LoanAgents $c with the enclosed loan conditions.
EOD;
        
        $paragraphs[] = <<<EOD
Electronic data for the specimens in this loan is available on request. For queries relating
to loans, exchange or donations, please email MEL at herbmel@rbg.vic.gov.au.
EOD;
        
        foreach ($paragraphs as $para)
            $pdf->Multicell(125, 5, $para, 0, 'J', 0, 1, 25, $pdf->GetY()+1, true, false, true);

        $pdf->MultiCell(125, 5, $this->loaninfo['ShippedBy'] . ' on behalf of the Collections Manager', 0, 'L', 0, 1, 25, $pdf->GetY()+8, true, false, true);

        $y = 231;
        //$image_file = base_url() . 'images/scissors.png';
        //$pdf->Image($image_file, 22, $y-2.5, 5, 5, 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);
        //$pdf->MultiCell(127, 5, '<hr/>', 0, 'L', 0, 1, 25.5, $y, true, false, true);
        $pdf->MultiCell(130, 5, '<hr/>', 0, 'L', 0, 1, 22.5, $y, true, false, true);
        $y = $pdf->GetY()-1;
        $pdf->MultiCell(45, 5, 'Number of parcels: ' . $this->loaninfo['NumberOfPackages'], 0, 'L', 0, 1, 25, $y, true, false, true);
        $pdf->MultiCell(75, 5, 'Number of specimens: ' . $this->Quantity(), 0, 'L', 0, 1, 75, $y, true, false, true);
        $pdf->MultiCell(125, 5, 'Material received in good condition.', 0, 'L', 0, 1, 25, $pdf->GetY()+3, true, false, true);
        $y = $pdf->GetY()+3;
        $pdf->MultiCell(25, 5, 'Comments: ', 0, 'L', 0, 1, 25, $y, true, false, true);
        $pdf->MultiCell(105, 5, '<hr/>', 0, 'L', 0, 1, 45, $y+4, true, false, true);
        $pdf->MultiCell(125, 5, '<hr/>', 0, 'L', 0, 1, 25, $pdf->GetY()+1, true, false, true);
        $pdf->MultiCell(125, 5, '<hr/>', 0, 'L', 0, 1, 25, $pdf->GetY()+1, true, false, true);
        
        $y = $pdf->GetY()+1;
        $pdf->MultiCell(35, 5, 'Receiving officer:', 0, 'L', 0, 1, 25, $y, true, false, true);
        $pdf->MultiCell(60, 5, '<hr/>', 0, 'L', 0, 1, 53, $y+4, true, false, true);
        $pdf->MultiCell(15, 5, 'Date: ', 0, 'L', 0, 1, 115, $y, true, false, true);
        $pdf->MultiCell(25, 5, '<hr/>', 0, 'L', 0, 1, 125, $y+4, true, false, true);

        $pdf->setY(0);
        $x = 165;
        $pdf->SetFont('helvetica', '', 7);
        
        $letterh = <<<EOD
        <div style="color:#999999">National Herbarium of Victoria (MEL)<br />Birdwood Avenue<br />
            South Yarra<br />
            Victoria 3141<br />
            Australia
        </div>
EOD;
        $pdf->MultiCell(30, 5, $letterh, 0, 'L', 0, 1, $x, 60, true, false, true);

        $letterh = <<<EOD
        <div style="color:#999999">CITES<br />AU 026</div>
EOD;
        $pdf->MultiCell(30, 5, $letterh, 0, 'L', 0, 1, $x, $pdf->getY()+1, true, false, true);

        $letterh = <<<EOD
        <div style="color:#999999">Telephone<br />(03) 9252 2300</div>
EOD;
        $pdf->MultiCell(30, 5, $letterh, 0, 'L', 0, 1, $x, $pdf->getY()+1, true, false, true);

        $letterh = <<<EOD
        <div style="color:#999999">Facsimile<br />(03) 9252 2413</div>
EOD;
        $pdf->MultiCell(30, 5, $letterh, 0, 'L', 0, 1, $x, $pdf->getY()+1, true, false, true);

        $letterh = <<<EOD
        <div style="color:#999999">Email<br />herbmel@rbg.vic.gov.au</div>
EOD;
        $pdf->MultiCell(30, 5, $letterh, 0, 'L', 0, 1, $x, $pdf->getY()+1, true, false, true);

        $letterh = <<<EOD
        <div style="color:#999999">Web<br/ >www.rbg.vic.gov.au/science</div>
EOD;
        $pdf->MultiCell(31, 5, $letterh, 0, 'L', 0, 1, $x, $pdf->getY()+1, true, false, true);

        $letterh = <<<EOD
        <div style="color:#999999">The Royal Botanic Gardens Board (Victoria)</div>
EOD;
        $pdf->MultiCell(30, 5, $letterh, 0, 'L', 0, 1, $x, $pdf->getY()+111, true, false, true);

        $letterh = <<<EOD
        <div style="color:#999999">Patron<br />Dame Elisabeth Murdoch</div>
EOD;
        $pdf->MultiCell(30, 5, $letterh, 0, 'L', 0, 1, $x, $pdf->getY()+1, true, false, true);

        $letterh = <<<EOD
        <div style="color:#999999">Incorporating:</div>
EOD;
        $pdf->MultiCell(30, 5, $letterh, 0, 'L', 0, 1, $x, $pdf->getY()+5, true, false, true);

        $letterh = <<<EOD
        <div style="color:#999999">Royal Botanic Gardens Melbourne</div>
EOD;
        $pdf->MultiCell(30, 5, $letterh, 0, 'L', 0, 1, $x, $pdf->getY()+1, true, false, true);

        $letterh = <<<EOD
        <div style="color:#999999">National Herbarium of Victoria</div>
EOD;
        $pdf->MultiCell(30, 5, $letterh, 0, 'L', 0, 1, $x, $pdf->getY()+1, true, false, true);

        $letterh = <<<EOD
        <div style="color:#999999">Royal Botanic Gardens Cranbourne</div>
EOD;
        $pdf->MultiCell(30, 5, $letterh, 0, 'L', 0, 1, $x, $pdf->getY()+1, true, false, true);

        $letterh = <<<EOD
        <div style="color:#999999">Australian Research Centre for Urban Ecology</div>
EOD;
        $pdf->MultiCell(30, 5, $letterh, 0, 'L', 0, 1, $x, $pdf->getY()+1, true, false, true);

        // move pointer to last page
        $pdf->lastPage();

        // ---------------------------------------------------------

        //Close and output PDF document
        $pdf->Output('loan.pdf', 'I');
    }

    function preparationListPDF() {
        $this->loan->LoanNumber = $this->loaninfo['LoanNumber'];
        $this->Address();
        $this->LoanAgentString();

        $this->preptypes = array(
            'Sheet' => 'sheet',
            'Packet' => 'packet',
            'Microscope slide' => 'microscope slide',
            'Spirit' => 'spirit jar',
            'Cibachrome' => 'cibachrome',
            'Photograph of specimen' => 'photograph',
            'Type' => 'type'
        );

        $this->LoanSummaryString();
        $this->loanPrepHeader = array(
            'LoanNumber' => $this->loan->LoanNumber,
            'LoanAgent' => $this->LoanAgents,
            'ShipmentDate' => $this->loaninfo['ShipmentDate'],
            'LoanPrepSummary' => $this->LoanSummaryString()
        );

        set_time_limit(600);
        $pdf = new LoanPDF($this->loanPrepHeader);

        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Niels Klazenga');
        $pdf->SetTitle('MEL Label');
        $pdf->SetSubject('MEL Label');

        // set font
        $pdf->SetFont('helvetica', '', 9);
        //set margins
        $pdf->SetMargins(25, 37, 25);

        //set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, 21);


        // start list of loan preparations
        $pdf->AddPage();
        $this->getLoanPreparationsHTML();


        $pdf->MultiCell(160, 5, $this->loanPreparationsHTML, 0, 'L', 0, 1, 25, 37, true, false, true);

        // move pointer to last page
        $pdf->lastPage();

        // ---------------------------------------------------------

        //Close and output PDF document
        $pdf->Output('preplist.pdf', 'I');
    }

    function addressLabelPDF($outputformat) {
        $pdf = new TCPDF('L', 'mm', 'A4', true, 'UTF-8', false);

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
        if ($outputformat == 3)
            $pdf->SetFont('helvetica', '', 12);
        else 
            $pdf->SetFont('helvetica', '', 14);
        // set cell padding
        $pdf->setCellPaddings(0, 0, 0, 0);
        // set cell margins
        $pdf->setCellMargins(0, 0, 0, 0);

        $pdf->addPage();

        if ($outputformat == 3) {
            $x = 135;
            $y = 40;
        }
        else {
            $x = 165;
            $y = 30;
        }

        $this->Address();
        $pdf->MultiCell(100, 5, $this->loan->ShippedTo, 0, 'L', 0, 1, $x, $y, true, false, true);


        // move pointer to last page
        $pdf->lastPage();

        // ---------------------------------------------------------

        //Close and output PDF document
        $pdf->Output('mellabel.pdf', 'I');
	}


    function Address() {
        $shippedto = array();
        if ($this->loaninfo['ShippedTo']['Attn']) $shippedto[] = $this->loaninfo['ShippedTo']['Attn'];

        $institution = substr($this->loaninfo['Institution'], strpos($this->loaninfo['Institution'], '--')+3);

        $shippedto[] = $institution;
        $shippedto[] = $this->loaninfo['ShippedTo']['Address'];
        if ($this->loaninfo['ShippedTo']['Address2']) $shippedto[] = $this->loaninfo['ShippedTo']['Address2'];
        if ($this->loaninfo['ShippedTo']['Address3']) $shippedto[] = $this->loaninfo['ShippedTo']['Address3'];
        if ($this->loaninfo['ShippedTo']['RoomOrBuilding']) $shippedto[] = $this->loaninfo['ShippedTo']['RoomOrBuilding'];
        $shippedto[] = $this->loaninfo['ShippedTo']['City'] . ' ' .
                $this->loaninfo['ShippedTo']['State'] . ' ' . $this->loaninfo['ShippedTo']['PostCode'];
        if ($this->loaninfo['ShippedTo']['Country'] != 'Australia')
                $shippedto[] = $this->loaninfo['ShippedTo']['Country'];
        $this->loan->ShippedTo = implode('<br/>', $shippedto);
    }

    function LoanAgentString() {
        $this->LoanAgents = '';
        for ($i = 0; $i < count($this->loaninfo['LoanAgents']); $i++) {
            if ($i > 0 && $i < count($this->loaninfo['LoanAgents'])-1)
                $this->LoanAgents .= ', ';
            elseif ($i > 0 && $i == count($this->loaninfo['LoanAgents'])-1)
                $this->LoanAgents .= ' and ';
            $this->LoanAgents .= $this->loaninfo['LoanAgents'][$i]['Name'];
        }
    }

    function LoanSummaryString() {
        $numbers = array();
        foreach ($this->preptypes as $key => $value) {
            if (isset($this->loanpreparationsummary[$key])){
                if ($this->loanpreparationsummary[$key] > 1)
                    $numbers[] = $this->loanpreparationsummary[$key] . ' ' . $value . 's';
                else 
                    $numbers[] = $this->loanpreparationsummary[$key] . ' ' . $value;
            }
        }
        $numbers = implode('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;', $numbers);

        $loannumber = $this->loan->LoanNumber;
        $shipmentdate = $this->loaninfo['ShipmentDate'];

        return "Number of specimens: $numbers";
    }

    function Quantity() {
        $q = 0;
        foreach ($this->loanpreparationsummary as $key => $value) {
            if ($key != 'Type') $q += $value;
        }
        $t = '';
        if (isset($this->loanpreparationsummary['Type'])) {
            if ($this->loanpreparationsummary['Type'] > 1)
                $t = ' including ' . $this->loanpreparationsummary['Type'] . ' types';
            else
                $t = ' including ' . $this->loanpreparationsummary['Type'] . ' type';
        }
        return $q . $t;
    }

    function getLoanPreparationsHTML() {
        $loanpreparationHTML = array();
        $loanpreparationHTML[] = '<table>';
        foreach ($this->loanpreparations as $group) {
            $loanpreparationHTML[] = '<tr><td colspan="5"><b>' . ucfirst($this->preptypes[$group['PrepType']]) . '</b></td></tr>';
            
            // sort preparations by taxon name and catalogue number (or sample number)
            foreach ($group['Preparations'] as $key => $row) {
                $CatalogueNumber[$key] = $row['CatalogueNumber'];
                $SampleNumber[$key] = $row['SampleNumber'];
                $TaxonName[$key] = $row['TaxonName'];
            }
            if ($group['PrepType'] == 'Spirit' || $group['PrepType'] == 'Microscope slide')
                array_multisort ($TaxonName, SORT_ASC, $SampleNumber, SORT_ASC, $group['Preparations']);
            else
                array_multisort($TaxonName, SORT_ASC, $CatalogueNumber, SORT_ASC, $group['Preparations']);
            
            
            // try to do the thing with the multisheets
            $multiid = array();
            foreach ($group['Preparations'] as $key => $row) {
                if ($row['Multisheet']) {
                    //echo $row['Multisheet'] . "\n";
                    $multifirst = substr($row['Multisheet'],
                        strpos($row['Multisheet'], 'MEL ')+4,
                        strpos(str_replace(';', ',', $row['Multisheet']), ',')-strpos($row['Multisheet'], 'MEL ')-4);
                    if ($multifirst)
                        $multiid[$key] = $multifirst;
                }
            }
            $multiarray = array();
            //print_r($multiid);
            if ($multiid) {
                $countmulti = array_count_values(array_values($multiid));
                $multi_gt_one = array();
                foreach ($countmulti as $key => $value) {
                    if ($value > 1)
                        $multi_gt_one[] = $key;
                }
                foreach ($multiid as $key => $value) {
                    if (in_array($value, $multi_gt_one))
                        $multiarray[] = $key;
                }
            }

            foreach ($group['Preparations'] as $key => $prep) {
                $loanpreparationHTML[] = '<tr>';
                if ($group['PrepType'] == 'Spirit' || $group['PrepType'] == 'Microscope slide') {
                    if ($group['PrepType'] == 'Spirit')
                        $loanpreparationHTML[] = '<td width="15%">SP ' . $prep['SampleNumber'] . '</td>';
                    else
                        $loanpreparationHTML[] = '<td width="15%">SL ' . $prep['SampleNumber'] . '</td>';
                    $loanpreparationHTML[] = '<td width="3%" style="margin-right: 10px;">' . $prep['Quantity'] . '</td>';
                }
                else {
                    $melnumber = 'MEL ' . (integer) substr($prep['CatalogueNumber'], 0, 7);
                    $loanpreparationHTML[] = '<td colspan="2" width="18%">' . $melnumber . '</td>';
                }
                $loanpreparationHTML[] = '<td width="38%">' . $prep['TaxonName'] . '</td>';
                $loanpreparationHTML[] = '<td width="12%">' . (($prep['TypeStatus']) ? ucfirst($prep['TypeStatus']) : '&nbsp;') . '</td>';
                $loanpreparationHTML[] = '<td width="32%">' . ((in_array($key, $multiarray)) ? $prep['Multisheet'] : '&nbsp;') . '</td>';
                $loanpreparationHTML[] = '</tr>';
            }
            $loanpreparationHTML[] = '<tr><td colspan="5">&nbsp;</td></tr>';
        }
        $loanpreparationHTML[] = '</table>';
        $this->loanPreparationsHTML = implode('', $loanpreparationHTML);
    }

    function deletedups() {
        $data['bannerimage'] = $this->banner();
        $this->load->model('loansmodel');
        $del = $this->loansmodel->deleteDuplicates();
        $data['message'] = "$del loan preparation records of duplicates have been deleted";
        $this->load->view('message', $data);
    }

}

?>