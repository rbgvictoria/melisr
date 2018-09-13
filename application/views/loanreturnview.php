<?php require_once('header_1.php'); ?>

<div class="container">
    <div class="row">
        <form action="<?=site_url()?>loanreturn/prepare" method="post" 
              class="form-horizontal">
            
            <div class="col-md-12">
                <h2>Loan return</h2>
                <?php 
                    if (isset($loannumber))
                        echo form_hidden('loanid', $loannumber);

                ?>
            </div>


            <div class="col-md-6">
                <div class="form-group">
                    <label class="control-label col-md-4" for="loannumber">
                        Loan number
                    </label>
                    <div class="col-md-8">
                        <select name="loannumber" id="loannumber" class="form-control">
                            <option value="">(select loan number)</option>
                            <?php foreach($loans as $loan): ?>
                                <?php if($this->input->post('loannumber') && $loan['loanid']==$this->input->post('loannumber')) $selected = ' selected="selected"';
                                    elseif (isset($loannumber) && $loan['loanid'] == $loannumber) $selected = ' selected="selected"';
                                    else $selected = NULL;
                                ?>
                                <?php
                                    $grey = null;
                                    if ($loan['haspreps'] == 0) $grey = ' style="color: #999999"';
                                ?>
                                <option value="<?=$loan['loanid'] ?>"<?=$grey ?><?=$selected?>><?=$loan['loannumber'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div> <!-- /.form-group -->
                    
                <div class="form-group">
                    <label class="control-label col-md-4" for="allpreps">
                        Show
                    </label>
                    <div class="col-md-8">
                        <select name="allpreps" id="allpreps" class="form-control">
                            <option value="0" selected="true">Preparations in this batch</option>
                            <option value="1">All preparations in loan</option>
                            <option value="2">Outstanding preparations</option>
                        </select>
                    </div>
                </div>
                    
                <div>
                    <label class="control-label" for="melnumbers">
                        Enter MEL numbers
                    </label>
                </div>
                <div>
                    <textarea name="melnumbers" id="melnumbers" rows="10"
                              class="form-control"></textarea>
                </div>

                <div class="form-group">
                    <div class="col-md-6">
                        <button type="submit" name="submit" 
                            class="btn btn-primary btn-block">
                                    Update batch
                        </button>
                    </div>
                    <div class="col-md-6">
                        <button type="submit" name="clear" 
                            class="btn btn-primary btn-block">
                                    Clear form
                        </button>
                    </div>
                </div>
            </div> <!-- /.col-md-6 -->

            <div class="col-md-6">
                <?php if(isset($loaninfo)): ?>
                <h4>Loan info.</h4>
                <div class="form-group">
                    <label class="control-label col-md-6">
                        Preparations in loan
                    </label>
                    <div class="col-md-6">
                        <p class="form-control-static"><?=$loaninfo['Quantity']?></p>
                    </div>
                </div>
                
                <?php if (isset($loaninfo['ReturnedBatches'])):?>
                <h4>Returned before</h4>
                <?php foreach ($loaninfo['ReturnedBatches'] as $batch): ?>
                <div class="form-group">
                    <label class="control-label col-md-6">
                        <?=$batch['ReturnedDate']?>
                    </label>
                    <div class="col-md-6">
                        <p class="form-control-static"><?=$batch['QuantityReturned']?></p>
                    </div>
                </div>
                <?php endforeach; ?>
                <div class="form-group">
                    <label class="control-label col-md-6">
                        Total
                    </label>
                    <div class="col-md-6">
                        <p class="form-control-static"><?=$loaninfo['QuantityReturned']?></p>
                    </div>
                </div>
                <?php else: ?>
                <div class="form-group">
                    <label class="control-label col-md-6">
                        Quantity returned
                    </label>
                    <div class="col-md-6">
                        <p class="form-control-static"><?=$loaninfo['QuantityReturned']?></p>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if (isset($loanpreps) && $loanpreps): ?>
                <div class="form-group">
                    <label class="control-label col-md-6">
                        Returned in this batch
                    </label>
                    <div class="col-md-6">
                        <p class="form-control-static"><?=count($loanpreps)?></p>
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label col-md-6">
                        Still on loan
                    </label>
                    <div class="col-md-6">
                        <p class="form-control-static"><?=$loaninfo['Quantity']-$loaninfo['QuantityReturned']-count($loanpreps);?></p>
                    </div>
                </div>
                <?php else: ?>
                <div class="form-group">
                    <label class="control-label col-md-6">
                        Still on loan
                    </label>
                    <div class="col-md-6">
                        <p class="form-control-static"><?=$loaninfo['Quantity']-$loaninfo['QuantityReturned'];?></p>
                    </div>
                </div>
                <?php endif;?>
                
                <div class="form-group">
                    <label class="control-label col-md-6">
                        Curation officer
                    </label>
                    <div class="col-md-6">
                        <?php
                            $options = array(
                                '' => '',
                                '80' => 'Aaron',
                                '72' => 'Alison',
                                '13' => 'Catherine',
                                '6' => 'Helen',
                                '1' => 'Niels',
                                '7' => 'Nimal',
                                '8' => 'Rita', 
                                '78' => 'Wayne',
                            );
                        ?>
                        <?=form_dropdown('specifyuser', $options, $this->input->post('specifyuser'), 'id="specifyuser" class="form-control"');?>`            
                        
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="control-label col-md-6">
                        Return date
                    </label>
                    <div class="col-md-6">
                        <input type="text" name="returndate" id="returndate"
                               value="<?=date('Y-m-d')?>" placeholder="yyyy-mm-dd"
                               class="form-control" />
                    </div>
                </div>
                
                <div>
                    <label class="control-label" for="quarantinemessage">
                        Quarantine message
                    </label>
                    <textarea name="quarantinemessage" id="quarantinemessage"
                              rows="3" class="form-control"></textarea>
                </div>
                
                <div class="checkbox">
                    <label>
                        <input type="checkbox" name="transfer" value="transfer">
                        Transfer
                    </label>
                </div>
                
                <div>
                    <button class="btn btn-primary btn-block"
                            type="submit" name="return" value="1">
                        Return batch
                    </button>
                </div>
                <br/>
                <?php endif; ?>
            </div> <!-- /.col-md-6 -->

            <?php if (isset($allpreps) && $allpreps): ?>
            <div class="col-md-12">
                <?php
                    $loanprepid = array();
                    if (isset($loanpreps) && $loanpreps) {
                        foreach ($loanpreps as $key => $row)
                            $loanprepid[$key] = $row['LoanPreparationID'];
                    }
                ?>
                <table id="loanpreptable" class="table table-bordered table-condensed table-responsive">
                    <thead>
                        <tr>
                            <th style="width: 85px; text-align: left;">Catalogue number</th>
                            <th style="width: 65px; text-align: left;">Prep. type</th>
                            <th style="width: 80px; text-align: left;">Quantity returned</th>
                            <th style="width: 95px; text-align: left;">Date returned</th>
                            <th style="width: 280px; text-align: left;">Taxon name</th>
                            <th style="width: auto; text-align: left;">Comments</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($allpreps as $key => $prep): ?>
                        <?php 
                            $quantity = FALSE;
                            $returned = FALSE;
                            $key = array_search($prep['LoanPreparationID'], $loanprepid);
                            $returnprep = FALSE;
                            if ($key !== FALSE)
                                $returnprep = TRUE;
                        ?>
                        <tr>
                            <td style="width: 85px;">
                                <?php 
                                    if ($returnprep) {
                                        $data = array(
                                            'cataloguenumber[]' => $loanpreps[$key]['CatalogNumber'],
                                            'loanpreparationid[]' => $loanpreps[$key]['LoanPreparationID'],
                                            'preptype[]' => $loanpreps[$key]['PrepType'],
                                            'taxonname[]' => $loanpreps[$key]['TaxonName'],
                                        );
                                        echo form_hidden($data);
                                    }
                                    echo $prep['CatalogNumber'];
                                ?>
                            </td>
                            <td style="width: 65px;"><?=$prep['PrepType']?></td>
                            <td style="width: 120px;">
                                <?php
                                    if ($returnprep) {
                                        echo '<select name="quantity[]">';
                                        for ($i = 0; $i < $loanpreps[$key]['Quantity']; $i++)
                                            echo "<option value=\"$i\">$i</option>";
                                        echo '<option value="' . $loanpreps[$key]['Quantity'] . '" selected="selected">' . 
                                                $loanpreps[$key]['Quantity'] . '</option>';
                                        echo '</select>&nbsp;';

                                        $data = array(
                                            'name'        => "returned[$key]",
                                            'value'       => '1',
                                            'checked'     => 'checked',
                                            );
                                        echo form_checkbox($data);
                                     }
                                     else
                                         echo $prep['QuantityReturned'];
                                 ?>
                            </td>
                            <td style="width: 95px;"><?=$prep['ReturnedDate']?></td>
                            <td style="width: 280px; text-align: left;"><?=$prep['TaxonName']?></td>
                            <td>
                                <?php
                                    if ($returnprep) {
                                        $data = array(
                                            'name'        => 'remarks[]',
                                            'value'       => $loanpreps[$key]['Remarks'],
                                            'size'        => '10',
                                            //'style'       => 'width:50%',
                                        );
                                        echo form_input($data);
                                    }
                                    else echo $prep['Remarks'];
                                ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>

                <div style="padding-top: 10px;"><?=anchor('loanreturn/startover/' . $this->input->post('loannumber'), 'Start over');?></div>
            </div>

            <?php elseif (isset($loanpreps) && $loanpreps): ?>
            <div class="col-md-12">
                <table class="table table-bordered table-condensed table-responsive">
                    <tr>
                        <th style="width: 85px; text-align: left;">Catalogue number</th>
                        <th style="width: 65px; text-align: left;">Prep. type</th>
                        <th style="width: 120px; text-align: left;">Quantity returned</th>
                        <th style="width: 95px; text-align: left;">Date returned</th>
                        <th style="width: 280px; text-align: left;">Taxon name</th>
                        <th style="text-align: left;">Comments</th>
                    </tr>
                </table>
                <div style="max-height: 401px; overflow: auto">
                <table  style="width: 870px">
                    <?php foreach ($loanpreps as $key => $prep): ?>
                    <tr><td style="width: 85px;">
                            <?php 
                                $data = array(
                                    'cataloguenumber[]' => $prep['CatalogNumber'],
                                    'loanpreparationid[]' => $prep['LoanPreparationID'],
                                    'preptype[]' => $prep['PrepType'],
                                    'taxonname[]' => $prep['TaxonName'],
                                );
                                echo form_hidden($data);
                            ?>
                            <?=$prep['CatalogNumber']?>
                        </td>
                        <td style="width: 65px;"><?=$prep['PrepType']?></td>
                        <td style="width: 120px;">
                            <select name="quantity[]">
                            <?php for ($i = 0; $i < $prep['Quantity']; $i++): ?>
                                <option value="<?=$i?>"><?=$i?></option>
                            <?php endfor;?>
                                <option value="<?=$prep['Quantity']?>" selected="selected"><?=$prep['Quantity']?></option>
                            </select>
                            &nbsp;
                            <?php
                                $data = array(
                                    'name'        => "returned[$key]",
                                    'value'       => '1',
                                    'checked'     => 'checked',
                                    );
                                echo form_checkbox($data);
                            ?>
                        </td>
                        <td style="width: 95px">&nbsp;</td>
                        <td style="width: 280px"><?=$prep['TaxonName']?></td>
                        <td>
                            <?php
                                $data = array(
                                    'name'        => 'remarks[]',
                                    'value'       => $prep['Remarks'],
                                    'size'        => '10',
                                    //'style'       => 'width:50%',
                                );
                                echo form_input($data);
                            ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </table>
                </div>
                <div style="padding-top: 10px;"><?=anchor('loanreturn/startover/' . $this->input->post('loannumber'), 'Start over');?></div>
            </div> <!-- /.col-md-12 -->
            <?php endif; ?>

        </form>

            
    </div> <!-- /.row -->
</div> <!-- /.container -->

<?php require_once('footer.php'); ?>

