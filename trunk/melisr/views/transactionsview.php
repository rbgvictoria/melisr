<?php require_once('header_1.php'); ?>

    <h2>Herbarium transaction paperwork</h2>
    <?=form_open('transactions/loanpaperwork'); ?>
    <?=form_fieldset('<b>Loans</b>')?>
    <br />
    <select name="loannumber" id="loannumber">
        <option value="">(select loan number)</option>
        <?php foreach($loans as $loan): ?>
            <?php
                $grey = null;
                if ($loan['haspreps'] == 0) $grey = ' style="color: #999999"';
            ?>
            <option value="<?=$loan['loanid'] ?>"<?=$grey ?>><?=$loan['loannumber'] ?></option>
        <?php endforeach; ?>
    </select>
    &nbsp;&nbsp;<?=form_submit('deleteduplicates', 'Delete \'duplicate\' loan preparations')?>
    <br /><br />
    <?=form_radio(array('name' => 'output', 'id' => 'output_option1', 'value' => 1, 'checked' => true))?>
    <?=form_label('Loan paperwork', 'output_option1', array('style' => 'position: relative; top: -4px; width: 200px'))?>
    <br />
    <?=form_radio(array('name' => 'output', 'id' => 'output_option2', 'value' => 2))?>
    <?=form_label('List of preparations', 'output_option2', array('style' => 'position: relative; top: -4px; width: 200px'))?>
    <br />
    <?=form_radio(array('name' => 'output', 'id' => 'output_option3', 'value' => 3))?>
    <?=form_label('Envelope', 'output_option3',  array('style' => 'position: relative; top: -4px; width: 200px'))?>
    <br />
    <?=form_radio(array('name' => 'output', 'id' => 'output_option4', 'value' => 4))?>
    <?=form_label('Parcel label', 'output_option4',  array('style' => 'position: relative; top: -4px; width: 200px'))?>
    <br />
    <?=form_radio(array('name' => 'output', 'id' => 'output_option5', 'value' => 5))?>
    <?=form_label('Conditions of loan', 'output_option5',  array('style' => 'position: relative; top: -4px; width: 200px'))?>
    <?=form_fieldset_close()?>
    <br />
    <?=form_fieldset('<b>Exchange</b>')?>
    <br />
    <select name="exchangeoutnumber" id="exchangeoutnumber">
        <option value="">(select outgoing exchange number)</option>
        <?php foreach($exchange_out as $exchange): ?>
            <?php
                $grey = null;
                if ($exchange['haspreps'] == 0) $grey = ' style="color: #999999;"';
            ?>
            <option value="<?=$exchange['giftid'] ?>"<?=$grey ?>><?=$exchange['giftnumber'] ?></option>
        <?php endforeach; ?>
    </select>
    &nbsp;&nbsp;<?=form_submit('fixexchangenumbers', 'Clever button thing')?>
    <br /><br />
    <?=form_radio(array('name' => 'output', 'id' => 'output_option6', 'value' => 6))?>
    <?=form_label('Exchange paperwork', 'output_option6', array('style' => 'position: relative; top: -4px; width: 200px'))?>
    <br />
    <?=form_radio(array('name' => 'output', 'id' => 'output_option7', 'value' => 7))?>
    <?=form_label('List of preparations', 'output_option7', array('style' => 'position: relative; top: -4px; width: 200px'))?>
    <br />
    <?=form_radio(array('name' => 'output', 'id' => 'output_option8', 'value' => 8))?>
    <?=form_label('Envelope', 'output_option8',  array('style' => 'position: relative; top: -4px; width: 200px'))?>
    <br />
    <?=form_radio(array('name' => 'output', 'id' => 'output_option9', 'value' => 9))?>
    <?=form_label('Parcel label', 'output_option9',  array('style' => 'position: relative; top: -4px; width: 200px'))?>
    <?=form_fieldset_close()?>
    <br />
    <?=form_fieldset('<b>Non-MEL loans</b>')?>
    <?=form_dropdown('nonmelloan', $non_mel_loans, FALSE, 'id="nonmelloans"');?>
    <br /><br />
    <?=form_radio(array('name' => 'output', 'id' => 'output_option12', 'value' => 12))?>
    <?=form_label('Non-MEL loan paperwork', 'output_option12', array('style' => 'position: relative; top: -4px; width: 200px'))?>
    <br />
    <?=form_radio(array('name' => 'output', 'id' => 'output_option14', 'value' => 14))?>
    <?=form_label('Envelope', 'output_option14',  array('style' => 'position: relative; top: -4px; width: 200px'))?>
    <br />
    <?=form_radio(array('name' => 'output', 'id' => 'output_option15', 'value' => 15))?>
    <?=form_label('Parcel label', 'output_option15',  array('style' => 'position: relative; top: -4px; width: 200px'))?>
    <?=form_fieldset_close()?>
    <br />
    <?=form_fieldset('<b>Address labels</b>')?>
    <br />
    <select name="institution" id="institution">
        <option value="">(select institution or person name)</option>
        <?php foreach($institutions as $inst): ?>
            <option value="<?=$inst['agentid'] ?>"><?=$inst['agentname'] ?></option>
        <?php endforeach; ?>
    </select>
    <br /><br />
    <?=form_radio(array('name' => 'output', 'id' => 'output_option10', 'value' => 10))?>
    <?=form_label('Envelope', 'output_option10',  array('style' => 'position: relative; top: -4px; width: 200px'))?>
    <br />
    <?=form_radio(array('name' => 'output', 'id' => 'output_option11', 'value' => 11))?>
    <?=form_label('Parcel label', 'output_option11',  array('style' => 'position: relative; top: -4px; width: 200px'))?>
    <?=form_fieldset_close()?>
    <br />
    <?=form_submit('submit', 'Submit')?>
<?=form_close(); ?>
    <div style="clear: both;">&nbsp;</div>
<?php require_once('footer.php')?>


