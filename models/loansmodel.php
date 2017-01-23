<?php
/**
 * @property CI_Loader $load
 * @property CI_Form_validation $form_validation
 * @property CI_Input $input
 * @property CI_Email $email
 * @property CI_DB_active_record $db
 * @property CI_DB_forge $dbforge
 */
require_once('transactionmodel.php');

class LoansModel extends TransactionModel {

    function  __construct() {
        parent::__construct();

        // connect to database
        $this->load->database();
    }

    function getLoanNumbers() {
        $this->db->select('l.LoanID, l.LoanNumber, count(lp.LoanPreparationID) AS numpreps');
        $this->db->from('loan l');
        $this->db->join('loanpreparation lp', 'l.LoanID=lp.LoanID', 'left');
        $this->db->where('l.DisciplineID', 3);
        $this->db->group_by('l.LoanID');
        $this->db->order_by('LoanNumber', 'desc');
        $query = $this->db->get();
        if($query->num_rows() > 0) {
            $ret = array();
            foreach ($query->result() as $row) {
                $haspreps = ($row->numpreps > 0) ? 1 : 0;
                $ret[] = array(
                    'loanid' => $row->LoanID,
                    'loannumber' => $row->LoanNumber,
                    'haspreps' => $haspreps
                );
            }
            return $ret;
        } else return false;
    }
    
    function getLoanInfo($loanid) {
        $this->db->select("l.LoanNumber, s.ShippedToID, a.LastName, DATE_FORMAT(s.ShipmentDate, '%e %M %Y') AS ShipmentDate,
            l.Text1 AS Description, DATE_FORMAT(l.CurrentDueDate, '%e %M %Y') AS CurrentDueDate, s.ShipmentMethod, s.Text1, l.SpecialConditions,
            s.NumberOfPackages, s.ShippedByID", FALSE);
        $this->db->from('loan l');
        $this->db->join('shipment s', 'l.LoanID=s.LoanID');
        $this->db->join('agent a', 's.ShippedToID=a.AgentID');
        $this->db->where('l.LoanID', $loanid);
        $this->db->order_by('s.ShipmentDate', 'desc');
        $this->db->limit(1);
        
        
        
        $query = $this->db->get();
        if ($query->num_rows) {
            $row = $query->row();
            $loaninfo = array();
            $loaninfo['LoanNumber'] = trim($row->LoanNumber);
            $loaninfo['Institution'] = $row->LastName;
            $loaninfo['ShippedTo'] = $this->getAddress($row->ShippedToID);
            $loaninfo['Description'] = $row->Description;
            $loaninfo['CurrentDueDate'] = $row->CurrentDueDate;
            $loaninfo['SpecialConditions'] = $row->SpecialConditions;
            $loaninfo['ShipmentDate'] = $row->ShipmentDate;
            $loaninfo['ShipmentMethod'] = $this->getShipmentMethod($row->ShipmentMethod);
            $loaninfo['ShippedBy'] = $this->getAgent($row->ShippedByID);
            $loaninfo['TrackingLabels'] = $row->Text1;
            $loaninfo['NumberOfPackages'] = $row->NumberOfPackages;
            $loaninfo['LoanAgents'] = $this->getLoanAgents($loanid);
            return $loaninfo;
        }
        else 
            return FALSE;
    }

    /*function getAddress($agentid) {
    /*    $this->db->select('Address5, Address, Address2, Address3, Address4, City, State, PostalCode, Country');
        $this->db->from('address');
        $this->db->where('AgentID', $agentid);
        $query = $this->db->get();
        if ($query->num_rows) {
            $row = $query->row();

            return array(
                'Attn' => $row->Address5,
                'Address' => $row->Address,
                'Address2' =>$row->Address2,
                'Address3' =>$row->Address3,
                'Address4' =>$row->Address4,
                'City' => $row->City,
                'State' => $row->State,
                'PostCode' => $row->PostalCode,
                'Country' => $row->Country
            );
        }
    }*/
    
    function getLoanPreparations($loanid) {
        $this->db->select('PrepTypeID, Name');
        $this->db->from('preptype');
        $this->db->where('IsLoanable', 1);
        $this->db->where_not_in('PreptypeID', array(15, 16, 17, 18, 7));
        $query = $this->db->get();
        if ($query->num_rows) {
            $loan = array();
            foreach ($query->result() as $row) {
                $loanpreparations = $this->getLoanPreparationsByPrepType($loanid, $row->PrepTypeID);
                if ($loanpreparations) {
                    $loan[] = array(
                        'PrepType' => $row->Name,
                        'Preparations' => $loanpreparations
                    );
                }
            }
            return $loan;
        } 
        else 
            return FALSE;
    }
    
    function getLoanPreparationsByPrepType($loanid, $preptype) {
        $this->db->select('co.CollectionObjectID, co.CatalogNumber, t.FullName, p.SampleNumber, l.Quantity, p.Remarks');
        $this->db->from('loanpreparation l');
        $this->db->join('preparation p', 'l.PreparationID=p.PreparationID');
        $this->db->join('collectionobject co', 'p.CollectionObjectID=co.CollectionObjectID');
        $this->db->join('determination d', 'co.CollectionObjectID=d.CollectionObjectID');
        $this->db->join('taxon t', 'd.TaxonID=t.TaxonID');
        $this->db->where('l.LoanID', $loanid);
        $this->db->where('p.PrepTypeID', $preptype);
        $this->db->where("SUBSTRING(CatalogNumber, 8, 1)='A'", FALSE, FALSE);
        $this->db->where('d.Iscurrent', 1);
        $query = $this->db->get();
        if ($query->num_rows()) {
            $loanpreps = array();
            foreach ($query->result() as $row) {
                $type = $this->getTypeInfo($row->CollectionObjectID);
                if ($type) {
                    $taxonname = $type['Basionym'];
                    $typestatus = $type['TypeStatus'];
                }
                else {
                    $taxonname = $row->FullName;
                    $typestatus = FALSE;
                }
                $loanpreps[] = array(
                    'CatalogueNumber' => $row->CatalogNumber,
                    'SampleNumber' => $row->SampleNumber,
                    'Quantity' => $row->Quantity,
                    'TaxonName' => $taxonname,
                    'TypeStatus' => $typestatus,
                    'Multisheet' => $row->Remarks
                );
            }
            return $loanpreps;
            
        } 
        else
            return FALSE;
    }

    function getTypeInfo($colobj) {
        $this->db->select('d.TypeStatusName, t.FullName');
        $this->db->from('determination d');
        $this->db->join('taxon t', 'd.TaxonID=t.TaxonID');
        $this->db->where('d.CollectionObjectID', $colobj);
        $this->db->where('d.YesNo1', 1);
        $query = $this->db->get();
        if ($query->num_rows()) {
            $row = $query->row();
            return array(
                'TypeStatus' => $row->TypeStatusName,
                'Basionym' => $row->FullName
            );
        }
        else
            return FALSE;
    }
    
    function getLoanPreparationSummary($loanid) {
        $this->db->select('PrepTypeID, Name');
        $this->db->from('preptype');
        $this->db->where('IsLoanable', 1);
        $this->db->where_not_in('PrepTypeID', array(15, 16, 17, 18, 7));
        $query = $this->db->get();
        if ($query->num_rows) {
            $loansummary = array();
            foreach ($query->result() as $row) {
                $quantity = $this->getNumberOfPreparations($loanid, $row->PrepTypeID);  
                if ($quantity)    
                    $loansummary[$row->Name] = $quantity;
            }
            $types = $this->getNumberOfTypes($loanid);
            if ($types)
                $loansummary['Type'] = $types;
            return $loansummary;
        } 
        else 
            return FALSE;
    }
    
    function getNumberOfPreparations($loanid, $preptype) {
        $this->db->select('SUM(l.Quantity) AS Quantity');
        $this->db->from('loanpreparation l');
        $this->db->join('preparation p', 'l.PreparationID=p.PreparationID');
        $this->db->join('collectionobject co', 'p.CollectionObjectID=co.CollectionObjectID');
        $this->db->where('l.LoanID', $loanid);
        $this->db->where('p.PrepTypeID', $preptype);
        $this->db->where("SUBSTRING(co.CatalogNumber, 8, 1)='A'", FALSE, FALSE);
        $query = $this->db->get();
        if ($query->num_rows()) {
            $row = $query->row();
            return $row->Quantity;
        }
        else 
            return FALSE;
    }
    
    function getNumberOfTypes($loanid) {
        $this->db->select('SUM(l.Quantity) AS Quantity');
        $this->db->from('loanpreparation l');
        $this->db->join('preparation p', 'l.PreparationID=p.PreparationID');
        $this->db->join('collectionobject co', 'p.CollectionObjectID=co.CollectionObjectID');
        $this->db->join('determination d', 'co.CollectionObjectID=d.CollectionObjectID');
        $this->db->where('l.LoanID', $loanid);
        $this->db->where('d.YesNo1', 1);
        $this->db->where("SUBSTRING(co.CatalogNumber, 8, 1)='A'", FALSE, FALSE);
        $query = $this->db->get();
        $row = $query->row();
        return $row->Quantity;
    }

    function deleteDuplicates() {
        $this->db->select('count(lp.LoanPreparationID) AS Amount', false);
        $this->db->from('loanpreparation lp');
        $this->db->join('preparation p', 'lp.PreparationID=p.PreparationID');
        $this->db->where_in('p.PrepTypeID', array(15, 16, 17, 18, 7, 27, 24, 8));
        $query = $this->db->get();
        $row = $query->row();
        
        if ($row->Amount) {
            $delete = "DELETE FROM loanpreparation
                USING loanpreparation
                JOIN preparation ON loanpreparation.PreparationID=preparation.PreparationID
                WHERE preparation.PrepTypeID IN (15, 16, 17, 18, 7, 27, 24, 8, 10)";
            $this->db->query($delete);
        }    
        return $row->Amount;
    }
    
    function deleteNonDuplicateGiftPreparations() {
        $this->db->select('count(gp.GiftPreparationID) AS Amount', false);
        $this->db->from('giftpreparation gp');
        $this->db->join('preparation p', 'gp.PreparationID=p.PreparationID');
        $this->db->where_not_in('p.PrepTypeID', array(15, 16, 17, 18));
        $query = $this->db->get();
        $row = $query->row();
        
        if ($row->Amount) {
            $delete = "DELETE FROM giftpreparation
                USING giftpreparation
                JOIN preparation ON giftpreparation.PreparationID=preparation.PreparationID
                WHERE preparation.PrepTypeID NOT IN (15, 16, 17, 18)";
            $this->db->query($delete);
        }
        return $row->Amount;
    }
    
    function resetGiftPrepQuantity() {
        $this->db->select('COUNT(*) AS `Count`', FALSE);
        $this->db->from('giftpreparation');
        $this->db->where('Quantity >', 1);
        $query = $this->db->get();
        $row = $query->row();
        
        if ($row->Count) {
            $update = "UPDATE giftpreparation
                SET Quantity=1
                WHERE Quantity>1";
            $this->db->query($update);
        }
        return $row->Count; 
    }
    
    

}



?>
