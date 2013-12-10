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
    
    function sortByLoan($catalognumbers, $nonmel=FALSE) {
        $this->db->select("l.LoanID, IF(l.DisciplineID=32768, CONCAT('MEL Ref. No. ', l.LoanNumber, ' (', l.srcTaxonomy, ')'), l.LoanNumber) AS LoanNumber", FALSE);
        $this->db->from('collectionobject co');
        $this->db->join('preparation p', 'co.CollectionObjectID=p.CollectionObjectID');
        $this->db->join('loanpreparation lp', 'p.PreparationID=lp.PreparationID');
        $this->db->join('loan l', 'lp.LoanID=l.LoanID');
        $this->db->where_in('co.CatalogNumber', $catalognumbers);
        if ($nonmel)
            $this->db->where('l.DisciplineID', 32768);
        else
            $this->db->where('l.DisciplineID', 3);
        $this->db->group_by('lp.LoanID');
        
        $query = $this->db->get();
        if ($query->num_rows()) {
            $ret = array();
            $catnos = array();
            foreach ($query->result() as $row) {
                $loan = array();
                $loan['LoanID'] = $row->LoanID;
                $loan['LoanNumber'] = $row->LoanNumber;
                if (!$nonmel)
                    $this->db->select("CONCAT('MEL ', CAST(SUBSTRING(co.CatalogNumber, 1, 7) AS unsigned)) 
                        AS MelNumber, co.CatalogNumber", FALSE);
                else
                    $this->db->select('co.CatalogNumber');
                $this->db->from('collectionobject co');
                $this->db->join('preparation p', 'co.CollectionObjectID=p.CollectionObjectID');
                $this->db->join('loanpreparation lp', 'p.PreparationID=lp.PreparationID');
                $this->db->where_in('co.CatalogNumber', $catalognumbers);
                $this->db->where('lp.LoanID', $row->LoanID);
                if (!$nonmel)
                    $this->db->group_by('MelNumber');
                else
                    $this->db->group_by('CatalogNumber');
                
                $query2 = $this->db->get();
                foreach ($query2->result() as $row2) {
                    $loan['MelNumber'][] = (!$nonmel) ? $row2->MelNumber : $row2->CatalogNumber;
                    $catnos[] = $row2->CatalogNumber;
                }
                $ret[] = $loan;
            }
            
            $notinloan = array();
            foreach ($catalognumbers as $number) {
                if (!in_array($number, $catnos)) {
                    $notinloan[] = (!$nonmel) ? 'MEL ' . (int) substr($number,0,7) : $number;
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
