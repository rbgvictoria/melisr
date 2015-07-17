<?php

/**
 * @property CI_Loader $load
 * @property CI_Form_validation $form_validation
 * @property CI_Input $input
 * @property CI_Email $email
 * @property CI_DB_active_record $db
 * @property CI_DB_forge $dbforge
 */

class Destroyer extends Controller {
    var $data;
    
    public function __construct() {
        parent::Controller();
        $this->load->helper('form');
        $this->load->helper('file');
        $this->load->helper('url');
        $this->load->model('destructormodel');
        $this->output->enable_profiler(TRUE);
        $this->data['bannerimage'] = $this->banner();
        $this->data['title'] = 'MELISR | Destroyer';
    }
    
    public function index() {
        $this->load->model('destroyermodel');
        if ($this->input->post('submit') && $this->input->post('agent') && isset($_FILES['uploadedfile']['tmp_name'])) {
            $conservdescid = $this->destroyermodel->getNewConservDescriptionID();
            $recordids = array();
            $handle = fopen($_FILES['uploadedfile']['tmp_name'], 'r');
            while (!feof($handle)) {
                $line = fgetcsv($handle);
                if (substr($line[0], 0, 4) == 'MEL ' && is_numeric(substr($line[0], 4))) {
                    $colobjids = $this->destroyermodel->getCollectionObjectIDs(trim($line[0]));
                    if ($colobjids) {
                        foreach ($colobjids as $colobj) {
                            $recordids[] = $colobj;
                            
                            $date = date('Y-m-d H:i:s');

                            // Pick list values
                            if ($line[1]) {
                                $preparation = $this->destroyermodel->getPickListItemValue(39, trim($line[1]));
                                if (!$preparation) {
                                    $this->data['errors'][] = "Preparation for $line[0] is not in pick list.";
                                    $preparation = NULL;
                                }
                            }
                            else
                                $preparation = 'Sheet';
                            
                            if ($line[2]) {
                                $eventtype = $this->destroyermodel->getPickListItemValue(244, trim($line[2]));
                                if (!$eventtype) {
                                    $this->data['errors'][] = "Event type for $line[0] is not in pick list.";
                                    $eventtype = NULL;
                                }
                            }
                            
                            
                            $conservdesc = array(
                                'ConservDescriptionID' => $conservdescid,
                                'TimestampCreated' => $date,
                                'TimestampModified' => $date,
                                'Version' => 1,
                                'ShortDesc' => $preparation,
                                'BackGroundInfo' => $eventtype,
                                'CreatedByAgentID' => $this->input->post('agent'),
                                'CollectionObjectID' => $colobj,
                                'DivisionID' => 2
                            );
                            
                            
                            // Pick list values
                            if ($line[7]) {
                                $cause = $this->destroyermodel->getPickListItemValue(246, trim($line[7]));
                                if (!$cause) {
                                    $this->data['errors'][] = "Cause for $line[0] is not in pick list.";
                                    $cause = NULL;
                                }
                            }
                            
                            if ($line[8]) {
                                $cause = $this->destroyermodel->getPickListItemValue(247, trim($line[8]));
                                if (!$cause) {
                                    $this->data['errors'][] = "Severity for $line[0] is not in pick list.";
                                    $cause = NULL;
                                }
                            }
                            
                            // Agents
                            $researcherID = NULL;
                            if (isset($line[3]) && $line[3]) {
                                $researcherID = $this->destroyermodel->findAgentID(trim($line[3]));
                                if (!$researcherID) {
                                    $this->data['errors'][] = 'Couldn&apos;t find agent for Researcher in ' . trim($line[0]) . '.';
                                    $researcherID = NULL;
                                }
                            }
                            
                            $assessedByID = NULL;
                            if (isset($line[10]) && $line[10]) {
                                $assessedByID = $this->destroyermodel->findAgentID(trim($line[10]));
                                if (!$assessedByID) {
                                    $this->data['errors'][] = 'Couldn&apos;t find agent for Assessed by in ' . trim($line[0]) . '.';
                                    $assessedByID = NULL;
                                }
                            }
                            
                            
                            // Dates
                            $samplingdate = NULL;
                            $samplingdateprecision = 1;
                            if (isset($line[4]) && $line[4]) {
                                $tdate = $this->convertDate($line[4]);
                                if (!$tdate) {
                                    $this->data['errors'][] = 'Invalid date for Sample date in ' . trim($line[0]) . '.';
                                }
                                else {
                                    $samplingdate = $tdate[0];
                                    $samplingdateprecision = $tdate[1];
                                }
                            }
                            
                            $datenoticed = NULL;
                            $datenoticedprecision = 1;
                            if (isset($line[9]) && $line[9]) {
                                $tdate = $this->convertDate($line[9]);
                                if (!$tdate) {
                                    $this->data['errors'][] = 'Invalid date for Date noticed in ' . trim($line[0]) . '.';
                                }
                                else {
                                    $datenoticed = $tdate[0];
                                    $datenoticedprecision = $tdate[1];
                                }
                            }
                            
                            $dateassessed = NULL;
                            $dateassessedprecision = 1;
                            if (isset($line[11]) && $line[11]) {
                                $tdate = $this->convertDate($line[11]);
                                if (!$tdate) {
                                    $this->data['errors'][] = 'Invalid date for Date assessed in ' . trim($line[0]) . '.';
                                }
                                else {
                                    $dateassessed = $tdate[0];
                                    $dateassessedprecision = $tdate[1];
                                }
                            }
                            
                            
                            
                            $conservevent = array(
                                'TimestampCreated' => $date,
                                'TimestampModified' => $date,
                                'Version' => 1,
                                'ExaminedByAgentID' => $researcherID,
                                'CompletedDate' => $samplingdate,
                                'CompletedDatePrecision' => $samplingdateprecision,
                                'Photodocs' => (isset($line[5]) && $line[5]) ? $line[5] : NULL,
                                'CompletedComments' => (isset($line[6]) && $line[6]) ? trim($line[6]) : NULL,
                                'AdvTestingExamResults' => (isset($line[7]) && $line[7]) ? $line[7] : NULL,
                                'ConditionReport' => (isset($line[8]) && $line[8]) ? trim($line[8]) : NULL,
                                'ExamDate' => $datenoticed,
                                'ExamDatePrecision' => $datenoticedprecision,
                                'CuratorID' => $assessedByID,
                                'TreatmentCompDate' => $dateassessed,
                                'TreatmentCompDatePrecision' => $dateassessedprecision,
                                'TreatmentReport' => (isset($line[12]) && $line[12]) ? trim($line[12]) : NULL,
                                'Text1' => (isset($line[13]) && $line[13]) ? trim($line[13]) : NULL,
                                'Remarks' => (isset($line[14]) && $line[14]) ? trim($line[14]) : NULL,
                                'ConservDescriptionID' => $conservdescid,
                                'CreatedByAgentID' => $this->input->post('agent')
                            );

                            $this->destroyermodel->insertConservDescription($conservdesc);
                            $this->destroyermodel->insertConservEvent($conservevent);
                            $conservdescid++;
                        }
                    }
                }
            }
            
            if ($recordids) {
                
                $specifyuserid = $this->destroyermodel->getSpecifyUserID($this->input->post('agent'));
                $recordsetid = $this->destroyermodel->getRecordSetID();
                
                $recordset = array(
                    'RecordSetID' => $recordsetid, 
                    'TimestampCreated' => $date, 
                    'Version' => 1, 
                    'CollectionMemberID' => 4, 
                    'TableID' => 1, 
                    'Name' => "Insect damage upload $date", 
                    'Type' => 0, 
                    'SpecifyUserID' => $specifyuserid, 
                    'CreatedByAgentID' => $this->input->post('agent'),
                );
                
                $this->destroyermodel->insertRecordSet($recordset);
                
                foreach ($recordids as $record) {
                    $recordsetitem = array(
                        'RecordID' => $record,
                        'RecordSetID' => $recordsetid,
                    );
                    
                    $this->destroyermodel->insertRecordSetItem($recordsetitem);
                }
                
                $recordsetid++;
                
                $this->data['messages'][] = count($recordids) . ' Conserv. Descriptions and Events have been uploaded.';
                $this->data['messages'][] = "Record set &apos;Destroyer upload $date&apos; has been created";
            }
        }
        
        
        $this->data['preparationItems'] = $this->destroyermodel->getPickListItems(39);
        $this->data['eventTypeItems'] = $this->destroyermodel->getPickListItems(244);
        $this->data['causeOfDamageItems'] = $this->destroyermodel->getPickListItems(246);
        $this->data['severityOfDamageItems'] = $this->destroyermodel->getPickListItems(247);
        
        $this->data['agents'] = $this->destroyermodel->getAgents();
        
        
        $this->load->view('destroyerview', $this->data);
    }
    
    private function convertDate($date) {
        if (!preg_match('/[0-9]{2}\/[0-9]{2}\/[0-9]{4}/', $date))
            return FALSE;
        $precision = 1;
        $bits = explode('/', $date);
        if ($bits[0] == '00') {
            $bits[0] = '01';
            $precision = 2;
        }
        if ($bits[1] == '00') {
            $bits[1] = '01';
            $precision = 3;
        }
        return array(
            implode('-', array_reverse($bits)),
            $precision
        );
    }
    
    function banner() {
        $banners = get_dir_file_info('./images/banners', TRUE);
        $banners = array_values($banners);
        $count = count($banners);
        $i = rand(0, $count-1);
        return $banners[$i]['name'];
    }
}
?>
