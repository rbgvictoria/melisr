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
class CupboardLabelPDF {
    
    public function render($labels) 
    {
        $pdf = new TCPDF('L');
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Niels Klazenga');
        $pdf->SetTitle('MEL Cupboard Label');
        $pdf->SetSubject('MEL Cupboard Label');

        //set margins
        $pdf->SetMargins(5, 7.5, 5);

        $pdf->SetAutoPageBreak(true, 7.5);

        // remove default header/footer
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

        // ---------------------------------------------------------

        // set font
        $pdf->SetFont('Helvetica', 'B', 24);
        // set cell padding
        $pdf->setCellPaddings(0, 0, 0, 0);
        // set cell margins
        $pdf->setCellMargins(0, 0, 0, 0);
        
        $perPage = 10;
        foreach ($labels as $index => $label) {
            if ($index % $perPage == 0) {
                $pdf->AddPage();
            }
            
            $y = ($index % $perPage) * 20;
            
            $print = [];
            $print[] = strtoupper($label->storageGroup);
            if ($label->prepType) {
                $print[] = $label->prepType;
            }
            $print[] = $label->taxonName;
            if ($label->extraInfo) {
                $print[] = $label->extraInfo;
            }
            $txt = implode('  ', $print);
            
            $border = 0; /*["B" => [
                "width" => 0.1,
                "color" => [155, 155, 155]
            ]];*/
            $pdf->MultiCell(260, 5, $txt, 0, 'L', false, 1, 10, 5+$y, false, false, false, false, 20, 'T');
            $pdf->Line(0, $y+20, 297, $y+20, ["width"=>0.05, "color" => [155, 155, 155]]);
            
            
            if (substr($label->prepType, 0, 1) === 'F') {
                $pdf->Image('http://melisr.rbg.vic.gov.au/dev/images/green-dot-md-50px.jpg', 275, 5+$y, 10, 10, 'jpg', '', 'T', true, 300);
            }
                    
        }
        
        $pdf->lastPage();
        $pdf->Output();
    }
}
