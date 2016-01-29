<?php require_once('header.php');?>
<h2>DNA sequences</h2>
<?php if (isset($intro)): ?>
<?=$intro?>
<?php endif; ?>

<?=form_open_multipart(site_url() . '/dnasequence'); ?>
<div>
    <p>
        <?=form_label('Specify user', 'specify_user', array('class' => 'required')); ?>
        <?=form_dropdown('specify_user', $specify_user, $this->input->post('specify_user'), 'id="specify_user"'); ?>
    </p>
    <?php if (!isset($header_row)): ?>
    <p>
    <?php
        echo form_label('File', 'upload', array('class' => 'required'));
    
        $inputdata = array(
              'name'        => 'upload',
              'id'          => 'upload',
        );
        echo form_upload($inputdata);
    ?>
        
    </p>
    
    <p>
        <?=form_submit('submit', 'Continue'); ?>
    </p>
    <?php endif; ?>
    <?php if(isset($messages) && $messages): ?>
    <div>
        <ul>
            <?php foreach ($messages as $message): ?>
            <li><span style="color:red"><?=$message?></span></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endif;?>
</div>

<?php if (isset($header_row)): ?>
<?=form_hidden('temp_file', $tempfile); ?>
<table>
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
    <?php endif; ?>
    <?=form_submit('cancel', 'Cancel'); ?>
</p>
<?php endif; ?>
<?=form_close(); ?>

<?php if (isset($report) && $report): ?>
<?php
    $errors = array();
    $warnings = array();
    $info = array();
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
<table>
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
<table>
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
<table>
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


<?php require_once('footer.php');?>