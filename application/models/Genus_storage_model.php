<?php
/**
 * @property CI_Loader $load
 * @property CI_Form_validation $form_validation
 * @property CI_Input $input
 * @property CI_Email $email
 * @property CI_DB_active_record $db
 * @property CI_DB_forge $dbforge
 */

class Genus_storage_model extends CI_Model {

    function  __construct() {
        parent::__construct();

        // connect to database
        $this->load->database();
    }
    
    function getTaxa() {
        $name = array();
        $taxonid = array();

        $this->db->select("t.TaxonID, t.Name, t.NodeNumber, t.HighestChildNodeNumber,
            IF(a.MiddleInitial IS NOT NULL, CONCAT(a.MiddleInitial, ' ', a.LastName), CONCAT(a.FirstName, ' ', a.LastName)) AS CreatedBy", false);
        $this->db->from('taxon t');
        $this->db->join('genusstorage gs', 't.TaxonID=gs.TaxonID', 'left');
        $this->db->join('agent a', 't.CreatedByAgentID=a.AgentID');
        //$this->db->join('determination d', 't.TaxonID=d.TaxonID');
        //$this->db->where('d.CollectionMemberID', 4);
        $this->db->where('gs.GenusStorageID');
        $this->db->where('t.TaxonTreeDefItemID', 12);
        $query = $this->db->get();
        
        if ($query->num_rows() > 0) {
            foreach ($query->result() as $row) {
                $num_query = $this->db->query("SELECT d.DeterminationID
                FROM taxon t
                JOIN determination d ON t.TaxonID=d.TaxonID
                WHERE t.NodeNumber>=$row->NodeNumber AND t.NodeNumber<=$row->HighestChildNodeNumber
                  AND (d.IsCurrent=1 OR d.YesNo1=1)");
                if ($num_query->num_rows() > 0) {
                    $name[] = $row->Name;
                    $taxonid[] = $row->TaxonID;
                    $createdby[] = $row->CreatedBy;
                }
            }
        }
       
        $this->db->select("t.TaxonID, t.Name,
            IF(a.MiddleInitial IS NOT NULL, CONCAT(a.MiddleInitial, ' ', a.LastName), CONCAT(a.FirstName, ' ', a.LastName)) AS CreatedBy", false);
        $this->db->from('taxon t');
        $this->db->join('taxontreedefitem td', 't.TaxonTreeDefItemID=td.TaxonTreeDefItemID');
        $this->db->join('determination d', 't.TaxonID=d.TaxonID');
        $this->db->join('genusstorage gs', 't.TaxonID=gs.TaxonID', 'left');
        $this->db->join('agent a', 't.CreatedByAgentID=a.AgentID');
        $this->db->where('gs.GenusStorageID');
        $this->db->where('td.RankID <', 180);
        $this->db->where('d.IsCurrent', 1);
        $this->db->group_by('t.TaxonID');
        $query = $this->db->get();

        if ($query->num_rows() > 0) {
            foreach ($query->result() as $row) {
                $name[] = $row->Name;
                $taxonid[] = $row->TaxonID;
                $createdby[] = $row->CreatedBy;
            }
        }
        
        if ($name) {
            array_multisort($name, SORT_ASC, $taxonid, SORT_ASC, $createdby, SORT_ASC);

            $ret = array();
            for ($i = 0; $i < count($name); $i++) {
                $ret[$i] = array('Name' => $name[$i],
                    'TaxonID' => $taxonid[$i],
                    'CreatedBy' => $createdby[$i]);
            }
            return $ret;
        }
        else return FALSE;
    }

    function getName($t) {
        $this->db->select('Name');
        $this->db->from('taxon');
        $this->db->where('TaxonID', $t);
        $query = $this->db->get();
        $row = $query->row();
        return $row->Name;
    }

    function getClassification($t) {
        $this->db->select('NodeNumber');
        $this->db->from('taxon');
        $this->db->where('TaxonID', $t);
        $tquery = $this->db->get();
        $trow = $tquery->row();
        $node = $trow->NodeNumber;

        if ($node) {
            $this->db->select('td.Name AS Rank, t.Name', false);
            $this->db->from('taxon t');
            $this->db->join('taxontreedefitem td', 't.TaxonTreeDefItemID=td.TaxonTreeDefItemID');
            $this->db->where('t.NodeNumber <', $node);
            $this->db->where('t.HighestChildNodeNumber >=', $node);
            $query = $this->db->get();
            if ($query->num_rows() > 0) {
                $ret = array();
                foreach ($query->result() as $row) {
                    $ret[] = array('Rank' => $row->Rank,
                        'Name' => $row->Name);
                }
                return $ret;
            }
            else return FALSE;
        }
    }

    function getStorageDropDown() {
        $this->db->select('Name, StorageID');
        $this->db->from('storage');
        $this->db->where('ParentID', 2);
        $query = $this->db->get();
        $optgroups = array();
        $optgroups[] = '<option value="">(Select storage)</option>';
        foreach ($query->result() as $row) {
            $optgroups[] = "<optgroup label=\"$row->Name\">";
            $options = $this->getStorageSubgroups($row->StorageID);
            foreach ($options as $opt)
                $optgroups[] = $opt;
            $optgroups[] = '</optgroup>';
        }
        return implode("\n", $optgroups);
    }
    
    function getStorageSubgroups($parent) {
        $options = array();
        $this->db->select('Name, StorageID');
        $this->db->from('storage');
        $this->db->where('ParentID', $parent);
        $this->db->order_by('Name');
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            foreach ($query->result() as $row) {
                $options[] = "<option value=\"$row->StorageID\">$row->Name</option>";
            }
        }
        else {
            $this->db->select('Name, StorageID');
            $this->db->from('storage');
            $this->db->where('StorageID', $parent);
            $query = $this->db->get();
            $row = $query->row();
            $options[] = "<option value=\"$row->StorageID\">$row->Name</option>";
        }
        return $options;
    }

    function insertTaxon($t, $name, $storedunder) {
        $main = $storedunder;
        $type = $this->getTypeStorageID($storedunder);

        $timestamp = date("Y-m-d H:i:s");

        $insertarray = array(
            'TimestampCreated' => $timestamp,
            'TimestampModified' => $timestamp,
            'TaxonID' => $t,
            'Name' => $name,
            'StorageID' => $main,
            'StorageIDTypes' => $type
        );
        $this->db->insert('genusstorage', $insertarray);
    }

    function getTypeStorageID($storedunder) {
        $this->db->select('Name');
        $this->db->from('storage');
        $this->db->where('StorageID', $storedunder);
        $query = $this->db->get();
        $row = $query->row();
        $name = $row->Name;

        $this->db->select('NodeNumber, HighestChildNodeNumber');
        $this->db->from('storage');
        $this->db->where('StorageID', 429);
        $query = $this->db->get();
        $row = $query->row();
        $nodenumber = $row->NodeNumber;
        $highestchildnodenumber = $row->HighestChildNodeNumber;

        $this->db->select('StorageID');
        $this->db->from('storage');
        $this->db->where('Name', $name);
        $this->db->where('NodeNumber >', $nodenumber);
        $this->db->where('NodeNumber <=', $highestchildnodenumber);
        $query = $this->db->get();
        $row = $query->row();
        return $row->StorageID;
    }
    
    function getCollectionObjects($taxonid) {
        $ret = array();
        $this->db->select('RankID');
        $this->db->from('taxon');
        $this->db->where('TaxonID', $taxonid);
        $query = $this->db->get();
        $row = $query->row();
        if ($row->RankID == 180) {
            // Name of  genus, so we need to find subordinate taxa as well
            $this->db->select('NodeNumber, HighestChildNodeNumber, TaxonID, FullName');
            $this->db->from('taxon');
            $this->db->where('TaxonID', $taxonid);
            $query = $this->db->get();
            if ($query->num_rows()) {
                $node = $query->row();
                $nodenumber = $node->NodeNumber;
                $highestchildnodenumber = $node->HighestChildNodeNumber;
                
                $ret = array();
                $this->db->select("t.TaxonID, t.FullName, 'Main' AS StorageType, 
                    co.CatalogNumber, co.CollectionObjectID, DATE(co.TimestampModified) AS DateModified, 
                IF(a.AgentID IS NOT NULL, IF(a.MiddleInitial IS NOT NULL, CONCAT(a.MiddleInitial, ' ', a.LastName), CONCAT(a.FirstName, ' ', a.LastName)), 
                IF(ca.MiddleInitial IS NOT NULL, CONCAT(ca.MiddleInitial, ' ', ca.LastName), CONCAT(ca.FirstName, ' ', ca.LastName))) AS ModifiedBy", FALSE);
                $this->db->from('taxon t');
                $this->db->join('determination d', 't.TaxonID=d.TaxonID AND d.IsCurrent=1');
                $this->db->join('collectionobject co', 'd.CollectionobjectID=co.CollectionObjectID');
                $this->db->join('agent a', 'co.ModifiedByAgentID=a.AgentID', 'left');
                $this->db->join('agent ca', 'co.CreatedByAgentID=ca.AgentID', 'left');
                $this->db->where("(t.NodeNumber >= $nodenumber 
                        AND t.NodeNumber <= $highestchildnodenumber)", FALSE, FALSE);
                $query = $this->db->get();
                $ret = $query->result_array();
                
                $this->db->select("t.TaxonID, t.FullName, 'Type' AS StorageType, 
                    co.CatalogNumber, co.CollectionObjectID, DATE(co.TimestampModified) AS DateModified, 
                IF(a.AgentID IS NOT NULL, IF(a.MiddleInitial IS NOT NULL, CONCAT(a.MiddleInitial, ' ', a.LastName), CONCAT(a.FirstName, ' ', a.LastName)), 
                IF(ca.MiddleInitial IS NOT NULL, CONCAT(ca.MiddleInitial, ' ', ca.LastName), CONCAT(ca.FirstName, ' ', ca.LastName))) AS ModifiedBy", FALSE);
                $this->db->from('taxon t');
                $this->db->join('determination d', 't.TaxonID=d.TaxonID AND d.Yesno1=1');
                $this->db->join('collectionobject co', 'd.CollectionobjectID=co.CollectionObjectID');
                $this->db->join('agent a', 'co.ModifiedByAgentID=a.AgentID', 'left') ;
                $this->db->join('agent ca', 'co.CreatedByAgentID=ca.AgentID', 'left');
                $this->db->where("(t.NodeNumber >= $nodenumber 
                        AND t.NodeNumber <= $highestchildnodenumber)", FALSE, FALSE);
                $query = $this->db->get();
                $ret = array_merge($ret, $query->result_array());
            }
        }
        else {
            $ret = array();
            $this->db->select("t.TaxonID, t.FullName, 'Main' AS StorageType, 
                co.CatalogNumber, co.CollectionObjectID, DATE(co.TimestampModified) AS DateModified, 
                IF(a.AgentID IS NOT NULL, IF(a.MiddleInitial IS NOT NULL, CONCAT(a.MiddleInitial, ' ', a.LastName), CONCAT(a.FirstName, ' ', a.LastName)), 
                IF(ca.MiddleInitial IS NOT NULL, CONCAT(ca.MiddleInitial, ' ', ca.LastName), CONCAT(ca.FirstName, ' ', ca.LastName))) AS ModifiedBy", FALSE);
            $this->db->from('taxon t');
            $this->db->join('determination d', 't.TaxonID=d.TaxonID AND d.IsCurrent=1');
            $this->db->join('collectionobject co', 'd.CollectionobjectID=co.CollectionObjectID');
            $this->db->join('agent a', 'co.ModifiedByAgentID=a.AgentID', 'left') ;
            $this->db->join('agent ca', 'co.CreatedByAgentID=ca.AgentID', 'left');
            $this->db->where('t.TaxonID', $taxonid);
            $query = $this->db->get();
            $ret = $query->result_array();

            $this->db->select("t.TaxonID, t.FullName, 'Type' AS StorageType, 
                co.CatalogNumber, co.CollectionObjectID, DATE(co.TimestampModified) AS DateModified, 
                IF(a.AgentID IS NOT NULL, IF(a.MiddleInitial IS NOT NULL, CONCAT(a.MiddleInitial, ' ', a.LastName), CONCAT(a.FirstName, ' ', a.LastName)), 
                IF(ca.MiddleInitial IS NOT NULL, CONCAT(ca.MiddleInitial, ' ', ca.LastName), CONCAT(ca.FirstName, ' ', ca.LastName))) AS ModifiedBy", FALSE);
            $this->db->from('taxon t');
            $this->db->join('determination d', 't.TaxonID=d.TaxonID AND d.Yesno1=1');
            $this->db->join('collectionobject co', 'd.CollectionobjectID=co.CollectionObjectID');
            $this->db->join('agent a', 'co.ModifiedByAgentID=a.AgentID', 'left') ;
            $this->db->join('agent ca', 'co.CreatedByAgentID=ca.AgentID', 'left');
            $this->db->where('t.TaxonID', $taxonid);
            $query = $this->db->get();
            $ret = array_merge($ret, $query->result_array());
            
        }
        return $ret;
    }
    
    public function updateCollectionObjectStorage($colobjs, $storagetypes, $storedunder) {
        // Disable triggers
        $this->db->query('SET @DISABLE_TRIGGER=1');
        
        foreach ($colobjs as $index=>$obj) {
            $storageid = ($storagetypes[$index] == 'Type') ? $this->getTypeStorageID($storedunder) : $storedunder;
            
            $this->db->where('CollectionObjectID', $obj);
            $this->db->update('preparation', array('StorageID' => $storageid));
        }
        
        // Enable triggers again
        $this->db->query('SET @DISABLE_TRIGGER=NULL');
    }
}

?>
