<?php
/**
 * @property CI_Loader $load
 * @property CI_Form_validation $form_validation
 * @property CI_Input $input
 * @property CI_Email $email
 * @property CI_DB_active_record $db
 * @property CI_DB_forge $dbforge
 */
require_once APPPATH . 'libraries/LoanPDF.php';
require_once APPPATH . 'libraries/ExchangePDF.php';

class Transaction_controller extends CI_Controller 
{
    var $loan;
    var $loaninfo;

    function __construct() {
        parent::__construct();
        $this->load->helper('file');
        $this->output->enable_profiler(false);
        $this->load->model('Loan_model', 'loansmodel');
        $this->load->model('Exchange_model', 'exchangemodel');
        $this->load->model('Non_mel_loan_model', 'nonmelloanmodel');
        $this->data['title'] = 'MELISR | Transactions';
        $this->loan = new stdClass();
    }

    function index() {
        $this->data['loans'] = $this->loansmodel->getLoanNumbers();
        $this->data['exchange_out'] = $this->exchangemodel->getGiftNumbers();
        $this->data['non_mel_loans'] = $this->nonmelloanmodel->getNonMelLoanNumbers();
        $this->data['institutions'] = $this->exchangemodel->getInstitutions();
        $this->load->view('transactionsview', $this->data);
    }

    function loanpaperwork() {
        if ($this->input->post('deleteduplicates')) {
            $this->deletedups();
            return FALSE;
        }
        
        
        if ($this->input->post('fixexchangenumbers')) {
            $this->deletenondupgiftpreps();
            return FALSE;
        }
        
        if ($this->input->post('output') < 6 || in_array($this->input->post('output'), array(16, 19))) { // loans
            if ($this->input->post('loannumber')) {
                $this->loaninfo = $this->loansmodel->getLoanInfo($this->input->post('loannumber'));
                if(!$this->loaninfo) {
                    $this->data['message'] = 'You are way ahead of yourself';
                    $this->load->view('message', $this->data);
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
                            $this->data['message'] = 'This loan has no preparations';
                            $this->load->view('message', $this->data);
                        }
                        break;
                    case 3:
                    case 4:
                    case 16:
                    case 19:
                        $this->addressLabelPDF($this->input->post('output'));
                        break;
                    case 5:
                        $newurl = base_url() . 'pdf/MEL_Conditions_of_Loan.pdf';
                        redirect($newurl);
                        break;
                }   
            }
            else {
                $this->data['message'] = 'Please select a loan.';
                $this->load->view('message', $this->data);
            }
        }
        elseif ($this->input->post('output') < 10 || in_array($this->input->post('output'), array(17, 20))) { // gifts
            if ($this->input->post('exchangeoutnumber')) {
                if(!$this->loaninfo = $this->exchangemodel->getInfo($this->input->post('exchangeoutnumber'))) {
                    $this->data['message'] = 'You are way ahead of yourself';
                    $this->load->view('message', $this->data);
                }
                //print_r($this->loaninfo);
                $this->loanpreparationsummary = $this->exchangemodel->getPreparationSummary($this->input->post('exchangeoutnumber'));

                switch ($this->input->post('output')) {
                    case 6:
                        $this->exchangePaperWorkPDF();
                        break;
                    case 7:
                        $this->loanpreparations = $this->exchangemodel->getPreparations($this->input->post('exchangeoutnumber'));
                        if ($this->loanpreparations) {
                            $this->exchangePreparationListPDF();
                        }
                        else {
                            $this->data['message'] = 'This loan has no preparations';
                            $this->load->view('message', $this->data);
                        }
                        break;
                    case 8:
                    case 9:
                    case 17:
                    case 20:
                        $this->addressLabelPDF($this->input->post('output'));
                        break;
               }   
            }
            else {
                $this->data['message'] = 'Please select an exchange.';
                $this->load->view('message', $this->data);
            }
        }
        elseif (in_array($this->input->post('output'), array(10, 11, 18, 21))) {
            if ($this->input->post('institution')) {
                $this->loaninfo = $this->exchangemodel->getAddressLabelInfo($this->input->post('institution'));
                $this->addressLabelPDF($this->input->post('output'));
            }
            else {
                $this->data['message'] = 'Please select an institution or person for which to print an address label.';
                $this->load->view('message', $this->data);
            }
        }
        elseif ($this->input->post('output') > 11 ) { // non MEL loans
            if ($this->input->post('nonmelloan')) {
                if(!$this->loaninfo = $this->nonmelloanmodel->getNonMelLoanInfo($this->input->post('nonmelloan'))) {
                    $this->data['message'] = 'You are way ahead of yourself';
                    $this->load->view('message', $this->data);
                }
                switch ($this->input->post('output')) {
                    case 12:
                        $this->nonMELLoanPaperWorkPDF();
                        break;
                    case 14:
                    case 15:
                    case 22:
                    case 23:
                        $this->addressLabelPDF($this->input->post('output'));
                        break;

                    default:
                        break;
                }
                
            }
            else {
                $this->data['message'] = 'Please select an a non-MEL loan.';
                $this->load->view('message', $this->data);
            }
        }
        else {
            $this->data['message'] = 'Please select an output option.';
            $this->load->view('message', $this->data);
        }
    }
    
    function loanPaperWorkPDF() {
        $this->loan->LoanNumber = substr($this->loaninfo['LoanNumber'], 0, 9);
        $this->Address();
        $this->LoanAgentString();

        $this->preptypes = array(
            'Sheet' => 'sheet',
            'Packet' => 'packet',
            'Microscope slide' => 'microscope slide',
            'Silica gel sample' => 'silica gel sample',
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
        $pdf->SetMargins(20, 30, 20);

        //set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, 10);

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

        $pdf->Image('images/mel-letterhead.jpg', 25, 0, '', '', '', '', 'T', true, 300, '', false, false, 0, false, false, false);
        
        $x = 20;
        $pdf->MultiCell(120, 5, $this->loaninfo['ShipmentDate'], 0, 'L', 0, 1, $x, 12, true, false, true);
        $pdf->MultiCell(120, 5, $this->loan->ShippedTo, 0, 'L', 0, 1, $x, 19, true, false, true);

        $pdf->MultiCell(125, 5, '<span style="font-size:16pt;font-weight:bold">MEL loan ' . $this->loan->LoanNumber . '</span>',
                0, 'L', 0, 1, $x, 60, true, false, true);
        $pdf->MultiCell(140, 1, '<hr/>', 0, 'L', 0, 1, $x-2.5, $pdf->GetY()+1, true, false, true);

        $w = 30;
        $y = $pdf->GetY()-2;
        $pdf->MultiCell($w, 5, 'For study by:', 0, 'L', 0, 1, $x, $y, true, false, true);
        $pdf->MultiCell(130-$w, 5, $this->LoanAgents, 0, 'L', 0, 1, $x+$w+5, $y, true, false, true);

        $y = $pdf->GetY()+1;
        $pdf->MultiCell($w, 5, 'Description:', 0, 'L', 0, 1, $x, $y, true, false, true);
        $description = $this->loaninfo['Description'];
        if (strpos($description, '||'))
            $description = substr($description, 0, strpos($description, '||'));
        $description = trim($description);
        $pdf->MultiCell(130-$w, 5, $description, 0, 'L', 0, 1, $x+$w+5, $y, true, false, true);

        $y = $pdf->GetY()+1;
        $pdf->MultiCell($w, 5, 'Quantity:', 0, 'L', 0, 1, $x, $y, true, false, true);
        $pdf->MultiCell(130-$w, 5, $this->Quantity(), 0, 'L', 0, 1, $x+$w+5, $y, true, false, true);

        $y = $pdf->GetY()+1;
        $pdf->MultiCell($w, 5, 'Due date:', 0, 'L', 0, 1, $x, $y, true, false, true);
        $pdf->MultiCell(130-$w, 5, $this->loaninfo['CurrentDueDate'], 0, 'L', 0, 1, $x+$w+5, $y, true, false, true);

        $y = $pdf->GetY()+1;
        $pdf->MultiCell($w, 5, 'Shipment details:', 0, 'L', 0, 1, $x, $y, true, false, true);
        $pdf->MultiCell(130-$w, 5, $this->loaninfo['ShipmentMethod'], 0, 'L', 0, 1, $x+$w+5, $y, true, false, true);

        if ($this->loaninfo['TrackingLabels']) {
            $y = $pdf->GetY()+1;
            $pdf->MultiCell($w, 5, 'Tracking label(s):', 0, 'L', 0, 1, $x, $y, true, false, true);
            $pdf->MultiCell(130-$w, 5, $this->loaninfo['TrackingLabels'], 0, 'L', 0, 1, $x+$w+5, $y, true, false, true);
        }

        $y = $pdf->GetY()+1;
        $pdf->MultiCell($w, 5, 'Loan conditions:', 0, 'L', 0, 1, $x, $y, true, false, true);
        $pdf->MultiCell(130-$w, 5, str_replace("\n", '<br/>', $this->loaninfo['SpecialConditions']), 0, 'L', 0, 1, $x+$w+5, $y, true, false, true);

        $pdf->MultiCell(140, 5, '<hr/>', 0, 'L', 0, 1, $x-2.5, $pdf->GetY()+1, true, false, true);

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

/*        $paragraphs[] = <<<EOD
Please ensure that $this->LoanAgents $c with the enclosed loan conditions.
EOD;*/
        $loannumber = $this->loan->LoanNumber;
/*        $paragraphs[] = <<<EOD
Electronic data for the specimens in this loan can be accessed through <i>Australia's Virtual Herbarium</i> 
(AVH), <a href="http://avh.ala.org.au">http://avh.ala.org.au</a>. You can query for the records in this loan using the Advanced search by typing '$loannumber' in the Loan identifier field (under Herbarium transactions) 
and selecting 'National Herbarium of Victoria' from the Herbarium drop-down list (under Specimen).<br/>
<a href="http://avh.ala.org.au/occurrences/search?q=loan_identifier:$loannumber&fq=collection_uid:co55">http://avh.ala.org.au/occurrences/search?q=loan_identifier:$loannumber&fq=collection_uid:co55</a> will get you to the results directly.
EOD;
 * */
 
        $paragraphs[] = <<<EOD
Data for the specimens in this loan is available through <i>Australia’s Virtual Herbarium</i> (AVH, <a href="http://avh.chah.org.au">http://avh.chah.org.au</a>). 
Access the data directly at:<br/><a href="http://avh.ala.org.au/occurrences/search?q=loan_identifier:$loannumber&fq=collection_uid:co55">http://avh.ala.org.au/occurrences/search?q=loan_identifier:$loannumber&fq=collection_uid:co55</a>, 
or go to Advanced search and select ‘National Herbarium of Victoria’ under Herbarium and enter '$loannumber' in Loan number.
EOD;
        
        $paragraphs[] = <<<EOD
For queries relating to loans, exchange or donations, please email MEL at herbmel@rbg.vic.gov.au.
EOD;
        
        $paragraphs[] = <<<EOD
<span style="color:#ff0000;font-weight:bold;">NOTE: Before returning loan, please contact MEL for current Biosecurity documentation.</span>
EOD;
        
        $pdf->SetY($pdf->GetY()-2);
        foreach ($paragraphs as $para) {
            $pdf->Multicell(135, 5, $para, 0, 'L', 0, 1, $x, $pdf->GetY()+1, true, false, true);
        }
        
        $pdf->MultiCell(135, 5, $this->loaninfo['ShippedBy'] . ' on behalf of the Collections Manager', 0, 'L', 0, 1, $x, $pdf->GetY()+3, true, false, true);

        $y = 236;
        $pdf->MultiCell(140, 5, '<hr/>', 0, 'L', 0, 1, $x-2.5, $y, true, false, true);
        $y = $pdf->GetY()-1;
        $pdf->MultiCell(45, 5, 'Number of parcels: ' . $this->loaninfo['NumberOfPackages'], 0, 'L', 0, 1, $x, $y, true, false, true);
        $pdf->MultiCell(75, 5, 'Number of specimens: ' . $this->Quantity(), 0, 'L', 0, 1, 75, $y, true, false, true);
        $pdf->MultiCell(125, 5, 'Material received in good condition.', 0, 'L', 0, 1, $x, $pdf->GetY()+3, true, false, true);
        $y = $pdf->GetY()+3;
        $pdf->MultiCell(25, 5, 'Comments: ', 0, 'L', 0, 1, $x, $y, true, false, true);
        $pdf->MultiCell(115, 5, '<hr/>', 0, 'L', 0, 1, $x+20, $y+4, true, false, true);
        $pdf->MultiCell(135, 5, '<hr/>', 0, 'L', 0, 1, $x, $pdf->GetY()+1, true, false, true);
        $pdf->MultiCell(135, 5, '<hr/>', 0, 'L', 0, 1, $x, $pdf->GetY()+1, true, false, true);
        
        $y = $pdf->GetY()+1;
        $pdf->MultiCell(35, 5, 'Receiving officer:', 0, 'L', 0, 1, $x, $y, true, false, true);
        $pdf->MultiCell(60, 5, '<hr/>', 0, 'L', 0, 1, $x+30, $y+4, true, false, true);
        $pdf->MultiCell(15, 5, 'Date: ', 0, 'L', 0, 1, $x+90, $y, true, false, true);
        $pdf->MultiCell(25, 5, '<hr/>', 0, 'L', 0, 1, $x+100, $y+4, true, false, true);

        $pdf->lastPage();

        // ---------------------------------------------------------

        //Close and output PDF document
        $pdf->Output('loan.pdf', 'I');
    }

    function nonMelLoanPaperWorkPDF() {
        $this->loan->LoanNumber = $this->loaninfo['LoanNumber'];
        $this->Address();

        $pdf = new TCPDF();
        
        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Niels Klazenga');
        $pdf->SetTitle('non-MEL loan paperwork');
        $pdf->SetSubject('non-MEL loan paperwork');

        //set margins
        $pdf->SetMargins(20, 30, 20);

        //set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, 10);

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

        $pdf->Image('images/mel-letterhead.jpg', 25, 0, '', '', '', '', 'T', true, 300, '', false, false, 0, false, false, false);
        
        
        $x = 20;
        $pdf->MultiCell(120, 5, $this->loaninfo['ShipmentDate'], 0, 'L', 0, 1, $x, 12, true, false, true);
        $pdf->MultiCell(120, 5, $this->loan->ShippedTo, 0, 'L', 0, 1, $x, 19, true, false, true);

        $pdf->MultiCell(140, 5, '<span style="font-size:16pt;font-weight:bold">MEL returning loan ' . $this->loan->LoanNumber . ' (MEL ref. ' . $this->loaninfo['MELRefNo'] . ')</span>',
                0, 'L', 0, 1, $x, 60, true, false, true);
        $pdf->MultiCell(140, 1, '<hr/>', 0, 'L', 0, 1, $x-2.5, $pdf->GetY()+1, true, false, true);

        $w = 30;
        $y = $pdf->GetY()-2;
        $pdf->MultiCell($w, 5, 'Taxa:', 0, 'L', 0, 1, $x, $y, true, false, true);
        $pdf->MultiCell(130-$w, 5, $this->loaninfo['TaxaOnLoan'], 0, 'L', 0, 1, $x+$w+5, $y, true, false, true);

        $y = $pdf->GetY()+1;
        $pdf->MultiCell($w, 5, 'Sent for study by:', 0, 'L', 0, 1, $x, $y, true, false, true);
        $pdf->MultiCell(130-$w, 5, $this->loaninfo['LoanAgents'], 0, 'L', 0, 1, $x+$w+5, $y, true, false, true);

        $y = $pdf->GetY()+1;
        $pdf->MultiCell($w, 5, 'Shipment details:', 0, 'L', 0, 1, $x, $y, true, false, true);
        $pdf->MultiCell(130-$w, 5, $this->loaninfo['ShipmentMethod'], 0, 'L', 0, 1, $x+$w+5, $y, true, false, true);

        if ($this->loaninfo['TrackingLabels']) {
            $y = $pdf->GetY()+1;
            $pdf->MultiCell($w, 5, 'Tracking label(s):', 0, 'L', 0, 1, $x, $y, true, false, true);
            $pdf->MultiCell(130-$w, 5, $this->loaninfo['TrackingLabels'], 0, 'L', 0, 1, $x+$w+5, $y, true, false, true);
        }
        
        $y = $pdf->GetY()+1;
        $pdf->MultiCell($w, 5, 'Loan summary:', 0, 'L', 0, 1, $x, $y, true, false, true);
        $pdf->MultiCell(40, 5, $this->loaninfo['DateReceived'], 0, 'L', 0, 1, $x+$w+35, $y, true, false, true);
        $pdf->MultiCell(35, 5, (integer) $this->loaninfo['QuantityReceived'] . ' received', 
                0, 'L', 0, 1, $x+$w+5, $y, true, false, true);
        
        $last = count($this->loaninfo['ShipmentSummary'])-1;
        foreach ($this->loaninfo['ShipmentSummary'] as $index=>$row) {
            $y = $pdf->GetY();
            if ($index == $last) {
                $pdf->MultiCell(40, 5, '<b>' . $row['ShipmentDate'] . '</b>', 0, 'L', 0, 1, $x+$w+35, $y, true, false, true);
                $pdf->MultiCell(35, 5, '<b>' . (integer) $row['Number1'] . ' returned</b>', 0, 'L', 0, 1, $x+$w+5, $y, true, false, true);
            }               
            else {
                $pdf->MultiCell(40, 5, $row['ShipmentDate'], 0, 'L', 0, 1, $x+$w+35, $y, true, false, true);
                $pdf->MultiCell(35, 5, (integer) $row['Number1'] . ' returned', 0, 'L', 0, 1, $x+$w+5, $y, true, false, true);
            }
        }

        $y = $pdf->GetY()+1;
        $pdf->MultiCell($w, 5, 'Outstanding:', 0, 'L', 0, 1, $x, $y, true, false, true);
        $outstanding = ($this->loaninfo['Outstanding'] > 0) ? (integer) $this->loaninfo['Outstanding'] . ' specimens' : 'This loan has now been fully returned';
        $pdf->MultiCell(130-$w, 5, $outstanding, 0, 'L', 0, 1, $x+$w+5, $y, true, false, true);

        $pdf->MultiCell(140, 5, '<hr/>', 0, 'L', 0, 1, $x-2.5, $pdf->GetY()+1, true, false, true);

        $paragraphs = array();
        $paragraphs[] = <<<EOD
Please acknowledge receipt by returning the yellow copy of this form. Any damage should be noted on the form.
EOD;
        $paragraphs[] = <<<EOD
For queries relating to loans, exchange or donations please email MEL at HerbMEL@rbg.vic.gov.au.
EOD;

        $pdf->SetY($pdf->GetY()-2);
        foreach ($paragraphs as $para)
            $pdf->Multicell(135, 5, $para, 0, 'L', 0, 1, $x, $pdf->GetY()+1, true, false, true);

        $pdf->MultiCell(135, 5, $this->loaninfo['ShippedBy'] . '<br/>on behalf of the Collections Manager', 0, 'L', 0, 1, $x, 220, true, false, true);

        $y = 236;
        //$image_file = base_url() . 'images/scissors.png';
        //$pdf->Image($image_file, 22, $y-2.5, 5, 5, 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);
        //$pdf->MultiCell(127, 5, '<hr/>', 0, 'L', 0, 1, 25.5, $y, true, false, true);
        $pdf->MultiCell(140, 5, '<hr/>', 0, 'L', 0, 1, $x-2.5, $y, true, false, true);
        $y = $pdf->GetY()-1;
        $pdf->MultiCell(45, 5, 'Number of parcels: ' . $this->loaninfo['NumberOfPackages'], 0, 'L', 0, 1, $x, $y, true, false, true);
        $pdf->MultiCell(75, 5, 'Number of specimens: ' . (integer) $this->loaninfo['QuantityReturned'], 0, 'L', 0, 1, 75, $y, true, false, true);
        $pdf->MultiCell(125, 5, 'Material received in good condition.', 0, 'L', 0, 1, $x, $pdf->GetY()+3, true, false, true);
        $y = $pdf->GetY()+3;
        $pdf->MultiCell(25, 5, 'Comments: ', 0, 'L', 0, 1, $x, $y, true, false, true);
        $pdf->MultiCell(115, 5, '<hr/>', 0, 'L', 0, 1, $x+20, $y+4, true, false, true);
        $pdf->MultiCell(135, 5, '<hr/>', 0, 'L', 0, 1, $x, $pdf->GetY()+1, true, false, true);
        $pdf->MultiCell(135, 5, '<hr/>', 0, 'L', 0, 1, $x, $pdf->GetY()+1, true, false, true);
        
        $y = $pdf->GetY()+1;
        $pdf->MultiCell(35, 5, 'Receiving officer:', 0, 'L', 0, 1, $x, $y, true, false, true);
        $pdf->MultiCell(60, 5, '<hr/>', 0, 'L', 0, 1, $x+30, $y+4, true, false, true);
        $pdf->MultiCell(15, 5, 'Date: ', 0, 'L', 0, 1, $x+90, $y, true, false, true);
        $pdf->MultiCell(25, 5, '<hr/>', 0, 'L', 0, 1, $x+100, $y+4, true, false, true);

        $pdf->Output('nonmelloan.pdf', 'I');
    }

    function exchangePaperWorkPDF() {
        $this->loan->GiftNumber = $this->loaninfo['GiftNumber'];
        $this->Address();
        $this->LoanAgentString();

        $this->preptypes = array(
            'Duplicate' => 'duplicate',
            'Seed duplicate' => 'seed duplicate',
            'Silica gel sample' => 'silica gel sample',
            'Shipping material' => 'shipping material',
            'Type' => 'type'
        );

        $this->exchangeSummaryString();
        $this->loanPrepHeader = array(
            'ExchangeNumber' => $this->loan->GiftNumber,
            'LoanAgent' => $this->LoanAgents,
            'ShipmentDate' => $this->loaninfo['ShipmentDate'],
            'LoanPrepSummary' => $this->exchangeSummaryString()
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

        $pdf->Image('images/mel-letterhead.jpg', 25, 0, '', '', '', '', 'T', true, 300, '', false, false, 0, false, false, false);

        $pdf->MultiCell(120, 5, $this->loaninfo['ShipmentDate'], 0, 'L', 0, 1, 25, 15, true, false, true);
        $pdf->MultiCell(120, 5, $this->loan->ShippedTo, 0, 'L', 0, 1, 25, 22, true, false, true);

        $pdf->MultiCell(130, 5, '<span style="font-size:16pt;font-weight:bold">MEL ' . $this->loaninfo['ExchangeType'] .
                ' to ' . $this->loaninfo['Acronym'] . ' (MEL ref.' . $this->loan->GiftNumber . ')</span>',
                0, 'L', 0, 1, 25, 65, true, false, true);
        $pdf->MultiCell(130, 1, '<hr/>', 0, 'L', 0, 1, 22.5, $pdf->GetY()+1, true, false, true);

        $w = 30;
        $y = $pdf->GetY()-2;
        if ($this->loaninfo['GiftAgents']) {
            $pdf->MultiCell($w, 5, 'Attention:', 0, 'L', 0, 1, 25, $y, true, false, true);
            $pdf->MultiCell(130-$w, 5, $this->loaninfo['GiftAgents'], 0, 'L', 0, 1, 30+$w, $y, true, false, true);
            $y = $pdf->GetY()+1;
        }
        $pdf->MultiCell($w, 5, 'Description:', 0, 'L', 0, 1, 25, $y, true, false, true);
        $description = $this->loaninfo['Description'];
        if (strpos($description, '||'))
            $description = substr($description, 0, strpos($description, '||'));
        $description = trim($description);
        $pdf->MultiCell(120-$w, 5, $description, 0, 'L', 0, 1, 30+$w, $y, true, false, true);

        $y = $pdf->GetY()+1;
        $pdf->MultiCell($w, 5, 'Quantity:', 0, 'L', 0, 1, 25, $y, true, false, true);
        $pdf->MultiCell(120-$w, 5, $this->Quantity(), 0, 'L', 0, 1, 30+$w, $y, true, false, true);
        
        if ($this->loaninfo['ExchangeType'] != 'shipping material') {
            $y = $pdf->GetY()+1;
            $pdf->MultiCell($w, 5, 'Electronic data:', 0, 'L', 0, 1, 25, $y, true, false, true);
            if ($this->LoanAgents) {
                $when = $this->loaninfo['ShipmentDate'];
                $what = $this->loaninfo['ExchangeFileName'];
                $text = $what . ' emailed to ' . $this->LoanAgents;
            }
            else {
                $text = 'Available on request';
            }
            $pdf->MultiCell(120-$w, 5, $text, 0, 'L', 0, 1, 30+$w, $y, true, false, true);
        }
        

        $y = $pdf->GetY()+1;
        $pdf->MultiCell($w, 5, 'Shipment details:', 0, 'L', 0, 1, 25, $y, true, false, true);
        $pdf->MultiCell(120-$w, 5, $this->loaninfo['ShipmentMethod'], 0, 'L', 0, 1, 30+$w, $y, true, false, true);

        if ($this->loaninfo['TrackingLabels']) {
            $y = $pdf->GetY()+1;
            $pdf->MultiCell($w, 5, 'Tracking label(s):', 0, 'L', 0, 1, 25, $y, true, false, true);
            $pdf->MultiCell(120-$w, 5, $this->loaninfo['TrackingLabels'], 0, 'L', 0, 1, 30+$w, $y, true, false, true);
        }

        $pdf->MultiCell(130, 5, '<hr/>', 0, 'L', 0, 1, 22.5, $pdf->GetY()+1, true, false, true);

        $paragraphs = array();
        $paragraphs[] = <<<EOD
Please verify the contents of this consignment against the attached specimen list and acknowledge
receipt by returning the yellow copy of this form.
EOD;
        $paragraphs[] = <<<EOD
For queries relating to loans, exchange or donations, please email MEL at herbmel@rbg.vic.gov.au.
EOD;

        foreach ($paragraphs as $para)
            $pdf->Multicell(125, 5, $para, 0, 'J', 0, 1, 25, $pdf->GetY()+1, true, false, true);

        $pdf->MultiCell(125, 5, $this->loaninfo['ShippedBy'] . ' on behalf of the Collections Manager', 0, 'L', 0, 1, 25, $pdf->GetY()+8, true, false, true);

        $y = 231;
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

        $pdf->Output('exchange.pdf', 'I');
    }

    function preparationListPDF() {
        $this->loan->LoanNumber = substr($this->loaninfo['LoanNumber'], 0, 9);
        $this->Address();
        $this->LoanAgentString();

        $this->preptypes = array(
            'Sheet' => 'sheet',
            'Packet' => 'packet',
            'Microscope slide' => 'microscope slide',
            'Silica gel sample' => 'silica gel sample',
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

    function exchangePreparationListPDF() {
        $this->loan->LoanNumber = $this->loaninfo['GiftNumber'];
        $this->Address();
        $this->LoanAgentString();

        $this->preptypes = array(
            'Duplicate' => 'duplicate',
            'Duplicate seed' => 'duplicate seed',
            'Silica gel sample' => 'silica gel sample',
            'Shipping material' => 'shipping material sample',
            'Type' => 'type'
        );

        $this->loanPrepHeader = array(
            'ExchangeNumber' => $this->loaninfo['GiftNumber'],
            'ExchangeType' => $this->loaninfo['ExchangeType'],
            'ExchangeTo' => $this->loaninfo['Acronym'],
            'LoanAgent' => $this->LoanAgents,
            'ShipmentDate' => $this->loaninfo['ShipmentDate'],
            'LoanPrepSummary' => $this->LoanSummaryString()
        );

        set_time_limit(600);
        $pdf = new ExchangePDF($this->loanPrepHeader);

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
        $this->getExchangePreparationsHTML();


        $pdf->MultiCell(160, 5, $this->loanPreparationsHTML, 0, 'L', 0, 1, 25, 37, true, false, true);

        // move pointer to last page
        $pdf->lastPage();

        // ---------------------------------------------------------

        //Close and output PDF document
        $pdf->Output('preplist.pdf', 'I');
    }

    function addressLabelPDF($outputformat) {
        if (in_array($outputformat, [16, 17, 18, 22])) { // 
            $format = [220, 110];
        }
        elseif (in_array($outputformat, [19, 20, 21, 23])) {
            $format = [213, 99];
        }
        else {
            $format = 'A4';
        }
        $pdf = new TCPDF('L', 'mm', $format, true, 'UTF-8', false);

        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Niels Klazenga');
        $pdf->SetTitle('Address Label');
        $pdf->SetSubject('Address Label');

        //set margins
        $pdf->SetTopMargin(7.5);

        //set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, 0);

        // remove default header/footer
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        // ---------------------------------------------------------

        // set font
        if (in_array($outputformat, array(3, 8, 10, 14, 16, 17, 18)))
            $pdf->SetFont('helvetica', '', 12);
        else 
            $pdf->SetFont('helvetica', '', 14);
        // set cell padding
        $pdf->setCellPaddings(0, 0, 0, 0);
        // set cell margins
        $pdf->setCellMargins(0, 0, 0, 0);

        $pdf->addPage();

        if (in_array($outputformat, array(16, 17, 18))) {
            $x = 60;
            $y = 40;
        }
        elseif (in_array($outputformat, array(19, 20, 21))) {
            $x = 30;
            $y = 30;
        }
        elseif (in_array($outputformat, array(3, 8, 10, 14))) {
            $x = 135;
            $y = 40;
        }
        else {
            $x = 117;
            $y = 30;
        }

        $this->Address();
        $pdf->MultiCell(100, 5, $this->loan->ShippedTo, 0, 'L', 0, 1, $x, $y, true, false, true);


        // move pointer to last page
        $pdf->lastPage();

        // ---------------------------------------------------------

        //Close and output PDF document
        $pdf->Output('addresslabel.pdf', 'I');
    }

    function addressLabelPDFNew($outputformat) {
        if (in_array($outputformat, array(3, 8, 10, 14))) {
            $format = array(220, 110);
        }
        else {
            $format = array(213, 99);
        }
        $pdf = new TCPDF('L', 'mm', $format, true, 'UTF-8', false);

        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Niels Klazenga');
        $pdf->SetTitle('Address Label');
        $pdf->SetSubject('Address Label');

        //set margins
        $pdf->SetTopMargin(7.5);

        //set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, 0);

        // remove default header/footer
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        // ---------------------------------------------------------

        // set font
        if (in_array($outputformat, array(3, 8, 10, 14)))
            $pdf->SetFont('helvetica', '', 12);
        else 
            $pdf->SetFont('helvetica', '', 14);
        // set cell padding
        $pdf->setCellPaddings(0, 0, 0, 0);
        // set cell margins
        $pdf->setCellMargins(0, 0, 0, 0);

        $pdf->addPage();

        if (in_array($outputformat, array(3, 8, 10, 14))) {
            $x = 60;
            $y = 40;
        }
        else {
            $x = 50;
            $y = 30;
        }

        $this->Address();
        $pdf->MultiCell(100, 5, $this->loan->ShippedTo, 0, 'L', 0, 1, $x, $y, true, false, true);


        // move pointer to last page
        $pdf->lastPage();

        // ---------------------------------------------------------

        //Close and output PDF document
        $pdf->Output('addresslabel.pdf', 'I');
    }

    function Address() {
        $shippedto = array();
        if ($this->loaninfo['ShippedTo']['Attn']) $shippedto[] = $this->loaninfo['ShippedTo']['Attn'];

        $institution = substr($this->loaninfo['Institution'], strpos($this->loaninfo['Institution'], '--')+3);

        $shippedto[] = $institution;
        $shippedto[] = $this->loaninfo['ShippedTo']['Address'];
        if ($this->loaninfo['ShippedTo']['Address2']) $shippedto[] = $this->loaninfo['ShippedTo']['Address2'];
        if ($this->loaninfo['ShippedTo']['Address3']) $shippedto[] = $this->loaninfo['ShippedTo']['Address3'];
        if ($this->loaninfo['ShippedTo']['Address4']) $shippedto[] = $this->loaninfo['ShippedTo']['Address4'];
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
                if ($key == 'Type') {
                    if ($this->loanpreparationsummary[$key] > 1)
                        $numbers[] = '(including ' . $this->loanpreparationsummary[$key] . ' ' . $value . 's)';
                    else 
                        $numbers[] = '(including ' . $this->loanpreparationsummary[$key] . ' ' . $value . ')';
                }
                else {
                    if ($this->loanpreparationsummary[$key] > 1)
                        $numbers[] = $this->loanpreparationsummary[$key] . ' ' . $value . 's';
                    else 
                        $numbers[] = $this->loanpreparationsummary[$key] . ' ' . $value;
                }
            }
        }
        $numbers = implode('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;', $numbers);

        $loannumber = $this->loan->LoanNumber;
        $shipmentdate = $this->loaninfo['ShipmentDate'];

        return "Number of specimens: $numbers";
    }
    
    function exchangeSummaryString() {
        //print_r($this->preptypes);
        //print_r($this->loanpreparationsummary);
        foreach ($this->preptypes as $key => $value) {
            if (isset($this->loanpreparationsummary[$key])){
                if (strtolower($key) != 'type') {
                    if ($this->loanpreparationsummary[$key] > 1)
                        $specimens = $this->loanpreparationsummary[$key] . ' specimens';
                    else
                        $specimens = $this->loanpreparationsummary[$key] . ' ' . $value;
                }
                else {
                    if ($this->loanpreparationsummary[$key] > 1)
                        $types = '(including ' . $this->loanpreparationsummary[$key] . ' types)';
                    else
                        $types = '(including 1 type)';
                }
            }
        }

        if (isset($types))
            return "Number of specimens: $specimens $types";
        else
            return "Number of specimens: $specimens";
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
            
            $CatalogueNumber = array();
            $SampleNumber = array();
            $TaxonName = array();
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

    function getExchangePreparationsHTML() {
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
            array_multisort($CatalogueNumber, SORT_ASC, $group['Preparations']);


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
                $loanpreparationHTML[] = '<td width="50%">' . $prep['TaxonName'] . '</td>';
                $loanpreparationHTML[] = '<td width="32%">' . (($prep['TypeStatus']) ? ucfirst($prep['TypeStatus']) : '&nbsp;') . '</td>';
                $loanpreparationHTML[] = '</tr>';
            }
            $loanpreparationHTML[] = '<tr><td colspan="5">&nbsp;</td></tr>';
        }
        $loanpreparationHTML[] = '</table>';
        $this->loanPreparationsHTML = implode('', $loanpreparationHTML);
    }

    function deletedups() {
        $this->load->model('loansmodel');
        $del = $this->loansmodel->deleteDuplicates();
        $this->data['message'] = "$del loan preparation records of duplicates have been deleted";
        $this->load->view('message', $this->data);
    }

    function deletenondupgiftpreps() {
        $this->load->model('loansmodel');
        $del = $this->loansmodel->deleteNonDuplicateGiftPreparations();
        $this->data['message'] = "$del gift preparation records of non-duplicate prep. types have been deleted.";
        $quantity = $this->loansmodel->resetGiftPrepQuantity();
        $this->data['message'] .= "<br/>Quantity set to 1 in $quantity records.";
        $this->load->view('message', $this->data);
    }

}

?>