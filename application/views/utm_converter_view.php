<?php require_once 'header.php'; ?>
<div class="container">
    <h1>UTM to lat./long. converter</h1>

    <div class="row">
        <div class="col-md-12">
        <?=form_open_multipart()?>
        <h3>Single point</h3>
        
            <div class="form-horizontal">
                <div class="form-group">
                    <div class="col-md-3">
                        <div class="input-group">
                            <span class="input-group-addon"><b>Grid</b></span>
                            <?php 
                                $options = array(
                                    'AMG66' => 'AGD66 / AMG',
                                    'AMG84' => 'AGD84 / AMG',
                                    'MGA' => 'GDA94 / MGA'
                                );
                                $default = ($this->input->post('grid')) ? $this->input->post('grid') : 'MGA';
                                echo form_dropdown('grid', $options, $default, ' class="form-control"');
                            ?>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="input-group">
                            <span class="input-group-addon"><b>Zone</b></span>
                            <?php
                                $options = array(
                                    '48' => '48',
                                    '49' => '49',
                                    '50' => '50',
                                    '51' => '51',
                                    '52' => '52',
                                    '53' => '53',
                                    '54' => '54',
                                    '55' => '55',
                                    '56' => '56',
                                    '57' => '57',
                                    '58' => '58',
                                );
                                $default = ($this->input->post('zone')) ? $this->input->post('zone') : '55';
                                echo form_dropdown('zone', $options, $default, ' class="form-control"');
                            ?>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="input-group">
                            <span class="input-group-addon"><b>Easting</b></span>
                            <?=form_input(array(
                                'name' => 'easting', 
                                'id' => 'easting', 
                                'min' => '100000', 
                                'max' => '999999',
                                'placeholder' => '000000[.0...]',
                                'value' => ($this->input->post('easting')) ? $this->input->post('easting') : FALSE,
                                'class' => 'form-control'
                                ));
                            ?>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="input-group">
                            <span class="input-group-addon"><b>Northing</b></span>
                            <?=form_input(array(
                                'name' => 'northing', 
                                'id' => 'northing', 
                                'min' => '1000000', 
                                'max' => '9999999',
                                'placeholder' => '0000000[.0...]', 
                                'value' => ($this->input->post('northing')) ? $this->input->post('northing') : FALSE,
                                'class' => 'form-control'
                                ));
                            ?>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-md-3">
                        <div class="input-group">
                            <span class="input-group-addon"><b>Output datum</b></span>
                                <?php
                                    $options = array(
                                        'GDA94' => 'GDA94',
                                        'WGS84' => 'WGS84'
                                    );
                                    $default = ($this->input->post('outputdatum')) ? $this->input->post('outputdatum') : 'WGS84';
                                    echo form_dropdown('outputdatum', $options, $default, ' class="form-control"');
                                ?>
                        </div>
                    </div>
                </div>
            </div>
        
        
        
        <?=form_submit('submit', 'Convert', ' class="btn btn-primary"')?>
        
        
        <?php if(isset($point)): ?>
        <p>
            <?=form_label('GridSRS: ', 'gridsrs')?>
            <?=form_input(array('name' => 'gridsrs', 'value' => 'EPSG:' . $point['gridsrs'], 'disabled' => 'disabled'))?>
            <?=form_label('LatLongSRS: ', 'srid')?>
            <?=form_input(array('name' => 'srid', 'value' => 'EPSG:' . $point['srs'], 'disabled' => 'disabled'))?>
            <?=form_label('Latitude: ', 'latitude')?>
            <?=form_input(array('name' => 'latitude', 'value' => $point['lat'], 'disabled' => 'disabled'))?>
            <?=form_label('Longitude: ', 'longitude')?>
            <?=form_input(array('name' => 'longitude', 'value' => $point['lng'], 'disabled' => 'disabled'))?>
        </p>
        <?php else: ?>
        <p>&nbsp;</p>
        <?php endif; ?>
        
        <p>&nbsp;</p>
        <h3>CSV file with co-ordinates</h3>
        <p>The input file must contain <b>Easting</b> and <b>Northing</b> – and optionally <b>Grid</b> and <b>Zone</b> – columns, 
            with these headings and valid values in every cell. Valid values for grid are <b>AMG</b>, <b>AMG66</b>, <b>AMG84</b> 
            or <b>MGA</b>, with AMG assumed to be AMG66. Zone can be between 
            <b>48</b> and <b>58</b>. 
            
            If there are no Grid and/or Zone columns, default values <b>MGA</b> and <b>55</b> respectively will be assumed. 
            There are no default values for empty cells, so, if the column is there, it needs to have a valid value in 
            every cell.
            
            Everything is case-sensitive. 
            
            The output file will contain all columns of the input file with <b>GridSRS</b>, <b>LatLongSRS</b>, <b>Latitude</b>
            and <b>Longitude</b> columns added. If there is no Grid and/or Zone column in the input file, these columns will be
            added as well.
        </p>
        <p>
            <?=form_upload(array('name' => 'upload', 'id' => 'upload'))?>
            <?php
                $options = array(
                    'GDA94' => 'GDA94',
                    'WGS84' => 'WGS84'
                );

                $default = ($this->input->post('outputdatum_2')) ? $this->input->post('outputdatum_2') : 'WGS84';

                echo form_label('Output datum: ', 'outputdatum_2');
                echo form_dropdown('outputdatum_2', $options, $default);
            ?>
            <?=form_submit('submit_2', 'Convert')?>
            
        </p>
        
        <?=form_close()?>
    </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>
