<?php require_once('header.php');?>

<div class="container">
    <div class="row">
        <div class="col-md-12">

            <h2>DNA sequences</h2>
            <?php if (isset($intro)): ?>
            <?=$intro?>
            <p><?=anchor(site_url() . 'dnasequence/example', 'Example CSV file')?></p>
            <?php endif; ?>

            <form action="<?=site_url()?>dnasequence" method="post"
                  enctype="multipart/form-data" class="form-horizontal">
                <div class="form-group">
                    <label class="control-label col-md-2" for="specify_user">
                        Specify user:
                    </label>
                    <div class="col-md-10">
                        <?=form_dropdown('specify_user', $specify_user, 
                                $this->input->post('specify_user'), 
                                'id="specify_user" class="form-control"'); ?>
                    </div>
                </div> <!-- /.form-group -->
                
                <?php if (!isset($header_row)): ?>
                <div class="form-group">
                    <label class="control-label col-md-2" 
                          for="upload">Load file:</label>
                    <div class="col-md-10">
                        <div class="input-group">
                            <span class="btn btn-primary btn-file input-group-addon">
                                Browse...
                                <input type="file" name="upload"
                                    id="image_metadata_upload"/>
                            </span>
                            <input type="text" class="form-control" />
                        </div>
                    </div>
                </div> <!-- /.form-group -->
                
                <div>
                    <button type="submit" name="submit" value="1" 
                            class="btn btn-primary btn-block">
                        Continue
                    </button>
                </div>
                <?php endif; ?>
                
                

            <?php if (isset($header_row)): ?>
            <?=form_hidden('temp_file', $tempfile); ?>
            <table class="table table-bordered table-condensed table-responsive">
                <tr>
                    <th colspan="2">&nbsp</th>
                    <th>Column</th>
                    <th>Default value</th>
                </tr>
                <tr>
                    <td colspan="2">Catalogue number</td>
                    <td><?=$header_row['catalog_number_column']?></td>
                    <td>&nbsp;</td>
                </tr>
                <tr>
                    <td colspan="2">Sample number</td>
                    <td><?=$header_row['sample_number_column']?></td>
                    <td>&nbsp;</td>
                </tr>
                <tr>
                    <td colspan="2">Prepared by</td>
                    <td><?=$header_row['prepared_by_column']?></td>
                    <td><?=form_input('prepared_by_default')?></td>
                </tr>
                <tr>
                    <td colspan="2">Prepared date</td>
                    <td><?=$header_row['prepared_date_column']?></td>
                    <td><?=form_input('prepared_date_default')?></td>
                </tr>
                <tr>
                    <td colspan="2">Sequencer</td>
                    <td><?=$header_row['sequencer_column']?></td>
                    <td><?=form_input('sequencer_default')?></td>
                </tr>
                <tr>
                    <td colspan="2">Project</td>
                    <td><?=$header_row['project_column']?></td>
                    <td><?=form_input('project_default')?></td>
                </tr>
                <tr>
                    <td colspan="2">BOLD barcode ID</td>
                    <td><?=$header_row['bold_barcode_id_column']?></td>
                    <td>&nbsp;</td>
                </tr>
                <tr>
                    <td colspan="2">BOLD sample ID</td>
                    <td><?=$header_row['bold_sample_id_column']?></td>
                    <td>&nbsp;</td>
                </tr>
                <?php $first = TRUE; ?>
                <?php foreach($header_row['marker_columns'] as $key => $value): ?>
                <?php 
                    $class = FALSE;
                    if (!in_array($key, $markers)) {
                        $class = ' class="marker-not-in-picklist"';
                    }
                ?>
                <tr>
                    <?php if ($first): ?>
                    <td rowspan="<?=count($header_row['marker_columns']);?>">Markers</td>
                    <?php $first = FALSE?>
                    <?php endif; ?>
                    <td><?=$key?></td>
                    <td><?=$value?></td>
                    <td<?=$class?>><?=($class) ? 'Marker not in pick list' : '&nbsp;'?></td>
                </tr>
                <?php endforeach; ?>

            </table>
            <p>
                <?php if ($header_row['catalog_number_column'] !== FALSE): ?>
                <?=form_submit('submit_2', 'Continue'); ?>
                <button type="submit" name="submit_2" value="1"
                        class="btn btn-primary">Continue</button>
                <?php endif; ?>
                <button type="submit" name="cancel" value="1" 
                        class="btn btn-primary">Cancel</button>
            </p>
            <?php endif; ?>
            </form>

            <?php if (isset($report) && $report): ?>
            <?php
                $errors = [];
                $warnings = [];
                $info = [];
                foreach ($report as $colobj) {
                    foreach ($colobj as $item) {
                        switch ($item['type']) {
                            case 'error':
                                $errors[] = $item;
                                break;
                            case 'warning':
                                $warnings[] = $item;
                                break;
                            case 'info':
                                $info[] = $item;
                                break;
                            default:
                                break;
                        }
                    }
                }
            ?>

            <?php if ($errors): ?>
            <h3>Errors</h3>
            <table class="table table-bordered table-condensed table-responsive">
                <thead>
                    <th>Catalogue number</th>
                    <th colspan="2">Error</th>
                </thead>
                <tbody>
                    <?php foreach ($errors as $row): ?>
                    <tr class="<?=$row['type']?>">
                        <td><b><?=$row['catalogNumber']?></b></td>
                        <td><?=$row['type']?></td>
                        <td><?=$row['note']?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>

            <?php if ($warnings): ?>
            <h3>Warnings</h3>
            <table class="table table-bordered table-condensed table-responsive">
                <thead>
                    <th>Catalogue number</th>
                    <th colspan="2">Error</th>
                </thead>
                <tbody>
                    <?php foreach ($warnings as $row): ?>
                    <tr class="<?=$row['type']?>">
                        <td><b><?=$row['catalogNumber']?></b></td>
                        <td><?=$row['type']?></td>
                        <td><?=$row['note']?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>

            <?php if ($info): ?>
            <h3>Info.</h3>
            <table class="table table-bordered table-condensed table-responsive">
                <thead>
                    <th>Catalogue number</th>
                    <th colspan="2">Error</th>
                </thead>
                <tbody>
                    <?php foreach ($info as $row): ?>
                    <tr class="<?=$row['type']?>">
                        <td><b><?=$row['catalogNumber']?></b></td>
                        <td><?=$row['type']?></td>
                        <td><?=$row['note']?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>

            <?php endif; ?>

        </div> <!-- /.col-md-12 -->
    </div> <!-- /.row -->
</div> <!-- /.container -->

<?php require_once('footer.php');?>