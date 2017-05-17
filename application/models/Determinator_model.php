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
 * Description of Determinator_model
 *
 * @author nklazenga
 */
class Determinator_model extends CI_Model {
    
    public function __construct() {
        parent::__construct();
    }
    
    public function getScientificName($taxonId)
    {
        $this->db->select('FullName, Author');
        $this->db->from('taxon');
        $this->db->where('TaxonID', $taxonId);
        $query = $this->db->get();
        if ($query->num_rows()) {
            return $query->row();
        }
        else {
            return false;
        }
    }
    
    public function getAgent($agentId)
    {
        $this->db->select('FirstName, LastName');
        $this->db->from('agent');
        $this->db->where('AgentID', $agentId);
        $query = $this->db->get();
        if ($query->num_rows()) {
            return $query->row();
        }
        else {
            return false;
        }
    }
}
