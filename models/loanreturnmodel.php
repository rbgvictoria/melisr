<?php
/**
 * @property CI_Loader $load
 * @property CI_Form_validation $form_validation
 * @property CI_Input $input
 * @property CI_Email $email
 * @property CI_DB_active_record $db
 * @property CI_DB_forge $dbforge
 */

class LoanReturnModel extends Model {

    function  __construct() {
        parent::Model();

        // connect to database
        $this->load->database();
    }
    
    function findLoan($melnumber) {
        /*
        SELECT l.LoanID, l.LoanNumber
        FROM collectionobject co
        JOIN preparation p ON 
        JOIN loanpreparation lp ON p.PreparationID=lp.PreparationID
        JOIN loan l ON 
        WHERE ='0105288';
        */
        $this->db->select('lp.LoanID');
        $this->db->from('collectionobject co');
        $this->db->join('preparation p', 'co.CollectionObjectID=p.CollectionobjectID');
        $this->db->join('loanpreparation lp', 'p.PreparationID=lp.PreparationID');
        $this->db->join('loan l', 'lp.LoanID=l.LoanID');
        $this->db->where("substring(co.CatalogNumber, 1, 7)='$melnumber'");
        $this->db->where('l.IsClosed !=', 1);
        
        $query = $this->db->get();
        if ($query->num_rows()) {
            $row = $query->row();
            return $row->LoanID;
        }
        else
            return FALSE;
        
    }
    
    function getLoanInfo($loanid) {
        $this->db->select('sum(lp.Quantity) AS Quantity, sum(lp.QuantityReturned) AS QuantityReturned', FALSE);
        $this->db->from('loan l');
        $this->db->join('loanpreparation lp', 'l.LoanID=lp.LoanID');
        $this->db->join('loanreturnpreparation lrp', 'lp.LoanPreparationID=lrp.LoanPreparationID', 'left');
        $this->db->join('preparation p', 'lp.PreparationID=p.PreparationID');
        $this->db->join('collectionobject co', 'p.CollectionObjectID=co.CollectionObjectID');
        $this->db->where('l.LoanID', $loanid);
        $this->db->where("SUBSTRING(co.CatalogNumber, 8)='A'", FALSE, FALSE);
        $this->db->group_by('l.LoanID');
        $query = $this->db->get();
        if ($query->num_rows()) {
            $row = $query->row();
            $ret['Quantity'] = $row->Quantity;
            $ret['QuantityReturned'] = $row->QuantityReturned;
            
            if ($row->QuantityReturned) {
                $this->db->select("DATE_FORMAT(lrp.ReturnedDate, '%e %b %Y') AS ReturnedDate, SUM(lrp.QuantityReturned) AS QuantityReturned, 
                    SUM(lrp.QuantityResolved) AS QuantityResolved", FALSE);
                $this->db->from('loanreturnpreparation lrp');
                $this->db->join('loanpreparation lp', 'lrp.LoanPreparationID=lp.LoanPreparationID');
                $this->db->join('preparation p', 'lp.PreparationID=p.PreparationID');
                $this->db->join('collectionobject co', 'p.CollectionObjectID=co.CollectionObjectID');
                $this->db->where('lp.LoanID', $loanid);
                $this->db->where("SUBSTRING(co.CatalogNumber, 8)='A'", FALSE, FALSE);
                $this->db->group_by('DATE(lrp.ReturnedDate)');
                $query = $this->db->get();
                if ($query->num_rows()) {
                    foreach ($query->result() as $row)
                        $ret['ReturnedBatches'][] = array(
                            'ReturnedDate' => $row->ReturnedDate,
                            'QuantityReturned' => $row->QuantityReturned
                        );
                }
            }
            
            return $ret;
        }
    }
    
    function getAllPreparationsInLoan($loanid, $type) {
        /*
        SELECT co.CatalogNumber, lp.LoanPreparationID, lp.Quantity, lrp.ReturnedDate, lrp.Remarks
        FROM collectionobject co
        JOIN 
        JOIN preptype pt ON p.PrepTypeID=pt.PrepTypeID
        JOIN loanpreparation lp ON p.PreparationID=lp.PreparationID
        LEFT JOIN loanreturnpreparation lrp ON lp.LoanPreparationID=lrp.LoanPreparationID
        WHERE lp.LoanID=413;
        */
        
        $this->db->select('co.CollectionObjectID, co.CatalogNumber, lp.LoanPreparationID, pt.Name AS PrepType, lp.QuantityReturned, lrp.ReturnedDate, lp.InComments AS Remarks');
        $this->db->from('collectionobject co');
        $this->db->join('preparation p', 'co.CollectionObjectID=p.CollectionObjectID');
        $this->db->join('preptype pt', 'p.PrepTypeID=pt.PrepTypeID');
        $this->db->join('loanpreparation lp', 'p.PreparationID=lp.PreparationID');
        $this->db->join('loanreturnpreparation lrp', 'lp.LoanPreparationID=lrp.LoanPreparationID', 'left');
        $this->db->where('lp.LoanID', $loanid);
        if ($type == 2)
            $this->db->where('lp.IsResolved', 0);
        $this->db->order_by('CatalogNumber');
        
        $query = $this->db->get();
        if ($query->num_rows()) {
            foreach ($query->result_array() as $row) {
                $row['TaxonName'] = $this->xml_convert($this->getFormattedNameString($row['CollectionObjectID']));
                array_shift($row);
                $ret[] = $row;
            }
            
            return $ret;
        }    
        else
            return FALSE;
    }
    
    function findLoanPreparations($loannumber, $melnumbers) {
        $this->db->select('co.CatalogNumber, co.CollectionObjectID, p.preparationID, 
            lp.LoanPreparationID, pt.Name AS PrepType, lp.Quantity');
        $this->db->from('collectionobject co');
        $this->db->join('preparation p', 'co.CollectionObjectID=p.CollectionObjectID');
        $this->db->join('preptype pt', 'p.PrepTypeID=pt.PrepTypeID');
        $this->db->join('loanpreparation lp', 'p.PreparationID=lp.PreparationID');
        $this->db->where('lp.LoanID', $loannumber);
        $this->db->where_in('SUBSTRING(co.CatalogNumber, 1, 7)', $melnumbers, FALSE);
        
        $query = $this->db->get();
        if ($query->num_rows()) {
            $ret = array();
            foreach ($query->result() as $row) {
                $loanprep = array();
                $loanprep['LoanPreparationID'] = $row->LoanPreparationID;
                $loanprep['CatalogNumber'] = $row->CatalogNumber;
                $loanprep['PrepType'] = $row->PrepType;
                $loanprep['Quantity'] = $row->Quantity;
                $loanprep['Remarks'] = FALSE;
                $loanprep['TaxonName'] = $this->xml_convert($this->getFormattedNameString($row->CollectionObjectID));
                $ret[] = $loanprep;
            }
            return $ret;
        }
        else
            return FALSE;
    }
    
    public function returnLoan($loanid, $loanpreps, $specifyuserid, $returndate, $quarantine, $transferto=FALSE) {
        $this->db->select('AgentID');
        $this->db->from('agent');
        $this->db->where('SpecifyUserID', $specifyuserid);
        $query = $this->db->get();
        $row = $query->row();
        $agentid = $row->AgentID;
        
        foreach ($loanpreps as $prep) {
            $this->db->select('IsResolved=1 AS IsResolved', FALSE);
            $this->db->from('loanpreparation');
            $this->db->where('LoanPreparationID', $prep['LoanPreparationID']);
            $query = $this->db->get();
            $row = $query->row();
            if (!$row->IsResolved){
                echo 'hello...' . "\n";
                $this->db->trans_start();
                $this->insertLoanReturnPreparation($prep['LoanPreparationID'], $prep['Quantity'], $quarantine, $returndate, $agentid);
                $this->updateLoanPreparation($prep['LoanPreparationID'], $prep['Quantity'], $agentid, $prep['Remarks'], $transferto);
                $this->db->trans_complete();
            }
            else {
                continue;
            }
            
            if ($transferto)
                $this->transferPreparation($prep['LoanPreparationID'], $prep['Quantity'], $agentid, $transferto);
        
        }
        
        $this->closeLoan($loanid, $returndate, $agentid);
        $this->updateLoanQuantity($loanid);
        
        if ($transferto)
            $this->updateLoanQuantity($transferto);
    }
    
    private function insertLoanReturnPreparation($loanpreparationid, $quantityreturned, $quarantine, $returndate, $agentid) {
        $date = date("Y-m-d H:i:s");
        
        $insertdata = array(
            'TimestampCreated' => $date,
            'TimestampModified' => $date,
            'Version' => 1,
            'QuantityResolved' => $quantityreturned,
            'QuantityReturned' => $quantityreturned,
            'Remarks' => $quarantine,
            'ReturnedDate' => $returndate,
            'ReceivedByID' => $agentid,
            'DisciplineID' => 3,
            'ModifiedByAgentID' => $agentid,
            'LoanPreparationID' => $loanpreparationid,
            'CreatedByAgentID' => $agentid,
        );
        
        $this->db->insert('loanreturnpreparation', $insertdata);
    }
    
    private function updateLoanPreparation($loanpreparationid, $quantityreturned, $agentid, $remarks, $transferred) {
        $this->db->select('Quantity, QuantityResolved, QuantityReturned');
        $this->db->from('loanpreparation');
        $this->db->where('LoanPreparationID', $loanpreparationid);
        $query = $this->db->get();
        $row = $query->row();
        $returned = $row->QuantityReturned+$quantityreturned;
        $resolved = $row->QuantityResolved+$quantityreturned;
        $isresolved = ($row->Quantity-$row->QuantityResolved-$quantityreturned) ? 0 : 1;
        
        $updatedata = array(
            'TimestampModified' => date("Y-m-d H:i:s"),
            'IsResolved' => $isresolved,
            'QuantityResolved' => $resolved,
            'QuantityReturned' => $returned,
            'InComments' => $remarks,
            'ModifiedByAgentID' => $agentid,
        );
        if ($transferred)
            $updatedata['DescriptionOfMaterial'] = 'Transferred';
        $this->db->where('LoanPreparationID', $loanpreparationid);
        $this->db->update('loanpreparation', $updatedata);
    }
    
    private function transferPreparation($loanpreparationid, $quantityreturned, $agentid, $transferto) {
        $this->db->select('PreparationID');
        $this->db->from('loanpreparation');
        $this->db->where('LoanPreparationID', $loanpreparationid);
        $query = $this->db->get();
        $row = $query->row();
        $timestamp = date("Y-m-d H:i:s");
        
        $transferArray = array(
            'TimestampCreated' => $timestamp,
            'TimestampModified' => $timestamp,
            'Version' => 1,
            'IsResolved' => 0,
            'Quantity' => $quantityreturned,
            'QuantityResolved' => 0,
            'QuantityReturned' => 0,
            'ModifiedByAgentID' => $agentid,
            'PreparationID' => $row->PreparationID,
            'LoanID' => $transferto,
            'CreatedByAgentID' => $agentid,
            'DisciplineID' => 3            
        );
        
        $this->db->insert('loanpreparation', $transferArray);
        
    }
    
    private function closeLoan($loanid, $returndate, $agentid) {
        $this->db->select('Version');
        $this->db->from('loan');
        $this->db->where('LoanID', $loanid);
        $query = $this->db->get(0);
        $row = $query->row();
        $version = $row->Version + 1;
        
        $this->db->select('count(*) AS QuantityUnresolved', FALSE);
        $this->db->from('loanpreparation');
        $this->db->where('LoanID', $loanid);
        $this->db->where('IsResolved', 0);
        $query = $this->db->get();
        $row = $query->row();
        $unresolved = $row->QuantityUnresolved;
        if ($unresolved)
            return FALSE;
        else {
            $updatedata = array(
                'TimestampModified' => date("Y-m-d H:i:s"),
                'Version' => $version,
                'DateClosed' => $returndate,
                'IsClosed' => 1,
                'SrcGeography' => 'Complete',
                'ModifiedByAgentID' => $agentid,
            );
            $this->db->where('LoanID', $loanid);
            $this->db->update('loan', $updatedata);
        }
        
    }
    
    private function updateLoanQuantity($loanid) {
        $this->db->select('sum(lp.Quantity)-sum(lp.QuantityReturned) AS QuantityOutstanding', FALSE);
        $this->db->from('loan l');
        $this->db->join('loanpreparation lp', 'l.LoanID=lp.LoanID');
        $this->db->join('loanreturnpreparation lrp', 'lrp.LoanPreparationID=lp.LoanPreparationID', 'left');
        $this->db->join('preparation p', 'lp.PreparationID=p.PreparationID');
        $this->db->join('collectionobject co', 'p.CollectionObjectID=co.CollectionObjectID');
        $this->db->where("SUBSTRING(co.CatalogNumber, 8)='A'", FALSE, FALSE);
        $this->db->where('l.LoanID', $loanid);
        
        $query = $this->db->get();
        $row = $query->row();
        $this->db->set('Number1', $row->QuantityOutstanding);
        $this->db->where('LoanID', $loanid);
        $this->db->update('loan');
    }
    
    public function getLoans($discipline=3, $filter=FALSE, $institution=FALSE, $year=FALSE) {
        $ret = array();
        if ($discipline == 32768) {
            $loannumber = 'l.SrcTaxonomy AS LoanNumber';
            $melrefno = 'l.LoanNumber AS MELRefNo';
            $quantity = 'CAST(l.Number1 AS unsigned) AS Quantity';
            $quantityresolved = 'CAST(l.Number1-l.Number2 AS unsigned) AS QuantityResolved';
        }
        else {
            $loannumber = 'l.LoanNumber';
            $melrefno = 'NULL AS MELRefNo';
            $quantity = 'SUM(lp.Quantity) AS Quantity';
            $quantityresolved = 'SUM(lp.QuantityResolved) AS QuantityResolved';
        }
        $this->db->select("l.LoanID, 
            $loannumber,
            $melrefno,
            $quantity,
            $quantityresolved,
            l.IsClosed=1 AS IsClosed,
            l.SrcGeography AS LoanStatus,
            l.DateClosed,
            l.CurrentDueDate", FALSE);
        $this->db->from('loan l');
        $this->db->join('loanpreparation lp', 'l.LoanID=lp.LoanID', 'left');
        $this->db->join('loanreturnpreparation lrp', 'lp.LoanPreparationID=lrp.LoanPreparationID', 'left');
        $this->db->where('l.DisciplineID', $discipline);
        $this->db->group_by('l.LoanID');

        if ($discipline == 32768)
            $this->db->order_by('MELRefNo', 'desc');
        else
            $this->db->order_by('LoanNumber', 'desc');
        
        if ($discipline == 32768) {
            switch ($filter) {
                case 1: // current loans
                    $this->db->where('l.SrcGeography', 'Current');
                    break;
                case 2: // closed loans
                    $this->db->where('l.SrcGeography', 'Complete');
                    break;
                case 3: // partially returned loans
                    $this->db->where('l.SrcGeography', 'Returning');
                    break;
                default:
                    break;
            }
            
        }
        else {
            switch ($filter) {
                case 1: // open loans
                    $this->db->where('l.IsClosed', 0);
                    break;
                case 2: // closed loans
                    $this->db->where('l.IsClosed', 1);
                    break;
                case 3: // partially returned loans
                    $this->db->where('l.IsClosed', 0);
                    $this->db->having('SUM(lrp.QuantityResolved)>0', FALSE, FALSE);
                    break;
                case 4: // loans with preparations
                    $this->db->where('!isnull(lp.LoanPreparationID)', FALSE, FALSE);
                    break;
                case 5: // loans without preparations
                    $this->db->where('lp.LoanPreparationID');
                default:
                    break;
            }
        }
        if ($institution) {
            if ($discipline == 32768) {
                $this->db->like('l.SrcTaxonomy', $institution, 'after');
            }
            else {
                $this->db->join('shipment s', 'l.LoanID=s.LoanID');
                $this->db->where('s.ShippedToID', $institution);
            }
        }

        if ($year) {
            if ($discipline == 32768)
                $this->db->where("YEAR(DateReceived)='$year'", FALSE, FALSE);
            else
                $this->db->where("SUBSTRING(l.LoanNumber, 1, 4)='$year'", FALSE, FALSE);
        }

        $query = $this->db->get();
        if ($query->num_rows()) {
            foreach ($query->result() as $row) {
                $ret[] = array(
                    'LoanID' => $row->LoanID,
                    'LoanNumber' => $row->LoanNumber,
                    'MELRefNo' => $row->MELRefNo,
                    'Botanist' => $this->getBotanist($row->LoanID, $discipline),
                    'Quantity' => $row->Quantity,
                    'QuantityResolved' => $row->QuantityResolved,
                    'IsClosed' => $row->IsClosed,
                    'LoanStatus' => $row->LoanStatus,
                    'DateClosed' => $row->DateClosed,
                    'CurrentDueDate' => $row->CurrentDueDate,
                );
            }
        }
        return $ret;
    }
    
    function getYears($discipline=3) {
        if ($discipline == 32768) {
            $this->db->select('DISTINCT YEAR(DateReceived) AS `Year`', FALSE);
            $this->db->from('loan');
            $this->db->where('DisciplineID', $discipline);
            $this->db->where('DateReceived IS NOT NULL', false, false);
            $this->db->order_by('Year');
        }
        else {
            $this->db->select('DISTINCT SUBSTRING(LoanNumber, 1, 4) AS `Year`', FALSE);
            $this->db->from('loan');
            $this->db->where('DisciplineID', $discipline);
            $this->db->order_by('Year', 'desc');
        }
        
        $query = $this->db->get();
        return $query->result_array();
    }
    
    function getInstitutions($discipline=3) {
        $ret = array();
        $ret[] = '(select institution)';
        if ($discipline == 32768) {
            $query = $this->db->query("SELECT DISTINCT SUBSTRING(l.SrcTaxonomy, 1, LOCATE(' ', l.SrcTaxonomy)) AS InstitutionCode
                FROM loan l
                JOIN agent a ON SUBSTRING(l.SrcTaxonomy, 1, LOCATE(' ', l.SrcTaxonomy))=SUBSTRING(a.LastName, 1, LOCATE(' ', a.LastName))
                WHERE l.DisciplineID=32768
                AND l.SrcTaxonomy LIKE '% %' AND a.LastName LIKE '% %'
                ORDER BY InstitutionCode");
            foreach ($query->result() as $row) {
                $ret[$row->InstitutionCode] = $row->InstitutionCode;
            }
        }
        else {
            $this->db->select('a.AgentID, a.LastName');
            $this->db->from('loan l');
            $this->db->join('shipment s', 'l.LoanID=s.LoanID');
            $this->db->join('agent a', 's.ShippedToID=a.AgentID');
            $this->db->where('l.DisciplineID', $discipline);
            $this->db->group_by('a.AgentID');
            $this->db->order_by('a.Abbreviation');
            $query = $this->db->get();
            foreach ($query->result() as $row)
                $ret[$row->AgentID] = $row->LastName;
        }
        return $ret;
    }
    
    function getBotanist($loanid, $discipline=3) {
        $this->db->select("GROUP_CONCAT(IF(!isnull(a.FirstName), CONCAT(a.LastName, ', ', a.FirstName), a.LastName) SEPARATOR '; ') AS Botanist", FALSE);
        $this->db->from('loan l');
        $this->db->join('loanagent la', "l.LoanID=la.LoanID");
        $this->db->join('agent a', 'la.AgentID=a.AgentID');
        $this->db->where('l.LoanID', $loanid);
        if ($discipline == 32768)
            $this->db->where_in('la.Role', array('Botanist', 'Student'));
        $this->db->group_by('l.LoanID');
        
        $query = $this->db->get();
        if ($query->num_rows()) {
            $row = $query->row();
            return $row->Botanist;
        }
        else
            return FALSE;
    }
    
    /***************************************************************************************************/
    
    function getFormattedNameString($colobj, $style='i', $bas=FALSE) { // borrowed from labeldatamodel.php (slightly modified)
        $start = FALSE;
        $end = FALSE;
        if ($style) {
            $start = "<$style>";
            $end = "</$style>";
        }
        
        if (!$bas)
            $select = "SELECT TaxonID, Qualifier, VarQualifier AS QualifierRank, Addendum
                FROM determination
                WHERE IsCurrent=1 AND CollectionObjectID=$colobj";
        else
            $select = "SELECT TaxonID, Qualifier, VarQualifier AS QualifierRank
                FROM determination
                WHERE YesNo1=1 AND CollectionObjectID=$colobj";
        $query = $this->db->query($select);
        if ($query->num_rows() > 0) {
            $row = $query->row();
            $namearray = $this->getNameArray($row->TaxonID);
            //print_r($namearray);
            $qualifier = $row->Qualifier;
            if($qualifier && $qualifier!='?') $qualifier .= ' ';
            $qualifierrank = ($row->QualifierRank) ? $row->QualifierRank : $namearray['Rank'];
            $formattednamestring = '';
            if(isset($namearray['species'])) {
                if($qualifier && $qualifierrank=='genus')
                    $formattednamestring .= $qualifier;
                
                $formattednamestring .= $start;
                if ($namearray['genusHybrid'] == 'x')
                    $formattednamestring .= '×';
                $formattednamestring .= $namearray['genus'] . $end;
                if($qualifier && $qualifierrank=='species')
                    $formattednamestring .= ' ' . $qualifier;
                $formattednamestring .= " $start";
                if ($namearray['speciesHybrid'] == 'x')
                    $formattednamestring .= '×';
                elseif ($namearray['speciesHybrid'] == 'H')
                    $namearray['species'] = str_replace (' x ', ' × ', $namearray['species']);
                $formattednamestring .= $namearray['species'] . $end;
                if(isset($namearray['subspecies']) || isset($namearray['variety']) || isset($namearray['forma'])) {
                    if(isset($namearray['forma'])) {
                        if($namearray['forma']!=$namearray['species']) {
                            if($qualifier && $qualifierrank=='forma')
                                $formattednamestring .= ' ' . $qualifier;
                            if ($namearray['formaHybrid'] == 'x')
                                $formattednamestring .= " nothof. $start" . $namearray['forma'] . $end;
                            else {
                                if ($namearray['formaHybrid'] == 'H')
                                    $namearray['forma'] = str_replace (' x ', ' × ', $namearray['forma']);
                                $formattednamestring .= " f. $start" . $namearray['forma'] . $end;
                            }
                            $formattednamestring .= ' ' . $namearray['formaAuthor'];
                        } else {
                            $formattednamestring .= ' ' . $namearray['speciesAuthor'];
                            if($qualifier && $qualifierrank=='forma')
                                $formattednamestring .= ' ' . $qualifier;
                            $formattednamestring .= " f. $start" . $namearray['forma'] . $end;
                        }
                    } elseif(isset($namearray['variety'])) {
                        if($namearray['variety']!=$namearray['species']) {
                            if($qualifier && $qualifierrank=='variety')
                                $formattednamestring .= ' ' . $qualifier;
                            if ($namearray['varietyHybrid'] == 'x')
                                $formattednamestring .= " nothovar. $start" . $namearray['variety'] . $end;
                            else {
                                if ($namearray['varietyHybrid'] == 'H')
                                    $namearray['variety'] = str_replace (' x ', ' × ', $namearray['variety']);
                                $formattednamestring .= " var. $start" . $namearray['variety'] . $end;
                            }
                            $formattednamestring .= ' ' . $namearray['varietyAuthor'];
                        } else {
                            $formattednamestring .= ' ' . $namearray['speciesAuthor'];
                            if($qualifier && $qualifierrank=='variety')
                                $formattednamestring .= ' ' . $qualifier;
                            $formattednamestring .= " var. $start" . $namearray['variety'] . $end;
                        }
                    } elseif(isset($namearray['subspecies'])) {
                        if($namearray['subspecies']!=$namearray['species']) {
                            if($qualifier && $qualifierrank=='subspecies')
                                $formattednamestring .= " nothosubsp. $start" . $namearray['subspecies'] . $end;
                            if ($namearray['subspeciesHybrid'] == 'x')
                                $namearray['subspecies'] = '×' . $namearray['subspecies'];
                            else {
                                if ($namearray['subspeciesHybrid'] == 'H')
                                    $namearray['subspecies'] = str_replace (' x ', ' × ', $namearray['subspecies']);
                                $formattednamestring .= " subsp. $start" . $namearray['subspecies'] . $end;
                            }
                            $formattednamestring .= ' ' . $namearray['subspeciesAuthor'];
                        } else {
                            $formattednamestring .= ' ' . $namearray['speciesAuthor'];
                            if($qualifier && $qualifierrank=='subspecies')
                                $formattednamestring .= $qualifier;
                            $formattednamestring .= " subsp. $start" . $namearray['subspecies'] . $end;
                        }
                    }
                } else $formattednamestring .= ' ' . $namearray['speciesAuthor'];
            } else {
                $rankarray = array('genus', 'tribe', 'subfamily', 'family', 'suborder', 'order', 'superorder',
                    'subclass', 'class', 'subdivision', 'division', 'subkingdom', 'kingdom');
                foreach($rankarray as $rank) {
                    if(isset($namearray[$rank])) {
                        if($qualifier && $qualifierrank==$rank)
                            $formattednamestring .= ' ' . $qualifier;
                            if (isset($namearray['genus']) && $namearray['genusHybrid'] == 'x')
                                $namearray['genus'] = '×' . $namearray['genus'];
                            elseif (isset($namearray['genus']) && $namearray['genusHybrid'] == 'H')
                                $namearray['genus'] = str_replace (' x ', ' × ', $namearray['genus']);
                        $formattednamestring .= $start . $namearray[$rank] . $end;
                        $formattednamestring .= ($namearray[$rank.'Author']) ? ' ' . $namearray[$rank.'Author'] : '';
                        break;
                    }
                }
            }
            $formattednamestring = str_replace("$end $start", ' ', $formattednamestring);
            if (isset($row->Addendum) && $row->Addendum) $formattednamestring .= ' ' . $row->Addendum;
            return $formattednamestring;
        } else return FALSE;
    }
    
    function getNameArray($taxonid) { // borrowed from labeldatamodel
        if($taxonid) {
            $namearray = array();
            $select = "SELECT t.Name, t.Author, d.Name AS Rank, t.UsfwsCode AS HybridFlag, d.RankID, t.ParentID
                FROM taxon t
                JOIN taxontreedefitem d ON t.TaxonTreeDefItemID=d.TaxonTreeDefItemID
                WHERE t.TaxonID=$taxonid";
            $query = $this->db->query($select);
            $row = $query->row();

            $namearray['Rank'] = $row->Rank;
            $namearray[$row->Rank] = $row->Name;
            $namearray[$row->Rank.'Author'] = $row->Author;
            $namearray[$row->Rank.'Hybrid'] = $row->HybridFlag;
            $rankid = $row->RankID;
            $parentid = $row->ParentID;
            while($rankid>160) {
                $select = "SELECT t.Name, t.Author, d.Name AS Rank, t.UsfwsCode AS HybridFlag, d.RankID, t.ParentID
                    FROM taxon t
                    JOIN taxontreedefitem d ON t.TaxonTreeDefItemID=d.TaxonTreeDefItemID
                    WHERE t.TaxonID=$parentid";
                $query = $this->db->query($select);
                $row = $query->row();
                $namearray[$row->Rank] = $row->Name;
                $namearray[$row->Rank.'Author'] = $row->Author;
                $namearray[$row->Rank.'Hybrid'] = $row->HybridFlag;
                $rankid = $row->RankID;
                $parentid = $row->ParentID;
            }
            return $namearray;
        } else return false;
    }
    
    function xml_convert($string) {
        return str_replace('&', '&amp;', $string);
    }
    
    function getTransfers($loanid) {
        $this->db->select('SUBSTRING(LoanNumber, 1, 9) AS LoanNumber', FALSE);
        $this->db->from('loan');
        $this->db->where('LoanID', $loanid);
        $query = $this->db->get();
        $row = $query->row();
        $loanno = $row->LoanNumber;
        
        $this->db->select('LoanID, LoanNumber');
        $this->db->from('loan');
        $this->db->where("SUBSTRING(LoanNumber, 1, 9)='" . $loanno . "'", FALSE, FALSE);
        $this->db->where('LoanID !=', $loanid);
        $query = $this->db->get();
        if ($query->num_rows()) 
            return $query->result_array();
        else
            return FALSE;
    }
    
    public function autoBotanist($q, $discipline=3) {
        $this->db->select("CONCAT(a.LastName, IF(a.MiddleInitial IS NOT NULL, CONCAT(', ', a.MiddleInitial), 
            IF(a.FirstName IS NOT NULL, CONCAT(', ', a.FirstName), ''))) as Botanist", FALSE);
        $this->db->from('loanagent la');
        $this->db->join('loan l', "la.LoanID=l.LoanID AND l.DisciplineID=$discipline");
        $this->db->join('agent a', 'la.AgentID=a.AgentID');
        $this->db->like('a.LastName', $q, 'after');
        $this->db->or_like('a.MiddleInitial', $q, 'after');
        $this->db->or_like('a.FirstName', $q, 'after');
        $this->db->or_where("CONCAT (LastName, ', ', FirstName) LIKE '$q%'", false, false);
        $this->db->or_where("CONCAT (LastName, ', ', MiddleInitial) LIKE '$q%'", false, false);
        $this->db->or_where("CONCAT (MiddleInitial, ' ', LastName) LIKE '$q%'", false, false);
        $this->db->or_where("CONCAT (FirstName, ' ', LastName) LIKE '$q%'", false, false);
        $this->db->group_by('a.AgentID');
        $this->db->order_by('a.LastName');
        $this->db->order_by('a.FirstName');
        $query = $this->db->get();
        if ($query->num_rows()) {
            $ret = array();
            foreach ($query->result() as $row) {
                $ret[] = $row->Botanist;
            }
            return $ret;
        }
        
    }
    
}



?>
