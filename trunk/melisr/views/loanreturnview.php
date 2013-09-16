<?php require_once('header_1.php'); ?>

<h2>Loan return</h2>

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
<?=form_open('loanreturn/prepare'); ?>
<?php 
    if (isset($loannumber))
        echo form_hidden('loanid', $loannumber);

?>

<?php if(isset($loaninfo)): ?>
<div style="float: right; width: 399px; margin-right: 16px;">
    <fieldset style="min-height: 100px; width: 394px; margin-bottom: 5px;">
        <table>
            <tr><th colspan="2">Loan info</th></tr>
            <tr>
                <td><b>Preparations in loan</b></td>
                <td><?=$loaninfo['Quantity']?></td>
            </tr>
            <?php if(isset($loaninfo['ReturnedBatches'])): ?>
            <tr>
                <td colspan="2"><b>Returned before:</b></td>
            </tr>
            <?php foreach ($loaninfo['ReturnedBatches'] as $batch): ?>
            <tr>
                <td><?=$batch['ReturnedDate']?></td>
                <td><?=$batch['QuantityReturned']?></td>
            </tr>
            
            <?php endforeach; ?>
            <tr>
                <td>Total</td>
                <td><b><?=$loaninfo['QuantityReturned']?></b></td>
            </tr>
            <?php else: ?>
            <tr>
                <td><b>Returned before</b></td>
                <td><?=$loaninfo['QuantityReturned']?></td>
            </tr>
            <?php endif; ?>
            
            
            <?php if (isset($loanpreps) && $loanpreps): ?>
            <tr>
                <td><b>Returned in this batch</b></td>
                <td><?=count($loanpreps)?></td>
            </tr>
            <tr>
                <td><b>Still on loan</b></td>
                <td><?=$loaninfo['Quantity']-$loaninfo['QuantityReturned']-count($loanpreps);?></td>
            </tr>
            <?php else: ?>
            <tr>
                <td><b>Still on loan</b></td>
                <td><?=$loaninfo['Quantity']-$loaninfo['QuantityReturned'];?></td>
            </tr>
            <?php endif; ?>
        </table>
    </fieldset>
    <fieldset style="height: 197px; margin-bottom: 10px">
        <div>
            <label style="width: 180px;" for="specifyuser">Curation officer: </label>
            <?php
                $options = array(
                  '' => '',
                  '2' => 'Alison',
                  '13' => 'Catherine',
                  '6' => 'Helen',
                  '1' => 'Niels',
                  '7' => 'Nimal',
                  '8' => 'Rita', 
                  '5' => 'Wayne',
                );

                echo form_dropdown('specifyuser', $options, $this->input->post('specifyuser'), 'id="specifyuser" style="width: 135px;"');            
            ?>
        </div>
        <div>
            <label style="width: 180px;" for="returndate">Return date (yyyy-mm-dd): </label>
            <?php
                $data = array(
                              'name'        => 'returndate',
                              'id'          => 'returndate',
                              'value'       => date('Y-m-d'),
                              'maxlength'   => '10',
                              'size'        => '18',
                            );

                echo form_input($data);
            ?>
        </div>
        <div>
            <label style="width: 200px;" for="quarantinemessage">Quarantine message: </label>
            <?php
                $data = array(
                              'name'        => 'quarantinemessage',
                              'id'          => 'quarantinemessage',
                              'rows'        => '2',
                              'cols'        => '46',
                            );

                echo form_textarea($data);
            ?>
            
        </div>
        <div style="height: 30px">
            <?=form_checkbox(array('name'=>'transfer', 'id'=>'transfer', 'value'=>'transfer')); ?>
            <?=form_label('Transfer', 'transfer');?>
            <span id="ajax"></span>
        </div>
        <div style="text-align: right;">
            <?=form_submit('return', 'Return batch')?>
        </div>
    </fieldset>
    
</div>
<?php endif; ?>


    <fieldset style="height: 320px; width: 450px; margin-bottom: 10px;">
        <select name="loannumber" id="loannumber">
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
        <?php
        $attributes = array(
            'style' => 'width: 40px; margin-left: 20px;',

        );
        echo form_label('Show', 'allpreps', $attributes);

        $options = array(
                          '0'  => 'Preparations in this batch',
                          '1'    => 'All preparations in loan',
                          '2'   => 'Outstanding preparations',
                        );
        
        $default = ($this->input->post('allpreps')) ? $this->input->post('allpreps') : 1;
        echo form_dropdown('allpreps', $options, $default, 'id="allpreps"');
        ?>
        <div style="margin-top: 20px"><?=form_label('Enter MEL barcodes one by one: ', 'melnumber', array('style' => 'width: 220px'));?>
        <?php
            $data = array(
                'name' => 'melnumber',
                'id' => 'melnumber'
            );
            echo form_input($data);
        ?>
        </div>

        <div style="margin-top: 20px"><?=form_label('Enter multiple MEL barcodes: ', 'melnumbers', array('style' => 'width: 200px'));?></div>
        <?php
        $data = array(
                      'name'        => 'melnumbers',
                      'id'          => 'melnumbers',
                      'rows'        => '100',
                      'cols'        => '80',
                      'style'       => 'width:100%; height: 178px'
                    );

        echo form_textarea($data);

        ?>
        <br />
        <?=form_submit('submit', 'Update batch')?><?=form_submit('clear', 'Clear form')?>
        
        <p style="height: 5px; padding: 0; margin: 0;">&nbsp;</p>
    </fieldset>

<?php if (isset($allpreps) && $allpreps): ?>
<?php
    $loanprepid = array();
    if (isset($loanpreps) && $loanpreps) {
        foreach ($loanpreps as $key => $row)
            $loanprepid[$key] = $row['LoanPreparationID'];
    }
?>
<table id="loanpreptable" style="width: 100%">
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

<?php elseif (isset($loanpreps) && $loanpreps): ?>
<table style="width: 870px">
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
<?php endif; ?>

<?=form_close(); ?>




<?php require_once('footer.php'); ?>

