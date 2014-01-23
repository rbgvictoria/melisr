<?php require_once('header_1.php'); ?>

    <h2>Exchange metadata</h2>
    <?=form_open('exchangedata/getdata', array('target' => '_blank')); ?>
    <?=form_fieldset()?>
    <br />
    <label for="giftnumber">Exchange: </label>
    <select name="giftnumber" id="giftnumber">
        <option value="">(select exchange number)</option>
        <?php foreach($gifts as $gift): ?>
            <?php
                $grey = null;
                if ($gift['haspreps'] == 0) $grey = ' style="color: #999999"';
            ?>
            <option value="<?=$gift['giftid'] ?>"<?=$grey ?>><?=$gift['giftnumber'] ?></option>
        <?php endforeach; ?>
    </select>
    <br/>
    <?=form_label('Record set:', 'recordset'); ?>
    <select name="recordset" id="recordset">
        <option value="">(select record set)</option>
        <?php foreach($recordsets as $set): ?>
            <option value="<?=$set['RecordSetID'] ?>"><?=$set['Name'] ?> [<?=$set['SpecifyUser'] ?>]</option>
        <?php endforeach; ?>
    </select>
    <br/>
    <p>Output format:</p>
    <?php 
        $data = array(
            'name'        => 'format',
            'id'          => 'hispid',
            'value'       => 'hispid',
            'checked'     => 'checked',
        );
        echo form_radio($data);
        echo form_label('AVH data (ABCD 2.06)', 'hispid', array('style'=>'width:auto;'));
        echo '<br/>';
        $data = array(
            'name'        => 'format',
            'id'          => 'csv',
            'value'       => 'csv'
        );
        echo form_radio($data);
        echo form_label('CSV', 'csv');
        echo '<br/>';
        $data = array(
            'name'        => 'format',
            'id'          => 'biocase',
            'value'       => 'biocase'
        );
        echo form_radio($data);
        echo form_label('BioCASe', 'biocase');
    ?>

    <p><?=form_submit('submit', 'Submit')?></p>
    <?=form_fieldset_close()?>
<?=form_close(); ?>
    <div>
        <?=form_open('exchangedata/updateBiocase')?>
        <?=form_hidden('lastupdated', $biocaseLastUpdated)?>
        <p>If any of the records in the exchange or record set have been created or modified today, you may have to update
            the BioCASe tables in order to get the latest changes. The biocase tables are up-to-date to: <?=$biocaseLastUpdated?>.</p>
        <p><?=form_submit('submit','Update BioCASe')?></p>
        <?=form_close()?>
    </div>
    <div style="clear: both;">&nbsp;</div>
    </div>
</div>
</body>
</html>



