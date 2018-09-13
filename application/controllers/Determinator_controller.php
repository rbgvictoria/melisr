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

/**
 * @property CI_Loader $load
 * @property CI_Form_validation $form_validation
 * @property CI_Input $input
 * @property CI_Email $email
 * @property CI_DB_active_record $db
 * @property CI_DB_forge $dbforge
 */

/**
 * Description of Determinator_controller
 *
 * @author nklazenga
 */
class Determinator_controller extends CI_Controller {
    
    protected $data;
    
    public function __construct() 
    {
        parent::__construct();
        $this->output->enable_profiler(false);
        $this->load->model('Autocomplete_model', 'autocomplete');
        $this->load->model('Determinator_model', 'determinator_model');
        $this->data = [];
        $this->data['title'] = 'MELISR | Determinator';
        $this->data['js'][] = 'jquery.determinator.js';
    }
    
    public function index()
    {
        $this->load->view('determinator_view', $this->data);
    }
    
    public function taxon_name_autocomplete() 
    {
        if (empty($_GET['term'])) exit;
        $q = strtolower($_GET['term']);
        $data = $this->autocomplete->taxonNameAutocomplete($q);
        $this->jsonHeader();
        echo json_encode($data);
    }
    
    public function agent_autocomplete() 
    {
        if (empty($_GET['term'])) exit;
        $q = strtolower($_GET['term']);
        $data = $this->autocomplete->agentAutocomplete($q);
        $this->jsonHeader();
        echo json_encode($data);
    }
    
    public function printDetslips()
    {
        $data = json_decode($this->input->post('data'));
        $start = $data->start;
        
        $labeldata = [];
        foreach ($data->detslips as $rec) {
            $taxonName = $this->taxonName($rec->taxonID);
            $agentName = $this->agentName($rec->identifiedByID);
            if ($rec->day) {
                $detDate = $rec->day . ' ' . $this->month($rec->month) . ' ' . $rec->year;
            }
            elseif ($rec->month) {
                $detDate = $this->month($rec->month) . ' ' . $rec->year;
            }
            else {
                $detDate = $rec->year;
            }
            
            for ($i = 0; $i < $rec->number; $i++) {
                $label = [];
                $label['taxonName'] = $taxonName;
                $label['identifierRole'] = $rec->identifierRole;
                $label['identifiedBy'] = $agentName;
                $label['dateIdentified'] = $detDate;
                $label['note'] = $rec->note;
                $labeldata[] = $label;
            }
            
        }
        $this->printAveryLabel($labeldata, $start-1);
    }
    
    private function month($int)
    {
        $months = ['Jan.', 'Feb.', 'Mar.', 'Apr.', 'May', 'Jun.',
            'Jul.', 'Aug.', 'Sep.', 'Oct.', 'Nov.', 'Dec.'];
        return $months[$int-1];
    }
    
    private function taxonName($taxonId)
    {
            $tname = $this->determinator_model->getScientificName($taxonId);
            $taxonName = '<i>' . $tname->FullName . '</i>';
            $taxonName = str_replace(' subsp. ', '</i> subsp. <i>', $taxonName);
            $taxonName = str_replace(' var. ', '</i> var. <i>', $taxonName);
            $taxonName = str_replace(' f. ', '</i> var. <i>', $taxonName);
            if ($tname->Author) {
                $taxonName .= ' ' . $tname->Author;
            }
            return $taxonName;
    }
    
    private function agentName($agentId)
    {
        $agent = $this->determinator_model->getAgent($agentId);
        if ($agent->FirstName) {
            $agentName = $agent->FirstName . ' ' . $agent->LastName;
        }
        else {
            $agentName = $agent->LastName;
        }
        return $agentName;
    }
    
    private function labelProperties()
    {
        /*[
            'numx'  =>  3,
            'numy'  =>  10,
            'labelheight' => 26.7,
            'labelwidth' => 67.8,
            'wheader' => 56,
            'yheader' => 20,
            'whtml' =>  56,
            'yhtml' =>  24,
            'xpos'  =>  9,
            
        ];*/
            $props = [
                'numx' => 3,
                'numy' => 10,
                'xpos' => 9,
                'headerWidth' => 56,
                'headerX' => null,
                'headerY' => 19,
                'htmlWidth' => 57,
                'htmlY' => 24,
                'dimensions' => [
                    'labelheight' => 26.7,
                    'labelwidth' => 67.8,
                    
                ],
                'footerOffsetY' => 37
            ];
            $props['dimensions']['labelheader_pos'] = [];
            $props['dimensions']['labelheader_pos']['x'] = [];
            for ($i = 0; $i < $props['numx']; $i++) {
                $props['dimensions']['labelheader_pos']['x'][] = $props['xpos'] + $i * $props['dimensions']['labelwidth'];
            }
            $props['dimensions']['labelheader_pos']['y'] = [];
            for ($i = 0; $i < $props['numy']; $i++) {
                $props['dimensions']['labelheader_pos']['y'][] = $props['headerY'] + $i * $props['dimensions']['labelheight'];
            }
            $props['dimensions']['labelbody_pos'] = [];
            $props['dimensions']['labelbody_pos']['x'] = [];
            for ($i = 0; $i < $props['numx']; $i++) {
                $props['dimensions']['labelbody_pos']['x'][] = $props['xpos'] + $i * $props['dimensions']['labelwidth'];
            }
            $props['dimensions']['labelbody_pos']['y'] = [];
            for ($i = 0; $i < $props['numy']; $i++) {
                $props['dimensions']['labelbody_pos']['y'][] = $props['htmlY'] + $i*$props['dimensions']['labelheight'];
            }
            $props['dimensions']['labelfooter_pos'] = [];
            $props['dimensions']['labelfooter_pos']['x'] = [];
            for ($i = 0; $i < $props['numx']; $i++) {
                $props['dimensions']['labelfooter_pos']['x'][] = $props['xpos'] + $i * $props['dimensions']['labelwidth'];
            }
            $props['dimensions']['labelfooter_pos']['y'] = array();
            for ($i = 0; $i < $props['numy']; $i++) {
                $props['dimensions']['labelfooter_pos']['y'][] = $props['footerOffsetY'] + $i * $props['dimensions']['labelheight'];
            }
            return $props;
    }
    
    private function printAveryLabel($labeldata, $start=0) 
    {
        $props = $this->labelProperties();
        //print_r($labeldata);
        //print_r($props);
        //return false;
        $numx = $props['numx'];
        $numy = $props['numy'];
        $labelheight = $props['dimensions']['labelheight'];
        $labelwidht = $props['dimensions']['labelwidth'];
        $labelheader_pos = $props['dimensions']['labelheader_pos'];
        $labelbody_pos = $props['dimensions']['labelbody_pos'];
        $numlabels = $numx * $numy;

        set_time_limit (600);
        // create new PDF document
        $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);

        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Niels Klazenga');
        $pdf->SetTitle('Det. slips');
        $pdf->SetSubject('Det. slips');

        //set margins
        $pdf->SetMargins(5, 7.5, 5);

        //set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, 3);

        // remove default header/footer
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        // ---------------------------------------------------------

        // set font
        $pdf->SetFont('helvetica', '', 8);
        // set cell padding
        $pdf->setCellPaddings(0, 0, 0, 0);
        // set cell margins
        $pdf->setCellMargins(0, 0, 0, 0);

        if ($labelheader_pos) {
            $labelheader='<p style="font-weight: bold"><span style="font-size:7px;">NATIONAL HERBARIUM OF VICTORIA (MEL)</span></p>';
        }
        
        if ($start > 0) $pdf->AddPage();
        for($i=0; $i<count($labeldata); $i++) {
            $j = $i + $start;
            $offset = $j % ($numx * $numy);
            $x = $offset % $numx;
            $y = floor($offset / $numx);

            if($j % $numlabels == 0) $pdf->AddPage();

            $pdf->SetY($labelheader_pos['y'][$y]);
            $pdf->MultiCell($props['headerWidth'], 1, $labelheader, 0, 'C', 0, 1, $labelheader_pos['x'][$x], $pdf->GetY(), true, false, true);
            $pdf->MultiCell($props['htmlWidth'], 4, $labeldata[$i]['taxonName'], 0, 'L', 0, 1, $labelbody_pos['x'][$x], $pdf->GetY(), true, 0, true, true, 12, 'M', true);
            
            if ($labeldata[$i]['note']) {
                $pdf->MultiCell($props['htmlWidth'], 4, $labeldata[$i]['note'], 0, 'L', 0, 1, $labelbody_pos['x'][$x], $pdf->GetY(), true, 0, true, true, 0, 'T', false);
            }
            
            $identifiedBy = $labeldata[$i]['identifiedBy'];
            if ($identifiedBy == 'MEL -- National Herbarium of Victoria') {
                $identifiedBy = 'MEL';
            }
            $text = $labeldata[$i]['identifierRole'] . ' ' . $identifiedBy . ', ' . $labeldata[$i]['dateIdentified'];
            
            $pdf->SetY($props['dimensions']['labelfooter_pos']['y'][$y]);
            $pdf->MultiCell($props['htmlWidth'], 5, $text , 0, 'L', 0, 1, 
                    $labelbody_pos['x'][$x], $pdf->GetY(), true, 0, true, 
                    true, 0, 'T', false);
        }   
        // move pointer to last page
        $pdf->lastPage();

        // ---------------------------------------------------------

        //Close and output PDF document
        $pdf->Output('mellabel.pdf', 'I');
    }

    
    private function jsonHeader() {
        $this->output->enable_profiler(false);
        header('Content-type: application/json');
    }
}
