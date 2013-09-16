<?php

class BorrowerModel extends Model {
    
    public function __construct() {
        parent::Model();
        $this->load->database();
    }
    
    public function getNonMelLoanNumbers() {
        $this->db->select('l.LoanID, l.LoanNumber, l.SrcTaxonomy');
        $this->db->from('loan l');
        $this->db->where('l.DisciplineID', 32768);
        $this->db->order_by('LoanNumber', 'desc');
        $query = $this->db->get();
        $ret = array();
        $ret[0] = '(select a non-MEL loan)';
        foreach ($query->result() as $row)
            $ret[$row->LoanID] = $row->LoanNumber . ': ' . $row->SrcTaxonomy;
        return $ret;
    }
    
    public function getPrepTypes() {
        $this->db->select('PrepTypeID, Name');
        $this->db->from('preptype');
        $this->db->where('CollectionID', 32769);
        $this->db->order_by('PrepTypeID');
        $query = $this->db->get();
        $ret = array();
        foreach ($query->result() as $row)
            $ret[$row->PrepTypeID] = $row->Name;
        return $ret;
    }
    
    public function getLoanSummary($loanid) {
        $ret = array();
        $qty = array();
        $this->db->select('DateReceived, Number1');
        $this->db->from('loan');
        $this->db->where('LoanID', $loanid);
        $query = $this->db->get();
        $row = $query->row();
        $ret[] = $row->DateReceived . ': ' . (integer) $row->Number1 . ' specimens received';
        $qty[] = $row->Number1;
        
        $this->db->select('ShipmentDate, Number1');
        $this->db->from('shipment');
        $this->db->where('LoanID', $loanid);
        $this->db->order_by('ShipmentDate', 'asc');
        $query = $this->db->get();
        if ($query->num_rows()) {
            foreach ($query->result() as $row) {
                $ret[] = $row->ShipmentDate . ': ' . (integer) $row->Number1 . ' specimens returned';
                $qty[] = $row->Number1;
            }
        }
        
        $qty_outstanding = array_shift($qty);
        if ($qty)
            $qty_outstanding -= array_sum($qty);
        $ret[] = date('Y-m-d') . ': ' . (integer) $qty_outstanding . ' specimens outstanding';
        return $ret;
    }
    
    public function getTaxa($loanid) {
        $this->db->select('ReceivedComments');
        $this->db->from('loan');
        $this->db->where('LoanID', $loanid);
        $query = $this->db->get();
        $row = $query->row();
        return $row->ReceivedComments;
    }
    
    public function getBotanist($loanid) {
        $this->db->select('a.LastName, a.MiddleInitial, a.FirstName, la.Role');
        $this->db->from('loanagent la');
        $this->db->join('agent a', 'la.AgentID=a.AgentID');
        $this->db->where('la.LoanID', $loanid);
        $this->db->where_in('la.Role', array('botanist', 'student'));
        $this->db->order_by('Role');
        $this->db->order_by('LastName');
        $query = $this->db->get();
        if ($query->num_rows()) {
            $ret = array();
            foreach ($query->result() as $row) {
                $str = '';
                if ($row->MiddleInitial)
                    $str .= $row->MiddleInitial . ' ';
                elseif ($row->FirstName)
                    $str .= $row->FirstName . ' ';
                $str .= $row->LastName . ' (' . $row->Role . ')';
                $ret[] = $str;
            }
            return implode('; ', $ret);
        }
    }
    
    public function getLoanPreparations($loanid) {
        $this->db->select('lp.LoanPreparationID, co.CatalogNumber, pt.Name AS PrepType, lp.Quantity, lp.QuantityReturned, lrp.ReturnedDate AS DateReturned, 
            IF(t.FullName IS NOT NULL, t.FullName, d.AlternateName) AS TaxonName, lp.OutComments', FALSE);
        $this->db->from('loanpreparation lp');
        $this->db->join('preparation p', 'lp.PreparationID=p.PreparationID');
        $this->db->join('preptype pt', 'p.PrepTypeID=pt.PrepTypeID');
        $this->db->join('collectionobject co', 'p.CollectionObjectID=co.CollectionObjectID');
        $this->db->join('determination d', 'co.CollectionObjectID=d.CollectionObjectID', 'left');
        $this->db->join('taxon t', 'd.TaxonID=t.TaxonID', 'left');
        $this->db->join('loanreturnpreparation lrp', 'lp.LoanPreparationID=lrp.LoanpreparationID', 'left');
        $this->db->where('lp.LoanID', $loanid);
        $query = $this->db->get();
        if ($query->num_rows())
            return $query->result_array();
        else
            return FALSE;
    }
    
    public function addLoanPreps($melrefno, $specifyuser, $preptype, $barcodes) {
        $catalognumbers = explode("\n", trim($barcodes));
        foreach ($catalognumbers as $catalognumber) {
            $collectionobjectid = $this->addCollectionObjectRecord($catalognumber, $specifyuser);
            $preparationid = $this->addPreparationRecord($collectionobjectid, $preptype, $specifyuser);
            $this->addLoanPreparationRecord($melrefno, $preparationid, $specifyuser);
            
            $this->db->select('l.Number1 AS QtyBorrowed, l.Version, SUM(lp.Quantity) AS NumPreps', FALSE);
            $this->db->from('loan l');
            $this->db->join('loanpreparation lp', 'l.LoanID=lp.LoanID');
            $this->db->where('l.LoanID', $melrefno);
            $this->db->group_by('l.LoanID');
            $query = $this->db->get();
            if ($query->num_rows()) {
                $row = $query->row();
                if ($row->NumPreps > $row->QtyBorrowed) {
                    $updateArray = array(
                        'TimestampModified' => date('Y-m-d H:i:s'),
                        'ModifiedByAgentID' => $specifyuser,
                        'Version' => $row->Version + 1,
                        'Number1' => $row->NumPreps
                    );
                    $this->db->where('LoanID', $melrefno);
                    $this->db->update('loan', $updateArray);
                }
            }
        }
    }
    
    private function addCollectionObjectRecord($catalognumber, $specifyuser) {
        $collectionobjectid = NULL;
        $this->db->select('CollectionObjectID');
        $this->db->from('collectionobject');
        $this->db->where('CollectionID', 32769);
        $this->db->where('CatalogNumber', $catalognumber);
        $query = $this->db->get();
        if ($query->num_rows()) {
            $row = $query->row();
            $collectionobjectid = $row->CollectionObjectID;
        }
        else {
            $this->db->select('MAX(CollectionObjectID) AS maxid', FALSE);
            $this->db->from('collectionobject');
            $query = $this->db->get();
            $row = $query->row();
            $collectionobjectid = $row->maxid+1;
            
            $insertArray = array(
                'CollectionObjectID' => $collectionobjectid,
                'TimestampCreated' => date('Y-m-d H:i:s'),
                'Version' => 0,
                'CollectionMemberID' => 32769,
                'CollectionID' => 32769,
                'CatalogNumber' => $catalognumber,
                'CreatedByAgentID' => $specifyuser,
            );
            
            $this->db->insert('collectionobject', $insertArray);
        }
        return $collectionobjectid;
    }
    
    private function addPreparationRecord($collectionobjectid, $preptypeid, $specifyuser) {
        $preparationid = NULL;
        $this->db->select('PreparationID');
        $this->db->from('preparation');
        $this->db->where('CollectionMemberID', 32769);
        $this->db->where('CollectionObjectID', $collectionobjectid);
        $this->db->where('PrepTypeID', $preptypeid);
        $query = $this->db->get();
        if ($query->num_rows()) {
            $row = $query->row();
            $preparationid = $row->PreparationID;
        }
        else {
            $this->db->select('MAX(PreparationID) AS maxid', FALSE);
            $this->db->from('preparation');
            $query = $this->db->get();
            $row = $query->row();
            $preparationid = $row->maxid+1;
            
            $insertArray = array(
                'PreparationID' => $preparationid,
                'TimestampCreated' => date('Y-m-d H:i:s'),
                'Version' => 0,
                'CountAmt' => 1,
                'CollectionMemberID' => 32769,
                'CollectionObjectID' => $collectionobjectid,
                'PrepTypeID' => $preptypeid,
                'CreatedByAgentID' => $specifyuser,
            );
            
            $this->db->insert('preparation', $insertArray);
        }
        return $preparationid;
    }

    private function addLoanPreparationRecord($loanid, $preparationid, $specifyuser) {
        $this->db->select('LoanPreparationID');
        $this->db->from('loanpreparation');
        $this->db->where('DisciplineID', 32768);
        $this->db->where('LoanID', $loanid);
        $this->db->where('PreparationID', $preparationid);
        $query = $this->db->get();
        if ($query->num_rows()) {
            return FALSE;
        }
        else {
            $this->db->select('MAX(LoanPreparationID) AS maxid', FALSE);
            $this->db->from('loanpreparation');
            $query = $this->db->get();
            $row = $query->row();
            $loanpreparationid = $row->maxid + 1;
            
            $insertArray = array(
                'LoanPreparationID' => $loanpreparationid,
                'TimestampCreated' => date('Y-m-d H:i:s'),
                'Version' => 0,
                'Quantity' => 1,
                'QuantityResolved' => 0,
                'QuantityReturned' => 0,
                'PreparationID' => $preparationid,
                'LoanID' => $loanid,
                'CreatedByAgentID' => $specifyuser,
                'DisciplineID' => 32768,
            );
            
            $this->db->insert('loanpreparation', $insertArray);
        }
    }
    
    public function findLoanPrepsToReturn($loanid, $catalognumbers) {
        $ret = array();
        $catalognumbers = explode("\n", trim($catalognumbers));
        $this->db->select('lp.LoanPreparationID');
        $this->db->from('collectionobject co');
        $this->db->join('preparation p', 'co.CollectionObjectID=p.CollectionObjectID');
        $this->db->join('loanpreparation lp', 'p.PreparationID=lp.PreparationID');
        $this->db->where('lp.LoanID', $loanid);
        $this->db->where_in('co.CatalogNumber', $catalognumbers);
        $query = $this->db->get();
        if ($query->num_rows()) {
            foreach ($query->result() as $row)
                $ret[] = $row->LoanPreparationID;
        }
        return $ret;
    }
    
    public function returnLoanPreps($loanid, $loanpreps, $specifyuser, $returndate, $remarks, $returncomment=NULL) {
        $this->db->select('MAX(LoanReturnPreparationID) AS maxid', FALSE);
        $this->db->from('loanreturnpreparation');
        $query = $this->db->get();
        $row = $query->row();
        $loanreturnpreparationid = $row->maxid + 1;
        
        foreach ($loanpreps as $index=>$loanpreparationid) {
            // Insert new loan return preparation record
            $insertArray = array(
                'LoanPreparationID' => $loanreturnpreparationid,
                'TimestampCreated' => date('Y-m-d H:i:s'),
                'Version' => 0,
                'QuantityResolved' => 1,
                'QuantityReturned' => 1,
                'ReturnedDate' => $returndate,
                'DisciplineID' => 32768,
                'LoanPreparationID' => $loanpreparationid,
                'CreatedByAgentID' => $specifyuser,
            );
            $this->db->insert('loanreturnpreparation', $insertArray);
            $loanreturnpreparationid++;
            
            // Update loan preparation record
            $this->db->select('Version');
            $this->db->from('loanpreparation');
            $this->db->where('LoanPreparationID', $loanpreparationid);
            $query = $this->db->get();
            $row = $query->row();
            $version = $row->Version;
            
            $updateArray = array(
                'TimestampModified' => date('Y-m-d H:i:s'),
                'Version' => $version++,
                'IsResolved' => 1,
                'QuantityReturned' => 1,
                'QuantityResolved' => 1,
                'OutComments' => $remarks[$index],
                'ModifiedByAgentID' => $specifyuser,
            );
            $this->db->where('LoanPreparationID', $loanpreparationid);
            $this->db->update('loanpreparation', $updateArray);
            
        }
        
        /*
         SELECT ShipmentID
FROM shipment
WHERE LoanID=2022
AND ShipmentDate='2013-05-02'; 
         */
        
        $this->db->select('ShipmentID, Number1, Version');
        $this->db->from('shipment');
        $this->db->where('LoanID', $loanid);
        $this->db->where('ShipmentDate', $returndate);
        $query = $this->db->get();
        if ($query->num_rows()) {
            $row = $query->row();
            // Update shipment record
            $updateArray = array(
                'TimestampModified' => date('Y-m-d H:i:s'),
                'Version' => $row->Version + 1,
                'Number1' => $row->Number1 + count($loanpreps),
                'ShippedByID' => $specifyuser,
                'ModifiedByAgentID' => $specifyuser
            );
            $this->db->where('ShipmentID', $row->ShipmentID);
            $this->db->update('shipment', $updateArray);
        }
        else {
            // Insert new shipment record
            $this->db->select('MAX(ShipmentID) AS maxid, MAX(ShipmentNumber) AS maxnumber', FALSE);
            $this->db->from('shipment');
            $query = $this->db->get();
            $row = $query->row();
            $shipmentid = $row->maxid + 1;
            $shipmentnumber = str_pad((integer) $row->maxnumber +1, 5, '0', STR_PAD_LEFT);

            $this->db->select('AgentID');
            $this->db->from('loanagent');
            $this->db->where('LoanID', $loanid);
            $this->db->where('Role', 'Lending institution');
            $query = $this->db->get();
            $row = $query->row();
            $shippedtoid = $row->AgentID;

            $insertArray = array(
                'ShipmentID' => $shipmentid,
                'TimestampCreated' => date('Y-m-d H:i:s'),
                'Version' => 0,
                'Number1' => count($loanpreps),
                'ShipmentDate' => $returndate,
                'ShipmentNumber' => $shipmentnumber,
                'LoanID' => $loanid,
                'DisciplineID' => 32768,
                'ShippedByID' => $specifyuser,
                'ShippedToID' => $shippedtoid,
                'CreatedByAgentID' => $specifyuser,
            );
            $this->db->insert('shipment', $insertArray);
        }
        
        //
        /*
         SELECT l.Number1 AS QtyBorrowed, SUM(s.Number1) AS QtyReturned
FROM loan l
JOIN shipment s ON l.LoanID=s.LoanID
WHERE l.LoanID=2022
GROUP BY l.LoanID;
         */
        
        $this->db->select('l.Number1 AS QtyBorrowed, SUM(s.Number1) AS QtyReturned', FALSE);
        $this->db->from('loan l');
        $this->db->join('shipment s', 'l.LoanID=s.LoanID');
        $this->db->where('l.LoanID', $loanid);
        $this->db->group_by('l.LoanID');
        $query = $this->db->get();
        $row = $query->row();
        
        $this->db->where('LoanID', $loanid);
        $this->db->update('loan', array('Number2' => $row->QtyBorrowed-$row->QtyReturned));
    }
}

?>
