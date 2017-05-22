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
        $this->data['js'][] = 'jquery.melcensus.js';
    }
    
    public function index()
    {
        $this->data['majorGroups'] = $this->mel_census_model->getStorageGroups(2);
        $this->load->view('mel_census_view', $this->data);
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
    
    
}
