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
 * Description of Mel_census_controller
 *
 * @author Niels Klazenga <Niels.Klazenga@rbg.vic.gov.au>
 */
class Mel_census_controller extends CI_Controller {
    protected $data;
    
    public function __construct() 
    {
        parent::__construct();
        $this->load->model('mel_census_model');
        $this->load->helper('json');
        $this->data = [];
    }
    
    public function index()
    {
        $this->data['js'][] = 'jquery.melcensus.js';
        $this->data['majorGroups'] = $this->mel_census_model->getStorageGroups(2);
        $this->load->view('mel_census_view', $this->data);
    }
    
    public function label($type)
    {
        $this->data['css'][] = 'jqueryui.autocomplete.css';
        $this->data['js'][] = 'jquery.melcensus.label.js';
        if ($type == 'cupboard') {
            $this->cupboardLabel();
        }
        elseif ($type == 'strawboard') {
            $this->strawboardLabel();
        }
        elseif ($type == 'taxon-name') {
            $this->taxonNameLabel();
        }
        elseif ($type == 'crypto-box') {
            $this->cryptogamBoxLabel();
        }
    }
    
    protected function cryptogamBoxLabel()
    {
        if ($this->input->post('data')) {
            $data = json_decode($this->input->post('data'));
            $labels = $data->labels;
            foreach ($labels as $index => $label) {
                $labels[$index]->majorGroup = $this->mel_census_model->getMajorGroup($label->storageId);
            }
            $this->load->library('CryptoBoxLabelPDF');
            $this->cryptoboxlabelpdf->render($labels);
        }
        else {
            $this->load->view('crypto_box_label_view', $this->data);
        }
    }
    
    protected function taxonNameLabel()
    {
        if ($this->input->post('data')) {
            $data = json_decode($this->input->post('data'));
            $labels = [];
            foreach ($data->labels as $label) {
                for ($i = 0; $i < $label->num; $i++) {
                    $labels[] = (object) ['taxonName' => $label->taxonName];
                }
            }
            $this->load->library('TaxonNameLabelPDF');
            $this->taxonnamelabelpdf->render($labels, $data->offset);
        }
        else {
            $this->load->view('taxon_name_label_view', $this->data);
        }
    }
    
    protected function cupboardLabel()
    {
        if ($this->input->post('data')) {
            $data = json_decode($this->input->post('data'));
            $labels = $data->labels;
            $this->load->library('CupboardLabelPDF');
            $this->cupboardlabelpdf->render($labels);
        }
        else {
            $this->load->view('cupboard_label_view', $this->data);
        }
    }
    
    protected function strawboardLabel()
    {
        if ($this->input->post('data')) {
            $data = json_decode($this->input->post('data'));
            $labels = $data->labels;
            $this->load->library('StrawboardLabelPDF');
            $this->strawboardlabelpdf->render($labels);
        }
        $this->load->view('strawboard_label_view', $this->data);
    }
    
    public function subgroups($groupId)
    {
        $data = $this->mel_census_model->getStorageGroups($groupId);
        echo json_output($data);
    }
    
    public function taxa($storageId)
    {
        $data = $this->mel_census_model->getTaxa($storageId);
        echo json_output($data);
    }
    
    public function autocomplete_storage_group()
    {
        $data = $this->mel_census_model->getStorageGroupSuggestions(urldecode($this->input->get('term')));
        echo json_output($data);
    }
    
    public function autocomplete_taxon_name()
    {
        $data = $this->mel_census_model->getTaxonSuggestions(urldecode($this->input->get('term')), $this->input->get('storageId'), $this->input->get('withAuthor'));
        echo json_output($data);
    }
    
    
    
}
