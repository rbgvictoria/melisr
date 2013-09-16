<?php
/**
 * @property CI_Loader $load
 * @property CI_Form_validation $form_validation
 * @property CI_Input $input
 * @property CI_Email $email
 * @property CI_DB_active_record $db
 * @property CI_DB_forge $dbforge
 */

class AdminModel extends Model {

    public function  __construct() {
        parent::Model();

        // connect to database
        $this->load->database();
        $this->load->helper('xml');
    }

    public function activeLogins() {
        $ret = array();

        $this->db->select('SpecifyUserID, Name, LoginOutTime');
        $this->db->from('specifyuser');
        $this->db->where('IsLoggedIn', 1);

        $query = $this->db->get();
        if ($query->num_rows()) {
            foreach ($query->result() as $row) {
                $ret[] = array(
                    'SpecifyUserID' => $row->SpecifyUserID,
                    'Name' => $row->Name,
                    'LastLoggedOut' => $row->LoginOutTime
                );
            }
            return $ret;
        }
        else
            return false;
    }

    public function logOffUsers($users) {
        $this->db->where_in('SpecifyUserID', $users);
        $updatearray = array(
            'IsLoggedIn' => 0
        );
        $this->db->update('specifyuser', $updatearray);
    }

    public function showLocks() {
        $ret = array();
        $this->db->select('s.TaskSemaphoreID, s.LockedTime, s.MachineName, s.TaskName, s.Islocked=1 AS IsLocked, su.Name', FALSE);
        $this->db->from('sptasksemaphore s');
        $this->db->join('specifyuser su', 's.OwnerID=su.SpecifyUserID');
        $this->db->where('IsLocked', 1);
        $query = $this->db->get();
        if ($query->num_rows()) {
            foreach ($query->result() as $row) {
                $ret[] = array(
                    'TaskSemaphoreID' => $row->TaskSemaphoreID,
                    'LockedTime' => $row->LockedTime,
                    'TaskName' => $row->TaskName,
                    'SpecifyUser' => $row->Name
                );
            }
            return $ret;
        }
        return FALSE;
    }

    public function releaseLocks($tasks) {
        $this->db->where_in('TaskSemaphoreID', $tasks);
        $updatearray = array(
            'IsLocked' => 0,
            'MachineName' => NULL
        );
        $this->db->update('sptasksemaphore', $updatearray);
    }

}

?>
