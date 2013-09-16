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
        echo form_label('HISPID 5', 'hispid');
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

    <?=form_fieldset_close()?>
    <br />
    <?=form_submit('submit', 'Submit')?>
<?=form_close(); ?>
    <div style="clear: both;">&nbsp;</div>
    </div>
</div>
</body>
</html>



