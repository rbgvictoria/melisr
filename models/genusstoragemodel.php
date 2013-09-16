<?php
/**
 * @property CI_Loader $load
 * @property CI_Form_validation $form_validation
 * @property CI_Input $input
 * @property CI_Email $email
 * @property CI_DB_active_record $db
 * @property CI_DB_forge $dbforge
 */

class GenusStorageModel extends Model {

    function  __construct() {
        parent::Model();

        // connect to database
        $this->load->database();
    }

    function getTaxa() {
        $name = array();
        $taxonid = array();

        $this->db->select('t.TaxonID, t.Name, t.NodeNumber, t.HighestChildNodeNumber', false);
        $this->db->from('taxon t');
        $this->db->join('genusstorage gs', 't.TaxonID=gs.TaxonID', 'left');
        $this->db->where('gs.GenusStorageID');
        $this->db->where('t.TaxonTreeDefItemID', 12);
        $query = $this->db->get();
        
        if ($query->num_rows() > 0) {
            foreach ($query->result() as $row) {
                $num_query = $this->db->query("SELECT d.DeterminationID
                FROM taxon t
                JOIN determination d ON t.TaxonID=d.TaxonID
                WHERE t.NodeNumber>=$row->NodeNumber AND t.NodeNumber<=$row->HighestChildNodeNumber");
                if ($num_query->num_rows() > 0) {
                    foreach ($num_query->result() as $num_row) {
                        $this->db->select('count(*) AS num', FALSE);
                        $this->db->from('determination');
                        $this->db->where('DeterminationID', $num_row->DeterminationID);
                        $this->db->where('(IsCurrent=1 OR YesNo1=1)', FALSE, FALSE);
                        $ch_query = $this->db->get();
                        $ch_row = $ch_query->row();
                        if ($ch_row->num) {
                            $name[] = $row->Name;
                            $taxonid[] = $row->TaxonID;
                        }
                    }
                }
            }
        }
       
        $this->db->select('t.TaxonID, t.Name', false);
        $this->db->from('taxon t');
        $this->db->join('taxontreedefitem td', 't.TaxonTreeDefItemID=td.TaxonTreeDefItemID');
        $this->db->join('determination d', 't.TaxonID=d.TaxonID');
        $this->db->join('genusstorage gs', 't.TaxonID=gs.TaxonID', 'left');
        $this->db->where('gs.GenusStorageID');
        $this->db->where('td.RankID <', 180);
        $this->db->where('d.IsCurrent', 1);
        $this->db->group_by('t.TaxonID');
        $query = $this->db->get();

        if ($query->num_rows() > 0) {
            foreach ($query->result() as $row) {
                $name[] = $row->Name;
                $taxonid[] = $row->TaxonID;
            }
        }
        
        if ($name) {
            array_multisort($name, SORT_ASC, $taxonid, SORT_ASC);

            $ret = array();
            for ($i = 0; $i < count($name); $i++) {
                $ret[$i] = array('Name' => $name[$i],
                    'TaxonID' => $taxonid[$i]);
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
}

?>
