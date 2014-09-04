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

class ExchangeModel extends TransactionModel {

    function  __construct() {
        parent::Model();

        // connect to database
        $this->load->database();
    }

    function getGiftNumbers() {
        $this->db->select('g.GiftID, g.GiftNumber, a.Abbreviation, count(gp.GiftPreparationID) AS numpreps');
        $this->db->from('gift g');
        $this->db->join('giftpreparation gp', 'g.GiftID=gp.GiftID', 'left');
        $this->db->join('shipment s', 'g.GiftID=s.GiftID');
        $this->db->join('agent a', 's.ShippedToID=a.AgentID');
        $this->db->group_by('g.GiftID');
        $this->db->order_by('GiftNumber', 'desc');
        $query = $this->db->get();
        if($query->num_rows() > 0) {
            $ret = array();
            foreach ($query->result() as $row) {
                $haspreps = ($row->numpreps > 0) ? 1 : 0;
                $ret[] = array(
                    'giftid' => $row->GiftID,
                    'giftnumber' => ($row->Abbreviation) ? $row->GiftNumber . ' &mdash; ' . $row->Abbreviation : $row->GiftNumber,
                    'haspreps' => $haspreps
                );
            }
            return $ret;
        } else return false;
    }

    function getInfo($giftid) {
        $this->db->select("g.GiftNumber, g.SrcGeography AS ExchangeType, g.SrcTaxonomy AS ExchangeFileName, s.ShippedToID, a.Abbreviation, a.LastName, DATE_FORMAT(s.ShipmentDate, '%e %M %Y') AS ShipmentDate,
            g.Remarks AS Description, s.ShipmentMethod, s.Text1,
            s.NumberOfPackages, s.ShippedByID", FALSE);
        $this->db->from('gift g');
        $this->db->join('shipment s', 'g.GiftID=s.GiftID');
        $this->db->join('agent a', 's.ShippedToID=a.AgentID');
        $this->db->where('g.GiftID', $giftid);
        
        $query = $this->db->get();
        if ($query->num_rows) {
            $row = $query->row();
            $giftinfo = array();
            $giftinfo['GiftNumber'] = trim($row->GiftNumber);
            $giftinfo['ExchangeType'] = strtolower($row->ExchangeType);
            $giftinfo['Acronym'] = $row->Abbreviation;
            $giftinfo['Institution'] = $row->LastName;
            $giftinfo['ShippedTo'] = $this->getAddress($row->ShippedToID);
            $giftinfo['Description'] = $row->Description;
            $giftinfo['ShipmentDate'] = $row->ShipmentDate;
            $giftinfo['ShipmentMethod'] = $this->getShipmentMethod($row->ShipmentMethod);
            $giftinfo['ShippedBy'] = $this->getAgent($row->ShippedByID);
            $giftinfo['TrackingLabels'] = $row->Text1;
            $giftinfo['NumberOfPackages'] = $row->NumberOfPackages;
            $giftinfo['LoanAgents'] = $this->getLoanAgents($giftid);
            $giftinfo['ExchangeFileName'] = $row->ExchangeFileName;
            return $giftinfo;
        }
        else return FALSE;
    }

    function getAddressLabelInfo($agentid) {
        $this->db->select('a.LastName');
        $this->db->from('agent a');
        $this->db->where('a.AgentID', $agentid);
        $query = $this->db->get();
        if ($query->num_rows()) {
            $row = $query->row();
            $addressinfo = array();
            $addressinfo['Institution'] = $row->LastName;
            $addressinfo['ShippedTo'] = $this->getAddress($agentid);
            return $addressinfo;
        }
        else
            return FALSE;
    }

    function getAgent($agentid) {
        $this->db->select('LastName, MiddleInitial');
        $this->db->from('agent');
        $this->db->where('AgentID', $agentid);
        $query = $this->db->get();
        if ($query->num_rows()) {
            $row = $query->row();
            return $row->MiddleInitial . ' ' . $row->LastName;
        }
        else
            return FALSE;
    }

    function getAddress($agentid) {
        $this->db->select('Address4, Address, Address2, Address3, RoomOrBuilding, City, State, PostalCode, Country');
        $this->db->from('address');
        $this->db->where('AgentID', $agentid);
        $this->db->where('IsCurrent', 1);
        $query = $this->db->get();
        if ($query->num_rows) {
            $row = $query->row();

            return array(
                'Attn' => $row->Address4,
                'Address' => $row->Address,
                'Address2' =>$row->Address2,
                'Address3' =>$row->Address3,
                'Address4' =>$row->RoomOrBuilding,
                'City' => $row->City,
                'State' => $row->State,
                'PostCode' => $row->PostalCode,
                'Country' => $row->Country
            );
        }
    }
    
    function getLoanAgents($giftid) {
        $this->db->select('ga.Role, a.Title, a.MiddleInitial, a.FirstName, a.LastName');
        $this->db->from('giftagent ga');
        $this->db->join('agent a', 'ga.AgentID=a.AgentID');
        $this->db->where('ga.GiftID', $giftid);
        $this->db->where('ga.Role', 1);
        $this->db->order_by('ga.Role');
        $query = $this->db->get();
        if ($query->num_rows) {
            $loanagents = array();
            foreach ($query->result() as $row) {
                $name = array();
                if ($row->MiddleInitial) $name[] = $row->MiddleInitial;
                elseif ($row->FirstName) $name[] = $row->FirstName;
                elseif ($row->Title) $name = $row->Title;
                $name[] = $row->LastName;
                $name = implode(' ', $name);
                
                $loanagents[] = array(
                    'Role' => $row->Role,
                    'Name' => $name
                );
            }
            return $loanagents;
        }
        else
            return FALSE;
    }
    
    function getPreparations($giftid) {
        $this->db->select('PrepTypeID, Name');
        $this->db->from('preptype');
        $this->db->where('IsLoanable', 1);
        $this->db->where_in('PreptypeID', array(15, 16, 17, 7));
        $query = $this->db->get();
        if ($query->num_rows) {
            $loan = array();
            foreach ($query->result() as $row) {
                $loanpreparations = $this->getLoanPreparationsByPrepType($giftid, $row->PrepTypeID);
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
    
    function getLoanPreparationsByPrepType($giftid, $preptype) {
        $this->db->select('co.CollectionObjectID, co.CatalogNumber, t.FullName, p.SampleNumber, p.CountAmt, p.Remarks');
        $this->db->from('giftpreparation g');
        $this->db->join('preparation p', 'g.PreparationID=p.PreparationID');
        $this->db->join('collectionobject co', 'p.CollectionObjectID=co.CollectionObjectID');
        $this->db->join('determination d', 'co.CollectionObjectID=d.CollectionObjectID');
        $this->db->join('taxon t', 'd.TaxonID=t.TaxonID');
        $this->db->where('g.GiftID', $giftid);
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
                    'Quantity' => $row->CountAmt,
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
                'TypeStatus' => ($row->TypeStatusName == 'Holotype') ? 'Isotype' : $row->TypeStatusName,
                'Basionym' => $row->FullName
            );
        }
        else
            return FALSE;
    }
    
    function getPreparationSummary($giftid) {
        $this->db->select('PrepTypeID, Name');
        $this->db->from('preptype');
        $this->db->where('IsLoanable', 1);
        $this->db->where_in('PrepTypeID', array(15, 16, 17, 7));
        $query = $this->db->get();
        if ($query->num_rows) {
            $loansummary = array();
            foreach ($query->result() as $row) {
                $quantity = $this->getNumberOfPreparations($giftid, $row->PrepTypeID);
                if ($quantity)    
                    $loansummary[$row->Name] = $quantity;
            }
            $types = $this->getNumberOfTypes($giftid);
            if ($types)
                $loansummary['Type'] = $types;
            return $loansummary;
        } 
        else 
            return FALSE;
    }
    
    function getNumberOfPreparations($giftid, $preptype) {
        $this->db->select('SUM(g.Quantity) AS Quantity');
        $this->db->from('giftpreparation g');
        $this->db->join('preparation p', 'g.PreparationID=p.PreparationID');
        $this->db->join('collectionobject co', 'p.CollectionObjectID=co.CollectionObjectID');
        $this->db->where('g.GiftID', $giftid);
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
    
    function getNumberOfTypes($giftid) {
        $this->db->select('COUNT(p.CountAmt) AS Quantity');
        $this->db->from('giftpreparation g');
        $this->db->join('preparation p', 'g.PreparationID=p.PreparationID');
        $this->db->join('collectionobject co', 'p.CollectionObjectID=co.CollectionObjectID');
        $this->db->join('determination d', 'co.CollectionObjectID=d.CollectionObjectID');
        $this->db->where('g.GiftID', $giftid);
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
        $this->db->where_in('p.PrepTypeID', array(15, 16, 17, 18));
        $query = $this->db->get();
        $row = $query->row();

        $delete = "DELETE FROM loanpreparation
            USING loanpreparation
            JOIN preparation ON loanpreparation.PreparationID=preparation.PreparationID
            WHERE preparation.PrepTypeID IN (15, 16, 17, 18)";
        if ($this->db->query($delete))
            return $row->Amount;
    }
    
    public function getCatalogNumbersExchange($giftid) {
        $ret = array();
        $this->db->select('co.CatalogNumber');
        $this->db->from('giftpreparation gp');
        $this->db->join('preparation p', 'gp.PreparationID=p.PreparationID');
        $this->db->join('collectionobject co', 'p.CollectionObjectID=co.collectionObjectID');
        $this->db->where('gp.GiftID', $giftid);
        $query = $this->db->get();
        
        if ($query->num_rows()) {
            foreach ($query->result() as $row)
                $ret[] = $row->CatalogNumber;
        }
        return $ret;
    }
    
    public function getCatalogNumbersRecordSet($recordsetid) {
        $ret = array();
        $this->db->select('co.CatalogNumber');
        $this->db->from('recordsetitem rsi');
        $this->db->join('collectionobject co', 'rsi.RecordID=co.collectionObjectID');
        $this->db->where('rsi.RecordSetID', $recordsetid);
        $query = $this->db->get();
        
        if ($query->num_rows()) {
            foreach ($query->result() as $row)
                $ret[] = $row->CatalogNumber;
        }
        return $ret;
    }
    

}



?>
