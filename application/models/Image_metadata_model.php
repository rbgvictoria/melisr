<?php

class Image_metadata_model extends CI_Model {
    
    public function __construct() {
        parent::__construct();
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
    
    public function getImageRecords($startdate, $enddate=FALSE, $user=FALSE, $missingdata=FALSE, $extrafields=FALSE, $insufficient=FALSE) {
        $colobjatts = array();
        $coleventatts = array();
        
        // Collection Object attachments
        $this->db->select("co.CollectionObjectID, co.CatalogNumber, att.TableID AS `Table`, att.MimeType, att.GUID, 
            att.AttachmentLocation, att.Title, aia.ImageType, aia.Text1 AS Category, att.Remarks AS Subject, aia.Magnification,
            att.DateImaged, att.CopyrightHolder, att.CopyrightDate, att.License AS Restrictions, att.Credit, 
            coa.Remarks AS Comments, aia.Text2 AS Photographer,
            aia.CreativeCommons AS Licence,
            GROUP_CONCAT(tag.Tag ORDER BY tag.Tag SEPARATOR '|') AS Tags,
            att.TimestampCreated AS Created, IF(ca.FirstName IS NOT NULL, CONCAT(ca.LastName, ', ', ca.FirstName), ca.LastName) AS CreatedBy,
            att.TimestampModified AS Modified, IF(ma.FirstName IS NOT NULL, CONCAT(ma.LastName, ', ', ma.FirstName), ma.LastName) AS modifiedBy", FALSE);
        $this->db->from('collectionobject co');
        $this->db->join('collectionobjectattachment coa', 'co.CollectionObjectID=coa.CollectionObjectID', 'left');
        $this->db->join('attachment att', 'coa.AttachmentID=att.AttachmentID', 'left');
        $this->db->join('attachmentimageattribute aia', 'att.AttachmentImageAttributeID=aia.AttachmentImageAttributeID', 'left');
        $this->db->join('agent ca', 'att.CreatedByAgentID=ca.AgentID', 'left');
        $this->db->join('agent ma', 'att.ModifiedByAgentID=ma.AgentID', 'left');
        $this->db->join('attachmenttag tag', 'att.AttachmentID=tag.AttachmentID', 'left');
        $this->db->where('coa.CollectionObjectAttachmentID IS NOT NULL', FALSE, FALSE);
        $this->db->like('att.MimeType', FALSE, 'after');
        $this->db->where('att.TimestampCreated >', $startdate);
        if ($enddate) {
            $this->db->where("DATE(att.TimestampCreated) <= '$enddate'", FALSE, FALSE);
        }
        if ($user) {
            $this->db->where('att.CreatedByAgentID', $user);
        }
        if ($missingdata) {
            $where = array();
            foreach ($missingdata as $field) {
                $where[] = $field . ' IS NULL';
            }
            $where = '(' . implode(' OR ', $where) . ')';
            $this->db->where($where, FALSE, FALSE);
        }
        if ($insufficient) {
            $this->db->where("(aia.Text1 IS NULL OR att.Remarks IS NULL OR (att.CopyrightHolder IS NULL AND aia.Text2 IS NULL)
                OR (att.Credit IS NULL AND aia.Text2 IS NULL) OR aia.Text2 IS NULL OR (att.FileCreatedDate IS NULL AND
                att.CopyrightDate IS NULL) OR att.FileCreatedDate IS NULL OR aia.CreativeCommons IS NULL)", FALSE, FALSE);
        }
        
        $this->db->group_by('co.CollectionObjectID');
        $this->db->group_by('coa.CollectionObjectAttachmentID');
        $this->db->group_by('att.AttachmentID');
        $this->db->group_by('aia.AttachmentImageAttributeID');
        $this->db->group_by('ca.AgentID');
        $this->db->group_by('ma.AgentID');
        
        
        $query = $this->db->get();
        if ($query->num_rows()) {
            $colobjatts = $query->result_array();
        }

        // Collecting Event attachments
        $this->db->select("co.CollectionObjectID, co.CatalogNumber, att.TableID AS `Table`, att.MimeType, att.GUID, 
            att.AttachmentLocation, att.Title, aia.ImageType, aia.Text1 AS Category, att.Remarks AS Subject, aia.Magnification,
            att.DateImaged, att.CopyrightHolder, att.CopyrightDate, att.License AS Restrictions, att.Credit, 
            cea.Remarks AS Comments, aia.Text2 AS Photographer,
            aia.CreativeCommons AS Licence,
            GROUP_CONCAT(tag.Tag ORDER BY tag.Tag SEPARATOR '|') AS Tags,
            att.TimestampCreated AS Created, IF(ca.FirstName IS NOT NULL, CONCAT(ca.LastName, ', ', ca.FirstName), ca.LastName) AS CreatedBy,
            att.TimestampModified AS Modified, IF(ma.FirstName IS NOT NULL, CONCAT(ma.LastName, ', ', ma.FirstName), ma.LastName) AS modifiedBy", FALSE);
        $this->db->from('collectionobject co');
        $this->db->join('collectingevent ce', 'co.CollectingEventID=ce.CollectingEventID');
        $this->db->join('collectingeventattachment cea', 'ce.CollectingEventID=cea.CollectingEventID', 'left');
        $this->db->join('attachment att', 'cea.AttachmentID=att.AttachmentID', 'left');
        $this->db->join('attachmentimageattribute aia', 'att.AttachmentImageAttributeID=aia.AttachmentImageAttributeID', 'left');
        $this->db->join('agent ca', 'att.CreatedByAgentID=ca.AgentID', 'left');
        $this->db->join('agent ma', 'att.ModifiedByAgentID=ma.AgentID', 'left');
        $this->db->join('attachmenttag tag', 'att.AttachmentID=tag.AttachmentID', 'left');
        $this->db->where('cea.CollectingEventAttachmentID IS NOT NULL', FALSE, FALSE);
        $this->db->where('cea.CollectingEventID IS NOT NULL', FALSE, FALSE);
        $this->db->like('att.MimeType', 'image', 'after');
        $this->db->where('att.TimestampCreated >', $startdate);
        $this->db->where('co.CollectionMemberID', 4);
        if ($enddate) {
            $this->db->where("DATE(att.TimestampCreated) <= '$enddate'", FALSE, FALSE);
        }
        if ($user) {
            $this->db->where('att.CreatedByAgentID', $user);
        }
        if ($missingdata) {
            $where = array();
            foreach ($missingdata as $field) {
                $where[] = $field . ' IS NULL';
            }
            $where = '(' . implode(' OR ', $where) . ')';
            $this->db->where($where, FALSE, FALSE);
        }
        $this->db->group_by('co.CollectionObjectID');
        $this->db->group_by('cea.CollectingEventAttachmentID');
        $this->db->group_by('att.AttachmentID');
        $this->db->group_by('ca.AgentID');
        $this->db->group_by('ma.AgentID');
        
        $query = $this->db->get();
        if ($query->num_rows()) {
            $coleventatts = $query->result_array();
        }
        
        $union = array_merge($coleventatts, $colobjatts);
        if ($union) {
            //$union = array_map('unserialize', array_unique(array_map('serialize', $union)));
            $catnos = array();
            foreach ($union as $index => $row) {
                $catnos[] = $row['CatalogNumber'];
                if ($row['Table'] == 1)
                    $union[$index]['Table'] = 'Collection Object';
                elseif ($row['Table'] == 10)
                    $union[$index]['Table'] = 'Collecting Event';
                if ($extrafields && $extrafields[0]) {
                    $extra = $this->getExtraFields($row['CollectionObjectID'], $extrafields);
                    $union[$index] = array_merge($union[$index], $extra);
                }
            }
            array_multisort($catnos, SORT_ASC, $union);
            return $union;
        }
        else {
            return FALSE;
        }
    }
    
    private function getExtraFields($colobjid, $extrafields) {
        $this->db->from('collectionobject co');
        $this->db->where('co.CollectionObjectID', $colobjid);
        
        if (in_array('taxonname', $extrafields)) {
            $this->db->select('t.FullName AS TaxonName');
            $this->db->join('determination d', 'co.CollectionObjectID=d.CollectionObjectID');
            $this->db->join('taxon t', 'd.TaxonID=t.TaxonID');
            $this->db->where('d.IsCurrent', 1);
        }
        
        if (in_array('collector', $extrafields) ||
                in_array('collectingnumber', $extrafields) ||
                in_array('collectingdate', $extrafields) ||
                in_array('geography', $extrafields)) {
            $this->db->join('collectingevent ce', 'co.CollectingEventID=ce.CollectingEventID');
            
            if (in_array('collector', $extrafields)) {
                $this->db->select("GROUP_CONCAT(IF(a.FirstName IS NOT NULL, CONCAT(a.LastName, ', ', a.FirstName), 
                    a.LastName) ORDER BY c.OrderNumber SEPARATOR '|') AS Collector", FALSE);
                $this->db->join('collector c', 'ce.CollectingEventID=c.CollectingEventID');
                $this->db->join('agent a', 'c.AgentID=a.AgentID');
                $this->db->where('c.IsPrimary', 1);
                $this->db->group_by('co.CollectionObjectID');
            }
            
            if (in_array('collectingnumber', $extrafields)) {
                $this->db->select('ce.StationFieldNumber AS CollectingNumber');
            }
            
            if (in_array('collectingdate', $extrafields)) {
                $this->db->select("IF(ce.StartDatePrecision=3, YEAR(ce.StartDate), IF(ce.StartDatePrecision=2, 
                    SUBSTRING(ce.StartDate, 1, 7), ce.StartDate)) AS CollectingDate", FALSE);
            }
            
            if (in_array('geography', $extrafields)) {
                $this->db->select('g.FullName AS HigherGeography');
                $this->db->join('locality l', 'ce.LocalityID=l.LocalityID');
                $this->db->join('geography g', 'l.GeographyID=g.GeographyID');
            }
        }
        $query = $this->db->get();
        if ($query->num_rows()) {
            $row = $query->row_array();
            return $row;
        }
        else
            return array();
        
    }
    
    public function uploadImageMetadata($file, $user) {
        // split file contents into line
        $csv = preg_split ('/$\R?^/m', $file);
        
        // find out what delimiter has been used
        $delimiter = FALSE;
        
        $headerline = $csv[0];
        $headerarray = str_getcsv($headerline);
        if (count($headerarray) > 1)
            $delimiter = ',';
        else {
            $headerarray = str_getcsv($headerline, "\t");
            if (count($headerarray) > 1)
                $delimiter = "\t";
        }
        
        if ($delimiter) {
            $collobjs = array();
            foreach ($csv as $index => $line) {
                $row = array();
                if ($index > 0) {
                    $line = str_getcsv($line, $delimiter);
                    foreach ($line as $ind => $value) {
                        $row[$headerarray[$ind]] = ($value) ? $value : NULL;
                    }
                }
                
                if (isset($row['GUID'])) {
                    $this->db->select('att.TableID, att.AttachmentID, coa.CollectionObjectAttachmentID, cea.CollectingEventAttachmentID,
                        if(coa.CollectionObjectAttachmentID IS NOT NULL, co.CatalogNumber, co2.CatalogNumber) AS CatalogNumber,
                        if(coa.CollectionObjectAttachmentID IS NOT NULL, co.CollectionObjectID, co2.CollectionObjectID) AS CollectionObjectID,
                        if(coa.CollectionObjectAttachmentID IS NOT NULL, co.CollectingEventID, ce.CollectingEventID) AS CollectingEventID,
                        att.Version AS attVersion, aia.Version AS aiaVersion, att.AttachmentImageAttributeID', FALSE);
                    $this->db->from('attachment att');
                    $this->db->join('attachmentimageattribute aia', 'att.AttachmentImageAttributeID=aia.AttachmentImageAttributeID', 'left');
                    $this->db->join('collectionobjectattachment coa', 'att.AttachmentID=coa.AttachmentID', 'left');
                    $this->db->join('collectionobject co', 'coa.CollectionObjectID=co.CollectionObjectID', 'left');
                    $this->db->join('collectingeventattachment cea', 'att.AttachmentID=cea.AttachmentID', 'left');
                    $this->db->join('collectingevent ce', 'cea.CollectingEventID=ce.CollectingEventID', 'left');
                    $this->db->join('collectionobject co2', 'ce.CollectingEventID=co2.CollectingEventID', 'left');
                    $this->db->where('att.GUID', $row['GUID']);
                    $query = $this->db->get();
                    if ($query->num_rows()) {
                        $qrow = $query->row();
                        $collobjs[] = $qrow->CollectionObjectID;

                        // Table attachment record is linked to has changed
                        if ($row['Table'] == 'Collecting Event' && $qrow->TableID == 1) {
                            $this->db->where('CollectionObjectAttachmentID', $qrow->CollectionObjectAttachmentID);
                            $this->db->delete('collectionobjectattachment');

                            $insert = array(
                                'TimestampCreated' => date('Y-m-d H:i:s'),
                                'Version' => 0,
                                'CollectionMemberID' => 4,
                                'Ordinal' => 0,
                                'CollectingEventID' => $qrow->CollectingEventID,
                                'AttachmentID' => $qrow->AttachmentID,
                                'CreatedByAgentID' => $user
                            );
                            $this->db->insert('collectingeventattachment', $insert);

                            // now re-order the attachments
                            $rq = $this->db->query("SELECT CollectionObjectAttachmentID
                                FROM collectionobjectattachment
                                WHERE CollectionObjectID=$qrow->CollectionObjectID
                                ORDER BY Ordinal");
                            if ($rq->num_rows()) {
                                foreach ($rq->result() as $ordinal => $r) {
                                    $update = array(
                                        'Ordinal' => $ordinal,
                                        'TimestampModified' => date('Y-m-d H:i:s'),
                                        'ModifiedByAgentID' => $user
                                    );
                                    $this->db->where('CollectionObjectAttachmentID', $r->CollectionObjectAttachmentID);
                                    $this->db->update('collectionobjectattachment', $update);
                                }
                            }

                            $rq = $this->db->query("SELECT CollectingEventAttachmentID
                                FROM collectingeventattachment
                                WHERE CollectingEventID=$qrow->CollectingEventID
                                ORDER BY Ordinal");
                            if ($rq->num_rows()) {
                                foreach ($rq->result() as $ordinal => $r) {
                                    $update = array(
                                        'Ordinal' => $ordinal,
                                        'TimestampModified' => date('Y-m-d H:i:s'),
                                        'ModifiedByAgentID' => $user
                                    );
                                    $this->db->where('CollectingEventAttachmentID', $r->CollectingEventAttachmentID);
                                    $this->db->update('collectingeventattachment', $update);
                                }
                            }
                        }
                        elseif ($row['Table'] == 'Collection Object' && $qrow->TableID == 10) {
                            $this->db->where('CollectingEventAttachmentID', $qrow->CollectingEventAttachmentID);
                            $this->db->delete('collectingeventattachment');

                            $insert = array(
                                'TimestampCreated' => date('Y-m-d H:i:s'),
                                'Version' => 0,
                                'CollectionMemberID' => 4,
                                'Ordinal' => 0,
                                'CollectionObjectID' => $qrow->CollectionObjectID,
                                'AttachmentID' => $qrow->AttachmentID,
                                'CreatedByAgentID' => $user
                            );
                            $this->db->insert('collectionobjectattachment', $insert);

                            // now re-order the attachments
                            $rq = $this->db->query("SELECT CollectionObjectAttachmentID
                                FROM collectionobjectattachment
                                WHERE CollectionObjectID=$qrow->CollectionObjectID
                                ORDER BY Ordinal");
                            if ($rq->num_rows()) {
                                foreach ($rq->result() as $ordinal => $r) {
                                    $update = array(
                                        'Ordinal' => $ordinal,
                                        'TimestampModified' => date('Y-m-d H:i:s'),
                                        'ModifiedByAgentID' => $user
                                    );
                                    $this->db->where('CollectionObjectAttachmentID', $r->CollectionObjectAttachmentID);
                                    $this->db->update('collectionobjectattachment', $update);
                                }
                            }

                            $rq = $this->db->query("SELECT CollectingEventAttachmentID
                                FROM collectingeventattachment
                                WHERE CollectingEventID=$qrow->CollectingEventID
                                ORDER BY Ordinal");
                            if ($rq->num_rows()) {
                                foreach ($rq->result() as $ordinal => $r) {
                                    $update = array(
                                        'Ordinal' => $ordinal,
                                        'TimestampModified' => date('Y-m-d H:i:s'),
                                        'ModifiedByAgentID' => $user
                                    );
                                    $this->db->where('CollectingEventAttachmentID', $r->CollectingEventAttachmentID);
                                    $this->db->update('collectingeventattachment', $update);
                                }
                            }
                        }
                        
                        // Check if an Attachment Image Attribute is needed
                        if ($row['Photographer'] || $row['Category'] || $row['Magnification'] || 
                                $row['Licence'] || $row['ImageType']) {
                            
                            $updateArray = array(
                                'Text2' => ($row['Photographer']) ? $row['Photographer'] : NULL,
                                'Text1' => ($row['Category']) ? $row['Category'] : NULL,
                                'Magnification' => ($row['Magnification']) ? $row['Magnification'] : NULL,
                                'ImageType' => ($row['ImageType']) ? $row['ImageType'] : NULL,
                                'CreativeCommons' => ($row['Licence']) ? $row['Licence'] : NULL,
                            );
                            
                            if ($qrow->AttachmentImageAttributeID) {
                                $updateArray['TimestampModified'] = date('Y-m-d H:i:s');
                                $updateArray['ModifiedByAgentID'] = $user;
                                $updateArray['Version'] = $qrow->aiaVersion+1;
                                
                                $this->db->where('AttachmentImageAttributeID', $qrow->AttachmentImageAttributeID);
                                $this->db->update('attachmentimageattribute', $updateArray);
                            }
                            else { // We need to create a new Attachment Image Attribute record
                                $mquery = $this->db->query("SELECT MAX(AttachmentImageAttributeID)+1 AS NewID
                                    FROM attachmentimageattribute");
                                $mrow = $mquery->row();
                                $qrow->AttachmentImageAttributeID = $mrow->NewID;
                                
                                $insertArray = $updateArray;
                                $insertArray['AttachmentImageAttributeID'] = $qrow->AttachmentImageAttributeID;
                                $insertArray['Version'] = 0;
                                $insertArray['TimestampCreated'] = date('Y-m-d H:i:s');
                                $insertArray['Timestampmodified'] = date('Y-m-d H:i:s');
                                $insertArray['CreatedByAgentID'] = $user;
                                
                                $this->db->insert('attachmentimageattribute', $insertArray);
                                
                                $this->db->where('AttachmentID', $qrow->AttachmentID);
                                $this->db->update('attachment', array('AttachmentImageAttributeID' => $qrow->AttachmentImageAttributeID));
                            }
                            
                        }
                        
                        // Finally, update the Attachment record itself
                        $updateArray = array(
                            'TimestampModified' => date('Y-m-d H:i:s'),
                            'Version' => $qrow->attVersion+1,
                            'Remarks' => $row['Subject'],
                            'DateImaged' => ($row['DateImaged']) ? $row['DateImaged'] : NULL,
                            'CopyrightHolder' => ($row['CopyrightHolder']) ? $row['CopyrightHolder'] : NULL,
                            'CopyrightDate' => ($row['CopyrightDate']) ? $row['CopyrightDate'] : NULL,
                            'License' => ($row['Restrictions']) ? $row['Restrictions'] : NULL,
                            'Credit' => ($row['Credit']) ? $row['Credit'] : NULL,
                            //'Remarks' => ($row['Comments']) ? $row['Comments'] : NULL,
                            'ModifiedByAgentID' => $user
                        );
                        
                        $this->db->where('AttachmentID', $qrow->AttachmentID);
                        $this->db->update('attachment', $updateArray);
                        
                        // Actually, there is some more stuff to do, as I moved some fields around
                        // Update Collection Object Attachment Table
                        $upd = array(
                                'TimestampModified' => date('Y-m-d H:i:s'),
                                'Remarks' => ($row['Comments']) ? $row['Comments'] : NULL,
                                'ModifiedByAgentID' => $user
                            );
                        
                        if ($row['Table'] == 'Collection Object') {
                            $this->db->where('CollectionObjectAttachmentID', $qrow->CollectionObjectAttachmentID);
                            $this->db->update('collectionobjectattachment', $upd);
                        }
                        elseif ($row['Table'] == 'Collecting Event') {
                            $this->db->where('CollectingEventAttachmentID', $qrow->CollectingEventAttachmentID);
                            $this->db->update('collectingeventattachment', $upd);
                        }
                        
                        // And I implemented Tags
                        // Update Tags
                        /*if ($row['Tags']) {
                            $tags = explode('|', $row['Tags']);
                            
                            $this->db->select('AttachmentTagID, Tag');
                            $this->db->from('attachmenttag');
                            $this->db->where('AttachmentID', $qrow->AttachmentID);
                            $tagq = $this->db->get();
                            if ($tagq->num_rows()) {
                                $dbtags = array();
                                foreach ($tagq as $tagr) {
                                    $dbtags[] = $tagr->Tag;
                                    
                                    // Delete the tags that are not in the spreadsheet from the database
                                    if (!in_array($tagr->Tag, $tags)) {
                                        $this->db->where('AttachmentTagID', $tagr->AttachmentTagID);
                                        $this->db->delete('attachmenttag');
                                    }
                                }
                                
                                foreach ($tags as $tag) {
                                    // Edit tags from the spreadsheet that are not yet in the database
                                    if (!in_array($tag, $dbtags)) {
                                        $ins = array(
                                            'TimestampCreated' => date('Y-m-d H:i:s'),
                                            'TimestampModified' => date('Y-m-d H:i:s'),
                                            'Version' => 0,
                                            'Tag' => $tag,
                                            'AttachmentID' => $qrow->AttachmentID,
                                            'CreatedByAgentID' => $user
                                        );
                                        $this->db->insert('attachmenttag', $ins);
                                    }
                                }
                            }
                            else {
                                // There are no tags in the database yet, so let's add them
                                foreach ($tags as $tag) {
                                    $ins = array(
                                        'TimestampCreated' => date('Y-m-d H:i:s'),
                                        'TimestampModified' => date('Y-m-d H:i:s'),
                                        'Version' => 0,
                                        'Tag' => $tag,
                                        'AttachmentID' => $qrow->AttachmentID,
                                        'CreatedByAgentID' => $user
                                    );
                                    $this->db->insert('attachmenttag', $ins);
                                }
                            }
                        }
                        else {
                            $this->db->where('AttachmentID', $qrow->AttachmentID);
                            $this->db->delete('attachmenttag');
                        }*/
                        
                    }
                }
            }
            
            // create Collection Object Record Set
            $collobjs = array_unique($collobjs);
            
            $spuquery = $this->db->query("SELECT SpecifyUserID
                FROM agent
                WHERE AgentID=$user");
            $spurow = $spuquery->row();
            $spuser = $spurow->SpecifyUserID;
            
            $rsmaxquery = $this->db->query("SELECT MAX(RecordSetID)+1 AS NewID
                FROM recordset");
            $rsmaxrow = $rsmaxquery->row();
            $recordsetid = $rsmaxrow->NewID;
            
            $recordsetname = 'Attachment metadata upload ' . date('Ymd Hi');
            
            $data = array(
                'RecordSetID' => $recordsetid,
                'TimestampCreated' => date('Y-m-d H:i:s'),
                'TimestampModified' => date('Y-m-d H:i:s'),
                'Version' => 0,
                'CollectionMemberID' => 4,
                'TableID' => 1,
                'Name' => $recordsetname,
                'Type' => 0,
                'CreatedByAgentID' => $user,
                'ModifiedByAgentID' => $user,
                'SpecifyUserID' => $spuser,
            );
            
            $this->db->insert('recordset', $data);
            
            
            $data = array(
                'RecordSetID' => $recordsetid
            );
            
            foreach ($collobjs as $record) {
                $data['RecordID'] = $record;
                $this->db->insert('recordsetitem', $data);
            }
            
            return $recordsetname;
            
            
        }
        else
            return FALSE;
    }
}

?>
