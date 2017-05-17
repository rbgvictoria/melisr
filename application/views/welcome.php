<?php require_once('header.php'); ?>

<div class="container">
    <div class="row">
        <div class="col-md-12">
            
            <h2>MELISR labels</h2>
            <?=form_open('labels/label', array('target' => '_blank', 
                    'class' => 'form-horizontal')); ?>
                <?=form_fieldset('Records', array('id' => 'fieldset_records'));?>
                <div class="form-group">
                    <?=form_label('Select record set:', 'recordset', 
                            ['class' => 'col-md-3 control-label', 'for' => 'recordset']); ?>
                    <div class="col-md-9">
                        <select name="recordset" id="recordset" class="form-control">
                            <option value="">(select recordset)</option>
                            <?php foreach($recordsets as $set): ?>
                                <option value="<?=$set['RecordSetID'] ?>"><?=$set['Name'] ?> 
                                    [<?=$set['SpecifyUser'] ?>]</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

            <div class="form-group">
                <?=form_label('<small>or</small> MEL number:', 'melno_start', 
                        ['class' => 'col-md-3 control-label', 'for' => 'melno_start']); ?>
                <div class="col-md-9">
                    <div class="form-inline">
                        <?=form_input(array(
                            'name'        => 'melno_start',
                            'id'          => 'melno_start',
                            'value'       => '',
                            'maxlength'   => '7',
                            'size'        => '8',
                            'placeholder' => 'start',
                            'class'       => 'form-control'
                        )); ?>
                        &nbsp;
                        <?=form_input(array(
                            'name'        => 'melno_count',
                            'id'          => 'melno_count',
                            'value'       => '1',
                            'maxlength'   => '3',
                            'size'        => '5',
                            'placeholder' => 'count',
                            'class'       => 'form-control'
                        )); ?>
                        <small>or</small>
                        <?=form_input(array(
                            'name'        => 'melno_end',
                            'id'          => 'melno_end',
                            'value'       => '',
                            'maxlength'   => '7',
                            'size'        => '8',
                            'placeholder' => 'end',
                            'class'       => 'form-control'
                        )); ?>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <?=form_label('<small>or</small> VRS number:', 'vrsno_start', 
                        ['class' => 'col-md-3 control-label', 'for' => 'vrsno_start']); ?>
                <div class="col-md-9">
                    <div class="form-inline">
                        <?=form_input(array(
                            'name'        => 'vrsno_start',
                            'id'          => 'vrsno_start',
                            'value'       => '',
                            'maxlength'   => '7',
                            'size'        => '8',
                            'placeholder' => 'start',
                            'class'       => 'form-control'
                        )); ?>
                        &nbsp;
                        <?=form_input(array(
                            'name'        => 'vrsno_count',
                            'id'          => 'vrsno_count',
                            'value'       => '1',
                            'maxlength'   => '3',
                            'size'        => '5',
                            'placeholder' => 'count',
                            'class'       => 'form-control'
                        )); ?>
                        <small>or</small>
                        <?=form_input(array(
                            'name'        => 'vrsno_end',
                            'id'          => 'vrsno_end',
                            'value'       => '',
                            'maxlength'   => '7',
                            'size'        => '8',
                            'placeholder' => 'end',
                            'class'       => 'form-control'
                        )); ?>
                    </div>
                </div>
            </div>
                <?=form_fieldset_close()?>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <?=form_label('Start printing at label:', 'start', 
                                ['class' => 'col-md-6 control-label', 'for' => 'start']); ?>
                        <div class="col-md-2">
                            <?=form_input(['name' => 'start', 'id' => 'start', 'value' => '1', 'class' => 'form-control']); ?>
                        </div>
                    </div>
                </div>
                <div class="checkbox text-right col-md-6">
                    <label>
                        <?=form_checkbox(['name' => 'part', 'id' => 'part', 'value' => 'part']); ?>
                        print labels for parts
                    </label>
                </div>
            </div> <!-- /.row -->

            <fieldset>
                <legend>Label type

                </legend>
            <div>
                <div class="row">
                    <div class="col-lg-6">
                <div class="radio">
                    <label>
                        <?=form_radio(array(
                            'name' => 'labeltype',
                            'id' => 'lt3',
                            'value' => '3',
                            'checked' => true
                        )); ?>
                        Standard herbarium sheet label, 4 per page
                    </label>
                </div>
                <div class="radio">
                    <label>
                        <?=form_radio(array(
                            'name' => 'labeltype',
                            'id' => 'lt2',
                            'value' => '2',
                            'checked' => false
                        )); ?>
                        Long label, 2 per page
                    </label>
                </div>
                <div class="radio">
                    <label>
                        <?=form_radio(array(
                            'name' => 'labeltype',
                            'id' => 'lt1',
                            'value' => '1',
                            'checked' => false
                        )); ?>
                        Fungi and lichen packet label, 4 per page
                    </label>
                </div>
                <div class="radio">
                    <label>
                        <?=form_radio(array(
                            'name' => 'labeltype',
                            'id' => 'lt5',
                            'value' => '5',
                            'checked' => false
                        )); ?>
                        Fungi and lichen packet label, 2 per page
                    </label>
                </div>
                <div class="radio">
                    <label>
                        <?=form_radio(array(
                            'name' => 'labeltype',
                            'id' => 'lt4',
                            'value' => '4',
                            'checked' => false
                        )); ?>
                        Bryophyte label, 3 per page
                    </label>
                </div>
                <div class="radio">
                    <label>
                        <?=form_radio(array(
                            'name' => 'labeltype',
                            'id' => 'lt5',
                            'value' => '5',
                            'checked' => false
                        )); ?>
                        Bryophyte label, 2 per page
                    </label>
                </div>
                <div class="radio">
                    <label>
                        <?=form_radio(array(
                            'name' => 'labeltype',
                            'id' => 'lt6',
                            'value' => '6',
                            'checked' => false
                        )); ?>
                        Duplicate label, 4 per page
                    </label>
                </div>
                <div class="radio">
                    <label>
                        <?=form_radio(array(
                            'name' => 'labeltype',
                            'id' => 'lt7',
                            'value' => '7',
                            'checked' => false
                        )); ?>
                        Duplicate label, 2 per page
                    </label>
                </div>
                <div class="radio">
                    <label>
                        <?=form_radio(array(
                            'name' => 'labeltype',
                            'id' => 'lt13',
                            'value' => '13',
                            'checked' => false
                        )); ?>
                        Duplicate label for seed collections, 4 per page
                    </label>
                </div>
                <div class="radio">
                    <label>
                        <?=form_radio(array(
                            'name' => 'labeltype',
                            'id' => 'lt14',
                            'value' => '14',
                            'checked' => false
                        )); ?>
                        Duplicate label for seed collections, 2 per page
                    </label>
                </div>
                    </div> <!-- /.col- -->
                    <div class="col-lg-6">
                <div class="radio">
                    <label>
                        <?=form_radio(array(
                            'name' => 'labeltype',
                            'id' => 'lt8',
                            'value' => '8',
                            'checked' => false
                        )); ?>
                        Spirit jar label
                    </label>
                </div>
                <div class="radio">
                    <label>
                        <?=form_radio(array(
                            'name' => 'labeltype',
                            'id' => 'lt12',
                            'value' => '12',
                            'checked' => false
                        )); ?>
                        Spirit collection card
                    </label>
                </div>
                <div class="radio">
                    <label>
                        <?=form_radio(array(
                            'name' => 'labeltype',
                            'id' => 'lt22',
                            'value' => '22',
                            'checked' => false
                        )); ?>
                        Spirit collection card (mail area printer)
                    </label>
                </div>
                <div class="radio">
                    <label>
                        <?=form_radio(array(
                            'name' => 'labeltype',
                            'id' => 'lt17',
                            'value' => '17',
                            'checked' => false
                        )); ?>
                        Carpological collection card
                    </label>
                </div>
                <div class="radio">
                    <label>
                        <?=form_radio(array(
                            'name' => 'labeltype',
                            'id' => 'lt23',
                            'value' => '23',
                            'checked' => false
                        )); ?>
                        Carpological collection card (mail area printer)
                    </label>
                </div>
                <div class="radio">
                    <label>
                        <?=form_radio(array(
                            'name' => 'labeltype',
                            'id' => 'lt18',
                            'value' => '18',
                            'checked' => false
                        )); ?>
                        Silica gel sample label
                    </label>
                </div>
                <div class="radio">
                    <label>
                        <?=form_radio(array(
                            'name' => 'labeltype',
                            'id' => 'lt9',
                            'value' => '9',
                            'checked' => false
                        )); ?>
                        Multi-sheet label
                    </label>
                </div>
                <div class="radio">
                    <label>
                        <?=form_radio(array(
                            'name' => 'labeltype',
                            'id' => 'lt10',
                            'value' => '10',
                            'checked' => false
                        )); ?>
                        Type folder label
                    </label>
                </div>
                <div class="radio">
                    <label>
                        <?=form_radio(array(
                            'name' => 'labeltype',
                            'id' => 'lt11',
                            'value' => '11',
                            'checked' => false
                        )); ?>
                        Barcode label
                    </label>
                </div>
                    </div>
                </div> <!-- /.row -->
            </div>
            </fieldset>

                <?=form_fieldset('Annotation slips');?>
            <div class="radio">
                <label>
                    <?=form_radio(['name' => 'labeltype', 'id' => 'lt15', 'value' => '15']); ?>
                    Sticky labels, 30 per sheet
                </label>
            </div>
                <?=form_fieldset_close();?>

                <?=form_fieldset('Vic. Ref. Set');?>
            <div class="radio">
                <label>
                    <?=form_radio(['name' => 'labeltype', 'value' => '19']); ?>
                    Vic. Ref. Set label, 4 per page
                </label>
            </div>
            <div class="radio">
                <label>
                <?=form_radio(['name' => 'labeltype', 'value' => '21']); ?>
                    Vic. Ref. Set label, 2 per page
                </label>
            </div>
            <div class="radio">
                <label>
                <?=form_radio(['name' => 'labeltype','value' => '20']); ?>
                    Vic. Ref. Set barcode
                </label>
            </div>
            <div class="radio">
                <label>
                <?=form_radio(['name' => 'labeltype', 'value' => '24']); ?>
                    Vic. Ref. Set multi-sheet labels
                </label>
            </div>
                <?=form_fieldset_close();?>

            <br/>
                <input type="submit" name="submit" value="Submit" class="btn btn-primary btn-block"/>
            <?=form_close(); ?>
            <br/>
        </div> <!-- /.col- -->
    </div> <!-- /.row -->
</div> <!-- /.container -->

    
<?php require_once('footer.php'); ?>

