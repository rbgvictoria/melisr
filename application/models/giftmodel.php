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

class GiftModel extends TransactionModel {

    function  __construct() {
        parent::__construct();
    }
    
    public function getGifts($preps=FALSE, $type=FALSE, $institute=FALSE, $year=FALSE) {
        $this->db->select("g.GiftID, g.GiftNumber, g.GiftDate, g.DateReceived, 
            s.ShipmentDate, a.Abbreviation, IF(!isnull(a.FirstName), 
            CONCAT(a.LastName, ', ', a.FirstName), a.LastName) AS ShippedTo, 
            g.SrcGeography AS GiftType, SUM(gp.Quantity) AS Quantity", FALSE);
        $this->db->from('gift g');
        $this->db->join('shipment s', 'g.GiftID=s.GiftID');
        $this->db->join('agent a', 's.ShippedToID=a.AgentID');
        $this->db->join('giftpreparation gp', 'g.GiftID=gp.GiftID', 'left');
        $this->db->group_by('g.GiftID');
        $this->db->order_by('g.GiftNumber', 'desc');
        
        if ($type)
            $this->db->where('g.SrcGeography', $type);
        
        if ($institute)
            $this->db->where('s.ShippedToID', $institute);
        
        if ($year)
            $this->db->where('YEAR(s.ShipmentDate)', $year, FALSE);
        
        switch ($preps) {
            case 1:
                $this->db->where('!isnull(gp.GiftPreparationID)', FALSE, FALSE);
                break;
            case 2:
                $this->db->where('isnull(gp.GiftPreparationID)', FALSE, FALSE);
                break;
            default:
                break;
        }
        
        $query = $this->db->get();
        if ($query->num_rows()) {
            $ret = array();
            foreach ($query->result() as $row) {
                $ret[] = array(
                    'GiftID' => $row->GiftID,
                    'GiftNumber' => $row->GiftNumber,
                    'GiftType' => $row->GiftType,
                    'GiftDate' => $row->GiftDate,
                    'DateReceived' => $row->DateReceived,
                    'ShipmentDate' => $row->ShipmentDate,
                    'Abbreviation' => $row->Abbreviation,
                    'Quantity' => $row->Quantity,
                    'GiftAgent' => $this->getGiftAgent($row->GiftID),
                );
            }
            return $ret;
        }
        else
            return FALSE;
    }
    
    private function getGiftAgent($giftid) {
        $this->db->select("g.GiftNumber, ga.Role, GROUP_CONCAT(IF(!isnull(a.FirstName), 
            CONCAT(a.LastName, ', ', a.FirstName), a.LastName) SEPARATOR '; ') 
                AS GiftAgent", FALSE);
        $this->db->from('gift g');
        $this->db->join('giftagent ga', 'g.GiftID=ga.GiftID');
        $this->db->join('agent a', 'ga.AgentID=a.AgentID');
        $this->db->where('g.GiftID', $giftid);
        $this->db->group_by('g.GiftID');
        
        $query = $this->db->get();
        if ($query->num_rows()) {
            $row = $query->row();
            return $row->GiftAgent;
        }
        else
            return FALSE;
    }
    
    public function getGiftAgents($giftid) {
        $this->db->select("g.GiftID, ga.GiftAgentID, a.AgentID, IF(!isnull(a.FirstName), 
            CONCAT(a.LastName, ', ', a.FirstName), a.LastName) AS GiftAgent, ga.Role", FALSE);
        $this->db->from('gift g');
        $this->db->join('giftagent ga', 'g.GiftID=ga.GiftID');
        $this->db->join('agent a', 'ga.AgentID=a.AgentID');
        $this->db->where('g.GiftID', $giftid);
        $query = $this->db->get();
        if ($query->num_rows())
            return $query->result();
        else
            return FALSE;
    }
    
    public function getYears() {
        $this->db->select('DISTINCT YEAR(s.ShipmentDate) AS `Year`', FALSE);
        $this->db->from('gift g');
        $this->db->join('shipment s', 'g.GiftID=s.GiftID');
        $this->db->order_by('Year', 'desc');
        $query = $this->db->get();
        return $query->result_array();
    }
    
    public function getInstitutions() {
        $this->db->select('a.AgentID, a.LastName');
        $this->db->from('gift g');
        $this->db->join('shipment s', 'g.GiftID=s.GiftID');
        $this->db->join('agent a', 's.ShippedToID=a.AgentID');
        $this->db->group_by('a.AgentID');
        $this->db->order_by('a.Abbreviation');
        
        $query = $this->db->get();
        return $query->result();
    }
    
    public function getGiftTypes() {
        $this->db->select('Value, Title');
        $this->db->from('picklistitem');
        $this->db->where('PickListID', 54);
        $this->db->order_by('Value');
        $query = $this->db->get();
        return $query->result();
    }
    
    public function getGift($giftid) {
        $this->db->select('GiftID, GiftNumber, SrcGeography, Number1, SrcTaxonomy, 
            Remarks, DateReceived, ReceivedComments');
        $this->db->from('gift');
        $this->db->where('GiftID', $giftid);
        $query = $this->db->get();
        if ($query->num_rows())
            return $query->row();
        else
            return FALSE;
    }
    
    public function getGiftAgentRoles() {
        $this->db->select('Value, Title');
        $this->db->from('picklistitem');
        $this->db->where('PickListID', 55);
        $this->db->order_by('Value');
        $query = $this->db->get();
        return $query->result();
    }
    
    public function getShipments($giftid) {
        $this->db->select("s.ShipmentID, s.ShippedToID, 
            IF(!isnull(a1.FirstName), CONCAT(a1.LastName, ', ', a1.FirstName), a1.LastName) AS ShippedTo,
            s.ShipmentNumber, s.ShipmentDate, s.ShippedByID, 
            IF(!isnull(a2.FirstName), CONCAT(a2.LastName, ', ', a2.FirstName), a2.LastName) AS ShippedBy,
            s.ShipmentMethod, s.Text1 AS ReferenceNumber, s.NumberOfPackages, s.Weight, s.Text2 AS Postage,
            s.Remarks", FALSE);
        $this->db->from('shipment s');
        $this->db->join('agent a1', 's.ShippedToID=a1.AgentID');
        $this->db->join('agent a2', 's.ShippedByID=a2.AgentID');
        $this->db->where('s.GiftID', $giftid);
        $query = $this->db->get();
        if ($query->num_rows())
            return $query->result();
        else
            return FALSE;
    }
    
    public function getShipmentMethods() {
        $this->db->select('Value, Title');
        $this->db->from('picklistitem');
        $this->db->where('PickListID', 9);
        $this->db->order_by('Title');
        $query = $this->db->get();
        return $query->result();
    }
    
    public function getGiftPreparations($giftid) {
        $this->db->select('gp.GiftPreparationID, p.PreparationID, co.CollectionObjectID, co.CatalogNumber,
            pt.Name AS PrepType, p.CountAmt AS Quantity, pa.Text1 AS DuplicateString');
        $this->db->from('giftpreparation gp');
        $this->db->join('preparation p', 'gp.PreparationID=p.PreparationID');
        $this->db->join('preptype pt', 'p.PrepTypeID=pt.PrepTypeID');
        $this->db->join('preparationattribute pa', 'p.PreparationAttributeID=pa.PreparationAttributeID');
        $this->db->join('collectionobject co', 'p.CollectionObjectID=co.CollectionObjectID');
        $this->db->where('gp.giftID', $giftid);
        
        $query = $this->db->get();
        
        if ($query->num_rows()) {
            $giftpreps = array();
            foreach ($query->result() as $row) {
                $giftprep = new GiftPreparation();
                $giftprep->GiftPreparationID = $row->GiftPreparationID;
                $giftprep->PreparationID = $row->PreparationID;
                $giftprep->CatalogNumber = $row->CatalogNumber;
                $giftprep->PrepType = $row->PrepType;
                $giftprep->Quantity = $row->Quantity;
                $giftprep->DuplicateString = $row->DuplicateString;
                $giftprep->TaxonName = parent::getFormattedNameString($row->CollectionObjectID);
                
                if ($other = $this->getOtherGiftInfo($row->PreparationID)) {
                    $giftprep->QuantitySent = $other->QuantitySent;
                    $giftprep->DuplicatesSentTo = $other->DuplicatesSentTo;
                }
                
                $giftpreps[] = $giftprep;
            }
            return $giftpreps;
        }
        else
            return FALSE;
        
    }
    
    function getOtherGiftInfo($preparationid) {
        $this->db->select("count(*) AS QuantitySent, GROUP_CONCAT(a.Abbreviation ORDER BY a.Abbreviation SEPARATOR '; ') AS DuplicatesSentTo", FALSE);
        $this->db->from('giftpreparation gp');
        $this->db->join('gift g', 'gp.GiftID=g.GiftID');
        $this->db->join('shipment s', 'g.GiftID=s.GiftID');
        $this->db->join('agent a', 's.ShippedToID=a.AgentID');
        $this->db->where('gp.PreparationID', $preparationid);
        
        $query = $this->db->get();
        if ($query->num_rows())
            return $query->row();
        else
            return FALSE;
    }
    
    
    
}

class GiftPreparation {
    var $GiftPreparationID;
    var $PreparationID;
    var $CatalogNumber;
    var $PrepType;
    var $Quantity;
    var $DuplicateString;
    var $TaxonName;
    var $QuantitySent;
    var $DuplicatesSentTo;
}



?>
