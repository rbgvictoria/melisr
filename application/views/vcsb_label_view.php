<?php require_once('header_1.php'); ?>

<div class="container">
    <div class="row">
        <div class="col-md-12">
            <?php require_once APPPATH . 'views/includes/vcsb_links.php'; ?>
            <h2>VCSB labels</h2>
            <?=form_open('vcsb/labels', array('target' => '_blank', 
                    'class' => 'form-horizontal')); ?>
                <?=form_fieldset('Records', ['id' => 'fieldset_records']); ?>
                    <div class="form-group">
                        <?=form_label('Record set:', 'recordset', 
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
                <?=form_label('<small>or</small> VCSB number:', 'vcsbno_start', 
                        ['class' => 'col-md-3 control-label', 'for' => 'vcsbno_start']); ?>
                <div class="col-md-9">
                    <div class="form-inline">
                        <?=form_input(array(
                            'name'        => 'vcsbno_start',
                            'id'          => 'vcsbno_start',
                            'value'       => '',
                            'maxlength'   => '7',
                            'size'        => '8',
                            'placeholder' => 'start',
                            'class'       => 'form-control'
                        )); ?>
                        &nbsp;
                        <?=form_input(array(
                            'name'        => 'vcsbno_count',
                            'id'          => 'vcsbno_count',
                            'value'       => '',
                            'maxlength'   => '3',
                            'size'        => '5',
                            'placeholder' => 'count',
                            'class'       => 'form-control'
                        )); ?>
                        <small>or</small>
                        <?=form_input(array(
                            'name'        => 'vcsbno_end',
                            'id'          => 'vcsbno_end',
                            'value'       => '',
                            'maxlength'   => '7',
                            'size'        => '8',
                            'placeholder' => 'end',
                            'class'       => 'form-control'
                        )); ?>
                    </div>
                </div>
            </div>

                <?=form_fieldset_close(); ?>
            
            <div class="form-group">
                <?=form_label('Start printing at label:', 'start', 
                        ['class' => 'col-md-3 control-label', 'for' => 'start']); ?>
                <div class="col-md-1">
                    <?=form_input([
                            'name' => 'start', 
                            'id' => 'start', 
                            'type' => 'number',
                            'value' => '1', 
                            'class' => 'form-control',
                            'min' => 1,
                            'max' => 4,
                        ]); ?>
                </div>
            </div>
            
            <fieldset>
                <legend>Label type

                </legend>
                <div class="row">
                    <div class="col-lg-6">
                        <div class="radio">
                            <label>
                                <?=form_radio(array(
                                    'name' => 'labelType',
                                    'id' => 'seed-sample',
                                    'value' => 'seed-sample',
                                    'checked' => true
                                )); ?>
                                Seed sample label
                            </label>
                        </div>
                        <div class="radio">
                            <label>
                                <?=form_radio(array(
                                    'name' => 'labelType',
                                    'id' => 'seed-dup',
                                    'value' => 'seed-dup',
                                    'checked' => false
                                )); ?>
                                Seed duplicate label
                            </label>
                        </div>
                        <div class="radio">
                            <label>
                                <?=form_radio(array(
                                    'name' => 'labelType',
                                    'id' => 'seedling',
                                    'value' => 'seedling',
                                    'checked' => false
                                )); ?>
                                Seedling label
                            </label>
                        </div>
                    </div>
                </div>
            </fieldset>
            <br/>
            <input type="submit" name="submit" value="Print labels" class="btn btn-primary btn-block"/>
            <?=form_close(); ?>
            
        </div> <!-- /.col-md-12 -->
    </div> <!-- /.row -->
</div> <!-- /.container -->

<?php require_once('footer.php'); ?>