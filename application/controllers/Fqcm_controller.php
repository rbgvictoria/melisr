<?php
/**
 * @property CI_Loader $load
 * @property CI_Form_validation $form_validation
 * @property CI_Input $input
 * @property CI_Email $email
 * @property CI_DB_active_record $db
 * @property CI_DB_forge $dbforge
 */

class Fqcm_controller extends CI_Controller {
    var $data;

    function __construct() {
        parent::__construct();
        $this->load->helper('file');
        $this->output->enable_profiler(false);
        $this->data['title'] = 'MELISR | FQCM';
        $this->load->model('Fqcm_model', 'fqcmmodel');
        $this->load->model('Recordset_model', 'recordsetmodel');
        $this->data['Users'] = $this->fqcmmodel->getUsers();
        $this->data['js'][] = 'jquery.fqcm.js';
        $this->session->unset_userdata(['error', 'warning', 'success']);
    }

    function index() {
        $this->load->view('fqcmview', $this->data);
    }
    
    function doqc() {
        $request = array();
        $request = $this->uri->uri_to_assoc();
        if ($request && !isset($request['user']))
            $request['user'] = FALSE;
        if (!$request && ($this->input->post('submit') || $this->input->post('submit_localities') || $this->input->post('createrecordset'))){
            $request['startdate'] = $this->input->post('startdate');
            $request['user'] = $this->input->post('user');
            $request['createrecordset'] = $this->input->post('createrecordset');
        }    
        $this->data['request'] = $request;
        
        if (isset($request['startdate']) && $request['startdate']) {
            $startdate = $request['startdate'];
            if ($startdate < '2011-02-04' ) $startdate = '2011-02-04';
            $this->data['startdate'] = $startdate;
            if ($this->input->post('createrecordset')) {
                $this->createRecordSet();
            }
            $this->fqcmmodel->getCollectionObjects($startdate, FALSE, $request['user']);     
            if (($request || $this->input->post('createrecordset')) && !$this->input->post('submit_localities')) {
                if (!isset($request['fqcr']) || $request['fqcr'] == 'HighCatalogueNumbers')
                    $this->data['HighCatalogueNumbers'] = $this->fqcmmodel->HighCatalogueNumbers($startdate, FALSE, $request['user']);
                if (!isset($request['fqcr']) || $request['fqcr'] == 'MissingPreparation')
                    $this->data['MissingPreparation'] = $this->fqcmmodel->missingPreparation($startdate, FALSE, $request['user']);
                if (!isset($request['fqcr']) || $request['fqcr'] == 'MissingAltitudeUnit')
                    $this->data['MissingAltitudeUnit'] = $this->fqcmmodel->missingAltitudeUnit($startdate, FALSE, $request['user']);
                if (!isset($request['fqcr']) || $request['fqcr'] == 'TooMuchAltitude')
                    $this->data['TooMuchAltitude'] = $this->fqcmmodel->tooMuchAltitude($startdate, FALSE, $request['user']);
                if (!isset($request['fqcr']) || $request['fqcr'] == 'MissingLocality')
                    $this->data['MissingLocality'] = $this->fqcmmodel->missingLocality($startdate, FALSE, $request['user']);
                if (!isset($request['fqcr']) || $request['fqcr'] == 'MissingSourceOrPrecision')
                    $this->data['MissingSourceOrPrecision'] = $this->fqcmmodel->missingSourceOrPrecision($startdate, FALSE, $request['user']);
                if (!isset($request['fqcr']) || $request['fqcr'] == 'MissingDetermination')
                    $this->data['MissingDetermination'] = $this->fqcmmodel->missingDetermination($startdate, FALSE, $request['user']);
                if (!isset($request['fqcr']) || $request['fqcr'] == 'TypeMismatch')
                    $this->data['TypeMismatch'] = $this->fqcmmodel->typeMismatch($startdate, FALSE, $request['user']);
                if (!isset($request['fqcr']) || $request['fqcr'] == 'MissingTaxonName')
                    $this->data['MissingTaxonName'] = $this->fqcmmodel->missingTaxonName($startdate, FALSE, $request['user']);
                if (!isset($request['fqcr']) || $request['fqcr'] == 'TypeDetIsCurrent')
                    $this->data['TypeDetIsCurrent'] = $this->fqcmmodel->typeDetIsCurrent($startdate, FALSE, $request['user']);
                if (!isset($request['fqcr']) || $request['fqcr'] == 'TypeDetOverriddenByIndet')
                    $this->data['TypeDetOverriddenByIndet'] = $this->fqcmmodel->typeDetOverriddenByIndet($startdate, FALSE, $request['user']);
                if (!isset($request['fqcr']) || $request['fqcr'] == 'DetDateEarlierThanCollDate')
                    $this->data['DetDateEarlierThanCollDate'] = $this->fqcmmodel->detDateEarlierThanCollDate($startdate, FALSE, $request['user']);
                if (!isset($request['fqcr']) || $request['fqcr'] == 'MissingProtologue')
                    $this->data['MissingProtologue'] = $this->fqcmmodel->missingProtologue($startdate, FALSE, $request['user']);
                if (!isset($request['fqcr']) || $request['fqcr'] == 'MissingPrimaryCollectors')
                    $this->data['MissingPrimaryCollectors'] = $this->fqcmmodel->missingPrimaryCollectors($startdate, FALSE, $request['user']);
                if (!isset($request['fqcr']) || $request['fqcr'] == 'MissingCollectors')
                    $this->data['MissingCollectors'] = $this->fqcmmodel->missingCollectors($startdate, FALSE, $request['user']);
                if (!isset($request['fqcr']) || $request['fqcr'] == 'GroupCollectors')
                    $this->data['GroupCollectors'] = $this->fqcmmodel->groupCollectors($startdate, FALSE, $request['user']);                
                if (!isset($request['fqcr']) || $request['fqcr'] == 'DuplicateHerbariaInWrongPreparation')
                    $this->data['DuplicateHerbariaInWrongPreparation'] = $this->fqcmmodel->duplicateHerbariaInWrongPreparation($startdate, FALSE, $request['user']);
                if (!isset($request['fqcr']) || $request['fqcr'] == 'DuplicateCountMismatch')
                    $this->data['DuplicateCountMismatch'] = $this->fqcmmodel->DuplicateCountMismatch($startdate, FALSE, $request['user']);
                if (!isset($request['fqcr']) || $request['fqcr'] == 'SomethingInNumberThatShouldntBeThere')
                    $this->data['SomethingInNumberThatShouldntBeThere'] = $this->fqcmmodel->somethingInNumberThatShouldntBeThere($startdate, FALSE, $request['user']);
                if (!isset($request['fqcr']) || $request['fqcr'] == 'SomethingMissingFromNumberField')
                    $this->data['SomethingMissingFromNumberField'] = $this->fqcmmodel->somethingMissingFromNumberField($startdate, FALSE, $request['user']);
                if (!isset($request['fqcr']) || $request['fqcr'] == 'TooEarlyForGPS')
                    $this->data['TooEarlyForGPS'] = $this->fqcmmodel->tooEarlyForGPS($startdate, FALSE, $request['user']);
                if (!isset($request['fqcr']) || $request['fqcr'] == 'GroupAgentsWithoutIndividuals')
                    $this->data['GroupAgentsWithoutIndividuals'] = $this->fqcmmodel->groupAgentsWithoutIndividuals($startdate, FALSE, $request['user']);
                if (!isset($request['fqcr']) || $request['fqcr'] == 'PartMissingFromMultisheetMessage')
                    $this->data['PartMissingFromMultisheetMessage'] = $this->fqcmmodel->partMissingFromMultisheetMessage($startdate, FALSE, $request['user']);
                if (!isset($request['fqcr']) || $request['fqcr'] == 'NewSubgenus')
                    $this->data['NewSubgenus'] = $this->fqcmmodel->newSubgenus($startdate, FALSE, $request['user']);
                if (!isset($request['fqcr']) || $request['fqcr'] == 'MissingAuthor')
                    $this->data['MissingAuthor'] = $this->fqcmmodel->missingAuthor($startdate, FALSE, $request['user']);
                if (!isset($request['fqcr']) || $request['fqcr'] == 'EndDateWithNoStartDate')
                    $this->data['EndDateWithNoStartDate'] = $this->fqcmmodel->endDateWithNoStartDate($startdate, FALSE, $request['user']);
                if (!isset($request['fqcr']) || $request['fqcr'] == 'AgentsWithNoLastName')
                    $this->data['AgentsWithNoLastName'] = $this->fqcmmodel->agentsWithNoLastName($startdate, FALSE, $request['user']);
                if (!isset($request['fqcr']) || $request['fqcr'] == 'GroupAgentAsPersonAgent')
                    $this->data['GroupAgentAsPersonAgent'] = $this->fqcmmodel->groupAgentAsPersonAgent($startdate, FALSE, $request['user']);
                if (!isset($request['fqcr']) || $request['fqcr'] == 'IncorrectAgentAsCollector')
                    $this->data['IncorrectAgentAsCollector'] = $this->fqcmmodel->incorrectAgentAsCollector($startdate, FALSE, $request['user']);
                if (!isset($request['fqcr']) || $request['fqcr'] == 'DodgyPart')
                    $this->data['DodgyPart'] = $this->fqcmmodel->dodgyPart($startdate, FALSE, $request['user']);
                /*if (!isset($request['fqcr']) || $request['fqcr'] == 'PossiblyDodgyPart')
                    $this->data['PossiblyDodgyPart'] = $this->fqcmmodel->possiblyDodgyPart($startdate, FALSE, $request['user']);*/
                if (!isset($request['fqcr']) || $request['fqcr'] == 'AlternativeNameInCurrentDetermination')
                    $this->data['AlternativeNameInCurrentDetermination'] = $this->fqcmmodel->alternativeNameInCurrentDetermination($startdate, FALSE, $request['user']);
                if (!isset($request['fqcr']) || $request['fqcr'] == 'PrimaryCollectorNotFirst')
                    $this->data['PrimaryCollectorNotFirst'] = $this->fqcmmodel->primaryCollectorNotFirst($startdate, FALSE, $request['user']);
                if (!isset($request['fqcr']) || $request['fqcr'] == 'JarSizeMissing')
                    $this->data['JarSizeMissing'] = $this->fqcmmodel->jarSizeMissing($startdate, FALSE, $request['user']);
                if (!isset($request['fqcr']) || $request['fqcr'] == 'InappropriateQuantityInPreparation')
                    $this->data['InappropriateQuantityInPreparation'] = $this->fqcmmodel->inappropriateQuantityInPreparation($startdate, FALSE, $request['user']);
                if (!isset($request['fqcr']) || $request['fqcr'] == 'MissingDatum')
                    $this->data['MissingDatum'] = $this->fqcmmodel->missingDatum($startdate, FALSE, $request['user']);
                if (!isset($request['fqcr']) || $request['fqcr'] == 'StoredUnderMultipleNames')
                    $this->data['StoredUnderMultipleNames'] = $this->fqcmmodel->storedUnderMultipleNames($startdate, FALSE, $request['user']);
                if (!isset($request['fqcr']) || $request['fqcr'] == 'TooManyPrimaryPreparations')
                    $this->data['TooManyPrimaryPreparations'] = $this->fqcmmodel->tooManyPrimaryPreparations($startdate, FALSE, $request['user']);
                if (!isset($request['fqcr']) || $request['fqcr'] == 'NoPrimaryPreparations')
                    $this->data['NoPrimaryPreparations'] = $this->fqcmmodel->noPrimaryPreparations($startdate, FALSE, $request['user']);
                if (!isset($request['fqcr']) || $request['fqcr'] == 'NoCollectingDate')
                    $this->data['NoCollectingDate'] = $this->fqcmmodel->noCollectingDate($startdate, FALSE, $request['user']);
                if (!isset($request['fqcr']) || $request['fqcr'] == 'MissingGeography')
                    $this->data['MissingGeography'] = $this->fqcmmodel->missingGeography($startdate, FALSE, $request['user']);
                if (!isset($request['fqcr']) || $request['fqcr'] == 'CultivatedInGeography')
                    $this->data['CultivatedInGeography'] = $this->fqcmmodel->cultivatedInGeography($startdate, FALSE, $request['user']); 
                if (!isset($request['fqcr']) || $request['fqcr'] == 'MissingCultSource')
                    $this->data['MissingCultSource'] = $this->fqcmmodel->missingCultSource($startdate, FALSE, $request['user']);                
                if (!isset($request['fqcr']) || $request['fqcr'] == 'MissingIntroSource')
                    $this->data['MissingIntroSource'] = $this->fqcmmodel->missingIntroSource($startdate, FALSE, $request['user']);    
                if (!isset($request['fqcr']) || $request['fqcr'] == 'MissingStorage')
                    $this->data['MissingStorage'] = $this->fqcmmodel->missingStorage($startdate, FALSE, $request['user']);    
                if (!isset($request['fqcr']) || $request['fqcr'] == 'TreatedByNotNullAndCurationSponsorNull')
                    $this->data['TreatedByNotNullAndCurationSponsorNull'] = $this->fqcmmodel->treatedByNotNullAndCurationSponsorNull($startdate, FALSE, $request['user']);    
                if (!isset($request['fqcr']) || $request['fqcr'] == 'TreatedByNotNullOtherTreatmentFieldsNull')
                    $this->data['TreatedByNotNullOtherTreatmentFieldsNull'] = $this->fqcmmodel->treatedByNotNullOtherTreatmentFieldsNull($startdate, FALSE, $request['user']);    
                if (!isset($request['fqcr']) || $request['fqcr'] == 'SeverityOrCauseNotNullButAssessedByNull')
                    $this->data['SeverityOrCauseNotNullButAssessedByNull'] = $this->fqcmmodel->severityOrCauseNotNullButAssessedByNull($startdate, FALSE, $request['user']);    
                if (!isset($request['fqcr']) || $request['fqcr'] == 'DuplicateDuplicatePreparations')
                    $this->data['DuplicateDuplicatePreparations'] = $this->fqcmmodel->duplicateDuplicatePreparations($startdate, FALSE, $request['user'], 'duplicate');    
                if (!isset($request['fqcr']) || $request['fqcr'] == 'DuplicateSeedDuplicatePreparations')
                    $this->data['DuplicateSeedDuplicatePreparations'] = $this->fqcmmodel->duplicateDuplicatePreparations($startdate, FALSE, $request['user'], 'seed duplicate');    
                /*if (!isset($request['fqcr']) || $request['fqcr'] == 'MissingExHerbarium')
                    $this->data['MissingExHerbarium'] = $this->fqcmmodel->missingExHerbarium($startdate, FALSE, $request['user']);    
                if (!isset($request['fqcr']) || $request['fqcr'] == 'MissingExHerbariumCatalogNumber')
                    $this->data['MissingExHerbariumCatalogNumber'] = $this->fqcmmodel->missingExHerbariumCatalogNumber($startdate, FALSE, $request['user']);*/    
            }
            elseif ($this->input->post('submit_localities')) {
                $this->data['SharedLocalities'] = $this->fqcmmodel->sharedLocalities($startdate, FALSE, $this->input->post('user'));               
            }
            
            $this->load->view('fqcmview', $this->data);
        }
        else {
            $this->index();
        }
    }

    function createRecordSet() {
        if (!$this->input->post('user') || !$this->input->post('recordset') || !$this->input->post('recsetitems')) {
            if (!$this->input->post('user')) {
                $this->session->set_flashdata('error', 'Please select a Specify user...');
            }
            if (!$this->input->post('recordset')) {
                $this->session->set_flashdata('error', 'Please enter a record set name...');
            }
            if (!$this->input->post('recordsetitems')) {
                $this->session->set_flashdata('error', 'Please select some records...');
            }
            
        } else {
            if ($this->recordsetmodel->findRecordSetName($this->input->post('user'), $this->input->post('recordset')) != FALSE) {
                $this->session->set_flashdata('error', 'A record set of this name already exists');
            } else {
                $agentid = trim($this->input->post('user'));
                $specifyuser = $this->recordsetmodel->findSpecifyUserID($agentid);
                $recordsetname = trim($this->input->post('recordset'));
                
                $this->recordsetmodel->createRecordSet($specifyuser, $recordsetname, $agentid);
                $recordsetid = $this->recordsetmodel->findRecordSetName($specifyuser, $recordsetname);
                $this->fqcmmodel->createRecordSetItems($recordsetid, $this->input->post('recsetitems'));
                $this->session->set_flashdata('success', "Record set <b>$recordsetname</b> has been created.");
            }
        }
    }
    
    function createCatalogNumberString() {
        if ($this->input->post('recsetitems')) {
            $data = $this->fqcmmodel->catalogNumberString($this->input->post('recsetitems'));
            $this->data['catnostring'] = implode(',', $data);
        }
    }
    
    function taxonnames() {
        $this->load->view('fqcm_taxa_view', $this->data);
    }

}
?>
