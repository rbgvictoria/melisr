<?php
/**
 * @property CI_Loader $load
 * @property CI_Form_validation $form_validation
 * @property CI_Input $input
 * @property CI_Email $email
 * @property CI_DB_active_record $db
 * @property CI_DB_forge $dbforge
 */

class TransactionModel extends Model {

    function  __construct() {
        parent::Model();

        // connect to database
        $this->load->database();
    }
    
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
            $qualifier = $row->Qualifier;
            if($qualifier && $qualifier!='?') $qualifier .= ' ';
            $qualifierrank = ($row->QualifierRank) ? $row->QualifierRank : $namearray['Rank'];
            $formattednamestring = '';
            if(isset($namearray['Species'])) {
                if($qualifier && $qualifierrank=='Genus')
                    $formattednamestring .= $qualifier;
                
                $formattednamestring .= $start;
                if ($namearray['GenusHybrid'] == 'x')
                    $formattednamestring .= '×';
                $formattednamestring .= $namearray['Genus'] . $end;
                if($qualifier && $qualifierrank=='Species')
                    $formattednamestring .= ' ' . $qualifier;
                $formattednamestring .= " $start";
                if ($namearray['SpeciesHybrid'] == 'x')
                    $formattednamestring .= '×';
                elseif ($namearray['SpeciesHybrid'] == 'H')
                    $namearray['Species'] = str_replace (' x ', ' × ', $namearray['Species']);
                $formattednamestring .= $namearray['Species'] . $end;
                if(isset($namearray['Subspecies']) || isset($namearray['variety']) || isset($namearray['forma'])) {
                    if(isset($namearray['forma'])) {
                        if($namearray['forma']!=$namearray['Species']) {
                            if($qualifier && $qualifierrank=='forma')
                                $formattednamestring .= ' ' . $qualifier;
                            if ($namearray['formaHybrid'] == 'x')
                                $formattednamestring .= " nothof. $start" . $namearray['forma'] . $end;
                            else {
                                if ($namearray['formaHybrid'] == 'H')
                                    $namearray['forma'] = str_replace (' x ', ' × ', $namearray['Forma']);
                                $formattednamestring .= " f. $start" . $namearray['forma'] . $end;
                            }
                            $formattednamestring .= ' ' . $namearray['formaAuthor'];
                        } else {
                            $formattednamestring .= ' ' . $namearray['SpeciesAuthor'];
                            if($qualifier && $qualifierrank=='forma')
                                $formattednamestring .= ' ' . $qualifier;
                            $formattednamestring .= " f. $start" . $namearray['forma'] . $end;
                        }
                    } elseif(isset($namearray['variety'])) {
                        if($namearray['variety']!=$namearray['Species']) {
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
                            $formattednamestring .= ' ' . $namearray['SpeciesAuthor'];
                            if($qualifier && $qualifierrank=='variety')
                                $formattednamestring .= ' ' . $qualifier;
                            $formattednamestring .= " var. $start" . $namearray['variety'] . $end;
                        }
                    } elseif(isset($namearray['Subspecies'])) {
                        if($namearray['Subspecies']!=$namearray['Species']) {
                            if($qualifier && $qualifierrank=='subspecies')
                                $formattednamestring .= " nothosubsp. $start" . $namearray['Subspecies'] . $end;
                            if ($namearray['SubspeciesHybrid'] == 'x')
                                $namearray['Subspecies'] = '×' . $namearray['Subspecies'];
                            else {
                                if ($namearray['SubspeciesHybrid'] == 'H')
                                    $namearray['Subspecies'] = str_replace (' x ', ' × ', $namearray['Subspecies']);
                                $formattednamestring .= " subsp. $start" . $namearray['Subspecies'] . $end;
                            }
                            $formattednamestring .= ' ' . $namearray['SubspeciesAuthor'];
                        } else {
                            $formattednamestring .= ' ' . $namearray['SpeciesAuthor'];
                            if($qualifier && $qualifierrank=='subspecies')
                                $formattednamestring .= $qualifier;
                            $formattednamestring .= " subsp. $start" . $namearray['Subspecies'] . $end;
                        }
                    }
                } else $formattednamestring .= ' ' . $namearray['SpeciesAuthor'];
            } else {
                $rankarray = array('Genus', 'Tribe', 'Subfamily', 'Family', 'Suborder', 'Order', 'Superorder',
                    'Subclass', 'Class', 'Subdivision', 'Division', 'Subkingdom', 'Kingdom');
                foreach($rankarray as $rank) {
                    if(isset($namearray[$rank])) {
                        if($qualifier && $qualifierrank==$rank)
                            $formattednamestring .= ' ' . $qualifier;
                            if (isset($namearray['Genus']) && $namearray['GenusHybrid'] == 'x')
                                $namearray['Genus'] = '×' . $namearray['Genus'];
                            elseif (isset($namearray['Genus']) && $namearray['GenusHybrid'] == 'H')
                                $namearray['Genus'] = str_replace (' x ', ' × ', $namearray['Genus']);
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
    
    function getInstitutions() {
        $this->db->select('ag.AgentID, ag.LastName');
        $this->db->from('agent ag');
        $this->db->join('address ad', 'ag.AgentID=ad.AgentID');
        $this->db->order_by('LastName');
        $query = $this->db->get();
        if($query->num_rows() > 0) {
            $ret = array();
            foreach ($query->result() as $row) {
                $ret[] = array(
                    'agentid' => $row->AgentID,
                    'agentname' => str_replace('--', '&mdash;', $row->LastName),
                );
            }
            return $ret;
        } else return false;
    }
    
    public function getAddress($agentid) {
        $this->db->select('Address5, Address, Address2, Address3, Address4, City, State, PostalCode, Country');
        $this->db->from('address');
        $this->db->where('AgentID', $agentid);
        $this->db->where('IsCurrent', 1);
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

    function getShipmentMethod($method) {
        $this->db->select('Title');
        $this->db->from('picklistitem');
        $this->db->where('PickListID', 150);
        $this->db->where('Value', $method);
        $query = $this->db->get();
        if ($query->num_rows) {
            $row = $query->row();
            return $row->Title;
        }
        else
            return FALSE;
    }

    function xml_convert($string) {
        return str_replace('&', '&amp;', $string);
    }
    
    function getLoanAgents($loanid) {
        $this->db->select('la.Role, a.Title, a.MiddleInitial, a.FirstName, a.LastName');
        $this->db->from('loanagent la');
        $this->db->join('agent a', 'la.AgentID=a.AgentID');
        $this->db->where('la.LoanID', $loanid);
        $this->db->order_by('la.Role');
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

    
    
}



?>
