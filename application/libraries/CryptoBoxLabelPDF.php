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
class CryptoBoxLabelPDF {
    
    public function render($labels, $start=0) 
    {
        $props = $this->labelProperties();
        $numx = $props['numx'];
        $numy = $props['numy'];
        $labelbody_pos = $props['dimensions']['labelbody_pos'];
        $numlabels = $numx * $numy;

        set_time_limit (600);
        // create new PDF document
        $pdf = new TCPDF('L', 'mm', 'A4', true, 'UTF-8', false);

        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Niels Klazenga');
        $pdf->SetTitle('Cryptogam box labels');
        $pdf->SetSubject('Cryptogam box labels');

        //set margins
        $pdf->SetMargins(5, 7.5, 5);

        //set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, 3);

        // remove default header/footer
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        // ---------------------------------------------------------

        // set font
        $pdf->SetFont('helvetica', '', 14);
        // set cell padding
        $pdf->setCellPaddings(0, 0, 0, 0);
        // set cell margins
        $pdf->setCellMargins(0, 0, 0, 0);

        if ($start > 0) $pdf->AddPage();
        for($i=0; $i<count($labels); $i++) {
            $j = $i + $start;
            $offset = $j % ($numx * $numy);
            $x = $offset % $numx;
            $y = floor($offset / $numx);

            if($j % $numlabels == 0) $pdf->AddPage();

            $pdf->SetY($props['dimensions']['labelbody_pos']['y'][$y]);
            $pdf->SetFont('helvetica', '', 20);
            $pdf->MultiCell($props['htmlWidth'], 5, $labels[$i]->majorGroup, 0, 'L', 0, 1, $labelbody_pos['x'][$x], $pdf->GetY(), TRUE, 0, TRUE, TRUE, 0, 'T', false);
            $pdf->SetFont('helvetica', 'B', 20);
            $pdf->MultiCell($props['htmlWidth'], 5, $labels[$i]->storageGroup, 0, 'L', 0, 1, $labelbody_pos['x'][$x], $pdf->GetY(), TRUE, 0, TRUE, TRUE, 0, 'T', false);

            $pdf->SetFont('helvetica', 'B', 16);
            $pdf->MultiCell($props['htmlWidth'], 5, $labels[$i]->taxonName , 0, 'L', 0, 1, 
                    $labelbody_pos['x'][$x], $pdf->GetY()+3, true, 0, true, 
                    true, 0, 'T', false);
            $pdf->SetFont('helvetica', '', 16);
            $pdf->MultiCell($props['htmlWidth'], 5, $labels[$i]->extra , 0, 'L', 0, 1, 
                    $labelbody_pos['x'][$x], $pdf->GetY()+3, true, 0, true, 
                    true, 0, 'T', false);
        }   
        // move pointer to last page
        $pdf->lastPage();

        // ---------------------------------------------------------

        //Close and output PDF document
        $pdf->Output('mellabel.pdf', 'I');
    }
    
    protected function labelProperties()
    {
            $props = [
                'numx' => 3,
                'numy' => 1,
                'xpos' => 9,
                'htmlWidth' => 99,
                'htmlY' => 110,
                'dimensions' => [
                    'labelheight' => 210,
                    'labelwidth' => 99,
                    
                ],
            ];
            $props['dimensions']['labelbody_pos'] = [];
            $props['dimensions']['labelbody_pos']['x'] = [];
            for ($i = 0; $i < $props['numx']; $i++) {
                $props['dimensions']['labelbody_pos']['x'][] = $props['xpos'] + $i * $props['dimensions']['labelwidth'];
            }
            $props['dimensions']['labelbody_pos']['y'] = [];
            for ($i = 0; $i < $props['numy']; $i++) {
                $props['dimensions']['labelbody_pos']['y'][] = $props['htmlY'] + $i*$props['dimensions']['labelheight'];
            }
            return $props;
    }

}
