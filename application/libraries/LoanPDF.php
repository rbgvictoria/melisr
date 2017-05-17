<?php
class LoanPDF extends TCPDF {
    var $headerHtml;
    var $footerHtml;

    public function __construct($headerHtml, $footerHtml=false, $orientation='P', $unit='mm', $format='A4', $unicode=true, $encoding='UTF-8', $diskcache=false) {
        parent::__construct($orientation, $unit, $format, $unicode, $encoding, $diskcache);
        $this->headerHtml = $headerHtml;
        $this->footerHtml = $footerHtml;
    }

    //Page header
    public function Header() {
            // Set font
            $this->SetFont('helvetica', '', 10);
            // Title
            $this->MultiCell(160, 5, 'National Herbarium of Victoria', 0, 'L', 0, 1, 25, 15, true, false, true);
            if ($this->PageNo()%2 == 0)
                $this->MultiCell(160, 5, '<b>MEL loan ' . $this->headerHtml['LoanNumber'] . '</b>', 0, 'L', 0, 1, 25, $this->GetY()-1, true, false, true);
            else {
                $this->MultiCell(160, 5, '<b>MEL loan ' . $this->headerHtml['LoanNumber'] .
                    '</b> for study by ' . $this->headerHtml['LoanAgent'], 0, 'L', 0, 1, 25, $this->GetY()-1, true, false, true);
                $this->MultiCell(160, 5, $this->headerHtml['ShipmentDate'], 0, 'L', 0, 1, 25, $this->GetY()-1, true, false, true);
                $this->MultiCell(160, 5, $this->headerHtml['LoanPrepSummary'], 0, 'L', 0, 1, 25, $this->GetY()-1, true, false, true);
            }
            $this->Multicell(165, 5, '<hr/>', 0, 'L', 0, 1, 22.5, 33, true, false, true);
    }

    // Page footer
    public function Footer() {
            // Position at 15 mm from bottom
            $this->SetY(-20);
            // Set font
            $this->SetFont('helvetica', '', 8);
            // Page number
            $this->Multicell(165, 5, '<hr/>', 0, 'L', 0, 1, 22.5, $this->GetY(), true, false, true);

            //$pageno = $this->PageNo()-2;
            //$this->Multicell(160, 5, 'Page '.$pageno, 0, 'C', 0, 1, 25, $this->GetY(), true, false, false);
            $this->Cell(0, 1, 'Page '.$this->getAliasNumPage().' of '.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
    }
}
?>
