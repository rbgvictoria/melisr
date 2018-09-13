<?php require_once('header_1.php'); ?>

<div class="container">
    <div class="row">
        <div class="col-md-12">
            <h2>Borrower</h2>

            <form action="<?=site_url()?>borrower/index" method="post" class="form-horizontal">
            <div id="non-mel-loan" class="form-group">
                <label class="col-md-3 control-label" for="melrefno">MEL Reference number:</label>
                <div class="col-md-9 form-inline">
                    <?=form_dropdown('melrefno', $melrefnos, $this->input->post('melrefno'), 'id="melrefno" class="form-control"')?>
                    <button type="submit" name="find" class="btn btn-primary" value="1">Find</button>
                </div>
            </div>
            <?php if (isset($loansummary)): ?>
            <div id="workarea">
                 <div>
                    <h3>Enter barcodes</h3>
                    <div>
                        <textarea name="barcodes" id="barcodes"
                                  class="form-control" rows="10"><?=$this->input->post('stickybarcodes')?></textarea>
                    </div>
                </div>
                
                <div id="workarea_col2">
                    <div id="loansummary">
                        <h3>Loan summary</h3>
                        <div class="form-group">
                            <label class="col-md-2 control-label">
                                Botanist:
                            </label>
                            <div class="col-md-10">
                                <p class="form-control-static"><?=$botanist?></p>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="col-md-2 control-label">Taxa:</label>
                            <div class="col-md-10">
                                <p class="form-control-static"><?=$taxa?></p>
                            </div>
                        </div>
                        
                        <?php foreach ($loansummary as $value): ?>
                        <div class="form-group">
                            <?php list($date, $statement) = explode(': ', $value); ?>
                            <label class="col-md-2 control-label">
                                <?=$date ?: '&ndash;' ?>
                            </label>
                            <div class="col-md-10">
                                <p class="form-control-static"><?=$statement?></p>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <div id="add-to-loan">
                        <h3>Add preparations</h3>
                        <div class="form-group">
                            <label class="col-md-2 control-label" for="preptypeid">
                                Prep. type:
                            </label>
                            <div class="col-md-10">
                                <?=form_dropdown('preptypeid', $preptypes, 
                                        $this->input->post('preptypeid'), 
                                        'id="preptypeid" class="form-control"')?>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="col-md-2 control-label" for="specifyuser">
                                Curation officer:
                            </label>
                            <div class="col-md-10">
                                <?php
                                    $options = array(
                                      '' => '',
                                      '30781' => 'Aaron',
                                      '2' => 'Alison',
                                      '7132' => 'Catherine',
                                      '1028' => 'Helen',
                                      '1' => 'Niels',
                                      '10624' => 'Nimal',
                                      '12416' => 'Rita', 
                                      '7302' => 'Wayne',
                                    );
                                ?>
                                <?=form_dropdown('specifyuser', $options, 
                                        $this->input->post('specifyuser'), 
                                        'id="specifyuser" class="form-control"')?>
                            </div>
                        </div>
                        
                        <div class="submit">
                            <button type="submit" name="add" class="btn btn-primary" value="1">
                                Add to loan
                            </button>
                        </div>
                    </div>
                    
                    <div id="return">
                        <h3>Return preparations</h3>
                        <div class="form-group">
                            <label class="col-md-2 control-label" for="returningofficer">
                                Returning officer:
                            </label>
                            <div class="col-md-10">
                                <?=form_dropdown('returningofficer', $options, 
                                        ($this->input->post('returningofficer')) ? 
                                        $this->input->post('returningofficer') : 
                                    $this->input->post('specifyuser'), 
                                        'id="returningofficer" class="form-control"')?>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="col-md-2 control-label" for="returndate">
                                Return date:
                            </label>
                            <div class="col-md-10">
                                <input type="text" name="returndate"
                                       value="<?=date('Y-m-d')?>" placeholder="yyyy-mm-dd"
                                       class="form-control" />
                            </div>
                        </div>
                        <div class="submit">
                            <button type="submit" name="return" value="1" 
                                    class="btn btn-primary">
                                Prepare for return
                            </button>
                            <button type="submit" value="1" name="returnpreps" 
                                    class="btn btn-primary">
                                Return
                            </button>
                        </div>
                    </div>
                <?php endif; ?>
                </div>

            </div>

            <?php if (isset($loanpreparations)): ?>
            <div id="loanpreparations">
                <h3>Loan preparations</h3>
            <?php if ($loanpreparations): ?>
            <table id="loanpreptable" class="table table-condensed table-bordered table-responsive">
                <thead>
                    <tr>
                        <th style="width: 100px; text-align: left;">Catalogue number</th>
                        <th style="width: 65px; text-align: left;">Prep. type</th>
                        <th style="width: 80px; text-align: left;">Quantity returned</th>
                        <th>Return</th>
                        <th style="width: 95px; text-align: left;">Date returned</th>
                        <th style="width: 200px; text-align: left;">Taxon name</th>
                        <th style="width: auto; text-align: left;">Comments</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($loanpreparations as $key => $prep): ?>
                    <tr>
                        <td><?=$prep['CatalogNumber']?></td>
                        <td><?=$prep['PrepType']?></td>
                        <td><?=$prep['QuantityReturned']?></td>
                        <td>
                            <?php
                                $data = array();
                                $data[0] = 0;
                                for ($i = 0; $i < $prep['Quantity']-$prep['QuantityReturned']; $i++) {
                                    $data[$i+1] = $i+1;
                                }
                                ?>
                                <?=form_dropdown("quantity[$key]", $data, (in_array($prep['LoanPreparationID'], 
                                        $toreturn)) ? $prep['Quantity'] : 0, 
                                        ($prep['Quantity']-$prep['QuantityReturned']==0) ? 'disabled="true" class="form-control input-sm"' : 'class="form-control input-sm"');?>
                            
                            
                            
                                <?php
                                $data = array(
                                    'name'        => "toreturn[$key]",
                                    'value'       => $prep['LoanPreparationID'],
                                    'checked'     => (in_array($prep['LoanPreparationID'], 
                                            $toreturn) && $prep['Quantity']-$prep['QuantityReturned']!=0) ? 'checked' : FALSE,
                                    );
                                if ($prep['Quantity']-$prep['QuantityReturned']==0)
                                    $data['disabled'] = 'disabled';
                                ?>
                                <?=form_checkbox($data)?>
                        </td>
                        <td><?=$prep['DateReturned']?></td>
                        <td><?=$prep['TaxonName']?></td>
                        <td>
                            <?php
                                $data = array(
                                    'name'        => "remarks[$key]",
                                    'value'       => $prep['OutComments'],
                                    'size'        => '10',
                                    'class'       => 'form-control input-sm',
                                    'style'       => 'width: 100%;'
                                );
                                echo form_input($data);
                            ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>

            <div style="padding-bottom: 10px; padding-top: 10px">
                <button type="submit" name="update" value="1" 
                        class="btn btn-primary">
                    Update
                </button>
                <button type="submit" name="clear" value="1" 
                        class="btn btn-primary">
                    Start over
                </button>
            </div>
                
            <?php else: ?>
            <p>No loan preparations have been added for this loan.</p>
            <?php endif; ?>
            </div>
            <?php endif; ?>

            </form>
            
        </div> <!-- /.col-md-12-->
    </div> <!-- /.row -->
</div> <!-- /.container -->

<?php require_once('footer.php'); ?>