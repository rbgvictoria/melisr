<?php

/*
 * Copyright 2021 Royal Botanic Gardens Victoria.
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
 * Description of Exchange_Data_Model
 *
 * @author Niels.Klazenga <Niels.Klazenga at rbg.vic.gov.au>
 */
class Exchange_data_model extends CI_Model 
{
    function  __construct() {
        parent::__construct();

        // connect to database
        $this->load->database();
    }

    function getExchangeData($giftNumber)
    {
        $this->db->select('oc.*', false);
        $this->db->from('gift g');
        $this->db->join('giftpreparation gp', 'g.GiftID=gp.GiftID');
        $this->db->join('preparation p', 'gp.PreparationID=p.PreparationID');
        $this->db->join('mel_avh_occurrence_core oc', 'p.CollectionObjectID=oc.id');
        $this->db->where('g.GiftNumber', $giftNumber);
        $query = $this->db->get();
        return $query->result_array();
    }
    
    function getRecordSetData($recordSet)
    {
        $this->db->select('oc.*', false);
        $this->db->from('recordset rs');
        $this->db->join('recordsetitem rsi', 'rs.RecordSetID=rsi.RecordSetID');
        $this->db->join('mel_avh_occurrence_core oc', 'rsi.RecordID=oc.id');
        $this->db->where('rs.RecordSetID', $recordSet);
        $query = $this->db->get();
        return $query->result_array();
    }
}
