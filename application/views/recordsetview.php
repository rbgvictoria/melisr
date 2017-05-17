<?php require_once('header.php'); ?>

<div class="container">
    <div class="row">
        <div class="col-md-12">
            
<h2>Record set creator</h2>

<form action="<?=site_url()?>recordset/create" method="post" class="form-horizontal">
    <div class="row">
        <div class="col-lg-6">
            <div class="form-group">
                <label class="col-md-3 control-label" for="specifyuser">Specify user:</label>
                <div class="col-md-9">
                    <select name="specifyuser" id="specifyuser" class="form-control">
                        <option value="0">(select username)</option>
                        <?php foreach ($specifyusers as $user): ?>
                            <?php if($this->input->post('specifyuser') && $user['id']==$this->input->post('specifyuser')) $selected = ' selected="selected"';
                                else $selected = NULL;
                            ?>
                            <option value="<?=$user['id']?>"<?=$selected?>><?=$user['username']?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div> <!-- /.col-lg-6 -->
        <div class="col-lg-6">
            <div class="form-group">
                <label class="col-md-3 control-label" for="recordsetname">Record set name:</label>
                <div class="col-md-9">
                    <?=form_input([
                            'name' => 'recordsetname', 
                            'id' => 'recordsetname', 
                            'value' => isset($recordsetname) ? $recordsetname : '', 
                            'class' => 'form-control'
                        ]);?>
                </div>
            </div>
        </div>
    </div> <!-- /.row -->
    
    <div class="form-group">
        <?=form_label('MEL barcodes: ', 'melnumbers', ['class' => 'col-md-3 control-label', 'for' => 'allparts']);?>
        <div class="col-md-9 text-right">
            <div class="checkbox">
                <label>
                    <?=form_checkbox([
                            'name' => 'allparts',
                            'id' => 'allparts',
                            'value' => 'allparts',
                            'checked' => ($this->input->post('allparts')) ? true : false
                        ]);?>
                    All parts
                </label>
            </div>
        </div>
    </div>
    
    
<?php
if (!isset($melnumbers)) {
    if ($this->input->post('melnumbers'))
        $melnumbers = $this->input->post('melnumbers');
    else
        $melnumbers = '';
}
?>
    <p>
    <?=form_textarea([
            'name'        => 'melnumbers',
            'value'       => $melnumbers,
            'class'       => 'form-control'
        ]);?>
    </p>
    
    
<p class="text-right">
    <input type="submit" name="check_names" id="check_names" value="Check taxon names" 
           title="Check taxon names" formaction="<?=site_url()?>recordset/check_names" class="btn btn-primary"/>
    <input type="submit" name="submit2" id="submit2" value="Create barcode string" 
           title="Click this button to create a comma delimited string you can copy and paste into Specify" 
           class="btn btn-primary"/>
    <input type="submit" name="submit1" id="submit1" value="Create record set" 
           title="Click this button to create a record set in your Specify side panel" 
           class="btn btn-primary"/>
</p>

</form>

<?php if (isset($taxa) && $taxa): ?>
<p>&nbsp;</p>
    
<table class="table table-condensed table-bordered">
    <tr><th>Catalogue number</th><th>Taxon name</th><th>Authorship</th></tr>
    <?php foreach ($taxa as $row): ?>
    <tr>
        <td><?=$row['CatalogNumber']?></td>
        <td><?=$row['FullName']?></td>
        <td><?=$row['Author']?></td>
    </tr>
    <?php endforeach; ?>
</table>

<?php endif; ?>


        </div> <!-- /.col- -->
    </div> <!-- /.row -->
</div> <!-- /.container -->




<?php require_once('footer.php'); ?>

