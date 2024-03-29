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
 * Description of Autocomplete_model
 *
 * @author nklazenga
 */
class Autocomplete_model extends CI_Model 
{
    
    public function __construct() {
        parent::__construct();
    }
    
    public function taxonNameAutocomplete($term) 
    {
        $this->db->select("TaxonID as value, CONCAT_WS(' ', IF(FullName LIKE '%[ %', SUBSTRING(FullName, 1, LOCATE(' [', FullName)-1), FullName), Author) as label", false);
        $this->db->from('taxon');
        $this->db->like('FullName', $term, 'after');
        $this->db->order_by('label');
        $query = $this->db->get();
        return $query->result();
    }
    
    public function agentAutocomplete($term)
    {
        $this->db->select("AgentID as `value`, concat_ws(', ', LastName, FirstName) as label", false);
        $this->db->from('agent');
        $this->db->where("concat_ws(', ', LastName, FirstName) LIKE '$term%'", false, false);
        $this->db->order_by('label');
        $query = $this->db->get();
        return $query->result();
    }
}
