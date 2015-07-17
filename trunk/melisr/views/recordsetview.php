<?php require_once('header.php'); ?>

<h2>Record set creator</h2>

<?=form_open(site_url() . '/recordset/create'); ?>
<select name="specifyuser" id="specifyuser">
    <option value="0">(select username)</option>
    <?php foreach ($specifyusers as $user): ?>
        <?php if($this->input->post('specifyuser') && $user['id']==$this->input->post('specifyuser')) $selected = ' selected="selected"';
            else $selected = NULL;
        ?>
        <option value="<?=$user['id']?>"<?=$selected?>><?=$user['username']?></option>
    <?php endforeach; ?>
</select>
<?=form_label('Record set name: ', 'recordsetname', array('style' => 'width: 145px; text-align: right'));?>
<?php
    if (!isset($recordsetname)) $recordsetname='';
    $data = array(
      'name'        => 'recordsetname',
      'id'          => 'recordsetname',
      'value'       => $recordsetname,
      'maxlength'   => '32',
      'size'        => '20'
    );
    echo form_input($data);
?>
<div style="margin-top: 20px"><?=form_label('MEL barcodes: ', 'melnumbers', array('style' => 'width: 355px'));?>
    <?php
        $data = array(
            'name' => 'allparts',
            'id' => 'allparts',
            'value' => 'allparts',
            'checked' => ($this->input->post('allparts')) ? 'checked' : FALSE
        );
        echo form_checkbox($data);
        echo form_label('All parts', 'allparts');
    ?>
</div>
<?php
if (!isset($melnumbers)) {
    if ($this->input->post('melnumbers'))
        $melnumbers = $this->input->post('melnumbers');
    else
        $melnumbers = '';
}
$data = array(
              'name'        => 'melnumbers',
              'id'          => 'melnumbers',
              'value'       => $melnumbers,
              'rows'        => '100',
              'cols'        => '80',
              'style'       => 'width:50%; height: 120px'
            );

echo form_textarea($data);

?>
<div style="width: 50%; text-align: right;">
    <input type="submit" name="check_names" id="check_names" value="Check taxon names" title="Check taxon names" />
    <input type="submit" name="submit2" id="submit2" value="Create barcode string" title="Click this button to create a comma delimited string you can copy and paste into Specify" />
    <input type="submit" name="submit1" id="submit1" value="Create record set" title="Click this button to create a record set in your Specify side panel" />
</div>

<?=form_close(); ?>

<?php if (isset($taxa) && $taxa): ?>
<p>&nbsp;</p>
    
<table>
    <tr><th>Catalogue number</th><th>Taxon name</th></tr>
    <?php foreach ($taxa as $row): ?>
    <tr>
        <td><?=$row['CatalogNumber']?></td>
        <td><?=$row['TaxonName']?></td>
    </tr>
    <?php endforeach; ?>
</table>

<?php endif; ?>



<?php require_once('footer.php'); ?>

