<?php
/**
 * @property CI_Loader $load
 * @property CI_Form_validation $form_validation
 * @property CI_Input $input
 * @property CI_Email $email
 * @property CI_DB_active_record $db
 * @property CI_DB_forge $dbforge
 */

class LoanSorterModel extends Model {

    function  __construct() {
        parent::Model();

        // connect to database
        $this->load->database();
    }
    
    function sortByLoan($catalognumbers) {
        /*
        SELECT l.LoanID, l.LoanNumber, CONCAT('MEL ', CAST(SUBSTRING(co.CatalogNumber, 1, 7) AS unsigned)) AS MelNumber
        FROM collectionobject co
        JOIN preparation p ON co.CollectionObjectID=p.CollectionObjectID
        LEFT JOIN loanpreparation lp ON p.PreparationID=lp.PreparationID
        LEFT JOIN loan l ON lp.LoanID=l.LoanID
        WHERE co.CatalogNumber IN ('2113578A', '2113579A', '2266780A', '2272978A', '2272980A', '2273352A', '2288123A');
        */
        
        $this->db->select('l.LoanID, l.LoanNumber');
        $this->db->from('collectionobject co');
        $this->db->join('preparation p', 'co.CollectionObjectID=p.CollectionObjectID');
        $this->db->join('loanpreparation lp', 'p.PreparationID=lp.PreparationID');
        $this->db->join('loan l', 'lp.LoanID=l.LoanID');
        $this->db->where_in('co.CatalogNumber', $catalognumbers);
        $this->db->group_by('lp.LoanID');
        
        $query = $this->db->get();
        if ($query->num_rows()) {
            $ret = array();
            $catnos = array();
            foreach ($query->result() as $row) {
                $loan = array();
                $loan['LoanID'] = $row->LoanID;
                $loan['LoanNumber'] = $row->LoanNumber;
                $this->db->select("CONCAT('MEL ', CAST(SUBSTRING(co.CatalogNumber, 1, 7) AS unsigned)) 
                    AS MelNumber, co.CatalogNumber", FALSE);
                $this->db->from('collectionobject co');
                $this->db->join('preparation p', 'co.CollectionObjectID=p.CollectionObjectID');
                $this->db->join('loanpreparation lp', 'p.PreparationID=lp.PreparationID');
                $this->db->where_in('co.CatalogNumber', $catalognumbers);
                $this->db->where('lp.LoanID', $row->LoanID);
                $this->db->group_by('MelNumber');
                
                $query2 = $this->db->get();
                foreach ($query2->result() as $row2) {
                    $loan['MelNumber'][] = $row2->MelNumber;
                    $catnos[] = $row2->CatalogNumber;
                }
                $ret[] = $loan;
            }
            
            $notinloan = array();
            foreach ($catalognumbers as $number) {
                if (!in_array($number, $catnos)) {
                    $notinloan[] = 'MEL ' . (int) substr($number,0,7);
                }
            }
            
            if ($notinloan) {
                $loan = array();
                $loan['LoanID'] = 0;
                $loan['LoanNumber'] = 'Not in any loan';
                $loan['MelNumber'] = $notinloan;
                $ret[] = $loan;
            }
            return $ret;
        }
        else {
            $loan = array();
            $loan['LoanID'] = 0;
            $loan['LoanNumber'] = 'Not in any loan';
            foreach ($catalognumbers as $number) {
                $loan['MelNumber'][] = 'MEL ' . (int) substr($number,0,7);
            }
            return $loan;
        }
            
    }
    
}



?>
