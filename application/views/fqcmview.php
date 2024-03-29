<?php require_once('header.php'); ?>

<?php 

function specify7_link($colObjId, $catno) {
    return anchor("https://specify.rbg.vic.gov.au/specify/view/collectionobject/{$colObjId}/", $catno, ['target' => '_blank']);
}

?>

<div class="container">
    <div class="row">
        <div class="col-md-12">
            <h2>Fancy Quality Control Machine</h2>
            <form action="<?=site_url()?>fqcm/doqc" method="post" class="form-horizontal">
                
                <div class="form-group">
                    <label class="col-md-3 control-label" for="user">Username:</label>                    
                    <div class="col-md-9">
                    <select id="user" name="user" class="form-control" required="true">
                        <option value="">(select a user)</option>
                        <?php foreach($Users as $user): ?>
                        <?php
                            $selected = false;
                            if ($this->input->post('user') && $user['AgentID'] == $this->input->post('user'))
                                $selected = ' selected="selected"'
                        ?>
                        <option value="<?=$user['AgentID']?>"<?=$selected?>><?=$user['Name']?></option>
                        <?php endforeach;?>
                    </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="col-md-3 control-label" for="startdate">Start date:</label>
                    <div class="col-md-9">
                        <input type="text" name="startdate" id="startdate"
                               value="<?=(isset($startdate)) ? $startdate : date('Y-m-d')?>"
                               placeholder="yyyy-mm-dd"
                               class="form-control" required="true" />
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-md-3 control-label" for="enddate">End date:</label>
                    <div class="col-md-9">
                        <input type="text" name="enddate" id="enddate"
                               value="<?=(isset($enddate)) ? $enddate : date('Y-m-d')?>"
                               placeholder="yyyy-mm-dd"
                               class="form-control" required="true" />
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-md-3 control-label"></label>
                    <div class="col-md-9">
                        <input type="submit" name="submit" 
                               value="Check databasing"
                               class="btn btn-primary" />
                        <input type="submit" name="submit_localities" 
                               value="Check my shared localities"
                               class="btn btn-primary" />
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-md-3 control-label" for="recordset">Record set:</label>
                    <div class="col-md-9">
                    <input type="text" name="recordset" id="recordset"
                           value="<?=$this->input->post('recordset')?>"
                           class="form-control" />
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="col-md-3"></label>
                    <div class="col-md-9">
                        <input type="submit" class="btn btn-primary" name="createrecordset" value="Create record set" />
                        <button type="submit" class="btn btn-primary" name="catnostring">Create catalogue number string</button>
                    </div>
                </div>

                <div class="catnostring form-group">
                    <div class="col-md-12">
                        <textarea rows="6" class="form-control"></textarea>
                    </div>
                </div>

            <p>&nbsp;</p>

            <?php
                $url = site_url() . 'fqcm/doqc/';
                $uri = array();
                if (isset($request['startdate'])) $uri['startdate'] = $request['startdate'];
                if (isset($request['user'])) $uri['user'] = $request['user'];
                $url = $url . $this->uri->assoc_to_uri($uri);
            ?>
            <?php if (isset($request)): ?>
            <!--p><a href="<?=$url?>"><?=$url?></a></p-->
            <?php endif; ?>

            <?php if((isset($HighCatalogueNumbers) && $HighCatalogueNumbers) ||
                    (isset($catalogedBeforeCollectingDate) && $catalogedBeforeCollectingDate) ||
                    (isset($zombieCollector) && $zombieCollector) ||
                    (isset($inferredFromVerbatimCollector) && $inferredFromVerbatimCollector) ||
                    (isset($DodgyPart) && $DodgyPart) ||
                    (isset($PossiblyDodgyPart) && $PossiblyDodgyPart)): ?>
            <h3>Collection object</h3>

            <?php if(isset($HighCatalogueNumbers) && $HighCatalogueNumbers): ?>
            <h4>These catalogue numbers are too high (<?=count($HighCatalogueNumbers)?>):</h4>
            <div>
                <a href="#" class="selectall">select/clear all</a>
            </div>
            <table class="table table-condensed table-bordered dberrors headingcolour" style="width: 100%">
                <tr>
                    <th style="width: 4%">&nbsp;</th>
                    <th style="width: 18%">Catalogue number</th>
                    <th style="width: 25%">Created by</th>
                    <th style="width: 14%">Created on</th>
                    <th style="width: 25%">Edited by</th>
                    <th style="width: 14%">Edited on</th>
                </tr>
                <?php foreach ($HighCatalogueNumbers as $prep): ?>
                <tr>
                    <td style="width: 4%">
                        <?php
                            $value = $prep['CollectionObjectID'];
                            $opts = array(
                                'name' => 'recsetitems[]',
                                'value' => $value,
                                'checked' => ($this->input->post('recsetitems') && in_array($value, $this->input->post('recsetitems'))) ? TRUE : FALSE
                            );
                        ?>
                        <?=form_checkbox($opts)?>
                    </td>
                    <td><?=specify7_link($prep['CollectionObjectID'], $prep['CatalogNumber']);?></td>
                    <td><?=$prep['CreatedBy']?></td>
                    <td><?=$prep['Created']?></td>
                    <td><?=$prep['EditedBy']?></td>
                    <td><?=$prep['Edited']?></td>
                </tr>
                <?php endforeach; ?>
            </table>
            <?php endif; ?>

            <?php if(isset($catalogedBeforeCollectingDate) && $catalogedBeforeCollectingDate): ?>
            <h4>These records were ostensibly cataloged before they were collected (<?=count($catalogedBeforeCollectingDate)?>):</h4>
            <div>
                <a href="#" class="selectall">select/clear all</a>
            </div>
            <table class="table table-condensed table-bordered dberrors headingcolour" style="width: 100%">
                <tr>
                    <th style="width: 4%">&nbsp;</th>
                    <th style="width: 18%">Catalogue number</th>
                    <th style="width: 25%">Created by</th>
                    <th style="width: 14%">Created on</th>
                    <th style="width: 25%">Edited by</th>
                    <th style="width: 14%">Edited on</th>
                </tr>
                <?php foreach ($catalogedBeforeCollectingDate as $prep): ?>
                <tr>
                    <td style="width: 4%">
                        <?php
                            $value = $prep['CollectionObjectID'];
                            $opts = array(
                                'name' => 'recsetitems[]',
                                'value' => $value,
                                'checked' => ($this->input->post('recsetitems') && in_array($value, $this->input->post('recsetitems'))) ? TRUE : FALSE
                            );
                        ?>
                        <?=form_checkbox($opts)?>
                    </td>
                    <td><?=specify7_link($prep['CollectionObjectID'], $prep['CatalogNumber']);?></td>
                    <td><?=$prep['CreatedBy']?></td>
                    <td><?=$prep['Created']?></td>
                    <td><?=$prep['EditedBy']?></td>
                    <td><?=$prep['Edited']?></td>
                </tr>
                <?php endforeach; ?>
            </table>
            <?php endif; ?>

            <?php if(isset($zombieCollector) && $zombieCollector): ?>
            <h4>Collectors have been entered, but &apos;Collector Unknown&apos; box has been ticked (<?=count($zombieCollector)?>):</h4>
            <div>
                <a href="#" class="selectall">select/clear all</a>
            </div>
            <table class="table table-condensed table-bordered dberrors headingcolour" style="width: 100%">
                <tr>
                    <th style="width: 4%">&nbsp;</th>
                    <th style="width: 18%">Catalogue number</th>
                    <th style="width: 25%">Created by</th>
                    <th style="width: 14%">Created on</th>
                    <th style="width: 25%">Edited by</th>
                    <th style="width: 14%">Edited on</th>
                </tr>
                <?php foreach ($zombieCollector as $prep): ?>
                <tr>
                    <td style="width: 4%">
                        <?php
                            $value = $prep['CollectionObjectID'];
                            $opts = array(
                                'name' => 'recsetitems[]',
                                'value' => $value,
                                'checked' => ($this->input->post('recsetitems') && in_array($value, $this->input->post('recsetitems'))) ? TRUE : FALSE
                            );
                        ?>
                        <?=form_checkbox($opts)?>
                    </td>
                    <td><?=specify7_link($prep['CollectionObjectID'], $prep['CatalogNumber']);?></td>
                    <td><?=$prep['CreatedBy']?></td>
                    <td><?=$prep['Created']?></td>
                    <td><?=$prep['EditedBy']?></td>
                    <td><?=$prep['Edited']?></td>
                </tr>
                <?php endforeach; ?>
            </table>
            <?php endif; ?>

            <?php if(isset($inferredFromVerbatimCollector) && $inferredFromVerbatimCollector): ?>
            <h4>Verbatim collectors have been entered, but &apos;Collector Inferred&apos; box has been ticked (<?=count($inferredFromVerbatimCollector)?>):</h4>
            <div>
                <a href="#" class="selectall">select/clear all</a>
            </div>
            <table class="table table-condensed table-bordered dberrors headingcolour" style="width: 100%">
                <tr>
                    <th style="width: 4%">&nbsp;</th>
                    <th style="width: 18%">Catalogue number</th>
                    <th style="width: 25%">Created by</th>
                    <th style="width: 14%">Created on</th>
                    <th style="width: 25%">Edited by</th>
                    <th style="width: 14%">Edited on</th>
                </tr>
                <?php foreach ($inferredFromVerbatimCollector as $prep): ?>
                <tr>
                    <td style="width: 4%">
                        <?php
                            $value = $prep['CollectionObjectID'];
                            $opts = array(
                                'name' => 'recsetitems[]',
                                'value' => $value,
                                'checked' => ($this->input->post('recsetitems') && in_array($value, $this->input->post('recsetitems'))) ? TRUE : FALSE
                            );
                        ?>
                        <?=form_checkbox($opts)?>
                    </td>
                    <td><?=specify7_link($prep['CollectionObjectID'], $prep['CatalogNumber']);?></td>
                    <td><?=$prep['CreatedBy']?></td>
                    <td><?=$prep['Created']?></td>
                    <td><?=$prep['EditedBy']?></td>
                    <td><?=$prep['Edited']?></td>
                </tr>
                <?php endforeach; ?>
            </table>
            <?php endif; ?>

            <?php if (isset($DodgyPart)): ?>
            <?php if ($DodgyPart): ?>
            <h4>The part is not a letter (<?=count($DodgyPart)?>):</h4>
            <div><a href="#" class="selectall">select/clear all</a></div>
            <table class="table table-condensed table-bordered dberrors headingcolour" style="width: 100%">
                <tr>
                    <th style="width: 4%">&nbsp;</th>
                    <th style="width: 18%">Catalogue number</th>
                    <th style="width: 25%">Created by</th>
                    <th style="width: 14%">Created on</th>
                    <th style="width: 25%">Edited by</th>
                    <th style="width: 14%">Edited on</th>
                </tr>
                <?php foreach ($DodgyPart as $prep): ?>
                <tr>
                    <td style="width: 4%">
                        <?php
                            $value = $prep['CollectionObjectID'];
                            $opts = array(
                                'name' => 'recsetitems[]',
                                'value' => $value,
                                'checked' => ($this->input->post('recsetitems') && in_array($value, $this->input->post('recsetitems'))) ? TRUE : FALSE
                            );
                        ?>
                        <?=form_checkbox($opts)?>
                    </td>
                    <td><?=specify7_link($prep['CollectionObjectID'], $prep['CatalogNumber']);?></td>
                    <td><?=$prep['CreatedBy']?></td>
                    <td><?=$prep['Created']?></td>
                    <td><?=$prep['EditedBy']?></td>
                    <td><?=$prep['Edited']?></td>
                </tr>
                <?php endforeach; ?>
            </table>
            <?php endif; ?>
            <?php endif; ?>


            <?php if (isset($PossiblyDodgyPart)): ?>
            <?php if ($PossiblyDodgyPart): ?>
            <h4>Are there really this many parts to the collection? (<?=count($PossiblyDodgyPart)?>):</h4>
            <div><a href="#" class="selectall">select/clear all</a></div>
            <table class="table table-condensed table-bordered dberrors headingcolour" style="width: 100%">
                <tr>
                    <th style="width: 4%">&nbsp;</th>
                    <th style="width: 18%">Catalogue number</th>
                    <th style="width: 25%">Created by</th>
                    <th style="width: 14%">Created on</th>
                    <th style="width: 25%">Edited by</th>
                    <th style="width: 14%">Edited on</th>
                </tr>
                <?php foreach ($PossiblyDodgyPart as $prep): ?>
                <tr>
                    <td style="width: 4%">
                        <?php
                            $value = $prep['CollectionObjectID'];
                            $opts = array(
                                'name' => 'recsetitems[]',
                                'value' => $value,
                                'checked' => ($this->input->post('recsetitems') && in_array($value, $this->input->post('recsetitems'))) ? TRUE : FALSE
                            );
                        ?>
                        <?=form_checkbox($opts)?>
                    </td>
                    <td><?=specify7_link($prep['CollectionObjectID'], $prep['CatalogNumber']);?></td>
                    <td><?=$prep['CreatedBy']?></td>
                    <td><?=$prep['Created']?></td>
                    <td><?=$prep['EditedBy']?></td>
                    <td><?=$prep['Edited']?></td>
                </tr>
                <?php endforeach; ?>
            </table> 
            <?php endif; ?>
            <?php endif; ?>

            <?php endif; // collection object block ?>

            <?php if((isset($MissingDetermination) && $MissingDetermination) || 
                    (isset($MissingTaxonName) && $MissingTaxonName) ||
                    (isset($DetDateEarlierThanCollDate) && $DetDateEarlierThanCollDate) ||
                    (isset($MissingProtologue) && $MissingProtologue) ||
                    (isset($TypeMismatch) && $TypeMismatch) ||
                    (isset($TypeDetIsCurrent) && $TypeDetIsCurrent) ||
                    (isset($AlternativeNameInCurrentDetermination) && $AlternativeNameInCurrentDetermination) ||
                    (isset($TypeDetOverriddenByIndet) && $TypeDetOverriddenByIndet) ||
                    (isset($StoredUnderMultipleNames) && $StoredUnderMultipleNames)) : ?>

            <h3>Determination</h3>

            <?php if (isset($MissingDetermination)): ?>
            <?php if ($MissingDetermination): ?>
            <h4>These records don't have any determinations (<?=count($MissingDetermination)?>):</h4>
            <div><a href="#" class="selectall">select/clear all</a></div>
            <table class="table table-condensed table-bordered dberrors headingcolour1" style="width: 100%">
                <tr>
                    <th style="width: 4%">&nbsp;</th>
                    <th style="width: 18%">Catalogue number</th>
                    <th style="width: 25%">Created by</th>
                    <th style="width: 14%">Created on</th>
                    <th style="width: 25%">Edited by</th>
                    <th style="width: 14%">Edited on</th>
                </tr>
                <?php foreach ($MissingDetermination as $prep): ?>
                <tr>
                    <td style="width: 4%">
                        <?php
                            $value = $prep['CollectionObjectID'];
                            $opts = array(
                                'name' => 'recsetitems[]',
                                'value' => $value,
                                'checked' => ($this->input->post('recsetitems') && in_array($value, $this->input->post('recsetitems'))) ? TRUE : FALSE
                            );
                        ?>
                        <?=form_checkbox($opts)?>
                    </td>
                    <td><?=specify7_link($prep['CollectionObjectID'], $prep['CatalogNumber']);?></td>
                    <td><?=$prep['CreatedBy']?></td>
                    <td><?=$prep['Created']?></td>
                    <td><?=$prep['EditedBy']?></td>
                    <td><?=$prep['Edited']?></td>
                </tr>
                <?php endforeach; ?>
            </table> 
            <?php endif; ?>
            <?php endif; ?>

            <?php if (isset($MissingTaxonName)): ?>
            <?php if ($MissingTaxonName): ?>
            <h4>These records have determinations that are missing taxon names (<?=count($MissingTaxonName)?>):</h4>
            <div><a href="#" class="selectall">select/clear all</a></div>
            <table class="table table-condensed table-bordered dberrors headingcolour1" style="width: 100%">
                <tr>
                    <th style="width: 4%">&nbsp;</th>
                    <th style="width: 18%">Catalogue number</th>
                    <th style="width: 25%">Det. created by</th>
                    <th style="width: 14%">Det. created on</th>
                    <th style="width: 25%">Det. edited by</th>
                    <th style="width: 14%">Det. edited on</th>
                </tr>
                <?php foreach ($MissingTaxonName as $prep): ?>
                <tr>
                    <td style="width: 4%">
                        <?php
                            $value = $prep['CollectionObjectID'];
                            $opts = array(
                                'name' => 'recsetitems[]',
                                'value' => $value,
                                'checked' => ($this->input->post('recsetitems') && in_array($value, $this->input->post('recsetitems'))) ? TRUE : FALSE
                            );
                        ?>
                        <?=form_checkbox($opts)?>
                    </td>
                    <td><?=specify7_link($prep['CollectionObjectID'], $prep['CatalogNumber']);?></td>
                    <td><?=$prep['CreatedBy']?></td>
                    <td><?=$prep['Created']?></td>
                    <td><?=$prep['EditedBy']?></td>
                    <td><?=$prep['Edited']?></td>
                </tr>
                <?php endforeach; ?>
            </table> 
            <?php endif; ?>
            <?php endif; ?>

            <?php if (isset($DetDateEarlierThanCollDate)): ?>
            <?php if ($DetDateEarlierThanCollDate): ?>
            <h4>These records were apparently determined before they were collected (<?=count($DetDateEarlierThanCollDate)?>):</h4>
            <div><a href="#" class="selectall">select/clear all</a></div>
            <table class="table table-condensed table-bordered dberrors headingcolour1" style="width: 100%">
                <tr>
                    <th style="width: 4%">&nbsp;</th>
                    <th style="width: 18%">Catalogue number</th>
                    <th style="width: 25%">Created by</th>
                    <th style="width: 14%">Created on</th>
                    <th style="width: 25%">Edited by</th>
                    <th style="width: 14%">Edited on</th>
                </tr>
                <?php foreach ($DetDateEarlierThanCollDate as $prep): ?>
                <tr>
                    <td style="width: 4%">
                        <?php
                            $value = $prep['CollectionObjectID'];
                            $opts = array(
                                'name' => 'recsetitems[]',
                                'value' => $value,
                                'checked' => ($this->input->post('recsetitems') && in_array($value, $this->input->post('recsetitems'))) ? TRUE : FALSE
                            );
                        ?>
                        <?=form_checkbox($opts)?>
                    </td>
                    <td><?=specify7_link($prep['CollectionObjectID'], $prep['CatalogNumber']);?></td>
                    <td><?=$prep['CreatedBy']?></td>
                    <td><?=$prep['Created']?></td>
                    <td><?=$prep['EditedBy']?></td>
                    <td><?=$prep['Edited']?></td>
                </tr>
                <?php endforeach; ?>
            </table> 
            <?php endif; ?>
            <?php endif; ?>

            <?php if (isset($MissingProtologue)): ?>
            <?php if ($MissingProtologue): ?>
            <h4>These records are types, but some or all of the protologue details are missing (<?=count($MissingProtologue)?>):</h4>
            <div><a href="#" class="selectall">select/clear all</a></div>
            <table class="table table-condensed table-bordered dberrors headingcolour1" style="width: 100%">
                <tr>
                    <th style="width: 4%">&nbsp;</th>
                    <th style="width: 18%">Catalogue number</th>
                    <th style="width: 25%">Det. created by</th>
                    <th style="width: 14%">Det. created on</th>
                    <th style="width: 25%">Det. edited by</th>
                    <th style="width: 14%">Det. edited on</th>
                </tr>
                <?php foreach ($MissingProtologue as $prep): ?>
                <tr>
                    <td style="width: 4%">
                        <?php
                            $value = $prep['CollectionObjectID'];
                            $opts = array(
                                'name' => 'recsetitems[]',
                                'value' => $value,
                                'checked' => ($this->input->post('recsetitems') && in_array($value, $this->input->post('recsetitems'))) ? TRUE : FALSE
                            );
                        ?>
                        <?=form_checkbox($opts)?>
                    </td>
                    <td><?=specify7_link($prep['CollectionObjectID'], $prep['CatalogNumber']);?></td>
                    <td><?=$prep['CreatedBy']?></td>
                    <td><?=$prep['Created']?></td>
                    <td><?=$prep['EditedBy']?></td>
                    <td><?=$prep['Edited']?></td>
                </tr>
                <?php endforeach; ?>
            </table> 
            <?php endif; ?>
            <?php endif; ?>


            <?php if (isset($TypeMismatch)): ?>
            <?php if ($TypeMismatch): ?>
            <h4>&apos;Stored under this name&apos; is flagged, but the Det. type is not &apos;Type status&apos; (<?=count($TypeMismatch)?>):</h4>
            <div><a href="#" class="selectall">select/clear all</a></div>
            <table class="table table-condensed table-bordered dberrors headingcolour1" style="width: 100%">
                <tr>
                    <th style="width: 4%">&nbsp;</th>
                    <th style="width: 18%">Catalogue number</th>
                    <th style="width: 25%">Created by</th>
                    <th style="width: 14%">Created on</th>
                    <th style="width: 25%">Edited by</th>
                    <th style="width: 14%">Edited on</th>
                </tr>
                <?php foreach ($TypeMismatch as $prep): ?>
                <tr>
                    <td style="width: 4%">
                        <?php
                            $value = $prep['CollectionObjectID'];
                            $opts = array(
                                'name' => 'recsetitems[]',
                                'value' => $value,
                                'checked' => ($this->input->post('recsetitems') && in_array($value, $this->input->post('recsetitems'))) ? TRUE : FALSE
                            );
                        ?>
                        <?=form_checkbox($opts)?>
                    </td>
                    <td><?=specify7_link($prep['CollectionObjectID'], $prep['CatalogNumber']);?></td>
                    <td><?=$prep['CreatedBy']?></td>
                    <td><?=$prep['Created']?></td>
                    <td><?=$prep['EditedBy']?></td>
                    <td><?=$prep['Edited']?></td>
                </tr>
                <?php endforeach; ?>
            </table>
            <?php endif; ?>
            <?php endif; ?>

            <?php if (isset($TypeDetIsCurrent)): ?>
            <?php if ($TypeDetIsCurrent): ?>
            <h4>The type det. is flagged as the current det. in these records (<?=count($TypeDetIsCurrent)?>):</h4>
            <div><a href="#" class="selectall">select/clear all</a></div>
            <table class="table table-condensed table-bordered dberrors headingcolour1" style="width: 100%">
                <tr>
                    <th style="width: 4%">&nbsp;</th>
                    <th style="width: 18%">Catalogue number</th>
                    <th style="width: 25%">Created by</th>
                    <th style="width: 14%">Created on</th>
                    <th style="width: 25%">Edited by</th>
                    <th style="width: 14%">Edited on</th>
                </tr>
                <?php foreach ($TypeDetIsCurrent as $prep): ?>
                <tr>
                    <td style="width: 4%">
                        <?php
                            $value = $prep['CollectionObjectID'];
                            $opts = array(
                                'name' => 'recsetitems[]',
                                'value' => $value,
                                'checked' => ($this->input->post('recsetitems') && in_array($value, $this->input->post('recsetitems'))) ? TRUE : FALSE
                            );
                        ?>
                        <?=form_checkbox($opts)?>
                    </td>
                    <td><?=specify7_link($prep['CollectionObjectID'], $prep['CatalogNumber']);?></td>
                    <td><?=$prep['CreatedBy']?></td>
                    <td><?=$prep['Created']?></td>
                    <td><?=$prep['EditedBy']?></td>
                    <td><?=$prep['Edited']?></td>
                </tr>
                <?php endforeach; ?>
            </table>
            <?php endif; ?>
            <?php endif; ?>

            <?php if (isset($AlternativeNameInCurrentDetermination)): ?>
            <?php if ($AlternativeNameInCurrentDetermination): ?>
            <h4>The current determination has something in the 'Alternative name' field (<?=count($AlternativeNameInCurrentDetermination)?>):</h4>
            <div><a href="#" class="selectall">select/clear all</a></div>
            <table class="table table-condensed table-bordered dberrors headingcolour1" style="width: 100%">
                <tr>
                    <th style="width: 4%">&nbsp;</th>
                    <th style="width: 18%">Catalogue number</th>
                    <th style="width: 25%">Created by</th>
                    <th style="width: 14%">Created on</th>
                    <th style="width: 25%">Edited by</th>
                    <th style="width: 14%">Edited on</th>
                </tr>
                <?php foreach ($AlternativeNameInCurrentDetermination as $prep): ?>
                <tr>
                    <td style="width: 4%">
                        <?php
                            $value = $prep['CollectionObjectID'];
                            $opts = array(
                                'name' => 'recsetitems[]',
                                'value' => $value,
                                'checked' => ($this->input->post('recsetitems') && in_array($value, $this->input->post('recsetitems'))) ? TRUE : FALSE
                            );
                        ?>
                        <?=form_checkbox($opts)?>
                    </td>
                    <td><?=specify7_link($prep['CollectionObjectID'], $prep['CatalogNumber']);?></td>
                    <td><?=$prep['CreatedBy']?></td>
                    <td><?=$prep['Created']?></td>
                    <td><?=$prep['EditedBy']?></td>
                    <td><?=$prep['Edited']?></td>
                </tr>
                <?php endforeach; ?>
            </table>
            <?php endif; ?>
            <?php endif; ?>

            <?php if (isset($TypeDetOverriddenByIndet)): ?>
            <?php if ($TypeDetOverriddenByIndet): ?>
            <h4>Current det. for type is INDET (<?=count($TypeDetOverriddenByIndet)?>):</h4>
            <div><a href="#" class="selectall">select/clear all</a></div>
            <table class="table table-condensed table-bordered dberrors headingcolour1" style="width: 100%">
                <tr>
                    <th style="width: 4%">&nbsp;</th>
                    <th style="width: 18%">Catalogue number</th>
                    <th style="width: 25%">Created by</th>
                    <th style="width: 14%">Created on</th>
                    <th style="width: 25%">Edited by</th>
                    <th style="width: 14%">Edited on</th>
                </tr>
                <?php foreach ($TypeDetOverriddenByIndet as $prep): ?>
                <tr>
                    <td style="width: 4%">
                        <?php
                            $value = $prep['CollectionObjectID'];
                            $opts = array(
                                'name' => 'recsetitems[]',
                                'value' => $value,
                                'checked' => ($this->input->post('recsetitems') && in_array($value, $this->input->post('recsetitems'))) ? TRUE : FALSE
                            );
                        ?>
                        <?=form_checkbox($opts)?>
                    </td>
                    <td><?=specify7_link($prep['CollectionObjectID'], $prep['CatalogNumber']);?></td>
                    <td><?=$prep['CreatedBy']?></td>
                    <td><?=$prep['Created']?></td>
                    <td><?=$prep['EditedBy']?></td>
                    <td><?=$prep['Edited']?></td>
                </tr>
                <?php endforeach; ?>
            </table>
            <?php endif; ?>
            <?php endif; ?>

            <?php if (isset($StoredUnderMultipleNames)): ?>
            <?php if ($StoredUnderMultipleNames): ?>
            <h4>'Stored under this name' is flagged in more than one determination (<?=count($StoredUnderMultipleNames)?>):</h4>
            <div><a href="#" class="selectall">select/clear all</a></div>
            <table class="table table-condensed table-bordered dberrors headingcolour1" style="width: 100%">
                <tr>
                    <th style="width: 4%">&nbsp;</th>
                    <th style="width: 18%">Catalogue number</th>
                    <th style="width: 25%">Det. Created by</th>
                    <th style="width: 14%">Det. Created on</th>
                    <th style="width: 25%">Det. Edited by</th>
                    <th style="width: 14%">Det. Edited on</th>
                </tr>
                <?php foreach ($StoredUnderMultipleNames as $prep): ?>
                <tr>
                    <td style="width: 4%">
                        <?php
                            $value = $prep['CollectionObjectID'];
                            $opts = array(
                                'name' => 'recsetitems[]',
                                'value' => $value,
                                'checked' => ($this->input->post('recsetitems') && in_array($value, $this->input->post('recsetitems'))) ? TRUE : FALSE
                            );
                        ?>
                        <?=form_checkbox($opts)?>
                    </td>
                    <td><?=specify7_link($prep['CollectionObjectID'], $prep['CatalogNumber']);?></td>
                    <td><?=$prep['CreatedBy']?></td>
                    <td><?=$prep['Created']?></td>
                    <td><?=$prep['EditedBy']?></td>
                    <td><?=$prep['Edited']?></td>
                </tr>
                <?php endforeach; ?>
            </table>
            <?php endif; ?>
            <?php endif; ?>

            <?php endif; // determination block ?>

            <?php if((isset($NewSubgenus) && $NewSubgenus) ||
                    (isset($MissingAuthor) && $MissingAuthor) || (isset($MissingGenusStorage) && $MissingGenusStorage)): ?>
            <h3>Taxon name</h3>
            <?php endif; ?>

            <?php if (isset($NewSubgenus)): ?>
            <?php if ($NewSubgenus): ?>
            <h4>The following taxon has been added as a subgenus; check that it's not actually a species (<?=count($NewSubgenus)?>):</h4>
            <div><a href="#" class="selectall">select/clear all</a></div>
            <table class="table table-condensed table-bordered dberrors headingcolour3" style="width: 100%">
                <tr>
                    <th style="width: 4%">&nbsp;</th>
                    <th style="width: 18%">Taxon name</th>
                    <th style="width: 25%">Created by</th>
                    <th style="width: 14%">Created on</th>
                    <th style="width: 25%">Edited by</th>
                    <th style="width: 14%">Edited on</th>
                </tr>
                <?php foreach ($NewSubgenus as $prep): ?>
                <tr>
                    <td>
                    </td>
                    <td><?=$prep['FullName']?></td>
                    <td><?=$prep['CreatedBy']?></td>
                    <td><?=$prep['Created']?></td>
                    <td><?=$prep['EditedBy']?></td>
                    <td><?=$prep['Edited']?></td>
                </tr>
                <?php endforeach; ?>
            </table>
            <?php endif; ?>
            <?php endif; ?>

            <?php if (isset($MissingAuthor)): ?>
            <?php if ($MissingAuthor): ?>
            <h4>The following taxon names are missing the author (<?=count($MissingAuthor)?>):</h4>
            <div><a href="#" class="selectall">select/clear all</a></div>
            <table class="table table-condensed table-bordered dberrors headingcolour3" style="width: 100%">
                <tr>
                    <th style="width: 4%">&nbsp;</th>
                    <th style="width: 18%">Taxon name</th>
                    <th style="width: 25%">Created by</th>
                    <th style="width: 14%">Created on</th>
                    <th style="width: 25%">Edited by</th>
                    <th style="width: 14%">Edited on</th>
                </tr>
                <?php foreach ($MissingAuthor as $prep): ?>
                <tr>
                    <td>
                    </td>
                    <td><?=$prep['FullName']?></td>
                    <td><?=$prep['CreatedBy']?></td>
                    <td><?=$prep['Created']?></td>
                    <td><?=$prep['EditedBy']?></td>
                    <td><?=$prep['Edited']?></td>
                </tr>
                <?php endforeach; ?>
            </table>
            <?php endif; ?>
            <?php endif; ?>

            <?php if (isset($MissingGenusStorage)): ?>
            <?php if ($MissingGenusStorage): ?>
            <h4>The following taxon names have current determinations or type status assignations,
                but no storage family (<?=count($MissingGenusStorage)?>):</h4>
            <div><a href="#" class="selectall">select/clear all</a></div>
            <table class="table table-condensed table-bordered dberrors headingcolour3" style="width: 100%">
                <tr>
                    <th style="width: 4%">&nbsp;</th>
                    <th style="width: 18%">Taxon name</th>
                    <th style="width: 25%">Created by</th>
                    <th style="width: 14%">Created on</th>
                    <th style="width: 25%">Edited by</th>
                    <th style="width: 14%">Edited on</th>
                </tr>
                <?php foreach ($MissingGenusStorage as $prep): ?>
                <tr>
                    <td>
                    </td>
                    <td><?=$prep['Name']?></td>
                    <td><?=$prep['CreatedBy']?></td>
                    <td><?=$prep['Created']?></td>
                    <td><?=$prep['EditedBy']?></td>
                    <td><?=$prep['Edited']?></td>
                </tr>
                <?php endforeach; ?>
            </table>
            <?php endif; ?>
            <?php endif; ?>

            <?php if((isset($MissingPreparation) && $MissingPreparation) || 
                    (isset($DuplicateHerbariaInWrongPreparation) && $DuplicateHerbariaInWrongPreparation) ||
                    (isset($DuplicateCountMismatch) && $DuplicateCountMismatch) ||
                    (isset($PartMissingFromMultisheetMessage) && $PartMissingFromMultisheetMessage) ||
                    (isset($SomethingInNumberThatShouldntBeThere) && $SomethingInNumberThatShouldntBeThere) ||
                    (isset($SomethingMissingFromNumberField) && $SomethingMissingFromNumberField) ||
                    (isset($JarSizeMissing) && $JarSizeMissing) ||
                    (isset($InappropriateQuantityInPreparation) && $InappropriateQuantityInPreparation) ||
                    (isset($TooManyPrimaryPreparations) && $TooManyPrimaryPreparations) ||
                    (isset($NoPrimaryPreparations) && $NoPrimaryPreparations) ||
                    (isset($MissingStorage) && $MissingStorage)): ?>
            <h3>Preparation</h3>
            <?php endif; ?>

            <?php if (isset($MissingPreparation)): ?>
            <?php if ($MissingPreparation): ?>
            <h4>These records don't have any preparations (<?=count($MissingPreparation)?>):</h4>
            <div><a href="#" class="selectall">select/clear all</a></div>
            <table class="table table-condensed table-bordered dberrors headingcolour2" style="width: 100%">
                <tr>
                    <th style="width: 4%">&nbsp;</th>
                    <th style="width: 18%">Catalogue number</th>
                    <th style="width: 25%">Created by</th>
                    <th style="width: 14%">Created on</th>
                    <th style="width: 25%">Edited by</th>
                    <th style="width: 14%">Edited on</th>
                </tr>
                <?php foreach ($MissingPreparation as $prep): ?>
                <tr>
                    <td style="width: 4%">
                        <?php
                            $value = $prep['CollectionObjectID'];
                            $opts = array(
                                'name' => 'recsetitems[]',
                                'value' => $value,
                                'checked' => ($this->input->post('recsetitems') && in_array($value, $this->input->post('recsetitems'))) ? TRUE : FALSE
                            );
                        ?>
                        <?=form_checkbox($opts)?>
                    </td>
                    <td><?=specify7_link($prep['CollectionObjectID'], $prep['CatalogNumber']);?></td>
                    <td><?=$prep['CreatedBy']?></td>
                    <td><?=$prep['Created']?></td>
                    <td><?=$prep['EditedBy']?></td>
                    <td><?=$prep['Edited']?></td>
                </tr>
                <?php endforeach; ?>
            </table>
            <?php endif; ?>
            <?php endif; ?>

            <?php if (isset($DuplicateHerbariaInWrongPreparation)): ?>
            <?php if ($DuplicateHerbariaInWrongPreparation): ?>
            <h4>The list of herbaria that have been sent duplicates is in the wrong preparation in these records (<?=count($DuplicateHerbariaInWrongPreparation)?>):</h4>
            <div><a href="#" class="selectall">select/clear all</a></div>
            <table class="table table-condensed table-bordered dberrors headingcolour2" style="width: 100%">
                <tr>
                    <th style="width: 4%">&nbsp;</th>
                    <th style="width: 18%">Catalogue number</th>
                    <th style="width: 25%">Created by</th>
                    <th style="width: 14%">Created on</th>
                    <th style="width: 25%">Edited by</th>
                    <th style="width: 14%">Edited on</th>
                </tr>
                <?php foreach ($DuplicateHerbariaInWrongPreparation as $prep): ?>
                <tr>
                    <td style="width: 4%">
                        <?php
                            $value = $prep['CollectionObjectID'];
                            $opts = array(
                                'name' => 'recsetitems[]',
                                'value' => $value,
                                'checked' => ($this->input->post('recsetitems') && in_array($value, $this->input->post('recsetitems'))) ? TRUE : FALSE
                            );
                        ?>
                        <?=form_checkbox($opts)?>
                    </td>
                    <td><?=specify7_link($prep['CollectionObjectID'], $prep['CatalogNumber']);?></td>
                    <td><?=$prep['CreatedBy']?></td>
                    <td><?=$prep['Created']?></td>
                    <td><?=$prep['EditedBy']?></td>
                    <td><?=$prep['Edited']?></td>
                </tr>
                <?php endforeach; ?>
            </table>
            <?php endif; ?>
            <?php endif; ?>

            <?php if (isset($DuplicateCountMismatch)): ?>
            <?php if ($DuplicateCountMismatch): ?>
            <h4>The quantity of duplicates doesn&apos;t match the number of herbaria listed in the &apos;MEL duplicates at&apos; field (<?=count($DuplicateCountMismatch)?>):</h4>
            <div><a href="#" class="selectall">select/clear all</a></div>
            <table class="table table-condensed table-bordered dberrors headingcolour2" style="width: 100%">
                <tr>
                    <th style="width: 4%">&nbsp;</th>
                    <th style="width: 18%">Catalogue number</th>
                    <th style="width: 25%">Created by</th>
                    <th style="width: 14%">Created on</th>
                    <th style="width: 25%">Edited by</th>
                    <th style="width: 14%">Edited on</th>
                </tr>
                <?php foreach ($DuplicateCountMismatch as $prep): ?>
                <tr>
                    <td style="width: 4%">
                        <?php
                            $value = $prep['CollectionObjectID'];
                            $opts = array(
                                'name' => 'recsetitems[]',
                                'value' => $value,
                                'checked' => ($this->input->post('recsetitems') && in_array($value, $this->input->post('recsetitems'))) ? TRUE : FALSE
                            );
                        ?>
                        <?=form_checkbox($opts)?>
                    </td>
                    <td><?=specify7_link($prep['CollectionObjectID'], $prep['CatalogNumber']);?></td>
                    <td><?=$prep['CreatedBy']?></td>
                    <td><?=$prep['Created']?></td>
                    <td><?=$prep['EditedBy']?></td>
                    <td><?=$prep['Edited']?></td>
                </tr>
                <?php endforeach; ?>
            </table>
            <?php endif; ?>
            <?php endif; ?>

            <?php if (isset($DuplicateDuplicatePreparations)): ?>
            <?php if ($DuplicateDuplicatePreparations): ?>
            <h4>Multiple <i>Duplicate</i> preparations (<?=count($DuplicateDuplicatePreparations)?>):</h4>
            <div><a href="#" class="selectall">select/clear all</a></div>
            <table class="table table-condensed table-bordered dberrors headingcolour2" style="width: 100%">
                <tr>
                    <th style="width: 4%">&nbsp;</th>
                    <th style="width: 18%">Catalogue number</th>
                    <th style="width: 25%">Created by</th>
                    <th style="width: 14%">Created on</th>
                    <th style="width: 25%">Edited by</th>
                    <th style="width: 14%">Edited on</th>
                </tr>
                <?php foreach ($DuplicateDuplicatePreparations as $prep): ?>
                <tr>
                    <td style="width: 4%">
                        <?php
                            $value = $prep['CollectionObjectID'];
                            $opts = array(
                                'name' => 'recsetitems[]',
                                'value' => $value,
                                'checked' => ($this->input->post('recsetitems') && in_array($value, $this->input->post('recsetitems'))) ? TRUE : FALSE
                            );
                        ?>
                        <?=form_checkbox($opts)?>
                    </td>
                    <td><?=specify7_link($prep['CollectionObjectID'], $prep['CatalogNumber']);?></td>
                    <td><?=$prep['CreatedBy']?></td>
                    <td><?=$prep['Created']?></td>
                    <td><?=$prep['EditedBy']?></td>
                    <td><?=$prep['Edited']?></td>
                </tr>
                <?php endforeach; ?>
            </table>
            <?php endif; ?>
            <?php endif; ?>

            <?php if (isset($DuplicateSeedDuplicatePreparations)): ?>
            <?php if ($DuplicateSeedDuplicatePreparations): ?>
            <h4>Multiple <i>Seed duplicate</i> preparations (<?=count($DuplicateSeedDuplicatePreparations)?>):</h4>
            <div><a href="#" class="selectall">select/clear all</a></div>
            <table class="table table-condensed table-bordered dberrors headingcolour2" style="width: 100%">
                <tr>
                    <th style="width: 4%">&nbsp;</th>
                    <th style="width: 18%">Catalogue number</th>
                    <th style="width: 25%">Created by</th>
                    <th style="width: 14%">Created on</th>
                    <th style="width: 25%">Edited by</th>
                    <th style="width: 14%">Edited on</th>
                </tr>
                <?php foreach ($DuplicateSeedDuplicatePreparations as $prep): ?>
                <tr>
                    <td style="width: 4%">
                        <?php
                            $value = $prep['CollectionObjectID'];
                            $opts = array(
                                'name' => 'recsetitems[]',
                                'value' => $value,
                                'checked' => ($this->input->post('recsetitems') && in_array($value, $this->input->post('recsetitems'))) ? TRUE : FALSE
                            );
                        ?>
                        <?=form_checkbox($opts)?>
                    </td>
                    <td><?=specify7_link($prep['CollectionObjectID'], $prep['CatalogNumber']);?></td>
                    <td><?=$prep['CreatedBy']?></td>
                    <td><?=$prep['Created']?></td>
                    <td><?=$prep['EditedBy']?></td>
                    <td><?=$prep['Edited']?></td>
                </tr>
                <?php endforeach; ?>
            </table>
            <?php endif; ?>
            <?php endif; ?>

            <?php if (isset($PartMissingFromMultisheetMessage)): ?>
            <?php if ($PartMissingFromMultisheetMessage): ?>
            <h4>The part is missing from the multisheet message in these records (<?=count($PartMissingFromMultisheetMessage)?>):</h4>
            <div><a href="#" class="selectall">select/clear all</a></div>
            <table class="table table-condensed table-bordered dberrors headingcolour2" style="width: 100%">
                <tr>
                    <th style="width: 4%">&nbsp;</th>
                    <th style="width: 18%">Catalogue number</th>
                    <th style="width: 25%">Created by</th>
                    <th style="width: 14%">Created on</th>
                    <th style="width: 25%">Edited by</th>
                    <th style="width: 14%">Edited on</th>
                </tr>
                <?php foreach ($PartMissingFromMultisheetMessage as $prep): ?>
                <tr>
                    <td style="width: 4%">
                        <?php
                            $value = $prep['CollectionObjectID'];
                            $opts = array(
                                'name' => 'recsetitems[]',
                                'value' => $value,
                                'checked' => ($this->input->post('recsetitems') && in_array($value, $this->input->post('recsetitems'))) ? TRUE : FALSE
                            );
                        ?>
                        <?=form_checkbox($opts)?>
                    </td>
                    <td><?=specify7_link($prep['CollectionObjectID'], $prep['CatalogNumber']);?></td>
                    <td><?=$prep['CreatedBy']?></td>
                    <td><?=$prep['Created']?></td>
                    <td><?=$prep['EditedBy']?></td>
                    <td><?=$prep['Edited']?></td>
                </tr>
                <?php endforeach; ?>
            </table>
            <?php endif; ?>
            <?php endif; ?>

            <?php if (isset($SomethingInNumberThatShouldntBeThere)): ?>
            <?php if ($SomethingInNumberThatShouldntBeThere): ?>
            <h4>One (or more) of the preparations in this record has something in the storage number field that shouldn&apos;t be there (<?=count($SomethingInNumberThatShouldntBeThere)?>):</h4>
            <div><a href="#" class="selectall">select/clear all</a></div>
            <table class="table table-condensed table-bordered dberrors headingcolour2" style="width: 100%">
                <tr>
                    <th style="width: 4%">&nbsp;</th>
                    <th style="width: 18%">Catalogue number</th>
                    <th style="width: 25%">Created by</th>
                    <th style="width: 14%">Created on</th>
                    <th style="width: 25%">Edited by</th>
                    <th style="width: 14%">Edited on</th>
                </tr>
                <?php foreach ($SomethingInNumberThatShouldntBeThere as $prep): ?>
                <tr>
                    <td style="width: 4%">
                        <?php
                            $value = $prep['CollectionObjectID'];
                            $opts = array(
                                'name' => 'recsetitems[]',
                                'value' => $value,
                                'checked' => ($this->input->post('recsetitems') && in_array($value, $this->input->post('recsetitems'))) ? TRUE : FALSE
                            );
                        ?>
                        <?=form_checkbox($opts)?>
                    </td>
                    <td><?=specify7_link($prep['CollectionObjectID'], $prep['CatalogNumber']);?></td>
                    <td><?=$prep['CreatedBy']?></td>
                    <td><?=$prep['Created']?></td>
                    <td><?=$prep['EditedBy']?></td>
                    <td><?=$prep['Edited']?></td>
                </tr>
                <?php endforeach; ?>
            </table>
            <?php endif; ?>
            <?php endif; ?>

            <?php if (isset($SomethingMissingFromNumberField)): ?>
            <?php if ($SomethingMissingFromNumberField): ?>
            <h4>One (or more) of the preparations in these records is missing a storage number (<?=count($SomethingMissingFromNumberField)?>):</h4>
            <div><a href="#" class="selectall">select/clear all</a></div>
            <table class="table table-condensed table-bordered dberrors headingcolour2" style="width: 100%">
                <tr>
                    <th style="width: 4%">&nbsp;</th>
                    <th style="width: 18%">Catalogue number</th>
                    <th style="width: 25%">Created by</th>
                    <th style="width: 14%">Created on</th>
                    <th style="width: 25%">Edited by</th>
                    <th style="width: 14%">Edited on</th>
                </tr>
                <?php foreach ($SomethingMissingFromNumberField as $prep): ?>
                <tr>
                    <td style="width: 4%">
                        <?php
                            $value = $prep['CollectionObjectID'];
                            $opts = array(
                                'name' => 'recsetitems[]',
                                'value' => $value,
                                'checked' => ($this->input->post('recsetitems') && in_array($value, $this->input->post('recsetitems'))) ? TRUE : FALSE
                            );
                        ?>
                        <?=form_checkbox($opts)?>
                    </td>
                    <td><?=specify7_link($prep['CollectionObjectID'], $prep['CatalogNumber']);?></td>
                    <td><?=$prep['CreatedBy']?></td>
                    <td><?=$prep['Created']?></td>
                    <td><?=$prep['EditedBy']?></td>
                    <td><?=$prep['Edited']?></td>
                </tr>
                <?php endforeach; ?>
            </table>
            <?php endif; ?>
            <?php endif; ?>

            <?php if (isset($JarSizeMissing)): ?>
            <?php if ($JarSizeMissing): ?>
            <h4>The jar size hasn't been entered for these spirit preparations (<?=count($JarSizeMissing)?>):</h4>
            <div><a href="#" class="selectall">select/clear all</a></div>
            <table class="table table-condensed table-bordered dberrors headingcolour2" style="width: 100%">
                <tr>
                    <th style="width: 4%">&nbsp;</th>
                    <th style="width: 18%">Catalogue number</th>
                    <th style="width: 25%">Created by</th>
                    <th style="width: 14%">Created on</th>
                    <th style="width: 25%">Edited by</th>
                    <th style="width: 14%">Edited on</th>
                </tr>
                <?php foreach ($JarSizeMissing as $prep): ?>
                <tr>
                    <td style="width: 4%">
                        <?php
                            $value = $prep['CollectionObjectID'];
                            $opts = array(
                                'name' => 'recsetitems[]',
                                'value' => $value,
                                'checked' => ($this->input->post('recsetitems') && in_array($value, $this->input->post('recsetitems'))) ? TRUE : FALSE
                            );
                        ?>
                        <?=form_checkbox($opts)?>
                    </td>
                    <td><?=specify7_link($prep['CollectionObjectID'], $prep['CatalogNumber']);?></td>
                    <td><?=$prep['CreatedBy']?></td>
                    <td><?=$prep['Created']?></td>
                    <td><?=$prep['EditedBy']?></td>
                    <td><?=$prep['Edited']?></td>
                </tr>
                <?php endforeach; ?>
            </table> 
            <?php endif; ?>
            <?php endif; ?>

            <?php if (isset($InappropriateQuantityInPreparation)): ?>
            <?php if ($InappropriateQuantityInPreparation): ?>
            <h4>The quantity is invalid for the preparation type (<?=count($InappropriateQuantityInPreparation)?>):</h4>
            <div><a href="#" class="selectall">select/clear all</a></div>
            <table class="table table-condensed table-bordered dberrors headingcolour2" style="width: 100%">
                <tr>
                    <th style="width: 4%">&nbsp;</th>
                    <th style="width: 18%">Catalogue number</th>
                    <th style="width: 25%">Created by</th>
                    <th style="width: 14%">Created on</th>
                    <th style="width: 25%">Edited by</th>
                    <th style="width: 14%">Edited on</th>
                </tr>
                <?php foreach ($InappropriateQuantityInPreparation as $prep): ?>
                <tr>
                    <td style="width: 4%">
                        <?php
                            $value = $prep['CollectionObjectID'];
                            $opts = array(
                                'name' => 'recsetitems[]',
                                'value' => $value,
                                'checked' => ($this->input->post('recsetitems') && in_array($value, $this->input->post('recsetitems'))) ? TRUE : FALSE
                            );
                        ?>
                        <?=form_checkbox($opts)?>
                    </td>
                    <td><?=specify7_link($prep['CollectionObjectID'], $prep['CatalogNumber']);?></td>
                    <td><?=$prep['CreatedBy']?></td>
                    <td><?=$prep['Created']?></td>
                    <td><?=$prep['EditedBy']?></td>
                    <td><?=$prep['Edited']?></td>
                </tr>
                <?php endforeach; ?>
            </table>
            <?php endif; ?>
            <?php endif; ?>

            <?php if (isset($TooManyPrimaryPreparations)): ?>
            <?php if ($TooManyPrimaryPreparations): ?>
            <h4>There are too many primary preparations (Sheet, Spirit etc.) in these records (<?=count($TooManyPrimaryPreparations)?>):</h4>
            <div><a href="#" class="selectall">select/clear all</a></div>
            <table class="table table-condensed table-bordered dberrors headingcolour2" style="width: 100%">
                <tr>
                    <th style="width: 4%">&nbsp;</th>
                    <th style="width: 18%">Catalogue number</th>
                    <th style="width: 25%">Created by</th>
                    <th style="width: 14%">Created on</th>
                    <th style="width: 25%">Edited by</th>
                    <th style="width: 14%">Edited on</th>
                </tr>
                <?php foreach ($TooManyPrimaryPreparations as $prep): ?>
                <tr>
                    <td style="width: 4%">
                        <?php
                            $value = $prep['CollectionObjectID'];
                            $opts = array(
                                'name' => 'recsetitems[]',
                                'value' => $value,
                                'checked' => ($this->input->post('recsetitems') && in_array($value, $this->input->post('recsetitems'))) ? TRUE : FALSE
                            );
                        ?>
                        <?=form_checkbox($opts)?>
                    </td>
                    <td><?=specify7_link($prep['CollectionObjectID'], $prep['CatalogNumber']);?></td>
                    <td><?=$prep['CreatedBy']?></td>
                    <td><?=$prep['Created']?></td>
                    <td><?=$prep['EditedBy']?></td>
                    <td><?=$prep['Edited']?></td>
                </tr>
                <?php endforeach; ?>
            </table>
            <?php endif; ?>
            <?php endif; ?>

            <?php if (isset($NoPrimaryPreparations)): ?>
            <?php if ($NoPrimaryPreparations): ?>
            <h4>These records don't have a primary preparation (Sheet, Spirit etc.) (<?=count($NoPrimaryPreparations)?>):</h4>
            <div><a href="#" class="selectall">select/clear all</a></div>
            <table class="table table-condensed table-bordered dberrors headingcolour2" style="width: 100%">
                <tr>
                    <th style="width: 4%">&nbsp;</th>
                    <th style="width: 18%">Catalogue number</th>
                    <th style="width: 25%">Created by</th>
                    <th style="width: 14%">Created on</th>
                    <th style="width: 25%">Edited by</th>
                    <th style="width: 14%">Edited on</th>
                </tr>
                <?php foreach ($NoPrimaryPreparations as $prep): ?>
                <tr>
                    <td style="width: 4%">
                        <?php
                            $value = $prep['CollectionObjectID'];
                            $opts = array(
                                'name' => 'recsetitems[]',
                                'value' => $value,
                                'checked' => ($this->input->post('recsetitems') && in_array($value, $this->input->post('recsetitems'))) ? TRUE : FALSE
                            );
                        ?>
                        <?=form_checkbox($opts)?>
                    </td>
                    <td><?=specify7_link($prep['CollectionObjectID'], $prep['CatalogNumber']);?></td>
                    <td><?=$prep['CreatedBy']?></td>
                    <td><?=$prep['Created']?></td>
                    <td><?=$prep['EditedBy']?></td>
                    <td><?=$prep['Edited']?></td>
                </tr>
                <?php endforeach; ?>
            </table>
            <?php endif; ?>
            <?php endif; ?>

            <?php if (isset($MissingStorage)): ?>
            <?php if ($MissingStorage): ?>
            <h4>The records don&apos;t have the storage set (<?=count($MissingStorage)?>):</h4>
            <div><a href="#" class="selectall">select/clear all</a></div>
            <table class="table table-bordered table-condensed headingcolour2" style="width: 100%">
                <tr>
                    <th style="width: 4%">&nbsp;</th>
                    <th style="width: 18%">Catalogue number</th>
                    <th style="width: 25%">Created by</th>
                    <th style="width: 14%">Created on</th>
                    <th style="width: 25%">Edited by</th>
                    <th style="width: 14%">Edited on</th>
                </tr>
                <?php foreach ($MissingStorage as $prep): ?>
                <tr>
                    <td style="width: 4%">
                        <?php
                            $value = $prep['CollectionObjectID'];
                            $opts = array(
                                'name' => 'recsetitems[]',
                                'value' => $value,
                                'checked' => ($this->input->post('recsetitems') && in_array($value, $this->input->post('recsetitems'))) ? TRUE : FALSE
                            );
                        ?>
                        <?=form_checkbox($opts)?>
                    </td>
                    <td><?=specify7_link($prep['CollectionObjectID'], $prep['CatalogNumber']);?></td>
                    <td><?=$prep['CreatedBy']?></td>
                    <td><?=$prep['Created']?></td>
                    <td><?=$prep['EditedBy']?></td>
                    <td><?=$prep['Edited']?></td>
                </tr>
                <?php endforeach; ?>
            </table>
            <?php endif; ?>
            <?php endif; ?>

            <?php if (isset($MissingExHerbarium)): ?>
            <?php if ($MissingExHerbarium): ?>
            <h4>Duplicate lacking <i>Ex Herbarium</i> data (<?=count($MissingExHerbarium)?>):</h4>
            <div><a href="#" class="selectall">select/clear all</a></div>
            <table class="table table-condensed table-bordered dberrors headingcolour2" style="width: 100%">
                <tr>
                    <th style="width: 4%">&nbsp;</th>
                    <th style="width: 18%">Catalogue number</th>
                    <th style="width: 25%">Created by</th>
                    <th style="width: 14%">Created on</th>
                    <th style="width: 25%">Edited by</th>
                    <th style="width: 14%">Edited on</th>
                </tr>
                <?php foreach ($MissingExHerbarium as $prep): ?>
                <tr>
                    <td style="width: 4%">
                        <?php
                            $value = $prep['CollectionObjectID'];
                            $opts = array(
                                'name' => 'recsetitems[]',
                                'value' => $value,
                                'checked' => ($this->input->post('recsetitems') && in_array($value, $this->input->post('recsetitems'))) ? TRUE : FALSE
                            );
                        ?>
                        <?=form_checkbox($opts)?>
                    </td>
                    <td><?=specify7_link($prep['CollectionObjectID'], $prep['CatalogNumber']);?></td>
                    <td><?=$prep['CreatedBy']?></td>
                    <td><?=$prep['Created']?></td>
                    <td><?=$prep['EditedBy']?></td>
                    <td><?=$prep['Edited']?></td>
                </tr>
                <?php endforeach; ?>
            </table>
            <?php endif; ?>
            <?php endif; ?>

            <?php if (isset($MissingExHerbariumCatalogNumber)): ?>
            <?php if ($MissingExHerbariumCatalogNumber): ?>
            <h4>Duplicate lacking <i>Ex Herbarium</i> catalogue number (<?=count($MissingExHerbariumCatalogNumber)?>):</h4>
            <div><a href="#" class="selectall">select/clear all</a></div>
            <table class="table table-condensed table-bordered dberrors headingcolour2" style="width: 100%">
                <tr>
                    <th style="width: 4%">&nbsp;</th>
                    <th style="width: 18%">Catalogue number</th>
                    <th style="width: 25%">Created by</th>
                    <th style="width: 14%">Created on</th>
                    <th style="width: 25%">Edited by</th>
                    <th style="width: 14%">Edited on</th>
                </tr>
                <?php foreach ($MissingExHerbariumCatalogNumber as $prep): ?>
                <tr>
                    <td style="width: 4%">
                        <?php
                            $value = $prep['CollectionObjectID'];
                            $opts = array(
                                'name' => 'recsetitems[]',
                                'value' => $value,
                                'checked' => ($this->input->post('recsetitems') && in_array($value, $this->input->post('recsetitems'))) ? TRUE : FALSE
                            );
                        ?>
                        <?=form_checkbox($opts)?>
                    </td>
                    <td><?=specify7_link($prep['CollectionObjectID'], $prep['CatalogNumber']);?></td>
                    <td><?=$prep['CreatedBy']?></td>
                    <td><?=$prep['Created']?></td>
                    <td><?=$prep['EditedBy']?></td>
                    <td><?=$prep['Edited']?></td>
                </tr>
                <?php endforeach; ?>
            </table>
            <?php endif; ?>
            <?php endif; ?>

            <?php if((isset($MissingCollectors) && $MissingCollectors) ||
                    (isset($GroupCollectors) && $GroupCollectors) ||
                    (isset($PrimaryCollectorNotFirst) && $PrimaryCollectorNotFirst) ||
                    (isset($IncorrectAgentAsCollector) && $IncorrectAgentAsCollector) ||
                    (isset($EndDateWithNoStartDate) && $EndDateWithNoStartDate) ||
                    (isset($MissingPrimaryCollectors) && $MissingPrimaryCollectors) ||
                    (isset($NoCollectingDate) && $NoCollectingDate) ||
                    (isset($PartlyAtomisedHabitat) && $PartlyAtomisedHabitat) ||
                    (isset($MissingCultSource) && $MissingCultSource)  ||
                    (isset($MissingIntroSource) && $MissingIntroSource)): ?>
            <h3>Collecting event</h3>
            <?php endif; ?>

            <?php if (isset($MissingCollectors)): ?>
            <?php if ($MissingCollectors): ?>
            <h4>There are no collectors for these records and the verbatim collector, collector unknown and collector illegible fields are empty (<?=count($MissingCollectors)?>):</h4>
            <div><a href="#" class="selectall">select/clear all</a></div>
            <table class="table table-condensed table-bordered dberrors headingcolour3" style="width: 100%">
                <tr>
                    <th style="width: 4%">&nbsp;</th>
                    <th style="width: 18%">Catalogue number</th>
                    <th style="width: 25%">Created by</th>
                    <th style="width: 14%">Created on</th>
                    <th style="width: 25%">Edited by</th>
                    <th style="width: 14%">Edited on</th>
                </tr>
                <?php foreach ($MissingCollectors as $prep): ?>
                <tr>
                    <td style="width: 4%">
                        <?php
                            $value = $prep['CollectionObjectID'];
                            $opts = array(
                                'name' => 'recsetitems[]',
                                'value' => $value,
                                'checked' => ($this->input->post('recsetitems') && in_array($value, $this->input->post('recsetitems'))) ? TRUE : FALSE
                            );
                        ?>
                        <?=form_checkbox($opts)?>
                    </td>
                    <td><?=specify7_link($prep['CollectionObjectID'], $prep['CatalogNumber']);?></td>
                    <td><?=$prep['CreatedBy']?></td>
                    <td><?=$prep['Created']?></td>
                    <td><?=$prep['EditedBy']?></td>
                    <td><?=$prep['Edited']?></td>
                </tr>
                <?php endforeach; ?>
            </table>
            <?php endif; ?>
            <?php endif; ?>

            <?php if (isset($GroupCollectors)): ?>
            <?php if ($GroupCollectors): ?>
            <h4>A group agent has been entered as a collector (<?=count($GroupCollectors)?>):</h4>
            <div><a href="#" class="selectall">select/clear all</a></div>
            <table class="table table-condensed table-bordered dberrors headingcolour3" style="width: 100%">
                <tr>
                    <th style="width: 4%">&nbsp;</th>
                    <th style="width: 18%">Catalogue number</th>
                    <th style="width: 25%">Created by</th>
                    <th style="width: 14%">Created on</th>
                    <th style="width: 25%">Edited by</th>
                    <th style="width: 14%">Edited on</th>
                </tr>
                <?php foreach ($GroupCollectors as $prep): ?>
                <tr>
                    <td style="width: 4%">
                        <?php
                            $value = $prep['CollectionObjectID'];
                            $opts = array(
                                'name' => 'recsetitems[]',
                                'value' => $value,
                                'checked' => ($this->input->post('recsetitems') && in_array($value, $this->input->post('recsetitems'))) ? TRUE : FALSE
                            );
                        ?>
                        <?=form_checkbox($opts)?>
                    </td>
                    <td><?=specify7_link($prep['CollectionObjectID'], $prep['CatalogNumber']);?></td>
                    <td><?=$prep['CreatedBy']?></td>
                    <td><?=$prep['Created']?></td>
                    <td><?=$prep['EditedBy']?></td>
                    <td><?=$prep['Edited']?></td>
                </tr>
                <?php endforeach; ?>
            </table>
            <?php endif; ?>
            <?php endif; ?>

            <?php if (isset($MissingPrimaryCollectors)): ?>
            <?php if ($MissingPrimaryCollectors): ?>
            <h4>There are no primary collectors for these records (<?=count($MissingPrimaryCollectors)?>):</h4>
            <div><a href="#" class="selectall">select/clear all</a></div>
            <table class="table table-condensed table-bordered dberrors headingcolour3" style="width: 100%">
                <tr>
                    <th style="width: 4%">&nbsp;</th>
                    <th style="width: 18%">Catalogue number</th>
                    <th style="width: 25%">Created by</th>
                    <th style="width: 14%">Created on</th>
                    <th style="width: 25%">Edited by</th>
                    <th style="width: 14%">Edited on</th>
                </tr>
                <?php foreach ($MissingPrimaryCollectors as $prep): ?>
                <tr>
                    <td style="width: 4%">
                        <?php
                            $value = $prep['CollectionObjectID'];
                            $opts = array(
                                'name' => 'recsetitems[]',
                                'value' => $value,
                                'checked' => ($this->input->post('recsetitems') && in_array($value, $this->input->post('recsetitems'))) ? TRUE : FALSE
                            );
                        ?>
                        <?=form_checkbox($opts)?>
                    </td>
                    <td><?=specify7_link($prep['CollectionObjectID'], $prep['CatalogNumber']);?></td>
                    <td><?=$prep['CreatedBy']?></td>
                    <td><?=$prep['Created']?></td>
                    <td><?=$prep['EditedBy']?></td>
                    <td><?=$prep['Edited']?></td>
                </tr>
                <?php endforeach; ?>
            </table>
            <?php endif; ?>
            <?php endif; ?>

            <?php if (isset($PrimaryCollectorNotFirst)): ?>
            <?php if ($PrimaryCollectorNotFirst): ?>
            <h4>The primary collector is not listed first (<?=count($PrimaryCollectorNotFirst)?>):</h4>
            <div><a href="#" class="selectall">select/clear all</a></div>
            <table class="table table-condensed table-bordered dberrors headingcolour3" style="width: 100%">
                <tr>
                    <th style="width: 4%">&nbsp;</th>
                    <th style="width: 18%">Catalogue number</th>
                    <th style="width: 25%">Created by</th>
                    <th style="width: 14%">Created on</th>
                    <th style="width: 25%">Edited by</th>
                    <th style="width: 14%">Edited on</th>
                </tr>
                <?php foreach ($PrimaryCollectorNotFirst as $prep): ?>
                <tr>
                    <td style="width: 4%">
                        <?php
                            $value = $prep['CollectionObjectID'];
                            $opts = array(
                                'name' => 'recsetitems[]',
                                'value' => $value,
                                'checked' => ($this->input->post('recsetitems') && in_array($value, $this->input->post('recsetitems'))) ? TRUE : FALSE
                            );
                        ?>
                        <?=form_checkbox($opts)?>
                    </td>
                    <td><?=specify7_link($prep['CollectionObjectID'], $prep['CatalogNumber']);?></td>
                    <td><?=$prep['CreatedBy']?></td>
                    <td><?=$prep['Created']?></td>
                    <td><?=$prep['EditedBy']?></td>
                    <td><?=$prep['Edited']?></td>
                </tr>
                <?php endforeach; ?>
            </table>
            <?php endif; ?>
            <?php endif; ?>

            <?php if (isset($IncorrectAgentAsCollector)): ?>
            <?php if ($IncorrectAgentAsCollector): ?>
            <h4>An incorrect agent name has been entered as a collector (<?=count($IncorrectAgentAsCollector)?>):</h4>
            <div><a href="#" class="selectall">select/clear all</a></div>
            <table class="table table-condensed table-bordered dberrors headingcolour3" style="width: 100%">
                <tr>
                    <th style="width: 4%">&nbsp;</th>
                    <th style="width: 18%">Catalogue number</th>
                    <th style="width: 25%">Created by</th>
                    <th style="width: 14%">Created on</th>
                    <th style="width: 25%">Edited by</th>
                    <th style="width: 14%">Edited on</th>
                </tr>
                <?php foreach ($IncorrectAgentAsCollector as $prep): ?>
                <tr>
                    <td style="width: 4%">
                        <?php
                            $value = $prep['CollectionObjectID'];
                            $opts = array(
                                'name' => 'recsetitems[]',
                                'value' => $value,
                                'checked' => ($this->input->post('recsetitems') && in_array($value, $this->input->post('recsetitems'))) ? TRUE : FALSE
                            );
                        ?>
                        <?=form_checkbox($opts)?>
                    </td>
                    <td><?=specify7_link($prep['CollectionObjectID'], $prep['CatalogNumber']);?></td>
                    <td><?=$prep['CreatedBy']?></td>
                    <td><?=$prep['Created']?></td>
                    <td><?=$prep['EditedBy']?></td>
                    <td><?=$prep['Edited']?></td>
                </tr>
                <?php endforeach; ?>
            </table>
            <?php endif; ?>
            <?php endif; ?>

            <?php if (isset($EndDateWithNoStartDate)): ?>
            <?php if ($EndDateWithNoStartDate): ?>
            <h4>There is an end date, but no start date for these records (<?=count($EndDateWithNoStartDate)?>):</h4>
            <div><a href="#" class="selectall">select/clear all</a></div>
            <table class="table table-condensed table-bordered dberrors headingcolour3" style="width: 100%">
                <tr>
                    <th style="width: 4%">&nbsp;</th>
                    <th style="width: 18%">Catalogue number</th>
                    <th style="width: 25%">Created by</th>
                    <th style="width: 14%">Created on</th>
                    <th style="width: 25%">Edited by</th>
                    <th style="width: 14%">Edited on</th>
                </tr>
                <?php foreach ($EndDateWithNoStartDate as $prep): ?>
                <tr>
                    <td style="width: 4%">
                        <?php
                            $value = $prep['CollectionObjectID'];
                            $opts = array(
                                'name' => 'recsetitems[]',
                                'value' => $value,
                                'checked' => ($this->input->post('recsetitems') && in_array($value, $this->input->post('recsetitems'))) ? TRUE : FALSE
                            );
                        ?>
                        <?=form_checkbox($opts)?>
                    </td>
                    <td><?=specify7_link($prep['CollectionObjectID'], $prep['CatalogNumber']);?></td>
                    <td><?=$prep['CreatedBy']?></td>
                    <td><?=$prep['Created']?></td>
                    <td><?=$prep['EditedBy']?></td>
                    <td><?=$prep['Edited']?></td>
                </tr>
                <?php endforeach; ?>
            </table>
            <?php endif; ?>
            <?php endif; ?>

            <?php if (isset($NoCollectingDate)): ?>
            <?php if ($NoCollectingDate): ?>
            <h4>Do you remember what date you collected these records? (<?=count($NoCollectingDate)?>):</h4>
            <div><a href="#" class="selectall">select/clear all</a></div>
            <table class="table table-condensed table-bordered dberrors headingcolour3" style="width: 100%">
                <tr>
                    <th style="width: 4%">&nbsp;</th>
                    <th style="width: 18%">Catalogue number</th>
                    <th style="width: 25%">Created by</th>
                    <th style="width: 14%">Created on</th>
                    <th style="width: 25%">Edited by</th>
                    <th style="width: 14%">Edited on</th>
                </tr>
                <?php foreach ($NoCollectingDate as $prep): ?>
                <tr>
                    <td style="width: 4%">
                        <?php
                            $value = $prep['CollectionObjectID'];
                            $opts = array(
                                'name' => 'recsetitems[]',
                                'value' => $value,
                                'checked' => ($this->input->post('recsetitems') && in_array($value, $this->input->post('recsetitems'))) ? TRUE : FALSE
                            );
                        ?>
                        <?=form_checkbox($opts)?>
                    </td>
                    <td><?=specify7_link($prep['CollectionObjectID'], $prep['CatalogNumber']);?></td>
                    <td><?=$prep['CreatedBy']?></td>
                    <td><?=$prep['Created']?></td>
                    <td><?=$prep['EditedBy']?></td>
                    <td><?=$prep['Edited']?></td>
                </tr>
                <?php endforeach; ?>
            </table>
            <?php endif; ?>
            <?php endif; ?>

            <?php if (isset($MissingCultSource)): ?>
            <?php if ($MissingCultSource): ?>
            <h4>The following records are missing Cultivated Source (<?=count($MissingCultSource)?>):</h4>
            <div><a href="#" class="selectall">select/clear all</a></div>
            <table class="table table-condensed table-bordered dberrors headingcolour3" style="width: 100%">
                <tr>
                    <th style="width: 4%">&nbsp;</th>
                    <th style="width: 18%">Catalogue number</th>
                    <th style="width: 25%">Created by</th>
                    <th style="width: 14%">Created on</th>
                    <th style="width: 25%">Edited by</th>
                    <th style="width: 14%">Edited on</th>
                </tr>
                <?php foreach ($MissingCultSource as $prep): ?>
                <tr>
                    <td style="width: 4%">
                        <?php
                            $value = $prep['CollectionObjectID'];
                            $opts = array(
                                'name' => 'recsetitems[]',
                                'value' => $value,
                                'checked' => ($this->input->post('recsetitems') && in_array($value, $this->input->post('recsetitems'))) ? TRUE : FALSE
                            );
                        ?>
                        <?=form_checkbox($opts)?>
                    </td>
                    <td><?=specify7_link($prep['CollectionObjectID'], $prep['CatalogNumber']);?></td>
                    <td><?=$prep['CreatedBy']?></td>
                    <td><?=$prep['Created']?></td>
                    <td><?=$prep['EditedBy']?></td>
                    <td><?=$prep['Edited']?></td>
                </tr>
                <?php endforeach; ?>
            </table>
            <?php endif; ?>
            <?php endif; ?>

            <?php if (isset($MissingIntroSource)): ?>
            <?php if ($MissingIntroSource): ?>
            <h4>The following records are missing Introduced Source (<?=count($MissingIntroSource)?>):</h4>
            <div><a href="#" class="selectall">select/clear all</a></div>
            <table class="table table-condensed table-bordered dberrors headingcolour3" style="width: 100%">
                <tr>
                    <th style="width: 4%">&nbsp;</th>
                    <th style="width: 18%">Catalogue number</th>
                    <th style="width: 25%">Created by</th>
                    <th style="width: 14%">Created on</th>
                    <th style="width: 25%">Edited by</th>
                    <th style="width: 14%">Edited on</th>
                </tr>
                <?php foreach ($MissingIntroSource as $prep): ?>
                <tr>
                    <td style="width: 4%">
                        <?php
                            $value = $prep['CollectionObjectID'];
                            $opts = array(
                                'name' => 'recsetitems[]',
                                'value' => $value,
                                'checked' => ($this->input->post('recsetitems') && in_array($value, $this->input->post('recsetitems'))) ? TRUE : FALSE
                            );
                        ?>
                        <?=form_checkbox($opts)?>
                    </td>
                    <td><?=specify7_link($prep['CollectionObjectID'], $prep['CatalogNumber']);?></td>
                    <td><?=$prep['CreatedBy']?></td>
                    <td><?=$prep['Created']?></td>
                    <td><?=$prep['EditedBy']?></td>
                    <td><?=$prep['Edited']?></td>
                </tr>
                <?php endforeach; ?>
            </table>
            <?php endif; ?>
            <?php endif; ?>

            <?php if((isset($MissingLocality) && $MissingLocality) || 
                    (isset($MissingGeography) && $MissingGeography) ||
                    (isset($CultivatedInGeography) && $CultivatedInGeography) ||
                    (isset($localityNameNoDetailsGiven) && $localityNameNoDetailsGiven) ||
                    (isset($MissingSourceOrPrecision) && $MissingSourceOrPrecision) ||
                    (isset($MissingAltitudeUnit) && $MissingAltitudeUnit) ||
                    (isset($TooMuchAltitude) && $TooMuchAltitude) ||
                    (isset($TooEarlyForGPS) && $TooEarlyForGPS) ||
                    (isset($MissingDatum) && $MissingDatum)): ?>
            <h3>Locality</h3>
            <?php endif; ?>

            <?php if (isset($MissingLocality)): ?>
            <?php if ($MissingLocality): ?>
            <h4>The locality is missing in these records (<?=count($MissingLocality)?>):</h4>
            <div><a href="#" class="selectall">select/clear all</a></div>
            <table class="table table-condensed table-bordered dberrors headingcolour4" style="width: 100%">
                <tr>
                    <th style="width: 4%">&nbsp;</th>
                    <th style="width: 18%">Catalogue number</th>
                    <th style="width: 25%">Created by</th>
                    <th style="width: 14%">Created on</th>
                    <th style="width: 25%">Edited by</th>
                    <th style="width: 14%">Edited on</th>
                </tr>
                <?php foreach ($MissingLocality as $prep): ?>
                <tr>
                    <td style="width: 4%">
                        <?php
                            $value = $prep['CollectionObjectID'];
                            $opts = array(
                                'name' => 'recsetitems[]',
                                'value' => $value,
                                'checked' => ($this->input->post('recsetitems') && in_array($value, $this->input->post('recsetitems'))) ? TRUE : FALSE
                            );
                        ?>
                        <?=form_checkbox($opts)?>
                    </td>
                    <td><?=specify7_link($prep['CollectionObjectID'], $prep['CatalogNumber']);?></td>
                    <td><?=$prep['CreatedBy']?></td>
                    <td><?=$prep['Created']?></td>
                    <td><?=$prep['EditedBy']?></td>
                    <td><?=$prep['Edited']?></td>
                </tr>
                <?php endforeach; ?>
            </table>
            <?php endif; ?>
            <?php endif; ?>

            <?php if (isset($MissingGeography)): ?>
            <?php if ($MissingGeography): ?>
            <h4>The geography is missing in these records (<?=count($MissingGeography)?>):</h4>
            <div><a href="#" class="selectall">select/clear all</a></div>
            <table class="table table-condensed table-bordered dberrors headingcolour4" style="width: 100%">
                <tr>
                    <th style="width: 4%">&nbsp;</th>
                    <th style="width: 18%">Catalogue number</th>
                    <th style="width: 25%">Created by</th>
                    <th style="width: 14%">Created on</th>
                    <th style="width: 25%">Edited by</th>
                    <th style="width: 14%">Edited on</th>
                </tr>
                <?php foreach ($MissingGeography as $prep): ?>
                <tr>
                    <td style="width: 4%">
                        <?php
                            $value = $prep['CollectionObjectID'];
                            $opts = array(
                                'name' => 'recsetitems[]',
                                'value' => $value,
                                'checked' => ($this->input->post('recsetitems') && in_array($value, $this->input->post('recsetitems'))) ? TRUE : FALSE
                            );
                        ?>
                        <?=form_checkbox($opts)?>
                    </td>
                    <td><?=specify7_link($prep['CollectionObjectID'], $prep['CatalogNumber']);?></td>
                    <td><?=$prep['CreatedBy']?></td>
                    <td><?=$prep['Created']?></td>
                    <td><?=$prep['EditedBy']?></td>
                    <td><?=$prep['Edited']?></td>
                </tr>
                <?php endforeach; ?>
            </table>
            <?php endif; ?>
            <?php endif; ?>

            <?php if (isset($CultivatedInGeography)): ?>
            <?php if ($CultivatedInGeography): ?>
            <h4>'Cultivated' should not be entered in the geography field(<?=count($CultivatedInGeography)?>):</h4>
            <div><a href="#" class="selectall">select/clear all</a></div>
            <table class="table table-condensed table-bordered dberrors headingcolour4" style="width: 100%">
                <tr>
                    <th style="width: 4%">&nbsp;</th>
                    <th style="width: 18%">Catalogue number</th>
                    <th style="width: 25%">Created by</th>
                    <th style="width: 14%">Created on</th>
                    <th style="width: 25%">Edited by</th>
                    <th style="width: 14%">Edited on</th>
                </tr>
                <?php foreach ($CultivatedInGeography as $prep): ?>
                <tr>
                    <td style="width: 4%">
                        <?php
                            $value = $prep['CollectionObjectID'];
                            $opts = array(
                                'name' => 'recsetitems[]',
                                'value' => $value,
                                'checked' => ($this->input->post('recsetitems') && in_array($value, $this->input->post('recsetitems'))) ? TRUE : FALSE
                            );
                        ?>
                        <?=form_checkbox($opts)?>
                    </td>
                    <td><?=specify7_link($prep['CollectionObjectID'], $prep['CatalogNumber']);?></td>
                    <td><?=$prep['CreatedBy']?></td>
                    <td><?=$prep['Created']?></td>
                    <td><?=$prep['EditedBy']?></td>
                    <td><?=$prep['Edited']?></td>
                </tr>
                <?php endforeach; ?>
            </table>
            <?php endif; ?>
            <?php endif; ?>

            <?php if (isset($localityNameNoDetailsGiven)): ?>
            <?php if ($localityNameNoDetailsGiven): ?>
            <h4>'No details given' should only be entered in the Locality Name field if there is no geography information either; otherwise the country etc. should be entered (<?=count($localityNameNoDetailsGiven)?>):</h4>
            <div><a href="#" class="selectall">select/clear all</a></div>
            <table class="table table-condensed table-bordered dberrors headingcolour4" style="width: 100%">
                <tr>
                    <th style="width: 4%">&nbsp;</th>
                    <th style="width: 18%">Catalogue number</th>
                    <th style="width: 25%">Created by</th>
                    <th style="width: 14%">Created on</th>
                    <th style="width: 25%">Edited by</th>
                    <th style="width: 14%">Edited on</th>
                </tr>
                <?php foreach ($localityNameNoDetailsGiven as $prep): ?>
                <tr>
                    <td style="width: 4%">
                        <?php
                            $value = $prep['CollectionObjectID'];
                            $opts = array(
                                'name' => 'recsetitems[]',
                                'value' => $value,
                                'checked' => ($this->input->post('recsetitems') && in_array($value, $this->input->post('recsetitems'))) ? TRUE : FALSE
                            );
                        ?>
                        <?=form_checkbox($opts)?>
                    </td>
                    <td><?=specify7_link($prep['CollectionObjectID'], $prep['CatalogNumber']);?></td>
                    <td><?=$prep['CreatedBy']?></td>
                    <td><?=$prep['Created']?></td>
                    <td><?=$prep['EditedBy']?></td>
                    <td><?=$prep['Edited']?></td>
                </tr>
                <?php endforeach; ?>
            </table>
            <?php endif; ?>
            <?php endif; ?>

            <?php if (isset($MissingSourceOrPrecision)): ?>
            <?php if ($MissingSourceOrPrecision): ?>
            <h4>These records are missing geocode source and/or geocode precision (<?=count($MissingSourceOrPrecision)?>):</h4>
            <div><a href="#" class="selectall">select/clear all</a></div>
            <table class="table table-condensed table-bordered dberrors headingcolour4" style="width: 100%">
                <tr>
                    <th style="width: 4%">&nbsp;</th>
                    <th style="width: 18%">Catalogue number</th>
                    <th style="width: 25%">Created by</th>
                    <th style="width: 14%">Created on</th>
                    <th style="width: 25%">Edited by</th>
                    <th style="width: 14%">Edited on</th>
                </tr>
                <?php foreach ($MissingSourceOrPrecision as $prep): ?>
                <tr>
                    <td style="width: 4%">
                        <?php
                            $value = $prep['CollectionObjectID'];
                            $opts = array(
                                'name' => 'recsetitems[]',
                                'value' => $value,
                                'checked' => ($this->input->post('recsetitems') && in_array($value, $this->input->post('recsetitems'))) ? TRUE : FALSE
                            );
                        ?>
                        <?=form_checkbox($opts)?>
                    </td>
                    <td><?=specify7_link($prep['CollectionObjectID'], $prep['CatalogNumber']);?></td>
                    <td><?=$prep['CreatedBy']?></td>
                    <td><?=$prep['Created']?></td>
                    <td><?=$prep['EditedBy']?></td>
                    <td><?=$prep['Edited']?></td>
                </tr>
                <?php endforeach; ?>
            </table>
            <?php endif; ?>
            <?php endif; ?>

            <?php if (isset($MissingAltitudeUnit)): ?>
            <?php if ($MissingAltitudeUnit): ?>
            <h4>The altitude unit is missing in these records (<?=count($MissingAltitudeUnit)?>):</h4>
            <div><a href="#" class="selectall">select/clear all</a></div>
            <table class="table table-condensed table-bordered dberrors headingcolour4" style="width: 100%">
                <tr>
                    <th style="width: 4%">&nbsp;</th>
                    <th style="width: 18%">Catalogue number</th>
                    <th style="width: 25%">Created by</th>
                    <th style="width: 14%">Created on</th>
                    <th style="width: 25%">Edited by</th>
                    <th style="width: 14%">Edited on</th>
                </tr>
                <?php foreach ($MissingAltitudeUnit as $prep): ?>
                <tr>
                    <td style="width: 4%">
                        <?php
                            $value = $prep['CollectionObjectID'];
                            $opts = array(
                                'name' => 'recsetitems[]',
                                'value' => $value,
                                'checked' => ($this->input->post('recsetitems') && in_array($value, $this->input->post('recsetitems'))) ? TRUE : FALSE
                            );
                        ?>
                        <?=form_checkbox($opts)?>
                    </td>
                    <td><?=specify7_link($prep['CollectionObjectID'], $prep['CatalogNumber']);?></td>
                    <td><?=$prep['CreatedBy']?></td>
                    <td><?=$prep['Created']?></td>
                    <td><?=$prep['EditedBy']?></td>
                    <td><?=$prep['Edited']?></td>
                </tr>
                <?php endforeach; ?>
            </table>
            <?php endif; ?>
            <?php endif; ?>

            <?php if (isset($TooMuchAltitude)): ?>
            <?php if ($TooMuchAltitude): ?>
            <h4>The altitude is too high for the state or territory (<?=count($TooMuchAltitude)?>):</h4>
            <div><a href="#" class="selectall">select/clear all</a></div>
            <table class="table table-condensed table-bordered dberrors headingcolour4" style="width: 100%">
                <tr>
                    <th style="width: 4%">&nbsp;</th>
                    <th style="width: 18%">Catalogue number</th>
                    <th style="width: 25%">Created by</th>
                    <th style="width: 14%">Created on</th>
                    <th style="width: 25%">Edited by</th>
                    <th style="width: 14%">Edited on</th>
                </tr>
                <?php foreach ($TooMuchAltitude as $prep): ?>
                <tr>
                    <td style="width: 4%">
                        <?php
                            $value = $prep['CollectionObjectID'];
                            $opts = array(
                                'name' => 'recsetitems[]',
                                'value' => $value,
                                'checked' => ($this->input->post('recsetitems') && in_array($value, $this->input->post('recsetitems'))) ? TRUE : FALSE
                            );
                        ?>
                        <?=form_checkbox($opts)?>
                    </td>
                    <td><?=specify7_link($prep['CollectionObjectID'], $prep['CatalogNumber']);?></td>
                    <td><?=$prep['CreatedBy']?></td>
                    <td><?=$prep['Created']?></td>
                    <td><?=$prep['EditedBy']?></td>
                    <td><?=$prep['Edited']?></td>
                </tr>
                <?php endforeach; ?>
            </table>
            <?php endif; ?>
            <?php endif; ?>

            <?php if (isset($TooEarlyForGPS)): ?>
            <?php if ($TooEarlyForGPS): ?>
            <h4>These collections might be a bit too old for the geocode source to be GPS (<?=count($TooEarlyForGPS)?>):</h4>
            <div><a href="#" class="selectall">select/clear all</a></div>
            <table class="table table-condensed table-bordered dberrors headingcolour4" style="width: 100%">
                <tr>
                    <th style="width: 4%">&nbsp;</th>
                    <th style="width: 18%">Catalogue number</th>
                    <th style="width: 25%">Created by</th>
                    <th style="width: 14%">Created on</th>
                    <th style="width: 25%">Edited by</th>
                    <th style="width: 14%">Edited on</th>
                </tr>
                <?php foreach ($TooEarlyForGPS as $prep): ?>
                <tr>
                    <td style="width: 4%">
                        <?php
                            $value = $prep['CollectionObjectID'];
                            $opts = array(
                                'name' => 'recsetitems[]',
                                'value' => $value,
                                'checked' => ($this->input->post('recsetitems') && in_array($value, $this->input->post('recsetitems'))) ? TRUE : FALSE
                            );
                        ?>
                        <?=form_checkbox($opts)?>
                    </td>
                    <td><?=specify7_link($prep['CollectionObjectID'], $prep['CatalogNumber']);?></td>
                    <td><?=$prep['CreatedBy']?></td>
                    <td><?=$prep['Created']?></td>
                    <td><?=$prep['EditedBy']?></td>
                    <td><?=$prep['Edited']?></td>
                </tr>
                <?php endforeach; ?>
            </table>
            <?php endif; ?>
            <?php endif; ?>

            <?php if (isset($MissingDatum)): ?>
            <?php if ($MissingDatum): ?>
            <h4>The datum hasn't been entered for these records (<?=count($MissingDatum)?>):</h4>
            <div><a href="#" class="selectall">select/clear all</a></div>
            <table class="table table-condensed table-bordered dberrors headingcolour4" style="width: 100%">
                <tr>
                    <th style="width: 4%">&nbsp;</th>
                    <th style="width: 18%">Catalogue number</th>
                    <th style="width: 25%">Created by</th>
                    <th style="width: 14%">Created on</th>
                    <th style="width: 25%">Collector</th>
                    <th style="width: 14%">Collecting no.</th>
                </tr>
                <?php foreach ($MissingDatum as $prep): ?>
                <tr>
                    <td style="width: 4%">
                        <?php
                            $value = $prep['CollectionObjectID'];
                            $opts = array(
                                'name' => 'recsetitems[]',
                                'value' => $value,
                                'checked' => ($this->input->post('recsetitems') && in_array($value, $this->input->post('recsetitems'))) ? TRUE : FALSE
                            );
                        ?>
                        <?=form_checkbox($opts)?>
                    </td>
                    <td><?=specify7_link($prep['CollectionObjectID'], $prep['CatalogNumber']);?></td>
                    <td><?=$prep['CreatedBy']?></td>
                    <td><?=$prep['Created']?></td>
                    <td><?=$prep['Collector']?></td>
                    <td><?=$prep['StationFieldNumber']?></td>
                </tr>
                <?php endforeach; ?>
            </table><p><a href="<?=$url . '/fqcr/MissingDatum'?>"><?=$url . '/fqcr/MissingDatum'?></a></p> 
            <?php endif; ?>
            <?php endif; ?>

            <?php if((isset($GroupAgentsWithoutIndividuals) && $GroupAgentsWithoutIndividuals) ||
                    (isset($AgentsWithNoLastName) && $AgentsWithNoLastName) ||
                    (isset($GroupAgentAsPersonAgent) && $GroupAgentAsPersonAgent)): ?>
            <h3>Agent</h3>
            <?php endif; ?>

            <?php if (isset($GroupAgentsWithoutIndividuals)): ?>
            <?php if ($GroupAgentsWithoutIndividuals): ?>
            <h4>These group agents haven't had any individuals added to the group, or haven't been given a last name (<?=count($GroupAgentsWithoutIndividuals)?>):</h4>
            <table class="table table-condensed table-bordered dberrors headingcolour5" style="width: 100%">
                <tr>
                    <th style="width: 4%">&nbsp;</th>
                    <th style="width: 28%">Group name</th>
                    <th style="width: 20%">Created by</th>
                    <th style="width: 14%">Created on</th>
                    <th style="width: 20%">Edited by</th>
                    <th style="width: 14%">Edited on</th>
                </tr>
                <?php foreach ($GroupAgentsWithoutIndividuals as $prep): ?>
                <tr>
                    <td>&nbsp;</td>
                    <td><?=$prep ['LastName']?></td>
                    <td><?=$prep['AgentCreatedBy']?></td>
                    <td><?=$prep['AgentCreated']?></td>
                    <td><?=$prep['AgentEditedBy']?></td>
                    <td><?=$prep['AgentEdited']?></td>
                </tr>
                <?php endforeach; ?>
            </table>
            <?php endif; ?>
            <?php endif; ?>

            <?php if (isset($AgentsWithNoLastName)): ?>
            <?php if ($AgentsWithNoLastName): ?>
            <h4>These group agents haven't had any individuals added to the group (<?=count($AgentsWithNoLastName)?>):</h4>
            <table class="table table-condensed table-bordered dberrors headingcolour5" style="width: 100%">
                <tr>
                    <th style="width: 4%">&nbsp;</th>
                    <th style="width: 28%">Initials</th>
                    <th style="width: 20%">Created by</th>
                    <th style="width: 14%">Created on</th>
                    <th style="width: 20%">Edited by</th>
                    <th style="width: 14%">Edited on</th>
                </tr>
                <?php foreach ($AgentsWithNoLastName as $prep): ?>
                <tr>
                    <td>&nbsp;</td>
                    <td><?=$prep ['FirstName']?></td>
                    <td><?=$prep['AgentCreatedBy']?></td>
                    <td><?=$prep['AgentCreated']?></td>
                    <td><?=$prep['AgentEditedBy']?></td>
                    <td><?=$prep['AgentEdited']?></td>
                </tr>
                <?php endforeach; ?>
            </table> 
            <?php endif; ?>
            <?php endif; ?>

            <?php if (isset($GroupAgentAsPersonAgent)): ?>
            <?php if ($GroupAgentAsPersonAgent): ?>
            <h4>This appears to be a group agent, but has been entered as a person agent (<?=count($GroupAgentAsPersonAgent)?>):</h4>
            <table class="table table-condensed table-bordered dberrors headingcolour5" style="width: 100%">
                <tr>
                    <th style="width: 4%">&nbsp;</th>
                    <th style="width: 28%">Last name</th>
                    <th style="width: 20%">Created by</th>
                    <th style="width: 14%">Created on</th>
                    <th style="width: 20%">Edited by</th>
                    <th style="width: 14%">Edited on</th>
                </tr>
                <?php foreach ($GroupAgentAsPersonAgent as $prep): ?>
                <tr>
                    <td>&nbsp;</td>
                    <td><?=$prep ['LastName']?></td>
                    <td><?=$prep['AgentCreatedBy']?></td>
                    <td><?=$prep['AgentCreated']?></td>
                    <td><?=$prep['AgentEditedBy']?></td>
                    <td><?=$prep['AgentEdited']?></td>
                </tr>
                <?php endforeach; ?>
            </table> 
            <?php endif; ?>
            <?php endif; ?>

            <?php if((isset($TreatedByNotNullAndCurationSponsorNull) && $TreatedByNotNullAndCurationSponsorNull) ||
                    (isset($TreatedByNotNullOtherTreatmentFieldsNull) && $TreatedByNotNullOtherTreatmentFieldsNull) ||
                    (isset($SeverityOrCauseNotNullButAssessedByNull) && $SeverityOrCauseNotNullButAssessedByNull)): ?>
            <h3>Conserv. event</h3>
            <?php endif; ?>

            <?php if (isset($TreatedByNotNullAndCurationSponsorNull)): ?>
            <?php if ($TreatedByNotNullAndCurationSponsorNull): ?>
            <h4>There is something in Treated By but nothing in Curation Sponsor (<?=count($TreatedByNotNullAndCurationSponsorNull)?>):</h4>
            <div><a href="#" class="selectall">select/clear all</a></div>
            <table class="table table-condensed table-bordered dberrors headingcolour7" style="width: 100%">
                <tr>
                    <th style="width: 4%">&nbsp;</th>
                    <th style="width: 18%">Catalogue number</th>
                    <th style="width: 25%">Created by</th>
                    <th style="width: 14%">Created on</th>
                    <th style="width: 25%">Edited by</th>
                    <th style="width: 14%">Edited on</th>
                </tr>
                <?php foreach ($TreatedByNotNullAndCurationSponsorNull as $prep): ?>
                <tr>
                    <td style="width: 4%">
                        <?php
                            $value = $prep['CollectionObjectID'];
                            $opts = array(
                                'name' => 'recsetitems[]',
                                'value' => $value,
                                'checked' => ($this->input->post('recsetitems') && in_array($value, $this->input->post('recsetitems'))) ? TRUE : FALSE
                            );
                        ?>
                        <?=form_checkbox($opts)?>
                    </td>
                    <td><?=specify7_link($prep['CollectionObjectID'], $prep['CatalogNumber']);?></td>
                    <td><?=$prep['CreatedBy']?></td>
                    <td><?=$prep['Created']?></td>
                    <td><?=$prep['EditedBy']?></td>
                    <td><?=$prep['Edited']?></td>
                </tr>
                <?php endforeach; ?>
            </table>
            <?php endif; ?>
            <?php endif; ?>

            <?php if (isset($TreatedByNotNullOtherTreatmentFieldsNull)): ?>
            <?php if ($TreatedByNotNullOtherTreatmentFieldsNull): ?>
            <h4>There is something in Treated By but nothing in Treatment Completed or Treatment Report (<?=count($TreatedByNotNullOtherTreatmentFieldsNull)?>):</h4>
            <div><a href="#" class="selectall">select/clear all</a></div>
            <table class="table table-condensed table-bordered dberrors headingcolour7" style="width: 100%">
                <tr>
                    <th style="width: 4%">&nbsp;</th>
                    <th style="width: 18%">Catalogue number</th>
                    <th style="width: 25%">Created by</th>
                    <th style="width: 14%">Created on</th>
                    <th style="width: 25%">Edited by</th>
                    <th style="width: 14%">Edited on</th>
                </tr>
                <?php foreach ($TreatedByNotNullOtherTreatmentFieldsNull as $prep): ?>
                <tr>
                    <td style="width: 4%">
                        <?php
                            $value = $prep['CollectionObjectID'];
                            $opts = array(
                                'name' => 'recsetitems[]',
                                'value' => $value,
                                'checked' => ($this->input->post('recsetitems') && in_array($value, $this->input->post('recsetitems'))) ? TRUE : FALSE
                            );
                        ?>
                        <?=form_checkbox($opts)?>
                    </td>
                    <td><?=specify7_link($prep['CollectionObjectID'], $prep['CatalogNumber']);?></td>
                    <td><?=$prep['CreatedBy']?></td>
                    <td><?=$prep['Created']?></td>
                    <td><?=$prep['EditedBy']?></td>
                    <td><?=$prep['Edited']?></td>
                </tr>
                <?php endforeach; ?>
            </table>
            <?php endif; ?>
            <?php endif; ?>

            <?php if (isset($SeverityOrCauseNotNullButAssessedByNull)): ?>
            <?php if ($SeverityOrCauseNotNullButAssessedByNull): ?>
            <h4>There is something in Severity of Cause of Damage, but nothing in Assessed By (<?=count($SeverityOrCauseNotNullButAssessedByNull)?>):</h4>
            <div><a href="#" class="selectall">select/clear all</a></div>
            <table class="table table-condensed table-bordered dberrors headingcolour7" style="width: 100%">
                <tr>
                    <th style="width: 4%">&nbsp;</th>
                    <th style="width: 18%">Catalogue number</th>
                    <th style="width: 25%">Created by</th>
                    <th style="width: 14%">Created on</th>
                    <th style="width: 25%">Edited by</th>
                    <th style="width: 14%">Edited on</th>
                </tr>
                <?php foreach ($SeverityOrCauseNotNullButAssessedByNull as $prep): ?>
                <tr>
                    <td style="width: 4%">
                        <?php
                            $value = $prep['CollectionObjectID'];
                            $opts = array(
                                'name' => 'recsetitems[]',
                                'value' => $value,
                                'checked' => ($this->input->post('recsetitems') && in_array($value, $this->input->post('recsetitems'))) ? TRUE : FALSE
                            );
                        ?>
                        <?=form_checkbox($opts)?>
                    </td>
                    <td><?=specify7_link($prep['CollectionObjectID'], $prep['CatalogNumber']);?></td>
                    <td><?=$prep['CreatedBy']?></td>
                    <td><?=$prep['Created']?></td>
                    <td><?=$prep['EditedBy']?></td>
                    <td><?=$prep['Edited']?></td>
                </tr>
                <?php endforeach; ?>
            </table>
            <?php endif; ?>
            <?php endif; ?>

            <?php if (isset($SharedLocalities)): ?>
            <?php if ($SharedLocalities): ?>

            <h4>You might want to check your shared localities (<?=count($SharedLocalities)?>):</h4>
            <div><a href="#" class="selectall">select/clear all</a></div>
            <table class="table table-condensed table-bordered dberrors headingcolour6" style="width: 100%">
                <tr>
                    <th style="width: 4%">&nbsp;</th>
                    <th style="width: 10%">Catalogue number</th>
                    <th style="width: 10%">Locality ID</th>
                    <th style="width: 6%">Count</th>
                    <th style="width: 10%">Primary collectors</th>
                    <th style="width: 8%">Coll. no.</th>
                    <th style="width: 13%">Coll. date</th>
                    <th style="width: 16%">Multisheets</th>
                    <th style="width: 10%">Created by</th>
                    <th style="width: 13%">Created on</th>
                </tr>
                <?php foreach ($SharedLocalities as $prep): ?>
                <tr>
                    <td style="width: 4%">
                        <?php
                            $value = $prep['CollectionObjectID'];
                            $opts = array(
                                'name' => 'recsetitems[]',
                                'value' => $value,
                                'checked' => ($this->input->post('recsetitems') && in_array($value, $this->input->post('recsetitems'))) ? TRUE : FALSE
                            );
                        ?>
                        <?=form_checkbox($opts)?>
                    </td>
                    <td><?=specify7_link($prep['CollectionObjectID'], $prep['CatalogNumber']);?></td>
                    <td><?=$prep['LocalityID']?></td>
                    <td><?=$prep['LocCount']?></td>
                    <td><?=$prep['PrimaryCollectors']?></td>
                    <td><?=$prep['CollectingNo']?></td>
                    <td><?=$prep['CollDate']?></td>
                    <td><?=$prep['Multisheets']?></td>
                    <td><?=$prep['CreatedBy']?></td>
                    <td><?=$prep['Created']?></td>
                </tr>
                <?php endforeach; ?>
            </table>
            <?php endif; ?>
            <?php endif; ?>

            </form>
        </div> <!-- /.col-md-12 -->
    </div> <!-- /.row -->
</div> <!-- /.container -->


<?php require_once('footer.php'); ?>
