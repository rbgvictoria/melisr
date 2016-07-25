<?php

/**
 * @property CI_Loader $load
 * @property CI_Form_validation $form_validation
 * @property CI_Input $input
 * @property CI_Email $email
 * @property CI_DB_active_record $db
 * @property CI_DB_forge $dbforge
 */
class FqcmModel extends Model {
    private $collids;
        
    public function __construct() {
        parent::Model();

        // connect to database
        $this->load->database();
        $this->load->helper('xml');
    
        $this->collids=array();
    }
    
    public function getUsers() {
        $query = $this->db->query("SELECT a.AgentID,s.Name FROM specifyuser s
            JOIN agent a ON s.SpecifyUserID=a.SpecifyUserID
            WHERE UserType IN('FullAccess','Manager')
            ORDER BY Name ASC");

        if ($query->num_rows())
            return $query->result_array();

        else
            return false;
    }

        /** 
         * Creates an array of collection object identifiers for the other co 
         * queries to use.
         */
    public function getCollectionObjects ($startdate, $enddate=FALSE, $userid=FALSE) {
        $this->db->select("CollectionObjectID");
        $this->db->from("collectionobject");
        $this->db->where("(DATE(TimestampCreated)>='$startdate' OR DATE(TimestampModified)>='$startdate')", FALSE, FALSE);
        if ($enddate) {
            $this->db->where("(DATE(TimestampCreated)<='$enddate' OR DATE(TimestampModified)<='$enddate')", FALSE, FALSE);
        }
        if ($userid) {
            $this->db->where("(CreatedByAgentID=$userid OR ModifiedByAgentID=$userid)", FALSE, FALSE);
        }
        $query=$this->db->get();
        if ($query->num_rows()) {
            foreach ($query->result() as $row) {
                $this->collids[]=$row->CollectionObjectID;
            }
        }
    }
    
    
        /** Looks for collection object records that are missing a preparation 
         *  (i.e. an indication of what type of specimen it is and what preparations 
         *  are associated with it (Sheet, Packet, Spirit, Duplicate etc.) 
         */
    public function missingPreparation($startdate, $enddate=FALSE, $userid=FALSE, $recordset=FALSE) {
        $ret = array();
        $this->db->select("co.CollectionObjectID,co.CatalogNumber,CONCAT(a1.FirstName,' ',a1.LastName) AS CreatedBy,DATE(co.TimestampCreated) AS Created,CONCAT(a2.FirstName,' ',a2.LastName) AS EditedBy,DATE(co.TimestampModified) AS Edited", FALSE);
        $this->db->from("collectionobject co");
        $this->db->join("preparation p", "co.CollectionObjectID=p.CollectionObjectID", "left");
        $this->db->join("agent a1", "a1.AgentID=co.CreatedByAgentID");
        $this->db->join("agent a2", "a2.AgentID=co.ModifiedByAgentID");
        $this->db->where("co.CollectionMemberID", 4);
        $this->db->where("p.PreparationID IS NULL", FALSE, FALSE);
        
        if ($this->collids)
            $this->db->where_in("co.CollectionObjectID", $this->collids);

        if ($startdate)
            $this->db->where("DATE(co.TimestampCreated) >=$startdate", FALSE, FALSE);
            
        if ($userid)
            $this->db->where("co.CreatedByAgentID", $userid);
        
        if ($recordset)
            $this->db->where_in("co.CollectionObjectID", $recordset);

        $query = $this->db->get();
        if ($query->num_rows()) {
            return $query->result_array();
        }
        else
            return false;
        }
    
        /** Looks for collection object records that have more than one
         *  primary preparation, i.e. more than one preparation that should
         *  have a unique MEL number (Sheet, Packet, Spirit, Carpological etc.)
         */
    public function tooManyPrimaryPreparations($startdate, $enddate=FALSE, $userid=FALSE) {
        $ret = array();
        $this->db->select("co.CollectionObjectID,co.CatalogNumber,CONCAT(a1.FirstName,' ',a1.LastName) AS CreatedBy,DATE(co.TimestampCreated) AS Created,CONCAT(a2.FirstName,' ',a2.LastName) AS EditedBy,DATE(co.TimestampModified) AS Edited", FALSE);
        $this->db->from("collectionobject co");
        $this->db->join("preparation p", "co.CollectionObjectID=p.CollectionObjectID AND p.PrepTypeID IN (1,2,3,4,8,10,12,13)", "left");
        $this->db->join("agent a1", "a1.AgentID=co.CreatedByAgentID");
        $this->db->join("agent a2", "a2.AgentID=co.ModifiedByAgentID");
        $this->db->where("co.CollectionMemberID", 4);
        $this->db->where("DATE(co.TimestampCreated) >=", $startdate);
        $this->db->group_by("co.CatalogNumber");
        $this->db->having("COUNT(p.PrepTypeID)>1");

        if ($this->collids)
            $this->db->where_in("co.CollectionObjectID", $this->collids);

        if ($userid)
            $this->db->where("co.CreatedByAgentID", $userid);

        $query = $this->db->get();
        if ($query->num_rows()) {
            return $query->result_array();
        }
        else
            return false;
    }
    
    /** Looks for collection object records that are missing a primary
    *  preparation, and only have preparation(s) that should not be 
    *  assigned unique MEL numbers (Duplicate, Silica gel sample etc.)
    */
    public function noPrimaryPreparations($startdate, $enddate=FALSE, $userid=FALSE) {
        $ret = array();
        $this->db->select("co.CollectionObjectID,co.CatalogNumber,CONCAT(a1.FirstName,' ',a1.LastName) AS CreatedBy,DATE(co.TimestampCreated) AS Created,CONCAT(a2.FirstName,' ',a2.LastName) AS EditedBy,DATE(co.TimestampModified) AS Edited", FALSE);
        $this->db->from("collectionobject co");
        $this->db->join("preparation p", "co.CollectionObjectID=p.CollectionObjectID AND p.PrepTypeID IN (1,2,3,4,6,8,10,12,13)", "left");
        $this->db->join("agent a1", "a1.AgentID=co.CreatedByAgentID");
        $this->db->join("agent a2", "a2.AgentID=co.ModifiedByAgentID");
        $this->db->where("co.CollectionMemberID", 4);
        $this->db->where("DATE(co.TimestampCreated) >=", $startdate);
        $this->db->groupby("co.CatalogNumber", FALSE);
        $this->db->having("COUNT(p.PrepTypeID)=0", FALSE);

        if ($this->collids)
            $this->db->where_in("co.CollectionObjectID", $this->collids);

        if ($userid)
            $this->db->where("co.CreatedByAgentID", $userid);

        $query = $this->db->get();
        if ($query->num_rows()) {
            return $query->result_array();
        }
        else
            return false;
    }
    
    public function missingStorage($startdate, $enddate=FALSE, $userid=FALSE, $recordset=FALSE) {
        $ret = array();
        $this->db->select("co.CollectionObjectID,co.CatalogNumber,CONCAT(a1.FirstName,' ',a1.LastName) AS CreatedBy,DATE(co.TimestampCreated) AS Created,CONCAT(a2.FirstName,' ',a2.LastName) AS EditedBy,DATE(co.TimestampModified) AS Edited", FALSE);
        $this->db->from("collectionobject co");
        $this->db->join("preparation p", "co.CollectionObjectID=p.CollectionObjectID AND p.PrepTypeID IN (1,2,3,4,6,8,10,12,13)", "left");
        $this->db->join("agent a1", "a1.AgentID=co.CreatedByAgentID");
        $this->db->join("agent a2", "a2.AgentID=co.ModifiedByAgentID");
        $this->db->where('co.CollectionMemberID', 4);
        $this->db->where('p.StorageID IS NULL', FALSE, FALSE);
        
        if ($this->collids)
            $this->db->where_in("co.CollectionObjectID", $this->collids);

        if ($startdate)
            $this->db->where("DATE(co.TimestampModified)>=\"$startdate\"", FALSE, FALSE);
            
        if ($userid)
            $this->db->where("(co.ModifiedByAgentID=$userid OR (co.ModifiedByAgentID IS NULL AND co.CreatedByAgentID=$userid))", FALSE, FALSE);
        
        if ($recordset)
            $this->db->where_in("co.CollectionObjectID", $recordset);

        $query = $this->db->get();
        if ($query->num_rows()) {
            return $query->result_array();
        }
        else
            return false;
    }
    
        /** Looks for preparations for which the quantity is higher than
         *  it should be, primary preparations with a quantity higher than 1, 
         *  or any preparations with a null value, and anything other than a Silica gel preparation that has a value of 0.
         */
    public function inappropriateQuantityInPreparation($startdate, $enddate=FALSE, $userid=FALSE) {
        $ret = array();
        $this->db->select("co.CollectionObjectID,co.CatalogNumber,CONCAT(a1.FirstName,' ',a1.LastName) AS CreatedBy,DATE(co.TimestampCreated) AS Created,CONCAT(a2.FirstName,' ',a2.LastName) AS EditedBy,DATE(co.TimestampModified) AS Edited", FALSE);
        $this->db->from("collectionobject co");
        $this->db->join("preparation p", "co.CollectionObjectID=p.CollectionObjectID", "left");
        $this->db->join("agent a1", "a1.AgentID=co.CreatedByAgentID");
        $this->db->join("agent a2", "a2.AgentID=co.ModifiedByAgentID");
        $this->db->where("co.CollectionMemberID", 4);
        $this->db->where("((p.CountAmt > 1 AND p.PrepTypeID IN (1,3,4,8,10,12,13,14)) OR (p.CountAmt IS NULL ) OR (p.PrepTypeID != 7 AND p.CountAmt=0))", FALSE, FALSE);
        $this->db->where("DATE(co.TimestampModified) >=", $startdate);
        
        if ($this->collids)
            $this->db->where_in("co.CollectionObjectID", $this->collids);

        if ($userid)
            $this->db->where("co.CreatedByAgentID", $userid);

        $query = $this->db->get();
        if ($query->num_rows()) {
            return $query->result_array();
        }
        else
            return false;
    }
    
        /** Looks for multisheet messages which are missing the part (the 
         * letter suffix to the MEL catalogue number).
         */
    public function partMissingFromMultisheetMessage($startdate, $enddate=FALSE, $userid=FALSE) {
        $ret = array();
        $this->db->select("co.CollectionObjectID, co.CatalogNumber,CONCAT(a1.FirstName,' ',a1.LastName) AS CreatedBy,
            DATE(co.TimestampCreated) AS Created,CONCAT(a2.FirstName,' ',a2.LastName) AS EditedBy,
            DATE(co.TimestampModified) AS Edited, p.Remarks", FALSE);
        $this->db->from("collectionobject co");
        $this->db->join("preparation p", "co.CollectionObjectID=p.CollectionObjectID", "left");
        $this->db->join("agent a1", "a1.AgentID=co.CreatedByAgentID");
        $this->db->join("agent a2", "a2.AgentID=co.ModifiedByAgentID");
        $this->db->where("co.CollectionMemberID", 4);
        $this->db->where('p.PrepTypeID !=', 18);
        $this->db->where("p.Remarks IS NOT NULL AND p.Remarks !='' AND DATE(co.TimestampCreated) >=", $startdate);

        if ($this->collids)
            $this->db->where_in("co.CollectionObjectID", $this->collids);

        if ($userid)
            $this->db->where("co.CreatedByAgentID", $userid);

        $query = $this->db->get();
        if ($query->num_rows()) {
            $ret = array();
            foreach ($query->result() as $row) {
                preg_match_all('/(MEL ?\d{1,7})([[:alpha:]]?)/', $row->Remarks, $matches);
                if (count($matches) < 3) {
                    $ret[] = (array) $row;
                }
                else {
                    foreach ($matches[2] as $part) {
                        if (!$part) {
                            $ret[] = (array) $row;
                            break;
                        }
                    }
                }
            }
            return $ret;
        }
        else
            return false;
    }
    
        /** Looks for Spirit preparations that are missing the jar size. 
         */
    public function jarSizeMissing($startdate, $enddate=FALSE, $userid=FALSE) {
        $ret = array();
        $this->db->select("co.CollectionObjectID,co.CatalogNumber,CONCAT(a1.FirstName,' ',a1.LastName) AS CreatedBy,DATE(co.TimestampCreated) AS Created,CONCAT(a2.FirstName,' ',a2.LastName) AS EditedBy,DATE(co.TimestampModified) AS Edited", FALSE);
        $this->db->from("collectionobject co");
        $this->db->join("preparation p", "co.CollectionObjectID=p.CollectionObjectID", "left");
        $this->db->join("preptype pt", "pt.PrepTypeID=p.PrepTypeID AND pt.Name='Spirit'");
        $this->db->join("agent a1", "a1.AgentID=co.CreatedByAgentID");
        $this->db->join("agent a2", "a2.AgentID=co.ModifiedByAgentID");
        $this->db->where("co.CollectionMemberID", 4);
        $this->db->where("p.Status IS NULL AND DATE(co.TimestampCreated) >=", $startdate);

        if ($this->collids)
            $this->db->where_in("co.CollectionObjectID", $this->collids);

        if ($userid)
            $this->db->where("co.CreatedByAgentID", $userid);

        $query = $this->db->get();
        if ($query->num_rows()) {
            return $query->result_array();
        }
        else
            return false;
    }
    
    public function duplicateDuplicatePreparations($startdate, $enddate=FALSE, $userid=FALSE, $type='duplicate') {
        $this->db->select("co.CollectionObjectID,co.CatalogNumber,CONCAT(a1.FirstName,' ',a1.LastName) AS CreatedBy,
            DATE(co.TimestampCreated) AS Created,CONCAT(a2.FirstName,' ',a2.LastName) AS EditedBy,
            DATE(co.TimestampModified) AS Edited", FALSE);
        $this->db->from('collectionobject co');
        $this->db->join("agent a1", "a1.AgentID=co.CreatedByAgentID");
        $this->db->join("agent a2", "a2.AgentID=co.ModifiedByAgentID");
        
        $this->db->join('preparation p', 'co.CollectionObjectID=p.CollectionObjectID');
        $this->db->where("co.CollectionMemberID", 4);
        $this->db->where("DATE(co.TimestampCreated) >=", $startdate);
        if ($type == 'seed duplicate')
            $this->db->where('p.PrepTypeID', 16);
        else
            $this->db->where('p.PrepTypeID', 15);
        
        if ($this->collids)
            $this->db->where_in("co.CollectionObjectID", $this->collids);

        if ($userid)
            $this->db->where("co.CreatedByAgentID", $userid);
        $this->db->group_by('co.CollectionObjectID');
        $this->db->having('count(*)>1');

        $query = $this->db->get();
        if ($query->num_rows()) {
            return $query->result_array();
        }
        else
            return false;
    }
    
    public function missingExHerbarium($startdate, $enddate=FALSE, $userid=FALSE) {
        $this->db->select("co.CollectionObjectID,co.CatalogNumber,CONCAT(a1.FirstName,' ',a1.LastName) AS CreatedBy,
            DATE(co.TimestampCreated) AS Created,CONCAT(a2.FirstName,' ',a2.LastName) AS EditedBy,
            DATE(co.TimestampModified) AS Edited", FALSE);
        $this->db->from('collectionobject co');
        $this->db->join("agent a1", "a1.AgentID=co.CreatedByAgentID");
        $this->db->join("agent a2", "a2.AgentID=co.ModifiedByAgentID");
        
        $this->db->join('preparation p', 'co.CollectionObjectID=p.CollectionObjectID');
        $this->db->join('otheridentifier oi', "co.CollectionObjectID=oi.CollectionObjectID AND oi.Remarks='Ex herbarium'", 'left', FALSE);
        
        if ($this->collids)
            $this->db->where_in("co.CollectionObjectID", $this->collids);

        if ($userid)
            $this->db->where("co.CreatedByAgentID", $userid);
        $this->db->where("p.Text2 IS NOT NULL AND p.Text2!=''", FALSE, FALSE);
        $this->db->where('oi.OtherIdentifierID IS NULL', FALSE, FALSE);
        $this->db->group_by('co.CollectionObjectID');
        $query = $this->db->get();
        if ($query->num_rows()) {
            return $query->result_array();
        }
        else
            return false;
    }
    
    public function missingExHerbariumCatalogNumber($startdate, $enddate=FALSE, $userid=FALSE) {
        $this->db->select("co.CollectionObjectID,co.CatalogNumber,CONCAT(a1.FirstName,' ',a1.LastName) AS CreatedBy,
            DATE(co.TimestampCreated) AS Created,CONCAT(a2.FirstName,' ',a2.LastName) AS EditedBy,
            DATE(co.TimestampModified) AS Edited", FALSE);
        $this->db->from('collectionobject co');
        $this->db->join("agent a1", "a1.AgentID=co.CreatedByAgentID");
        $this->db->join("agent a2", "a2.AgentID=co.ModifiedByAgentID");
        
        $this->db->join('otheridentifier oi', "co.CollectionObjectID=oi.CollectionObjectID AND oi.Remarks='Ex herbarium'", 'left', FALSE);
        
        if ($startdate)
            $this->db->where("DATE(co.TimestampCreated)>=\"$startdate\"", FALSE, FALSE);
        
        if ($this->collids)
            $this->db->where_in("co.CollectionObjectID", $this->collids);

        if ($userid)
            $this->db->where("co.CreatedByAgentID", $userid);
        
        $this->db->where("(oi.Institution IS NULL OR oi.Institution='')", FALSE, FALSE);
        $query = $this->db->get();
        if ($query->num_rows()) {
            return $query->result_array();
        }
        else
            return false;
    }
    

    /* SELECT co.CatalogNumber,co.notifications AS Mixed,a1.LastName AS CreatedBy,LEFT(co.TimestampCreated,10) AS Created,a2.LastName AS EditedBy,LEFT(co.TimestampModified,10) AS Edited,p.PreparationID,p.Remarks
      FROM preparation p
      JOIN collectionobject co ON co.CollectionObjectID=p.CollectionObjectID
      LEFT JOIN agent a1 ON a1.AgentID=co.CreatedByAgentID
      LEFT JOIN agent a2 ON a2.AgentID=co.ModifiedByAgentID
      WHERE (p.Remarks IS NOT NULL AND p.Remarks !='') AND p.Remarks NOT LIKE '%A%'
      AND LEFT(co.TimestampCreated,10)> '2011-12-19'; */
    
        /** Looks for records that have an altitude value recorded, but do
         *  not have the altitude units recorded. 
         */
    public function missingAltitudeUnit($startdate, $enddate=FALSE, $userid=FALSE) {
        $ret = array();
        $this->db->select("co.CollectionObjectID,co.CatalogNumber,CONCAT(a.FirstName,' ',a.LastName) AS CreatedBy,DATE(co.TimestampCreated) AS Created,CONCAT(aa.FirstName,' ',aa.LastName) AS EditedBy,DATE(co.TimestampModified) AS Edited,
ce.StartDate AS DateCollected,l.LocalityName AS Locality,l.MinElevation,l.MaxElevation,l.Text1 AS Units", FALSE);
        $this->db->from("locality l");
        $this->db->join("collectingevent ce", "l.LocalityID=ce.LocalityID");
        $this->db->join("collectionobject co", "ce.CollectingEventID=co.CollectingEventID");
        $this->db->join("agent a", "a.AgentID=co.CreatedByAgentID");
        $this->db->join("agent aa", "aa.AgentID=co.ModifiedByAgentID");
        $this->db->where("co.CollectionMemberID", 4);
        $this->db->where("(l.MinElevation IS NOT NULL OR l.MaxElevation IS NOT NULL) AND l.Text1 IS NULL AND DATE(co.TimestampCreated)>='$startdate'", FALSE, FALSE);
        
        if ($this->collids)
            $this->db->where_in("co.CollectionObjectID", $this->collids);

        if ($userid)
            $this->db->where("co.CreatedByAgentID", $userid);

        $query = $this->db->get();
        if ($query->num_rows()) {
            return $query->result_array();
        }
        else
            return false;
    }

            /** Looks for records that have an altitude value high than the 
             * highest point in the state. 
         */
    public function tooMuchAltitude($startdate, $enddate=FALSE, $userid=FALSE) {
        $ret = array();
        $this->db->select("co.CollectionObjectID,co.CatalogNumber,CONCAT(a.FirstName,' ',a.LastName) AS CreatedBy,DATE(co.TimestampCreated) AS Created,CONCAT(aa.FirstName,' ',aa.LastName) AS EditedBy,DATE(co.TimestampModified) AS Edited,
l.MinElevation AS MinAltitude,l.MaxElevation AS MaxAltitude", FALSE);
        $this->db->from("locality l");
        $this->db->join("geography g", "l.GeographyID=g.GeographyID AND RankID=300");
        $this->db->join("collectingevent ce", "l.LocalityID=ce.LocalityID");
        $this->db->join("collectionobject co", "ce.CollectingEventID=co.CollectingEventID");
        $this->db->join("collection coll", "co.CollectionID=coll.CollectionID AND coll.CollectionID=4");
        $this->db->join("agent a", "a.AgentID=co.CreatedByAgentID");
        $this->db->join("agent aa", "aa.AgentID=co.ModifiedByAgentID");
        $this->db->where("((g.Name='Victoria' AND l.Text1='m' AND (l.MinElevation > 2010 OR l.maxElevation > 2010)) OR
(g.Name='Victoria' AND l.Text1='ft' AND (l.MinElevation > 6600 OR l.maxElevation > 6600)) OR
(g.Name='Western Australia' AND l.Text1='m' AND (l.MinElevation > 1280 OR l.maxElevation > 1280)) OR
(g.Name='Western Australia' AND l.Text1='ft' AND (l.MinElevation > 4200 OR l.maxElevation > 4200)) OR
(g.Name='Northern Territory' AND l.Text1='m' AND (l.MinElevation > 1560 OR l.maxElevation > 1560)) OR
(g.Name='Northern Territory' AND l.Text1='ft' AND (l.MinElevation > 5100 OR l.maxElevation > 5100)) OR
(g.Name='South Australia' AND l.Text1='m' AND (l.MinElevation > 1460 OR l.maxElevation > 1460)) OR
(g.Name='South Australia' AND l.Text1='ft' AND (l.MinElevation > 4800 OR l.maxElevation > 4800)) OR
(g.Name='Queensland' AND l.Text1='m' AND (l.MinElevation > 1650 OR l.maxElevation > 1650)) OR
(g.Name='Queensland' AND l.Text1='ft' AND (l.MinElevation > 5400 OR l.maxElevation > 5400)) OR
(g.Name='New South Wales' AND l.Text1='m' AND (l.MinElevation > 2250 OR l.maxElevation > 2250)) OR
(g.Name='New South Wales' AND l.Text1='ft' AND (l.MinElevation > 7400 OR l.maxElevation > 7400)) OR
(g.Name='Australian Capital Territory' AND l.Text1='m' AND (l.MinElevation > 2015 OR l.maxElevation > 2015)) OR
(g.Name='Australian Capital Territory' AND l.Text1='ft' AND (l.MinElevation > 6300 OR l.maxElevation > 6300)) OR
(g.Name='Tasmania' AND l.Text1='m' AND (l.MinElevation > 1640 OR l.maxElevation > 1640)) OR
(g.Name='Tasmania' AND l.Text1='ft' AND (l.MinElevation > 5350 OR l.maxElevation > 5350))) AND DATE(co.TimestampCreated)>='$startdate'", FALSE, FALSE);
        
        if ($this->collids)
            $this->db->where_in("co.CollectionObjectID", $this->collids);

        if ($userid)
            $this->db->where("co.CreatedByAgentID", $userid);

        $query = $this->db->get();
        if ($query->num_rows()) {
            return $query->result_array();
        }
        else
            return false;
    }
    
        /** Looks for records that are not linked to a locality record. 
         */
    public function missingLocality($startdate, $enddate=FALSE, $userid=FALSE) {
        $ret = array();
        $this->db->select("co.CollectionObjectID,co.CatalogNumber,CONCAT(a.FirstName,' ',a.LastName) AS CreatedBy,DATE(co.TimestampCreated) AS Created,CONCAT(aa.FirstName,' ',aa.LastName) AS EditedBy,DATE(co.TimestampModified) AS Edited", FALSE);
        $this->db->from("collectionobject co");
        $this->db->join("collectingevent ce", "ce.CollectingEventID=co.CollectingEventID", "left");
        $this->db->join("locality l", "l.LocalityID=ce.LocalityID", "left");
        $this->db->join("agent a", "a.AgentID=co.CreatedByAgentID");
        $this->db->join("agent aa", "aa.AgentID=co.ModifiedByAgentID");
        $this->db->where("co.CollectionMemberID", 4);
        $this->db->where("ce.LocalityID IS NULL AND DATE(co.TimestampCreated)>='$startdate'", FALSE, FALSE);

        if ($this->collids)
            $this->db->where_in("co.CollectionObjectID", $this->collids);

        if ($userid)
            $this->db->where("co.CreatedByAgentID", $userid);

        $query = $this->db->get();
        if ($query->num_rows()) {
            return $query->result_array();
        }
        else
            return false;
    }
    
        /** Looks for locality records that are not linked to a geography
         *  record.
         */
    public function missingGeography($startdate, $enddate=FALSE, $userid=FALSE, $recordset=FALSE) {
        $ret = array();
        $this->db->select("co.CollectionObjectID,co.CatalogNumber,CONCAT(a.FirstName,' ',a.LastName) AS CreatedBy,DATE(co.TimestampCreated) AS Created,CONCAT(aa.FirstName,' ',aa.LastName) AS EditedBy,DATE(co.TimestampModified) AS Edited", FALSE);
        $this->db->from("collectionobject co");
        $this->db->join("collectingevent ce", "ce.CollectingEventID=co.CollectingEventID", "left");
        $this->db->join("locality l", "l.LocalityID=ce.LocalityID", "left");
        $this->db->join("geography g", "l.GeographyID=g.GeographyID", "left");
        $this->db->join("agent a", "a.AgentID=co.CreatedByAgentID");
        $this->db->join("agent aa", "aa.AgentID=co.ModifiedByAgentID");
        $this->db->where("co.CollectionMemberID", 4);
        $this->db->where("l.GeographyID IS NULL", FALSE, FALSE);
        
        if ($this->collids)
            $this->db->where_in("co.CollectionObjectID", $this->collids);

        if ($startdate)
            $this->db->where("DATE(co.TimestampCreated)>='$startdate'", FALSE, FALSE);
        
        if ($userid)
            $this->db->where("co.CreatedByAgentID", $userid);
        
        if ($recordset)
            $this->db->where_in('co.CollectionObjectID', $recordset);

        $query = $this->db->get();
        if ($query->num_rows()) {
            return $query->result_array();
        }
        else
            return false;
    }
  
        /** Looks for newly databased records where 'Cultivated' has been entered in the geography instead of a country name
         */
    public function cultivatedInGeography($startdate, $enddate=FALSE, $userid=FALSE, $recordset=FALSE) {
        $ret = array();
        $this->db->select("co.CollectionObjectID,co.CatalogNumber,CONCAT(a.FirstName,' ',a.LastName) AS CreatedBy,DATE(co.TimestampCreated) AS Created,CONCAT(aa.FirstName,' ',aa.LastName) AS EditedBy,DATE(co.TimestampModified) AS Edited", FALSE);
        $this->db->from("collectionobject co");
        $this->db->join("collection coll", "co.CollectionID=coll.CollectionID AND coll.CollectionID=4");
        $this->db->join("collectingevent ce", "ce.CollectingEventID=co.CollectingEventID", "left");
        $this->db->join("locality l", "l.LocalityID=ce.LocalityID", "left");
        $this->db->join("geography g", "l.GeographyID=g.GeographyID", "left");
        $this->db->join("agent a", "a.AgentID=co.CreatedByAgentID");
        $this->db->join("agent aa", "aa.AgentID=co.ModifiedByAgentID");
        $this->db->where("l.GeographyID=31752", FALSE, FALSE);

        if ($this->collids)
            $this->db->where_in("co.CollectionObjectID", $this->collids);
        
        if ($startdate)
            $this->db->where("DATE(co.TimestampCreated)>='$startdate'", FALSE, FALSE);
        
        if ($userid)
            $this->db->where("co.CreatedByAgentID", $userid);
        
        if ($recordset)
            $this->db->where_in('co.CollectionObjectID', $recordset);

        $query = $this->db->get();
        if ($query->num_rows()) {
            return $query->result_array();
        }
        else
            return false;
    }
    
    /** Looks for georeferenced locality records that are missing the 
         *  geocode source, or a coded precision value. 
         */    
    public function missingSourceOrPrecision($startdate, $enddate=FALSE, $userid=FALSE) {
        $ret = array();
        $this->db->select("co.CollectionObjectID,co.CatalogNumber,CONCAT(a.FirstName,' ',a.LastName) AS CreatedBy,DATE(co.TimestampCreated) AS Created,CONCAT(aa.FirstName,' ',aa.LastName) AS EditedBy,DATE(co.TimestampModified) AS Edited", FALSE);
        $this->db->from("collectionobject co");
        $this->db->join("collectingevent ce", "ce.CollectingEventID=co.CollectingEventID", "left");
        $this->db->join("locality l", "l.LocalityID=ce.LocalityID", "left");
        $this->db->join("agent a", "a.AgentID=co.CreatedByAgentID");
        $this->db->join("agent aa", "aa.AgentID=co.ModifiedByAgentID");
        $this->db->where("co.CollectionMemberID", 4);
        $this->db->where("Latitude1 IS NOT NULL AND ((l.Text2 IS NULL AND DATE(co.TimestampCreated)>'2013-10-12') OR OriginalElevationUnit IS NULL) AND DATE(co.TimestampCreated)>='$startdate'", FALSE, FALSE);

        /*
        $this->db->select("co.CollectionObjectID,co.CatalogNumber,CONCAT(a.FirstName,' ',a.LastName) AS CreatedBy,DATE(co.TimestampCreated) AS Created,CONCAT(aa.FirstName,' ',aa.LastName) AS EditedBy,DATE(co.TimestampModified) AS Edited", FALSE);
        $this->db->from("collectionobject co");
        $this->db->join("collection coll", "co.CollectionID=coll.CollectionID AND coll.CollectionID=4");
        $this->db->join("collectingevent ce", "ce.CollectingEventID=co.CollectingEventID", "left");
        $this->db->join("locality l", "l.LocalityID=ce.LocalityID", "left");
        $this->db->join("agent a", "a.AgentID=co.CreatedByAgentID");
        $this->db->join("agent aa", "aa.AgentID=co.ModifiedByAgentID");
        $this->db->where("Latitude1 IS NOT NULL AND ((l.Text2 IS NULL AND DATE(co.TimestampCreated) > '2013-10-12') OR OriginalElevationUnit IS NULL) AND DATE(co.TimestampCreated)>='$startdate'", FALSE, FALSE);
         */
        if ($this->collids)
            $this->db->where_in("co.CollectionObjectID", $this->collids);

        if ($userid)
            $this->db->where("co.CreatedByAgentID", $userid);

        $query = $this->db->get();
        if ($query->num_rows()) {
            return $query->result_array();
        }
        else
            return false;
    }

        /** Looks for collection object records that are not linked to a
         *  determination record.
         */    
    public function missingDetermination($startdate, $enddate=FALSE, $userid=FALSE) {
        $ret = array();
        $this->db->select("co.CollectionObjectID,co.CatalogNumber,CONCAT(a.FirstName,' ',a.LastName) AS CreatedBy,DATE(co.TimestampCreated) AS Created,CONCAT(aa.FirstName,' ',aa.LastName) AS EditedBy,DATE(co.TimestampModified) AS Edited", FALSE);
        $this->db->from("collectionobject co");
        $this->db->join("determination d", "co.CollectionObjectID=d.CollectionObjectID", "left");
        $this->db->join("agent a", "a.AgentID=co.CreatedByAgentID");
        $this->db->join("agent aa", "aa.AgentID=co.ModifiedByAgentID");
        $this->db->where("co.CollectionMemberID", 4);
        $this->db->where("d.DeterminationID IS NULL AND DATE(co.TimestampCreated)>='$startdate'", FALSE, FALSE);

        if ($this->collids)
            $this->db->where_in("co.CollectionObjectID", $this->collids);

        if ($userid)
            $this->db->where("co.CreatedByAgentID", $userid);

        $query = $this->db->get();
        if ($query->num_rows()) {
            return $query->result_array();
        }
        else
            return false;
    }

        /** Looks for determination records that have the 'Stored under this
         *  name' field flagged (which should be only flagged for types), in a
         *  determination record that is not a 'Type status' determination.  
         */
    public function typeMismatch($startdate, $enddate=FALSE, $userid=FALSE) {
        $ret = array();
        $this->db->select("co.CollectionObjectID,co.CatalogNumber,CONCAT(a.FirstName,' ',a.LastName) AS CreatedBy,DATE(co.TimestampCreated) AS Created,CONCAT(aa.FirstName,' ',aa.LastName) AS EditedBy,DATE(co.TimestampModified) AS Edited", FALSE);
        $this->db->from("collectionobject co");
        $this->db->join("determination d", "co.CollectionObjectID=d.CollectionObjectID", "left");
        $this->db->join("agent a", "a.AgentID=co.CreatedByAgentID");
        $this->db->join("agent aa", "aa.AgentID=co.ModifiedByAgentID");
        $this->db->where("co.CollectionMemberID", 4);
        $this->db->where("d.FeatureOrBasis !='Type status' AND d.YesNo1=1 AND DATE(co.TimestampModified)>='$startdate'", FALSE, FALSE);

        if ($this->collids)
            $this->db->where_in("co.CollectionObjectID", $this->collids);

        if ($userid)
            $this->db->where("co.ModifiedByAgentID", $userid);

        $query = $this->db->get();
        if ($query->num_rows()) {
            return $query->result_array();
        }
        else
            return false;
    }

        /** Looks for records that do not have any collectors recorded, and 
         *  do not indicate that the collector(s) is unknown or illegible.
         */
        public function missingCollectors($startdate, $enddate=FALSE, $userid=FALSE) {
        $ret = array();
        $this->db->select("co.CollectionObjectID,co.CatalogNumber,CONCAT(a.FirstName,' ',a.LastName) AS CreatedBy,DATE(co.TimestampCreated) AS Created,CONCAT(aa.FirstName,' ',aa.LastName) AS EditedBy,DATE(co.TimestampModified) AS Edited", FALSE);
        $this->db->from("collectionobject co");
        $this->db->join("collectingevent ce", "ce.CollectingEventID=co.CollectingEventID");
        $this->db->join("collector col", "ce.CollectingEventID=col.CollectingEventID", "left");
        $this->db->join("collectingeventattribute cea", "ce.CollectingEventAttributeID=cea.CollectingEventAttributeID", "left");
        $this->db->join("agent a", "a.AgentID=co.CreatedByAgentID");
        $this->db->join("agent aa", "aa.AgentID=co.ModifiedByAgentID");
        $this->db->where("co.CollectionMemberID", 4);
        $this->db->where("DATE(co.TimestampCreated)>= '$startdate' AND col.CollectingEventID IS NULL AND (cea.Text1 IS NULL OR cea.Text1='') AND (cea.YesNo3 IS NULL OR cea.YesNo3 = 0) AND (cea.YesNo4 IS NULL OR cea.YesNo4 = 0)", FALSE, FALSE);
        
        if ($this->collids)
            $this->db->where_in("co.CollectionObjectID", $this->collids);

        if ($userid)
            $this->db->where("co.CreatedByAgentID", $userid);

        $query = $this->db->get();
        if ($query->num_rows()) {
            return $query->result_array();
        }
        else
            return false;
    }
  
    /** Looks for records that have group agents as the collector.
         */
        public function groupCollectors($startdate, $enddate=FALSE, $userid=FALSE) {
        $ret = array();
        $this->db->select("co.CollectionObjectID,co.CatalogNumber,CONCAT(a.FirstName,' ',a.LastName) AS CreatedBy,DATE(co.TimestampCreated) AS Created,CONCAT(aa.FirstName,' ',aa.LastName) AS EditedBy,DATE(co.TimestampModified) AS Edited", FALSE);
        $this->db->from("collectionobject co");
        $this->db->join("collection coll", "co.CollectionID=coll.CollectionID AND coll.CollectionID=4");
        $this->db->join("collectingevent ce", "ce.CollectingEventID=co.CollectingEventID");
        $this->db->join("collector col", "ce.CollectingEventID=col.CollectingEventID", "left");
        $this->db->join("agent a", "a.AgentID=co.CreatedByAgentID");
        $this->db->join("agent aa", "aa.AgentID=co.ModifiedByAgentID");
        $this->db->join("agent aaa", "aaa.AgentID=col.AgentID");
        $this->db->where("DATE(co.TimestampCreated)>= '$startdate' AND aaa.AgentType=3", FALSE, FALSE);
        
        if ($this->collids)
            $this->db->where_in("co.CollectionObjectID", $this->collids);

        if ($userid)
            $this->db->where("co.CreatedByAgentID", $userid);

        $query = $this->db->get();
        if ($query->num_rows()) {
            return $query->result_array();
        }
        else
            return false;
    }
    
        /** Looks for type status determinations that are flagged as the 
         *  current determination (there needs to be a separate determination
         *  record for the current determination, even if it is the same as the 
         *  typified name.
         */
    public function typeDetIsCurrent($startdate, $enddate=FALSE, $userid=FALSE) {
        $ret = array();
        $this->db->select("co.CollectionObjectID,co.CatalogNumber,CONCAT(a.FirstName,' ',a.LastName) AS CreatedBy,DATE(co.TimestampCreated) AS Created,CONCAT(aa.FirstName,' ',aa.LastName) AS EditedBy,DATE(co.TimestampModified) AS Edited", FALSE);
        $this->db->from("collectionobject co");
        $this->db->join("determination d", "co.CollectionObjectID=d.CollectionObjectID", "left");
        $this->db->join("agent a", "a.AgentID=co.CreatedByAgentID");
        $this->db->join("agent aa", "aa.AgentID=co.ModifiedByAgentID");
        $this->db->where("co.CollectionMemberID", 4);
        $this->db->where("d.FeatureOrBasis ='Type status' AND d.IsCurrent =1 AND DATE(co.TimestampModified)>='$startdate'", FALSE, FALSE);

        if ($this->collids)
            $this->db->where_in("co.CollectionObjectID", $this->collids);

        if ($userid)
            $this->db->where("co.ModifiedByAgentID", $userid);

        $query = $this->db->get();
        if ($query->num_rows()) {
            return $query->result_array();
        }
        else
            return false;
    }
    
        /** Looks for determination records flagged as current that have 
         *  something in the 'Alternative name' field (which should only
         *  be used for non-current determinations.
         */
    public function alternativeNameInCurrentDetermination($startdate, $enddate=FALSE, $userid=FALSE) {
        $ret = array();
        $this->db->select("co.CollectionObjectID,co.CatalogNumber,CONCAT(a.FirstName,' ',a.LastName) AS CreatedBy,DATE(d.TimestampCreated) AS Created,CONCAT(aa.FirstName,' ',aa.LastName) AS EditedBy,DATE(d.TimestampModified) AS Edited", FALSE);
        $this->db->from("collectionobject co");
        $this->db->join("determination d", "co.CollectionObjectID=d.CollectionObjectID AND d.IsCurrent=1");
        $this->db->join("agent a", "a.AgentID=d.CreatedByAgentID");
        $this->db->join("agent aa", "aa.AgentID=d.ModifiedByAgentID");
        $this->db->where("co.CollectionMemberID", 4);
        $this->db->where("d.AlternateName IS NOT NULL AND d.AlternateName !=''", FALSE, FALSE);
        $this->db->where("DATE(d.TimestampCreated) >='$startdate'", FALSE, FALSE);
        
        if ($userid)
            $this->db->where("d.CreatedByAgentID", $userid);

        $query = $this->db->get();
        if ($query->num_rows()) {
            return $query->result_array();
        }
        else
            return false;
    }

        /** Looks for records of types where the current determination is
         *  indeterminate.
         */
    public function typeDetOverriddenByIndet($startdate, $enddate=FALSE, $userid=FALSE) {
        $this->db->select("co.CollectionObjectID,co.CatalogNumber,CONCAT(a.FirstName,' ',a.LastName) AS CreatedBy,DATE(co.TimestampCreated) AS Created,CONCAT(aa.FirstName,' ',aa.LastName) AS EditedBy,DATE(co.TimestampModified) AS Edited", FALSE);
        $this->db->from("collectionobject co");
        $this->db->join("determination ty", "co.CollectionObjectID=ty.CollectionObjectID AND ty.YesNo1=1");
        $this->db->join("determination d", "co.CollectionObjectID=d.CollectionObjectID AND d.IsCurrent=1");
        $this->db->join("taxon t", "d.TaxonID=t.TaxonID");
        $this->db->join("agent a", "a.AgentID=co.CreatedByAgentID");
        $this->db->join("agent aa", "aa.AgentID=co.ModifiedByAgentID");
        $this->db->where("co.CollectionMemberID", 4);
        $this->db->where("t.RankID<220 AND DATE(co.TimestampModified)>='$startdate'", FALSE, FALSE);
        
        if ($this->collids)
            $this->db->where_in("co.CollectionObjectID", $this->collids);

        if ($userid)
            $this->db->where("co.ModifiedByAgentID", $userid);

        $query = $this->db->get();
        if ($query->num_rows()) {
            return $query->result_array();
        }
        else
            return false;
    }

        /** Looks for determination records that are missing a taxon name.
         */
    public function missingTaxonName($startdate, $enddate=FALSE, $userid=FALSE, $recordset=FALSE) {
        $ret = array();
        $this->db->select("co.CollectionObjectID,co.CatalogNumber,CONCAT(a.FirstName,' ',a.LastName) AS CreatedBy,DATE(d.TimestampCreated) AS Created,CONCAT(aa.FirstName,' ',aa.LastName) AS EditedBy,DATE(co.TimestampModified) AS Edited", FALSE);
        $this->db->from("collectionobject co");
        $this->db->join("determination d", "co.CollectionObjectID=d.CollectionObjectID");
        $this->db->join("agent a", "a.AgentID=d.CreatedByAgentID");
        $this->db->join("agent aa", "aa.AgentID=d.ModifiedByAgentID");
        $this->db->where("co.CollectionMemberID", 4);
        $this->db->where("d.TaxonID IS NULL AND d.AlternateName IS NULL", FALSE, FALSE);
        
        if ($this->collids)
            $this->db->where_in("co.CollectionObjectID", $this->collids);

        if ($startdate) 
            $this->db->where("DATE(d.TimestampCreated)>='$startdate'", FALSE, FALSE);

        if ($userid)
            $this->db->where("d.CreatedByAgentID", $userid);
        
        if ($recordset)
            $this->db->where_in('co.CollectionObjectID', $recordset);

        $query = $this->db->get();
        if ($query->num_rows()) {
            return $query->result_array();
        }
        else
            return false;
    }

        /** Looks for new taxon name records that have been added at the rank 
         *  of subgenus, and are possibly supposed to be species.
         */
    public function newSubgenus($startdate, $enddate=FALSE, $userid=FALSE) {
        $ret = array();
        $this->db->select("t.FullName,CONCAT(a.FirstName,' ',a.LastName) AS CreatedBy,DATE(t.TimestampCreated) AS Created,CONCAT(aa.FirstName,' ',aa.LastName) AS EditedBy,DATE(t.TimestampModified) AS Edited", FALSE);
        $this->db->from("taxon t");
        $this->db->join("agent a", "a.AgentID=t.CreatedByAgentID");
        $this->db->join("agent aa", "aa.AgentID=t.ModifiedByAgentID");
        if ($userid)
            $this->db->where("t.CreatedByAgentID", $userid);
        $this->db->where("DATE(t.TimestampCreated) >='$startdate'", FALSE, FALSE);
        $this->db->where("t.RankID", 190);

        $query = $this->db->get();
        if ($query->num_rows()) {
            return $query->result_array();
        }
        else
            return false;
    }

        /** Looks for new taxon name records that are missing the author.
         */
    public function missingAuthor($startdate, $enddate=FALSE, $userid=FALSE) {
        $ret = array();
        $this->db->select("t.FullName,CONCAT(a.FirstName,' ',a.LastName) AS CreatedBy,DATE(t.TimestampCreated) AS Created,CONCAT(aa.FirstName,' ',aa.LastName) AS EditedBy,DATE(t.TimestampModified) AS Edited", FALSE);
        $this->db->from("taxon t");
        $this->db->join("agent a", "a.AgentID=t.CreatedByAgentID");
        $this->db->join("agent aa", "aa.AgentID=t.ModifiedByAgentID");
        if ($userid)
            $this->db->where("t.CreatedByAgentID", $userid);
        $this->db->where("DATE(t.TimestampCreated) >='$startdate'", FALSE, FALSE);
        $this->db->where("t.RankID > 180", FALSE, FALSE);
        $this->db->where("t.Name NOT LIKE '%sp.%' AND t.NcbiTaxonNumber IS NULL AND IsHybrid IS NULL", FALSE, FALSE, FALSE);
        $this->db->where("t.Author IS NULL", FALSE, FALSE);


        if ($userid)
            $this->db->where("t.CreatedByAgentID", $userid);

        $query = $this->db->get();
        if ($query->num_rows()) {
            return $query->result_array();
        }
        else
            return false;
    }

            /** Looks for records where the date of determination is earlier than
         *  the date of collection.
         */
    public function detDateEarlierThanCollDate($startdate, $enddate=FALSE, $userid=FALSE) {
        $ret = array();
        $this->db->select("co.CollectionObjectID,co.CatalogNumber,CONCAT(a.FirstName,' ',a.LastName) AS CreatedBy,DATE(co.TimestampCreated) AS Created,CONCAT(aa.FirstName,' ',aa.LastName) AS EditedBy,DATE(co.TimestampModified) AS Edited", FALSE);
        $this->db->select("d.DeterminedDate, d.DeterminedDatePrecision, ce.StartDate, ce.StartDatePrecision");
        $this->db->from("collectionobject co");
        $this->db->join("determination d", "co.CollectionObjectID=d.CollectionObjectID");
        $this->db->join("collectingevent ce", "ce.CollectingEventID=co.CollectingEventID");
        $this->db->join("agent a", "a.AgentID=co.CreatedByAgentID");
        $this->db->join("agent aa", "aa.AgentID=co.ModifiedByAgentID");
        $this->db->where("co.CollectionMemberID", 4);
        if ($userid)
            $this->db->where("co.CreatedByAgentID", $userid);
        $this->db->where("DATE(co.TimestampCreated) >='$startdate'", FALSE, FALSE);
        
        if ($this->collids)
            $this->db->where_in("co.CollectionObjectID", $this->collids);

        /* $this->db->where("((d.DeterminedDatePrecision=1 AND d.DeterminedDate<ce.StartDate) OR
          (d.DeterminedDatePrecision=2 AND LEFT(d.DeterminedDate, 7)<LEFT(ce.StartDate, 7)) OR
          (d.DeterminedDatePrecision=3 AND YEAR(d.DeterminedDate)<YEAR(ce.StartDate)));",
          FALSE, FALSE); */



        $query = $this->db->get();
        if ($query->num_rows()) {
            $ret = array();
            foreach ($query->result() as $row) {
                $rec = array(
                    'CollectionObjectID' => $row->CollectionObjectID,
                    'CatalogNumber' => $row->CatalogNumber,
                    'CreatedBy' => $row->CreatedBy,
                    'Created' => $row->Created,
                    'EditedBy' => $row->EditedBy,
                    'Edited' => $row->Edited,
                );
                if ($row->DeterminedDate && $row->DeterminedDatePrecision == 1 && $row->DeterminedDate < $row->StartDate)
                    $ret[] = $rec;

                elseif ($row->DeterminedDate && $row->DeterminedDatePrecision == 2 && substr($row->DeterminedDate, 0, 7) < substr($row->StartDate, 0, 7))
                    $ret[] = $rec;
                elseif ($row->DeterminedDate && $row->DeterminedDatePrecision == 3 && substr($row->DeterminedDate, 0, 4) < substr($row->StartDate, 0, 4))
                    $ret[] = $rec;
            }
            return $ret;
            //return $query->result_array();
        }
        else
            return false;
    }

        /** Looks for taxon records for names of which we hold type specimens, 
         *  but which are missing protologue details.
         */
    public function missingProtologue($startdate, $enddate=FALSE, $userid=FALSE) {
        $ret = array();
        $this->db->select("co.CollectionObjectID,co.CatalogNumber,CONCAT(a.FirstName,' ',a.LastName) AS CreatedBy,DATE(d.TimestampCreated) AS Created,CONCAT(aa.FirstName,' ',aa.LastName) AS EditedBy,DATE(d.TimestampModified) AS Edited", FALSE);
        $this->db->from("collectionobject co");
        $this->db->join("determination d", "co.CollectionObjectID=d.CollectionObjectID AND d.FeatureOrBasis='Type status'");
        $this->db->join("taxon t", "d.TaxonID=t.TaxonID");
        $this->db->join("agent a", "a.AgentID=d.CreatedByAgentID");
        $this->db->join("agent aa", "aa.AgentID=d.ModifiedByAgentID");
        $this->db->where("co.CollectionMemberID", 4);
        $this->db->where("t.CommonName IS NULL AND NcbiTaxonNumber IS NULL AND DATE(co.TimestampCreated)>='$startdate'", FALSE, FALSE, FALSE);

        if ($this->collids)
            $this->db->where_in("co.CollectionObjectID", $this->collids);

        if ($userid)
            $this->db->where("co.ModifiedByAgentID", $userid);

        $query = $this->db->get();
        if ($query->num_rows()) {
            return $query->result_array();
        }
        else
            return false;
    }

        /** Looks for records that have the list of herbaria to which duplicates
         *  have been sent recorded in the wrong preparation (e.g. in the 
         *  primary preparation, not in the Duplicate preparation).
         */
    public function duplicateHerbariaInWrongPreparation($startdate, $enddate=FALSE, $userid=FALSE) {
        $ret = array();
        $this->db->select("co.CollectionObjectID,co.CatalogNumber,CONCAT(a.FirstName,' ',a.LastName) AS CreatedBy,DATE(co.TimestampCreated) AS Created,CONCAT(aa.FirstName,' ',aa.LastName) AS EditedBy,DATE(co.TimestampModified) AS Edited", FALSE);
        $this->db->from("collectionobject co");
        $this->db->join("preparation p", "co.CollectionObjectID=p.CollectionObjectID");
        $this->db->join("preptype pt", "p.PrepTypeID=pt.PrepTypeID");
        $this->db->join("agent a", "a.AgentID=co.CreatedByAgentID");
        $this->db->join("agent aa", "aa.AgentID=co.ModifiedByAgentID");
        $this->db->where("co.CollectionMemberID", 4);
        $this->db->where("p.PrepTypeID NOT IN (15,16,17) AND (p.Text1 IS NOT NULL AND p.Text1 !='')
            AND DATE(co.TimestampModified)>='$startdate'", FALSE, FALSE);

        if ($this->collids)
            $this->db->where_in("co.CollectionObjectID", $this->collids);
    
        if ($userid)
            $this->db->where("(co.CreatedByAgentID=$userid OR co.ModifiedByAgentID=$userid)", FALSE, FALSE);
        $query = $this->db->get();
        if ($query->num_rows()) {
            return $query->result_array();
        }
        else
            return false;
    }

        /** Looks for Duplicate preparation records where the quantity of 
         *  duplicate preparations does not equal the number of herbarium codes
         *  listed in the 'MEL duplicates at' field (calculated as the 
         *  number of commas in the 'MEL duplicates at' field, minus one).
         */
    public function duplicateCountMismatch($startdate, $enddate=FALSE, $userid=FALSE) {
        $ret = array();
        $this->db->select("co.CollectionObjectID,co.CatalogNumber,CONCAT(a.FirstName,' ',a.LastName) AS CreatedBy,DATE(co.TimestampCreated) AS Created,CONCAT(aa.FirstName,' ',aa.LastName) AS EditedBy,DATE(co.TimestampModified) AS Edited", FALSE);
        $this->db->from("collectionobject co");
        $this->db->join("preparation p", "co.CollectionObjectID=p.CollectionObjectID");
        $this->db->join("preptype pt", "p.PrepTypeID=pt.PrepTypeID");
        $this->db->join("agent a", "a.AgentID=co.CreatedByAgentID");
        $this->db->join("agent aa", "aa.AgentID=co.ModifiedByAgentID");
        $this->db->where("co.CollectionMemberID", 4);
        $this->db->where("LENGTH(p.Text1) - LENGTH(REPLACE(p.Text1, ',', '')) != p.CountAmt-1
            AND DATE(co.TimestampModified)>='$startdate'", FALSE, FALSE);

        if ($this->collids)
            $this->db->where_in("co.CollectionObjectID", $this->collids);

        if ($userid)
            $this->db->where("(co.CreatedByAgentID=$userid OR co.ModifiedByAgentID=$userid)", FALSE, FALSE);

        $query = $this->db->get();
        if ($query->num_rows()) {
            return $query->result_array();
        }
        else
            return false;
    }

        /** Looks for preparation records that have something in the 
         *  storage number field that shouldn't be there (e.g. a storage number
         *  in a Sheet, Packet or Carpological preparation).
         */
    public function somethingInNumberThatShouldntBeThere($startdate, $enddate=FALSE, $userid=FALSE) {
        $ret = array();
        $this->db->select("co.CollectionObjectID,co.CatalogNumber,CONCAT(a.FirstName,' ',a.LastName) AS CreatedBy,DATE(co.TimestampCreated) AS Created,CONCAT(aa.FirstName,' ',aa.LastName) AS EditedBy,DATE(co.TimestampModified) AS Edited", FALSE);
        $this->db->from("collectionobject co");
        $this->db->join("preparation p", "co.CollectionObjectID=p.CollectionObjectID");
        $this->db->join("preptype pt", "p.PrepTypeID=pt.PrepTypeID");
        $this->db->join("agent a", "a.AgentID=co.CreatedByAgentID");
        $this->db->join("agent aa", "aa.AgentID=co.ModifiedByAgentID");
        $this->db->where("co.CollectionMemberID", 4);
        $this->db->where("p.PrepTypeID IN (1,3,4,8,10,12,13,14,15,16,17,24) AND !(p.SampleNumber IS NULL OR p.SampleNumber='')
            AND DATE(co.TimestampModified)>='$startdate'", FALSE, FALSE);

        if ($this->collids)
            $this->db->where_in("co.CollectionObjectID", $this->collids);

        if ($userid)
            $this->db->where("(co.CreatedByAgentID=$userid OR co.ModifiedByAgentID=$userid)", FALSE, FALSE);

        $query = $this->db->get();
        if ($query->num_rows()) {
            return $query->result_array();
        }
        else
            return false;
    }

        /** Looks for preparation records that don't have anything in the 
         *  storage number field, but should have (e.g. no storage number
         *  in a Spirit, Silica gel sample or Microscope slide preparation).
         */
    public function somethingMissingFromNumberField($startdate, $enddate=FALSE, $userid=FALSE) {
        $ret = array();
        $this->db->select("co.CollectionObjectID,co.CatalogNumber,CONCAT(a.FirstName,' ',a.LastName) AS CreatedBy,DATE(co.TimestampCreated) AS Created,CONCAT(aa.FirstName,' ',aa.LastName) AS EditedBy,DATE(co.TimestampModified) AS Edited", FALSE);
        $this->db->from("collectionobject co");
        $this->db->join("preparation p", "co.CollectionObjectID=p.CollectionObjectID");
        $this->db->join("preptype pt", "p.PrepTypeID=pt.PrepTypeID");
        $this->db->join("agent a", "a.AgentID=co.CreatedByAgentID");
        $this->db->join("agent aa", "aa.AgentID=co.ModifiedByAgentID");
        $this->db->where("co.CollectionMemberID", 4);
        $this->db->where("p.PrepTypeID NOT IN (1,3,4,5,8,10,12,13,14,15,16,17,24) AND (p.SampleNumber IS NULL OR p.SampleNumber='')
            AND DATE(co.TimestampModified)>='$startdate'", FALSE, FALSE);

        if ($this->collids)
            $this->db->where_in("co.CollectionObjectID", $this->collids);

        if ($userid)
            $this->db->where("(co.CreatedByAgentID=$userid OR co.ModifiedByAgentID=$userid)", FALSE, FALSE);

        $query = $this->db->get();
        if ($query->num_rows()) {
            return $query->result_array();
        }
        else
            return false;
    }

        /** Looks for records for which none of the collectors are flagged
         *  as being the primary collector(s).
         */
    public function missingPrimaryCollectors($startdate, $enddate=FALSE, $userid=FALSE) {
        /*
          SELECT co.CollectionObjectID, co.CatalogNumber, CONCAT(a.FirstName, ' ', a.LastName) AS CreatedBy, DATE(co.TimestampCreated) AS Created, CONCAT(aa.FirstName, ' ', aa.LastName) AS EditedBy, DATE(co.TimestampModified) AS Edited
          FROM (collectionobject co)
          JOIN collectingevent ce ON ce.CollectingEventID=co.CollectingEventID
          JOIN collector c ON ce.CollectingEventID=c.CollectingEventID
          JOIN agent a ON a.AgentID=co.CreatedByAgentID
          JOIN agent aa ON aa.AgentID=co.ModifiedByAgentID
          WHERE DATE(co.TimestampCreated)>= '2012-01-01'
          GROUP BY co.CollectionObjectID
          HAVING SUM(IsPrimary)=0;
         */
        $this->db->select("co.CollectionObjectID, co.CatalogNumber, CONCAT(a.FirstName,' ',a.LastName) AS CreatedBy,
            DATE(co.TimestampCreated) AS Created, CONCAT(aa.FirstName,' ',aa.LastName) AS EditedBy,
            DATE(co.TimestampModified) AS Edited", FALSE);
        $this->db->from('collectionobject co');
        $this->db->join('collectingevent ce', 'ce.CollectingEventID=co.CollectingEventID');
        $this->db->join('collector c', 'ce.CollectingEventID=c.CollectingEventID');
        $this->db->join('agent a', 'a.AgentID=co.CreatedByAgentID');
        $this->db->join('agent aa', 'aa.AgentID=co.ModifiedByAgentID');
        $this->db->where("co.CollectionMemberID", 4);
        $this->db->where("co.TimestampCreated > '$startdate'", FALSE, FALSE);
        $this->db->group_by('co.CollectionObjectID');
        $this->db->having('SUM(IsPrimary)=0', FALSE);

        if ($this->collids)
            $this->db->where_in("co.CollectionObjectID", $this->collids);

        if ($userid)
            $this->db->where("co.CreatedByAgentID", $userid);

        $query = $this->db->get();
        if ($query->num_rows()) {
            return $query->result_array();
        }
        else
            return false;
    }

        /** Looks for records that have the 'Introduced status' field filled
         *  in, but don't indicate the source of the introduced status.
         */
    public function missingIntroSource($startdate, $enddate=FALSE, $userid=FALSE) {
        $ret = array();
        $this->db->select("co.CollectionObjectID,co.CatalogNumber,CONCAT(a.FirstName,' ',a.LastName) AS CreatedBy,DATE(co.TimestampCreated) AS Created,CONCAT(aa.FirstName,' ',aa.LastName) AS EditedBy,DATE(co.TimestampModified) AS Edited", FALSE);
        $this->db->from("collectionobject co");
        $this->db->join("collectingevent ce", "ce.CollectingEventID=co.CollectingEventID");
        $this->db->join("collectingeventattribute cea", "ce.CollectingEventAttributeID=cea.CollectingEventAttributeID", "left");
        $this->db->join("agent a", "a.AgentID=co.CreatedByAgentID");
        $this->db->join("agent aa", "aa.AgentID=co.ModifiedByAgentID");
        $this->db->where("co.CollectionMemberID", 4);
        $this->db->where("DATE(co.TimestampCreated)>= '$startdate'
AND cea.Text11 IS NOT NULL AND cea.Text12 IS NULL", FALSE, FALSE);

        if ($this->collids)
            $this->db->where_in("co.CollectionObjectID", $this->collids);

        if ($userid)
            $this->db->where("co.CreatedByAgentID", $userid);

        $query = $this->db->get();
        if ($query->num_rows()) {
            return $query->result_array();
        }
        else
            return false;
    }
    
        /** Looks for records that have the 'Cultivated status' field filled
         *  in, but don't indicate the source of the cultivated status.
         */
     public function missingCultSource($startdate, $enddate=FALSE, $userid=FALSE) {
        $ret = array();
        $this->db->select("co.CollectionObjectID,co.CatalogNumber,CONCAT(a.FirstName,' ',a.LastName) AS CreatedBy,DATE(co.TimestampCreated) AS Created,CONCAT(aa.FirstName,' ',aa.LastName) AS EditedBy,DATE(co.TimestampModified) AS Edited", FALSE);
        $this->db->from("collectionobject co");
        $this->db->join("collectingevent ce", "ce.CollectingEventID=co.CollectingEventID");
        $this->db->join("collector col", "ce.CollectingEventID=col.CollectingEventID", "left");
        $this->db->join("collectingeventattribute cea", "ce.CollectingEventAttributeID=cea.CollectingEventAttributeID", "left");
        $this->db->join("agent a", "a.AgentID=co.CreatedByAgentID");
        $this->db->join("agent aa", "aa.AgentID=co.ModifiedByAgentID");
        $this->db->where("co.CollectionMemberID", 4);
        $this->db->where("DATE(co.TimestampCreated)>= '$startdate'
AND cea.Text13 IS NOT NULL AND (cea.Text14 IS NULL OR cea.Text14='')", FALSE, FALSE);

        if ($this->collids)
            $this->db->where_in("co.CollectionObjectID", $this->collids);

        if ($userid)
            $this->db->where("co.CreatedByAgentID", $userid);

        $query = $this->db->get();
        if ($query->num_rows()) {
            return $query->result_array();
        }
        else
            return false;
    }
    
        /** Looks for records with multiple collectors for which the primary
         *  collector(s) has not been listed as the first collector.
         */
        public function primaryCollectorNotFirst($startdate, $enddate=FALSE, $userid=FALSE) {
        $ret = array();
        $this->db->select("co.CollectionObjectID,co.CatalogNumber,CONCAT(a.FirstName,' ',a.LastName) AS CreatedBy,DATE(co.TimestampCreated) AS Created,CONCAT(aa.FirstName,' ',aa.LastName) AS EditedBy,DATE(co.TimestampModified) AS Edited", FALSE);
        $this->db->from("collectionobject co");
        $this->db->join("collectingevent ce", "ce.CollectingEventID=co.CollectingEventID");
        $this->db->join("collector col", "ce.CollectingEventID=col.CollectingEventID", "left");
        $this->db->join("collectingeventattribute cea", "ce.CollectingEventAttributeID=cea.CollectingEventAttributeID", "left");
        $this->db->join("agent a", "a.AgentID=co.CreatedByAgentID");
        $this->db->join("agent aa", "aa.AgentID=co.ModifiedByAgentID");
        $this->db->where("co.CollectionMemberID", 4);
        $this->db->where("DATE(co.TimestampCreated)>= '$startdate'
AND col.OrderNumber = 0 AND col.IsPrimary !=1", FALSE, FALSE);

        if ($this->collids)
            $this->db->where_in("co.CollectionObjectID", $this->collids);

        if ($userid)
            $this->db->where("co.CreatedByAgentID", $userid);

        $query = $this->db->get();
        if ($query->num_rows()) {
            return $query->result_array();
        }
        else
            return false;
    }

        /** Looks for records that have an end date of collection recorded,
         *  but no start date.
         */
    public function endDateWithNoStartDate($startdate, $enddate=FALSE, $userid=FALSE) {
        $ret = array();
        $this->db->select("co.CollectionObjectID,co.CatalogNumber,CONCAT(a.FirstName,' ',a.LastName) AS CreatedBy,DATE(co.TimestampCreated) AS Created,
                        CONCAT(aa.FirstName,' ',aa.LastName) AS EditedBy,DATE(co.TimestampModified) AS Edited", FALSE);
        $this->db->from("collectionobject co");
        $this->db->join("collectingevent ce", "ce.CollectingEventID=co.CollectingEventID");
        $this->db->join("agent a", "a.AgentID=co.CreatedByAgentID");
        $this->db->join("agent aa", "aa.AgentID=co.ModifiedByAgentID");
        $this->db->where("co.CollectionMemberID", 4);
        $this->db->where("DATE(co.TimestampCreated)>= '$startdate'
                        AND ce.StartDate IS NULL AND ce.EndDate IS NOT NULL", FALSE, FALSE);

        if ($this->collids)
            $this->db->where_in("co.CollectionObjectID", $this->collids);
                
        if ($userid)
            $this->db->where("co.CreatedByAgentID", $userid);

        $query = $this->db->get();
        if ($query->num_rows()) {
            return $query->result_array();
        }
        else
            return false;
    }

        /** Looks for records that were databased by the person who collected
         *  the specimen. amd which don't have a collecting date recorded.
         */
        public function noCollectingDate($startdate, $enddate=FALSE, $userid=FALSE) {
        $ret = array();
        $this->db->select("co.CollectionObjectID,co.CatalogNumber,CONCAT(a.FirstName,' ',a.LastName) AS CreatedBy,DATE(co.TimestampCreated) AS Created,
                        CONCAT(aa.FirstName,' ',aa.LastName) AS EditedBy,DATE(co.TimestampModified) AS Edited", FALSE);
        $this->db->from("collectionobject co");
        $this->db->join("collectingevent ce", "ce.CollectingEventID=co.CollectingEventID");
        $this->db->join("collector col", "col.CollectingEventID=ce.CollectingEventID AND col.IsPrimary=1");
        $this->db->join("agent a", "a.AgentID=co.CreatedByAgentID");
        $this->db->join("agent aa", "aa.AgentID=co.ModifiedByAgentID");
        $this->db->where("co.CollectionMemberID", 4);
        $this->db->where("DATE(co.TimestampCreated)>= '$startdate'
                        AND ce.StartDate IS NULL AND col.AgentID=co.CreatedByAgentID", FALSE, FALSE);

        if ($this->collids)
            $this->db->where_in("co.CollectionObjectID", $this->collids);

        if ($userid)
            $this->db->where("co.CreatedByAgentID", $userid);

        $query = $this->db->get();
        if ($query->num_rows()) {
            return $query->result_array();
        }
        else
            return false;
    }
    
        /** Looks for records that have the geocode Protocol listed as 'GPS', 
         *  but which were collected before 1980 (and thus are unlikely to have
         *  been geocoded via GPS).
         */
    public function tooEarlyForGPS($startdate, $enddate=FALSE, $userid=FALSE) {
        $ret = array();
        $this->db->select("co.CollectionObjectID,co.CatalogNumber,CONCAT(a.FirstName,' ',a.LastName) AS CreatedBy,DATE(co.TimestampCreated) AS Created,CONCAT(aa.FirstName,' ',aa.LastName) AS EditedBy,DATE(co.TimestampModified) AS Edited", FALSE);
        $this->db->from("collectionobject co");
        $this->db->join("collectingevent ce", "ce.CollectingEventID=co.CollectingEventID", "left");
        $this->db->join("locality l", "l.LocalityID=ce.LocalityID", "left");
        $this->db->join("agent a", "a.AgentID=co.CreatedByAgentID");
        $this->db->join("agent aa", "aa.AgentID=co.ModifiedByAgentID");
        $this->db->where("co.CollectionMemberID", 4);
        $this->db->where("LatLongMethod=4 AND StartDate<'1980-01-01' AND DATE(co.TimestampCreated)>='$startdate'", FALSE, FALSE);

        if ($this->collids)
            $this->db->where_in("co.CollectionObjectID", $this->collids);

        if ($userid)
            $this->db->where("co.CreatedByAgentID", $userid);

        $query = $this->db->get();
        if ($query->num_rows()) {
            return $query->result_array();
        }
        else
            return false;
    }
    
        /** Looks for records that were databased by the person who collected
         *  the specimen, and were geocoded by GPS, but are missing the
         *  datum.
         */
        public function missingDatum($startdate, $enddate=FALSE, $userid=FALSE) {
        $ret = array();
        $this->db->select("co.CollectionObjectID,co.CatalogNumber,CONCAT(a.FirstName,' ',a.LastName) AS CreatedBy,DATE(co.TimestampCreated) AS Created,CONCAT(aa.FirstName,' ',aa.LastName) AS Collector,StationFieldNumber", FALSE);
        $this->db->from("collectionobject co");
        $this->db->join("collectingevent ce", "ce.CollectingEventID=co.CollectingEventID", "left");
        $this->db->join("locality l", "l.LocalityID=ce.LocalityID", "left");
        $this->db->join("collector col", "col.CollectingEventID=ce.CollectingEventID AND col.IsPrimary=1");
        $this->db->join("agent a", "a.AgentID=co.CreatedByAgentID");
        $this->db->join("agent aa", "aa.AgentID=col.AgentID");
        $this->db->where("co.CollectionMemberID", 4);
        $this->db->where("LatLongMethod=4 AND Datum IS NULL AND DATE(co.TimestampCreated)>='$startdate'", FALSE, FALSE);
        $this->db->orderby("Collector, StationFieldNumber", FALSE);
        $this->db->where("co.CreatedByAgentID=col.AgentID", FALSE, FALSE);

        if ($this->collids)
            $this->db->where_in("co.CollectionObjectID", $this->collids);

        if ($userid)
            $this->db->where("co.CreatedByAgentID", $userid);

        $query = $this->db->get();
        if ($query->num_rows()) {
            return $query->result_array();
        }
        else
            return false;
    }

        /** Looks for group agent records that haven't had individual agents
         *  added to the group.
         */
    public function groupAgentsWithoutIndividuals($startdate, $enddate=FALSE, $userid=FALSE) {
        $ret = array();
        $this->db->select("a.AgentID,a.LastName,CONCAT(a2.FirstName,' ',a2.LastName) AS AgentCreatedBy,
            LEFT(a.TimestampCreated,10) AS AgentCreated,CONCAT(a3.FirstName,' ',a3.LastName) AS AgentEditedBy,LEFT(a.TimestampModified,10) AS AgentEdited");
        $this->db->from("agent a");
        $this->db->join("agent a2", "a.CreatedByAgentID=a2.AgentID");
        $this->db->join("agent a3", "a.ModifiedByAgentID=a3.AgentID");
        $this->db->join("groupperson gp", "a.AgentID=gp.GroupID", "left");
        $this->db->where("a.AgentType=3 AND gp.GroupPersonID IS NULL AND DATE(a.TimestampCreated)>='$startdate'", FALSE, FALSE);

        if ($userid)
            $this->db->where("a.CreatedByAgentID", $userid);

        $query = $this->db->get();
        if ($query->num_rows()) {
            return $query->result_array();
        }
        else
            return false;
    }

        /** Looks for agent records that are missing data in the 'Last name'
         *  field.
         */
     public function agentsWithNoLastName($startdate, $enddate=FALSE, $userid=FALSE) {
        $ret = array();
        $this->db->select("a.AgentID,a.FirstName,CONCAT(a2.FirstName,' ',a2.LastName) AS AgentCreatedBy,
            LEFT(a.TimestampCreated,10) AS AgentCreated,CONCAT(a3.FirstName,' ',a3.LastName) AS AgentEditedBy,LEFT(a.TimestampModified,10) AS AgentEdited,a.FirstName AS FirstName");
        $this->db->from("agent a");
        $this->db->join("agent a2", "a.CreatedByAgentID=a2.AgentID");
        $this->db->join("agent a3", "a.ModifiedByAgentID=a3.AgentID");
        $this->db->join("groupperson gp", "a.AgentID=gp.GroupID", "left");
        $this->db->where("a.LastName IS NULL AND DATE(a.TimestampCreated)>='$startdate'", FALSE, FALSE);

        if ($userid)
            $this->db->where("a.CreatedByAgentID", $userid);

        $query = $this->db->get();
        if ($query->num_rows()) {
            return $query->result_array();
        }
        else
            return false;
    }
    
            /** Looks for agent records that appear to be group agents, but have 
             * been entered as person agents.
         */
   public function groupAgentAsPersonAgent($startdate, $enddate=FALSE, $userid=FALSE) {
        $ret = array();
        $this->db->select("a.AgentID,a.LastName,CONCAT(a2.FirstName,' ',a2.LastName) AS AgentCreatedBy,
            LEFT(a.TimestampCreated,10) AS AgentCreated,CONCAT(a3.FirstName,' ',a3.LastName) AS AgentEditedBy,LEFT(a.TimestampModified,10) AS AgentEdited,a.LastName AS LastName");
        $this->db->from("agent a");
        $this->db->join("agent a2", "a.CreatedByAgentID=a2.AgentID");
        $this->db->join("agent a3", "a.ModifiedByAgentID=a3.AgentID");
        $this->db->where("a.LastName LIKE '%;%' AND a.AgentType=1 AND DATE(a.TimestampCreated)>='$startdate'", FALSE, FALSE);

        if ($userid)
            $this->db->where("a.CreatedByAgentID", $userid);

        $query = $this->db->get();
        if ($query->num_rows()) {
            return $query->result_array();
        }
        else
            return false;
    }
                /** Looks for agent incorrect or misinterpreted agents (i.e. 
                 * with [] in initials) that have been recorded as a collector.
         */
   public function incorrectAgentAsCollector ($startdate, $enddate=FALSE, $userid=FALSE) {
        $ret = array();
        $this->db->select("co.CollectionObjectID,co.CatalogNumber,CONCAT(a2.FirstName,' ',a2.LastName) AS CreatedBy,DATE(co.TimestampCreated) AS Created,CONCAT(a3.FirstName,' ',a3.LastName) AS EditedBy,DATE(co.TimestampModified) AS Edited", FALSE);
        $this->db->from("agent a");
        $this->db->join("collector col", "a.AgentID=col.AgentID");
        $this->db->join("collectingevent ce", "col.CollectingEventID=ce.CollectingEventID");
        $this->db->join("collectionobject co", "co.CollectingEventID=ce.CollectingEventID");   
        $this->db->join("agent a2", "co.CreatedByAgentID=a2.AgentID");
        $this->db->join("agent a3", "co.ModifiedByAgentID=a3.AgentID");
        $this->db->where("a.FirstName LIKE '%[%' AND DATE(co.TimestampCreated)>='$startdate'", FALSE, FALSE);

        if ($this->collids)
            $this->db->where_in("co.CollectionObjectID", $this->collids);

        if ($userid)
            $this->db->where("a.CreatedByAgentID", $userid);

        $query = $this->db->get();
        if ($query->num_rows()) {
            return $query->result_array();
        }
        else
            return false;
    }
        
                /** Looks for ConservEvent records edited by Nimal with 
                 * something in Treated by, but nothing in co.Curation sponsor
         */
   public function treatedByNotNullAndCurationSponsorNull ($startdate, $enddate=FALSE, $userid=FALSE) {
        $ret = array();
        $this->db->select("co.CollectionObjectID,co.CatalogNumber,CONCAT(a2.FirstName,' ',a2.LastName) AS CreatedBy,DATE(co.TimestampCreated) AS Created,CONCAT(a3.FirstName,' ',a3.LastName) AS EditedBy,DATE(co.TimestampModified) AS Edited", FALSE);
        $this->db->from("conservevent cone");
        $this->db->join("conservdescription cond", "cone.ConservDescriptionID=cond.ConservDescriptionID");
        $this->db->join("collectionobject co", "co.CollectionObjectID=cond.CollectionObjectID");   
        $this->db->join("collectionobjectattribute coa", "coa.CollectionObjectAttributeID=co.CollectionObjectAttributeID", "left");   
        $this->db->join("agent a2", "co.CreatedByAgentID=a2.AgentID");
        $this->db->join("agent a3", "co.ModifiedByAgentID=a3.AgentID");
        $this->db->where("cone.TreatedByAgentID IS NOT NULL AND coa.Text4 IS NULL AND cone.CreatedByAgentID=10624 AND DATE(co.TimestampModified)>='$startdate'", FALSE, FALSE);

        if ($userid)
            $this->db->where("cone.CreatedByAgentID", $userid);

        $query = $this->db->get();
        if ($query->num_rows()) {
            return $query->result_array();
        }
        else
            return false;
    }
                    /** Looks for ConservEvent records edited by Nimal with 
                     * something in Treated by, but nothing in Treatment report 
                     * or Treatment completed
         */
   public function treatedByNotNullOtherTreatmentFieldsNull ($startdate, $enddate=FALSE, $userid=FALSE) {
        $this->db->select("co.CollectionObjectID,co.CatalogNumber,CONCAT(a2.FirstName,' ',a2.LastName) AS CreatedBy,DATE(co.TimestampCreated) AS Created,CONCAT(a3.FirstName,' ',a3.LastName) AS EditedBy,DATE(co.TimestampModified) AS Edited", FALSE);
        $this->db->from("conservevent cone");
        $this->db->join("conservdescription cond", "cone.ConservDescriptionID=cond.ConservDescriptionID");
        $this->db->join("collectionobject co", "co.CollectionObjectID=cond.CollectionObjectID");   
        $this->db->join("agent a2", "co.CreatedByAgentID=a2.AgentID");
        $this->db->join("agent a3", "co.ModifiedByAgentID=a3.AgentID");
        $this->db->where("cone.TreatedByAgentID IS NOT NULL AND (cone.TreatmentCompDate IS NULL AND cone.TreatmentReport IS NULL) AND DATE(co.TimestampModified)>='$startdate' AND cone.CreatedByAgentID=10624", FALSE, FALSE);

        if ($userid)
            $this->db->where("cone.CreatedByAgentID", $userid);

        $query = $this->db->get();
        if ($query->num_rows()) {
            return $query->result_array();
        }
        else
            return false;
    }
                    /** Looks for ConservEvent records with something in 
                     * Severity or Cause of damage fields, but nothing 
                     * in Assessed by
         */
   public function severityOrCauseNotNullButAssessedByNull ($startdate, $enddate=FALSE, $userid=FALSE) {
        $this->db->select("co.CollectionObjectID,co.CatalogNumber,CONCAT(a2.FirstName,' ',a2.LastName) AS CreatedBy,DATE(co.TimestampCreated) AS Created,CONCAT(a3.FirstName,' ',a3.LastName) AS EditedBy,DATE(co.TimestampModified) AS Edited", FALSE);
        $this->db->from("conservevent cone");
        $this->db->join("conservdescription cond", "cone.ConservDescriptionID=cond.ConservDescriptionID");
        $this->db->join("collectionobject co", "co.CollectionObjectID=cond.CollectionObjectID");   
        $this->db->join("agent a2", "co.CreatedByAgentID=a2.AgentID");
        $this->db->join("agent a3", "co.ModifiedByAgentID=a3.AgentID");
        $this->db->where("cone.CuratorID IS NULL AND (cone.AdvTestingExamResults IS NOT NULL OR cone.ConditionReport IS NOT NULL) AND DATE(co.TimestampModified)>='$startdate'", FALSE, FALSE);

        if ($userid)
            $this->db->where("cone.CreatedByAgentID", $userid);

        $query = $this->db->get();
        if ($query->num_rows()) {
            return $query->result_array();
        }
        else
            return false;
    }

        /** Looks for locality records that are linked to more than one 
         *  collecting event record, so people can check that locality records
         *  were shared intentionally, and not accidentally.
         */
    public function sharedLocalities($startdate, $enddate=FALSE, $userid=FALSE) {
        $ret = array();
        $this->db->select("l.LocalityID,COUNT(*) AS LocCount", FALSE);
        $this->db->from("locality l");
        $this->db->join("collectingevent ce", "l.LocalityID=ce.LocalityID");
        $this->db->where("DATE(l.TimestampCreated)>='$startdate'", FALSE, FALSE);
        $this->db->group_by("l.LocalityID");
        $this->db->having("COUNT(*)>", 1, FALSE);


        $query = $this->db->get();
        if ($query->num_rows()) {
            foreach ($query->result_array() as $row) {
                $this->db->select("co.CollectionObjectID,co.CatalogNumber,
                    collectorstring(ce.CollectingEventID,1) AS PrimaryCollectors,ce.StationFieldNumber AS CollectingNo,ce.StartDate AS CollDate,p.Remarks AS Multisheets,
                    CONCAT(a2.FirstName,' ',a2.LastName) AS CreatedBy, DATE(co.TimestampCreated) AS Created", FALSE);
                $this->db->from("locality l");
                $this->db->join("collectingevent ce", "ce.LocalityID=l.LocalityID");
                $this->db->join("collectionobject co", "co.CollectingEventID=ce.CollectingEventID");
                $this->db->join("agent a2", "co.CreatedByAgentID=a2.AgentID", "left");
                $this->db->join("preparation p", "p.CollectionObjectID=co.CollectionObjectID");
                $this->db->where("l.LocalityID", $row['LocalityID']);
                $this->db->where("DATE(co.TimestampCreated)>='$startdate'", FALSE, FALSE);
                $this->db->group_by("co.CatalogNumber");

                if ($userid)
                    $this->db->where("co.CreatedByAgentID", $userid);

                $query2 = $this->db->get();
                if ($query2->num_rows) {
                    foreach ($query2->result_array() as $row2) {
                        $ret[] = array_merge($row, $row2);
                    }
                }
            }
            return $ret;
        }
        else
            return false;
    }

        /** Looks for collection object records where the Catalogue number is
         *  higher than the highest catalogue number assigned to a user in the
         *  MEL numbers module (and which must therefore be an error).
         */
    public function highCatalogueNumbers($startdate, $enddate=FALSE, $userid=FALSE) {
        $this->db->select("MAX(EndNumber) AS maxnumber", FALSE);
        $this->db->from("melnumbers");

        $maxnumber = $this->db->get();
        $maxnumber = $maxnumber->row();

        $maxnumber = $maxnumber->maxnumber;

        $this->db->select("co.CollectionObjectID,co.CatalogNumber,CONCAT(a.FirstName,' ',a.LastName) AS CreatedBy,DATE(co.TimestampCreated) AS Created,CONCAT(aa.FirstName,' ',aa.LastName) AS EditedBy,DATE(co.TimestampModified) AS Edited", FALSE);
        $this->db->from("collectionobject co");
        $this->db->join("agent a", "a.AgentID=co.CreatedByAgentID");
        $this->db->join("agent aa", "aa.AgentID=co.ModifiedByAgentID");
        $this->db->where("co.CollectionMemberID", 4);
        $this->db->where("LEFT(co.CatalogNumber,7)>", $maxnumber, FALSE);
        $this->db->where("co.TimestampCreated >", $startdate);

        if ($this->collids)
            $this->db->where_in("co.CollectionObjectID", $this->collids);

        if ($userid)
            $this->db->where("co.CreatedByAgentID", $userid);

        $query = $this->db->get();
        if ($query->num_rows()) {
            return $query->result_array();
        }
        else
            return false;
    }
    
        /** Looks for records where the last character in the catalogue number
         *  is not a letter.
         */
        public function dodgyPart($startdate, $enddate=FALSE, $userid=FALSE) {
        $ret = array();
        $this->db->select("co.CollectionObjectID,co.CatalogNumber,CONCAT(a2.FirstName,' ',a2.LastName) AS CreatedBy,
            LEFT(co.TimestampCreated,10) AS Created,CONCAT(a3.FirstName,' ',a3.LastName) AS EditedBy,LEFT(co.TimestampModified,10) AS Edited");
        $this->db->from("collectionobject co");
        $this->db->join("agent a2", "co.CreatedByAgentID=a2.AgentID");
        $this->db->join("agent a3", "co.ModifiedByAgentID=a3.AgentID");
        $this->db->where("co.CollectionMemberID", 4);
        $this->db->where("RIGHT(CatalogNumber,1) NOT REGEXP '[a-zA-Z]'", FALSE, FALSE);
        $this->db->where("DATE(co.TimestampModified)>=", $startdate);
        
        if ($this->collids)
            $this->db->where_in("co.CollectionObjectID", $this->collids);

        if ($userid)
            $this->db->where("co.CreatedByAgentID", $userid);

        $query = $this->db->get();
        if ($query->num_rows()) {
            return $query->result_array();
        }
        else
            return false;
    }
 
        /** Looks for records where the part is between F-Z, and is possibly
         *  an error.
         */
    public function possiblyDodgyPart($startdate, $enddate=FALSE, $userid=FALSE) {
        $ret = array();
        $this->db->select("co.CollectionObjectID,co.CatalogNumber,CONCAT(a2.FirstName,' ',a2.LastName) AS CreatedBy,
            LEFT(co.TimestampCreated,10) AS Created,CONCAT(a3.FirstName,' ',a3.LastName) AS EditedBy,LEFT(co.TimestampModified,10) AS Edited");
        $this->db->from("collectionobject co");
        $this->db->join("agent a2", "co.CreatedByAgentID=a2.AgentID");
        $this->db->join("agent a3", "co.ModifiedByAgentID=a3.AgentID");
        $this->db->where("co.CollectionMemberID", 4);
        $this->db->where("RIGHT(CatalogNumber,1) NOT REGEXP '[a-eA-E]'", FALSE, FALSE);
        $this->db->where("DATE(co.TimestampModified)>=", $startdate);

        if ($this->collids)
            $this->db->where_in("co.CollectionObjectID", $this->collids);

        if ($userid)
            $this->db->where("co.CreatedByAgentID", $userid);

        $query = $this->db->get();
        if ($query->num_rows()) {
            return $query->result_array();
        }
        else
            return false;
    }
    
        /** Looks for records where the 'Stored under this name' field is 
         *  flaged in more than one determination record.
         */
    public function storedUnderMultipleNames($startdate, $enddate=FALSE, $userid=FALSE) {
        $ret = array();
        $this->db->select('co.CollectionObjectID');
        $this->db->from('collectionobject co');
        $this->db->join('determination d', 'co.CollectionObjectID=d.CollectionObjectID AND d.YesNo1=1');
        $this->db->where("co.CollectionMemberID", 4);
        $this->db->where('co.TimestampModified >', $startdate);
        
        if ($this->collids)
            $this->db->where_in("co.CollectionObjectID", $this->collids);

        if ($enddate)
            $this->db->where('DATE(co.TimestampModified) <=', $enddate, FALSE);
        if ($userid)
            $this->db->where('co.ModifiedByAgentID', $userid);
        $this->db->group_by('co.CollectionObjectID');
        $this->db->having('count(*)>1');
        $init = $this->db->get();
        if ($init->num_rows()) {
            foreach ($init->result() as $initrow) {
                $this->db->select("co.CollectionObjectID,co.CatalogNumber,CONCAT(a2.FirstName,' ',a2.LastName) AS CreatedBy,
                    LEFT(d.TimestampCreated,10) AS Created,CONCAT(a3.FirstName,' ',a3.LastName) AS EditedBy,LEFT(d.TimestampModified,10) AS Edited");
                    $this->db->from("determination d");        
                    $this->db->join("collectionobject co", 'd.CollectionObjectID=co.CollectionObjectID');        
                    $this->db->join("agent a2", "d.CreatedByAgentID=a2.AgentID");
                    $this->db->join("agent a3", "d.ModifiedByAgentID=a3.AgentID");
                    $this->db->where('d.YesNo1', 'Y');
                    $this->db->where('d.CollectionObjectID', $initrow->CollectionObjectID);
                    $this->db->order_by('d.TimestampCreated', 'desc');
                    
                    $query = $this->db->get();
                    if ($query->num_rows())
                        $ret[] = $query->row_array();
            }
        }
        return $ret;
    }

    function createRecordSetItems($recordsetid, $recordsetitems) {
        // adapted from function of same name in recordsetmodel.php
        foreach ($recordsetitems as $recordid) {
            $insert = "INSERT INTO recordsetitem (RecordSetID, RecordID)
                VALUES ($recordsetid, $recordid);";
            $this->db->query($insert);
        }
    }
    
        /** Looks for new taxon record for genera (or higher ranks) that have
         *  not had their storage locality set (the higher taxonomy in MELISR
         *  has been decoupled from the storage system at MEL, so the 
         *  relationship between names in the database and the storage system
         *  needs to be defined).
         */
    public function getMissingGenusStorage($startdate, $enddate=FALSE, $userid=FALSE) {
        $name = array();
        $taxonid = array();
        $ret = array();

        $this->db->select("t.TaxonID, t.Name, t.NodeNumber, t.HighestChildNodeNumber,
            CONCAT(a.FirstName,' ',a.LastName) AS CreatedBy,DATE(t.TimestampCreated) AS Created,CONCAT(aa.FirstName,' ',aa.LastName) AS EditedBy,DATE(t.TimestampModified) AS Edited", false);
        $this->db->from('taxon t');
        $this->db->join('genusstorage gs', 't.TaxonID=gs.TaxonID', 'left');
        $this->db->join("agent a", "a.AgentID=t.CreatedByAgentID");
        $this->db->join("agent aa", "aa.AgentID=t.ModifiedByAgentID");
        $this->db->where('gs.GenusStorageID');
        $this->db->where('t.TaxonTreeDefItemID', 12);
        if ($userid)
            $this->db->where("t.CreatedByAgentID", $userid);
        $this->db->where("DATE(t.TimestampCreated) >='$startdate'", FALSE, FALSE);
        $query = $this->db->get();

        if ($query->num_rows() > 0) {
            foreach ($query->result() as $row) {
                $num_query = $this->db->query("SELECT count(*) as num
                FROM taxon t
                JOIN determination d ON t.TaxonID=d.TaxonID
                WHERE (d.IsCurrent=1 OR d.YesNo1=1) AND t.NodeNumber>=$row->NodeNumber AND t.NodeNumber<=$row->HighestChildNodeNumber");
                $num_row = $num_query->row();
                if ($num_row->num > 0) {
                    $name[] = $row->Name;
                    $taxonid[] = $row->TaxonID;
                    $ret[] = (array) $row;
                }
            }
        }
        
        $this->db->select("t.TaxonID, t.Name,
            CONCAT(a.FirstName,' ',a.LastName) AS CreatedBy,DATE(t.TimestampCreated) AS Created,CONCAT(aa.FirstName,' ',aa.LastName) AS EditedBy,DATE(t.TimestampModified) AS Edited", false);
        $this->db->from('taxon t');
        $this->db->join('taxontreedefitem td', 't.TaxonTreeDefItemID=td.TaxonTreeDefItemID');
        $this->db->join('determination d', 't.TaxonID=d.TaxonID');
        $this->db->join('genusstorage gs', 't.TaxonID=gs.TaxonID', 'left');
        $this->db->join("agent a", "a.AgentID=t.CreatedByAgentID");
        $this->db->join("agent aa", "aa.AgentID=t.ModifiedByAgentID");
        $this->db->where('gs.GenusStorageID');
        $this->db->where('td.RankID <', 180);
        $this->db->where('d.IsCurrent', 1);
        if ($userid)
            $this->db->where("t.CreatedByAgentID", $userid);
        $this->db->where("DATE(t.TimestampCreated) >='$startdate'", FALSE, FALSE);
        $this->db->group_by('t.TaxonID');
        $query = $this->db->get();

        if ($query->num_rows() > 0) {
            foreach ($query->result() as $row) {
                $name[] = $row->Name;
                $taxonid[] = $row->TaxonID;
                $ret[] = (array) $row;
            }
        }
        
        if ($name) {
            array_multisort($name, SORT_ASC, $taxonid, SORT_ASC, $ret);
            return $ret;
        }
        else return FALSE;
    }

    //public function partlyAtomisedHabitat($startdate, $enddate=FALSE, $userid=FALSE) {
    //    $ret = array();
    //    $this->db->select("co.CollectionObjectID,co.CatalogNumber,CONCAT(a2.FirstName,' ',a2.LastName) AS CreatedBy,
    //        LEFT(co.TimestampCreated,10) AS Created,CONCAT(a3.FirstName,' ',a3.LastName) AS EditedBy,LEFT(co.TimestampModified,10) AS Edited", FALSE);
    //    $this->db->from("collectionobject co");
    //    $this->db->join("collectingevent ce", "co.CollectingEventID=ce.CollectingEventID");
    //    $this->db->join("collectingeventattribute cea", "ce.CollectingEventAttributeID=cea.CollectingEventAttributeID");
    //    $this->db->join("agent a2", "co.CreatedByAgentID=a2.AgentID");
    //    $this->db->join("agent a3", "co.ModifiedByAgentID=a3.AgentID");
    //    $this->db->where("co.CollectionMemberID", 4);
    //    $this->db->where("ce.Remarks Is Not Null AND (cea.Remarks Is Not Null OR cea.Text1 Is Not Null OR cea.Text2 Is Not Null 
    //        OR cea.Text7 Is Not Null OR cea.Text9 Is Not Null OR cea.Text17 Is Not Null OR cea.Text4 Is Not Null)", FALSE, FALSE);
    //    $this->db->where("DATE(co.TimestampCreated)>=", $startdate);

    //    if ($userid)
    //        $this->db->where("co.CreatedByAgentID", $userid);

    //    $query = $this->db->get();
    //    if ($query->num_rows()) {
    //        return $query->result_array();
    //    }
    //    else
    //        return false;
    //}
    
}

?>
