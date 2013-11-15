<?php

/**
 * @property CI_Loader $load
 * @property CI_Form_validation $form_validation
 * @property CI_Input $input
 * @property CI_Email $email
 * @property CI_DB_active_record $db
 * @property CI_DB_forge $dbforge
 */

class InsectDamage extends Controller {
    var $data;
    
    public function __construct() {
        parent::Controller();
        $this->load->helper('form');
        $this->load->helper('file');
        $this->load->helper('url');
        $this->load->model('destructormodel');
        $this->output->enable_profiler(TRUE);
        $this->data['bannerimage'] = $this->banner();
        $this->data['title'] = 'MELISR | Insect damage';
    }
    
    public function index() {
        $this->load->model('insectdamagemodel');
        if ($this->input->post('submit') && $this->input->post('agent') && isset($_FILES['uploadedfile']['tmp_name'])) {
            $conservdescid = $this->insectdamagemodel->getNewConservDescriptionID();
            $recordids = array();
            $handle = fopen($_FILES['uploadedfile']['tmp_name'], 'r');
            while (!feof($handle)) {
                $line = fgetcsv($handle);
                if (substr($line[0], 0, 4) == 'MEL ' && is_numeric(substr($line[0], 4))) {
                    $colobjids = $this->insectdamagemodel->getCollectionObjectIDs(trim($line[0]));
                    if ($colobjids) {
                        foreach ($colobjids as $colobj) {
                            $recordids[] = $colobj;
                            
                            $date = date('Y-m-d H:i:s');

                            if ($line[1]) {
                                $preparation = $this->insectdamagemodel->getPickListItemValue(39, trim($line[1]));
                                if (!$preparation) {
                                    $this->data['errors'][] = "Preparation for $line[0] is not in pick list.";
                                    $preparation = NULL;
                                }
                            }
                            else
                                $preparation = 'Sheet';
                            
                            
                            
                            $conservdesc = array(
                                'ConservDescriptionID' => $conservdescid,
                                'TimestampCreated' => $date,
                                'Version' => 1,
                                'ShortDesc' => $preparation,
                                'CreatedByAgentID' => $this->input->post('agent'),
                                'CollectionObjectID' => $colobj,
                            );
                            
                            $agentID = NULL;
                            if (isset($line[8]) && $line[8]) {
                                $agentID = $this->insectdamagemodel->findAgentID(trim($line[8]));
                                if (!$agentID) {
                                    $this->data['errors'][] = 'Couldn&apos;t find agent for ' . trim($line[0]) . '.';
                                    $agentID = NULL;
                                }
                            }
                            
                            $treatmentdate = NULL;
                            if (isset($line[9]) && $line[9]) {
                                $arr = explode('/', trim($line[9]));
                                $treatmentdate = $arr[2] . '-' . $arr[1] . '-' . $arr[0];
                            }
                            
                            $conservevent = array(
                                'TimestampCreated' => $date,
                                'Version' => 1,
                                'AdvTestingExam' => isset($line[5]) ? trim($line[5]) : NULL,
                                'AdvTestingExamResults' => isset($line[6]) ? $line[6] : NULL,
                                'CompletedComments' => (isset($line[3]) && $line[3]) ? trim($line[3]) : NULL,
                                'ConditionReport' => (isset($line[2]) && $line[2]) ? trim($line[2]) : NULL,
                                'Remarks' => (isset($line[4]) && $line[4]) ? trim($line[4]) : NULL,
                                'TreatmentReport' => (isset($line[7]) && $line[7]) ? trim($line[7]) : NULL,
                                'TreatedByAgentID' => $agentID,
                                'TreatmentCompDate' => $treatmentdate,
                                'ConservDescriptionID' => $conservdescid,
                                'CreatedByAgentID' => $this->input->post('agent'),
                            );

                            $this->insectdamagemodel->insertConservDescription($conservdesc);
                            $this->insectdamagemodel->insertConservEvent($conservevent);
                            $conservdescid++;
                        }
                    }
                }
            }
            
            if ($recordids) {
                
                $specifyuserid = $this->insectdamagemodel->getSpecifyUserID($this->input->post('agent'));
                $recordsetid = $this->insectdamagemodel->getRecordSetID();
                
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
                
                $this->insectdamagemodel->insertRecordSet($recordset);
                
                foreach ($recordids as $record) {
                    $recordsetitem = array(
                        'RecordID' => $record,
                        'RecordSetID' => $recordsetid,
                    );
                    
                    $this->insectdamagemodel->insertRecordSetItem($recordsetitem);
                }
                
                $recordsetid++;
                
                $this->data['messages'][] = count($recordids) . ' Conserv Descriptions and Events have been uploaded.';
                $this->data['messages'][] = "Record set &apos;Insect damage upload $date&apos; has been created";
            }
        }
        
        $this->data['agents'] = $this->insectdamagemodel->getAgents();
        
        $this->load->view('insectdamageview', $this->data);
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
