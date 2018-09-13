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
 * Description of Mel_census_model
 *
 * @author Niels Klazenga <Niels.Klazenga@rbg.vic.gov.au>
 */
class Mel_census_model extends CI_Model {
    
    public function __construct() {
        parent::__construct();
    }
    
    public function getStorageGroups($parentId) 
    {
        $this->db->select('StorageID, Name, FullName');
        $this->db->from('storage');
        $this->db->where('ParentID', $parentId);
        $this->db->order_by('NodeNumber');
        $query = $this->db->get();
        return $query->result_array();
    }
    
    public function getTaxa($storageId)
    {
        $typeStorageId = $this->getTypeStorageID($storageId);
        $taxonIds = [];
        $taxonNames = [];
        $authors = [];
        $taxonIdsMel = [];
        $taxonIdsMelTypes = [];
        $taxonIdsCensus = [];
        $alternateNames = [];
        $melTaxa = $this->getMelTaxa($storageId);
        foreach ($melTaxa as $item) {
            $taxonIdsMel[] = $item->TaxonID;
            $taxonIds[] = $item->TaxonID;
            $taxonNames[] = $item->FullName;
            $authors[] = $item->Author;
        }
        $melTaxaTypes = $this->getMelTaxaTypes($typeStorageId);
        foreach ($melTaxaTypes as $item) {
            $taxonIdsMelTypes[] = $item->TaxonID;
            if (!in_array($item->TaxonID, $taxonIds)) {
                $taxonIds[] = $item->TaxonID;
                $taxonNames[] = $item->FullName;
                $authors[] = $item->Author;
            }
        }
        
        $censusTaxa = $this->getCensusTaxa($storageId, $typeStorageId);
        foreach ($censusTaxa as $item) {
            $taxonIdsCensus[] = $item->TaxonID;
            if (!in_array($item->TaxonID, $taxonIds) || !$item->TaxonID) {
                $taxonIds[] = $item->TaxonID;
                $taxonNames[] = $item->FullName;
                $authors[] = $item->Author;
                if (!$item->TaxonID) {
                    $alternateNames[] = $item->FullName;
                }
            }
        }
        array_multisort($taxonNames, $authors, $taxonIds);
        $ret = [];
        $ret['data'] = [];
        foreach ($taxonIds as $index => $value) {
            $item = [
                'TaxonID' => $value,
                'FullName' => $taxonNames[$index],
                'Author' => $authors[$index],
                'acceptedName' => null,
            ];
            $key = array_search($value, $taxonIdsMel);
            $keyTypes = array_search($value, $taxonIdsMelTypes);
            if ($key !== false || $keyTypes !== false) {
                $mel = [];
                $mel['AU'] = [];
                $mel['F'] = [];
                $mel['AU']['AT'] = ($keyTypes !== false) ? $melTaxaTypes[$keyTypes]->AT : 0;
                $mel['F']['FT'] = ($keyTypes !== false) ? $melTaxaTypes[$keyTypes]->FT : 0;
                $mel['AU']['AM'] = ($key !== false) ? $melTaxa[$key]->AM : 0;
                $mel['F']['FM'] = ($key !== false) ? $melTaxa[$key]->FM : 0;
                $mel['AU']['AC'] = ($key !== false) ? $melTaxa[$key]->AC : 0;
                $mel['F']['FC'] = ($key !== false) ? $melTaxa[$key]->FC : 0;
                $mel['AU']['APkt'] = ($key !== false) ? $melTaxa[$key]->APkt : 0;
                $mel['F']['FPkt'] = ($key !== false) ? $melTaxa[$key]->FPkt : 0;
                $mel['AU']['ASp'] = ($key !== false) ? $melTaxa[$key]->ASp : 0;
                $mel['F']['FSp'] = ($key !== false) ? $melTaxa[$key]->FSp : 0;
                $mel['AU']['ACult'] = ($key !== false) ? $melTaxa[$key]->ACult : 0;
                $mel['F']['FCult'] = ($key !== false) ? $melTaxa[$key]->FCult : 0;
                $mel['AU']['total'] = array_sum($mel['AU']);
                $mel['F']['total'] = array_sum($mel['F']);
                $item['melisr'] = $mel;
            }
            if ($value) {
                $key = array_search($value, $taxonIdsCensus);
            }
            else {
                $key = array_search($taxonNames[$index], $alternateNames);
            }
            if ($key !== false) {
                $item['acceptedName'] = $censusTaxa[$key]->acceptedName;
                $census = [];
                $census['AU'] = [];
                $census['F'] = [];
                $census['AU']['AT'] = $censusTaxa[$key]->AT;
                $census['AU']['AUnm'] = $censusTaxa[$key]->AU;
                $census['AU']['AM'] = $censusTaxa[$key]->AM;
                $census['AU']['AC'] = $censusTaxa[$key]->AC;
                $census['AU']['ACult'] = $censusTaxa[$key]->ACult;
                $census['F']['FT'] = $censusTaxa[$key]->FT;
                $census['F']['FUnm'] = $censusTaxa[$key]->FU;
                $census['F']['FM'] = $censusTaxa[$key]->FP;
                $census['F']['FM'] = $censusTaxa[$key]->FM;
                $census['F']['FC'] = $censusTaxa[$key]->FC;
                $census['F']['FCult'] = $censusTaxa[$key]->FCult;
                $census['SP'] = $censusTaxa[$key]->SP;
                $census['AU']['APkt'] = $censusTaxa[$key]->APkt;
                $census['AU']['total'] = array_sum($census['AU']);
                $census['F']['total'] = array_sum($census['F']);
                $item['census'] = $census;
            }
            $ret['data'][] = $item;
        }
        return $ret;
    }
    
    public function getMelTaxa($storageId) 
    {
        $sql = <<<EOT
SELECT t.TaxonID, t.FullName AS FullName, t.Author AS Author,
  count(DISTINCT if(pt.Name='Sheet' AND p.StorageID=$storageId AND g.NodeNumber>=3361 AND g.HighestChildNodeNumber<=3377, co.CollectionObjectID, NULL)) AS AM,
  count(DISTINCT if(pt.Name='Sheet' AND p.StorageID=$storageId AND (g.NodeNumber<3361 OR g.HighestChildNodeNumber>3377), co.CollectionObjectID, NULL)) AS FM,
  count(DISTINCT if(pt.Name='Carpological' AND p.StorageID=$storageId AND g.NodeNumber>=3361 AND g.HighestChildNodeNumber<=3377, co.CollectionObjectID, NULL)) AS AC,
  count(DISTINCT if(pt.Name='Carpological' AND p.StorageID=$storageId AND (g.NodeNumber<3361 OR g.HighestChildNodeNumber>3377), co.CollectionObjectID, NULL)) AS FC,
  count(DISTINCT if(pt.Name='Packet' AND p.StorageID=$storageId AND g.NodeNumber>=3361 AND g.HighestChildNodeNumber<=3377, co.CollectionObjectID, NULL)) AS APkt,
  count(DISTINCT if(pt.Name='Packet' AND p.StorageID=$storageId AND (g.NodeNumber<3361 OR g.HighestChildNodeNumber>3377), co.CollectionObjectID, NULL)) AS FPkt,
  count(DISTINCT if(pt.Name='Spirit' AND p.StorageID=$storageId AND g.NodeNumber>=3361 AND g.HighestChildNodeNumber<=3377, co.CollectionObjectID, NULL)) AS ASp,
  count(DISTINCT if(pt.Name='Spirit' AND p.StorageID=$storageId AND (g.NodeNumber<3361 OR g.HighestChildNodeNumber>3377), co.CollectionObjectID, NULL)) AS FSp,
  count(DISTINCT if(cea.Text13='Cultivated' AND p.StorageID=$storageId AND g.NodeNumber>=3361 AND g.HighestChildNodeNumber<=3377, co.CollectionObjectID, NULL)) AS ACult,
  count(DISTINCT if(cea.Text13='Cultivated' AND p.StorageID=$storageId AND (g.NodeNumber<3361 OR g.HighestChildNodeNumber>3377), co.CollectionObjectID, NULL)) AS FCult
FROM preparation p
JOIN preptype pt ON p.PrepTypeID=pt.PrepTypeID
JOIN collectionobject co ON p.CollectionObjectID=co.CollectionObjectID
JOIN determination d ON co.CollectionObjectID=d.CollectionObjectID
JOIN taxon t ON d.TaxonID=t.TaxonID
JOIN collectingevent ce ON co.CollectingEventID=ce.CollectingEventID
LEFT JOIN collectingeventattribute cea ON ce.CollectingEventAttributeID=cea.CollectingEventAttributeID
JOIN locality l ON ce.LocalityID=l.LocalityID
JOIN geography g ON l.GeographyID=g.GeographyID
WHERE co.CollectionID=4 AND d.IsCurrent=1 AND p.StorageID IN ($storageId)
GROUP BY t.TaxonID
ORDER BY t.FullName;
EOT;
        $query = $this->db->query($sql);
        return $query->result();
    }
    
    public function getMelTaxaTypes($typeStorageId) 
    {
        $sql = <<<EOT
SELECT t.TaxonID, t.FullName AS FullName, t.Author AS Author,
  count(DISTINCT if(p.StorageID=$typeStorageId AND g.NodeNumber>=3361 AND g.HighestChildNodeNumber<=3377, co.CollectionObjectID, NULL)) AS AT,
  count(DISTINCT if(p.StorageID=$typeStorageId AND (g.NodeNumber<3361 OR g.HighestChildNodeNumber>3377), co.CollectionObjectID, NULL)) AS FT
FROM preparation p
JOIN preptype pt ON p.PrepTypeID=pt.PrepTypeID
JOIN collectionobject co ON p.CollectionObjectID=co.CollectionObjectID
JOIN determination d ON co.CollectionObjectID=d.CollectionObjectID
JOIN taxon t ON d.TaxonID=t.TaxonID
JOIN collectingevent ce ON co.CollectingEventID=ce.CollectingEventID
LEFT JOIN collectingeventattribute cea ON ce.CollectingEventAttributeID=cea.CollectingEventAttributeID
JOIN locality l ON ce.LocalityID=l.LocalityID
JOIN geography g ON l.GeographyID=g.GeographyID
WHERE co.CollectionID=4 AND d.YesNo1=1 AND p.StorageID IN ($typeStorageId)
GROUP BY t.TaxonID
ORDER BY t.FullName;
EOT;
        $query = $this->db->query($sql);
        return $query->result();
    }
    
    public function getCensusTaxa($storageId, $typeStorageId)
    {
        $sql = <<<EOT
SELECT t.TaxonID, coalesce(t.FullName, d.AlternateName) AS FullName, t.Author AS Author, d.Text1 AS acceptedName,
  count(if(pt.Name='AT', 1, NULL)) AS AT,
  count(if(pt.Name='AU', 1, NULL)) AS AU,
  count(if(pt.Name='AP', 1, NULL)) AS AP,
  count(if(pt.Name='AM', 1, NULL)) AS AM,
  count(if(pt.Name='AC', 1, NULL)) AS AC,
  count(if(pt.Name='ACult', 1, NULL)) AS ACult,
  count(if(pt.Name='FT', 1, NULL)) AS FT,
  count(if(pt.Name='FU', 1, NULL)) AS FU,
  count(if(pt.Name='FP', 1, NULL)) AS FP,
  count(if(pt.Name='FM', 1, NULL)) AS FM,
  count(if(pt.Name='FC', 1, NULL)) AS FC,
  count(if(pt.Name='FCult', 1, NULL)) AS FCult,
  count(if(pt.Name='SP', 1, NULL)) AS SP,
  count(if(pt.Name='APkt', 1, NULL)) AS APkt
FROM preparation p
JOIN preptype pt ON p.PrepTypeID=pt.PrepTypeID
JOIN collectionobject co ON p.CollectionObjectID=co.CollectionObjectID
JOIN determination d ON co.CollectionObjectID=d.CollectionObjectID
LEFT JOIN taxon t ON d.TaxonID=t.TaxonID
WHERE co.CollectionID=229377 AND d.IsCurrent=1 AND p.StorageID IN ($storageId, $typeStorageId)
GROUP BY t.TaxonID, d.DeterminationID
ORDER BY FullName;   
EOT;
        $query = $this->db->query($sql);
        return $query->result();
    }
    
    private function getNodes($name)
    {
        $this->db->select('NodeNumber, HighestChildNodeNumber');
        $this->db->from('storage');
        $this->db->where('Name', $name);
        $query = $this->db->get();
        if ($query->num_rows()) {
            return $query->row();
        }
        else {
            return false;
        }
    }
    
    private function getTypeStorageID($storageID)
    {
        $typeNodes = $this->getNodes('Types');
        $this->db->select('ts.StorageID');
        $this->db->from('storage ts');
        $this->db->join('storage ms', 'ts.Name=ms.Name');
        $this->db->where('ts.NodeNumber >', $typeNodes->NodeNumber);
        $this->db->where('ts.NodeNumber <=', $typeNodes->HighestChildNodeNumber);
        $this->db->where('ms.StorageID', $storageID);
        $query = $this->db->get();
        if ($query->num_rows()) {
            $row = $query->row();
            return $row->StorageID;
        }
        else {
            return false;
        }
    }
    
    public function getStorageGroupSuggestions($term)
    {
        $ret = [];
        $node = $this->getNode();
        $this->db->select('StorageID, Name');
        $this->db->from('storage');
        $this->db->where('NodeNumber >', $node->NodeNumber);
        $this->db->where('HighestChildNodeNumber <=', $node->HighestChildNodeNumber);
        $this->db->like('Name', $term, 'both');
        $this->db->order_by('Name');
        $query = $this->db->get();
        if ($query->num_rows()) {
            return $query->result();
        }
        return $ret;
    }
    
    public function getTaxonSuggestions($term, $storageId)
    {
        $ret = [];
        if (strpos($term, ' ') === false) {
            $sql = "SELECT t.Name
                FROM taxon t
                JOIN genusstorage g ON t.FullName=g.Name OR substring(t.FullName, 1, locate(' ', t.FullName)-1)=g.Name
                WHERE g.StorageID=$storageId AND t.Name LIKE '$term%' AND t.FullName LIKE '$term%'
                GROUP BY t.Name";
        }
        else {
            $sql = "SELECT t.FullName AS Name
                FROM taxon t
                JOIN genusstorage g ON substring(t.FullName, 1, locate(' ', t.FullName)-1)=g.Name
                WHERE g.StorageID=$storageId AND t.FullName LIKE '$term%'
                GROUP BY t.FullName";
        }
        $query = $this->db->query($sql);
        if ($query->num_rows()) {
            foreach ($query->result() as $row) {
                $ret[] = $row->Name;
            }
        }
        return $ret;
    }
    
    public function getGenusSuggestions($term, $storageID)
    {
        $ret = [];
        $this->db->select('Name');
        $this->db->from('genusstorage');
        $this->db->where('StorageID', $storageID);
        $this->db->like('Name', $term, 'after');
        $this->db->group_by('Name');
        $query = $this->db->get();
        if ($query->num_rows()) {
            foreach ($query->result() as $row) {
                $ret[] = $row->Name;
            }
        }
        return $ret;
    }
    
    public function getInfraGenusSuggestions($term, $genus)
    {
        $ret = [];
        $sql = "SELECT t.FullName
            FROM taxon t
            JOIN determination d ON t.TaxonID=d.DeterminationID
            WHERE (d.IsCurrent=true OR d.YesNo1=true) AND d.CollectionMemberID=4
              AND t.FullName LIKE '$genus $term%'";
        $query = $this->db->query($sql);
        if ($query->num_rows()) {
            foreach ($query->result() as $row) {
                $ret[] = substr($row->FullName, strlen($genus) + 1);
            }
        }
        return $ret;
    }
    
    protected function getNode()
    {
        $this->db->select('NodeNumber, HighestChildNodeNumber');
        $this->db->from('storage');
        $this->db->where('StorageId', 2);
        $query = $this->db->get();
        return $query->row();
    }
    
}
