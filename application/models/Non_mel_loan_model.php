<?php

require_once APPPATH . 'models/Transaction_model.php';

class Non_mel_loan_model extends Transaction_model {
    
    public function __construct() {
        parent::__construct();
    }
    
    public function getNonMelLoanNumbers() {
        $this->db->select('l.LoanID, l.LoanNumber, l.SrcTaxonomy, MAX(s.ShipmentDate) AS ShipmentDate');
        $this->db->from('loan l');
        $this->db->join('shipment s', 'l.LoanID=s.LoanID');
        $this->db->where('l.DisciplineID', 32768);
        $this->db->group_by('l.LoanID');
        $this->db->order_by('ShipmentDate', 'desc');
        $query = $this->db->get();
        $ret = array();
        $ret[0] = '(select a non-MEL loan)';
        foreach ($query->result() as $row)
            $ret[$row->LoanID] = $row->LoanNumber . ': ' . $row->SrcTaxonomy . ', ' . $row->ShipmentDate;
        return $ret;
    }

    function getNonMelLoanInfo($loanid) {
        $this->db->select("l.SrcTaxonomy, l.LoanNumber, s.ShippedToID, a.LastName, DATE_FORMAT(s.ShipmentDate, '%e %M %Y') AS ShipmentDate,
            DATE_FORMAT(l.DateReceived, '%d %b %Y') AS DateReceived, s.ShipmentMethod, s.Text1, l.ReceivedComments,
            s.NumberOfPackages, s.Number1 AS QuantityReturned, s.ShippedByID, l.Number1, l.Number2", FALSE);
        $this->db->from('loan l');
        $this->db->join('shipment s', 'l.LoanID=s.LoanID');
        $this->db->join('agent a', 's.ShippedToID=a.AgentID');
        $this->db->where('l.LoanID', $loanid);
        $this->db->order_by('s.ShipmentDate', 'desc');
        $this->db->limit(1);
        
        $query = $this->db->get();
        if ($query->num_rows()) {
            $row = $query->row();
            $loaninfo = array();
            $loaninfo['MELRefNo'] = $row->LoanNumber;
            $loaninfo['LoanNumber'] = trim($row->SrcTaxonomy);
            $loaninfo['Institution'] = $row->LastName;
            $loaninfo['ShippedTo'] = $this->getAddress($row->ShippedToID);
            $loaninfo['DateReceived'] = $row->DateReceived;
            $loaninfo['TaxaOnLoan'] = $row->ReceivedComments;
            $loaninfo['ShipmentDate'] = $row->ShipmentDate;
            $loaninfo['ShipmentMethod'] = $row->ShipmentMethod;
            $loaninfo['ShippedBy'] = $this->getAgent($row->ShippedByID);
            $loaninfo['TrackingLabels'] = $row->Text1;
            $loaninfo['NumberOfPackages'] = $row->NumberOfPackages;
            $loaninfo['QuantityReceived'] = $row->Number1;
            $loaninfo['QuantityReturned'] = $row->QuantityReturned;
            $loaninfo['Outstanding'] = $row->Number2;
            $loaninfo['ShipmentSummary'] = $this->getShipmentSummary($loanid);
            $loaninfo['LoanAgents'] = $this->getLoanAgents($loanid);
            return $loaninfo;
        }
        else return FALSE;
    }
    
    function getShipmentSummary($loanid) {
        $this->db->select("DATE_FORMAT(s.ShipmentDate, '%d %b %Y') AS ShipmentDate, s.Number1", FALSE);
        $this->db->from('shipment s');
        $this->db->where('s.LoanID', $loanid);
        $this->db->order_by('s.ShipmentDate');
        $query = $this->db->get();
        return $query->result_array();
    }

    function getLoanAgents($loanid) {
        $this->db->select('la.Role, a.MiddleInitial, a.FirstName, a.LastName');
        $this->db->from('loanagent la');
        $this->db->join('agent a', 'la.AgentID=a.AgentID');
        $this->db->where('la.LoanID', $loanid);
        $this->db->where_in('la.Role', array('Botanist', 'Student'));
        $this->db->order_by('la.Role', 'desc');
        $this->db->order_by('a.LastName', 'asc');
        $query = $this->db->get();
        if ($query->num_rows()) {
            $loanagents = array();
            foreach ($query->result() as $row) {
                $name = array();
                if ($row->MiddleInitial) $name[] = $row->MiddleInitial;
                elseif ($row->FirstName) $name[] = $row->FirstName;
                $name[] = $row->LastName;
                $name = implode(' ', $name);
                
                $loanagents[] = $name;
            }
            return implode(', ', $loanagents);
        }
        else
            return FALSE;
    }
    
}

?>
