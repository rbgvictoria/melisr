<?php

/*
 * Copyright 2017 Niels Klazenga, Royal Botanic Gardens Victoria.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Description of CupboardLabel
 *
 * @author Niels Klazenga <Niels.Klazenga@rbg.vic.gov.au>
 */
class StrawboardLabelPDF {
    
    public function render($labels) 
    {
        $format = [213, 99];
        $pdf = new TCPDF('L', 'mm', $format);
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Niels Klazenga');
        $pdf->SetTitle('MEL Strawboard Label');
        $pdf->SetSubject('MEL Strawboard Label');

        //set margins
        $pdf->SetMargins(5, 7.5, 5);

        $pdf->SetAutoPageBreak(false);

        // remove default header/footer
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

        // ---------------------------------------------------------

        // set font
        $pdf->SetFont('Helvetica', 'B');
        // set cell padding
        $pdf->setCellPaddings(0, 0, 0, 0);
        // set cell margins
        $pdf->setCellMargins(0, 0, 0, 0);
        
        $perPage = 10;
        foreach ($labels as $index => $label) {
            $pdf->AddPage();
            $pdf->SetFontSize(24);
            $pdf->MultiCell(130, 5, strtoupper($label->storageGroup), 0, 'C', 0, '', 5, 10);
            $pdf->MultiCell(130, 5, 'TYPES', 0, 'C', 0, '', 5, 25);
            $pdf->SetFontSize(20);
            $pdf->MultiCell(130, 5, $label->fromTaxonName, 0, 'C', 0, '', 5, 40);
            $pdf->MultiCell(130, 5, 'to', 0, 'C', 0, '', 5, 55);
            $pdf->MultiCell(130, 5, $label->toTaxonName, 0, 'C', 0, '', 5, 70);
            $pdf->SetFontSize(24);
            $pdf->MultiCell(130, 5, $label->prepType, 0, 'L', 0, '', 5, 85);
            
        }
        
        $pdf->lastPage();
        $pdf->Output();
    }
}
