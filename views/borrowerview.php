<?php require_once('header_1.php'); ?>
<h2>Borrower</h2>

<?php if (isset($message) AND $message): ?>
<ul style="color:red">
    <?php if (is_array($message)): ?>
        <?php foreach ($message as $m): ?>
            <li><?=$m?></li>
        <?php endforeach; ?>
    <?php else: ?>
        <li><?=$message?></li>
    <?php endif; ?>
</ul>
<?php endif; ?>

<?=form_open('borrower/index')?>
<div id="non-mel-loan">
    <?=form_label('MEL reference number: ', 'melrefno', array('style'=>'width:146px;'))?>
    <?=form_dropdown('melrefno', $melrefnos, $this->input->post('melrefno'), 'id="melrefno"')?>
    <?=form_submit('find', 'Find');?>
</div>
<?php if (isset($loansummary)): ?>
<div id="workarea">
     <div id="numbers">
        <div>
            <h3>Enter barcodes</h3>
            <?php
                $data = array(
                              'name'        => 'barcodes',
                              'id'          => 'barcodes',
                              'rows'        => '150',
                              'cols'        => '80',
                              'style'       => 'width:418px;height:320px;',
                              'value'       => ''
                            );
                echo form_textarea($data);
            ?>
        </div>
    </div>
    <div id="workarea_col2">
        <div id="loansummary">
            <h3>Loan summary</h3>
            <div><b>Botanist:</b> <?=$botanist?></div>
            <div><b>Taxa:</b> <?=$taxa?></div>
            <?php foreach ($loansummary as $value): ?>
            <div><?=$value?></div>
            <?php endforeach; ?>
        </div>
        <div id="add-to-loan">
            <h3>Add preparations</h3>
            <div>
                <?=form_label('Prep. type: ', 'preptypeid', array('style'=>'width:146px'))?>
                <?=form_dropdown('preptypeid', $preptypes, $this->input->post('preptypeid'), 'id="preptypeid"')?>
            </div>
            <div>
                <?= form_label('Curation officer: ', 'specifyuser', array('style'=>'width: 146px'))?>
                <?php
                    $options = array(
                      '' => '',
                      '2' => 'Alison',
                      '7132' => 'Catherine',
                      '1028' => 'Helen',
                      '1' => 'Niels',
                      '10624' => 'Nimal',
                      '12416' => 'Rita', 
                      '7302' => 'Wayne',
                    );
                    echo form_dropdown('specifyuser', $options, $this->input->post('specifyuser'), 'id="specifyuser" style="width: 135px;"');            
                ?>
            </div>
            <div class="submit">
                <?=form_submit('add', 'Add to loan')?>
            </div>
        </div>
        <div id="return">
            <div>
                <h3>Return preparations</h3>
                <?= form_label('Returning officer: ', 'returningofficer', array('style'=>'width: 112px'))?>
                <?php
                    echo form_dropdown('returningofficer', $options, ($this->input->post('returningofficer')) ? $this->input->post('returningofficer') : $this->input->post('specifyuser'), 'id="returningofficer" style="width: 135px;"');            
                ?>
            </div>
            <div>
                <?=form_label('Return date: ', 'returndate', array('style' => 'width: 110px')); ?>
                <?=form_input(array('name' => 'returndate', 'value' => date('Y-m-d'))); ?> (yyyy-mm-dd)
            </div>
            <div class="submit">
                <?=form_submit('return', 'Prepare for return')?>
                <?=form_submit('returnpreps', 'Return'); ?>
            </div>
        </div>
    <?php endif; ?>
    </div>
    
</div>

<?php if (isset($loanpreparations)): ?>
<div id="loanpreparations">
    <h3>Loan preparations</h3>
<?php if ($loanpreparations): ?>
<table id="loanpreptable" style="width: 100%">
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
            <td>
                <?php 
                    echo $prep['CatalogNumber'];
                ?>
            </td>
            <td><?=$prep['PrepType']?></td>
            <td><?=$prep['QuantityReturned']?></td>
            <td>
                <?php
                    $data = array();
                    $data[0] = 0;
                    for ($i = 0; $i < $prep['Quantity']-$prep['QuantityReturned']; $i++) {
                        $data[$i+1] = $i+1;
                    }
                    echo form_dropdown("quantity[$key]", $data, (in_array($prep['LoanPreparationID'], $toreturn)) ? $prep['Quantity'] : 0, 
                            ($prep['Quantity']-$prep['QuantityReturned']==0) ? 'disabled="disabled"' : FALSE);

                    $data = array(
                        'name'        => "toreturn[$key]",
                        'value'       => $prep['LoanPreparationID'],
                        'checked'     => (in_array($prep['LoanPreparationID'], $toreturn) && $prep['Quantity']-$prep['QuantityReturned']!=0) ? 'checked' : FALSE,
                        );
                    if ($prep['Quantity']-$prep['QuantityReturned']==0)
                        $data['disabled'] = 'disabled';
                    echo form_checkbox($data);
                 ?>
            </td>
            <td><?=$prep['DateReturned']?></td>
            <td><?=$prep['TaxonName']?></td>
            <td>
                <?php
                    $data = array(
                        'name'        => "remarks[$key]",
                        'value'       => $prep['OutComments'],
                        'size'        => '10',
                        //'style'       => 'width:50%',
                    );
                    echo form_input($data);
                ?>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<div style="padding-top: 10px;"><?=form_submit('update', 'Update');?><?=form_submit('clear', 'Start over');?></div>
<?php else: ?>
<p>No loan preparations have been added for this loan.</p>
<?php endif; ?>
</div>
<?php endif; ?>

<?=form_close()?>

<?php require_once('footer.php'); ?>