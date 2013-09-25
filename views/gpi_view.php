<?php require_once('header.php'); ?>

<h2>GPI scanning project metadata</h2>
<p>Upload a file with MEL numbers (barcodes) to extricate metadata for the GPI type scanning project from melisr. The uploaded
file has to be CSV and the MEL numbers should be in the first column. The script expects a header row or empty row at the top, so
the MEL numbers should start from the second row. Starting the MEL numbers in the first row will not break the script, but metadata
for the record with that MEL number will not be retrieved. Anything other than MEL numbers in the first column, apart from
the first row, will break the script.</p>
<?=form_open_multipart(site_url() . '/gpi/upload'); ?>
    <?php
        echo form_label('Load MEL numbers', 'upload',
                array('style' => 'position: relative; top: -4px; width: 150px'));
    
        $inputdata = array(
              'name'        => 'upload',
              'id'          => 'upload',
        );
        echo form_upload($inputdata);
        echo '&nbsp;&nbsp;';
        echo form_submit('submit', 'Load');
    ?>

<?=form_close(); ?>

<?php if(isset($DataSets) && $DataSets): ?>
<h3>Uploaded data sets:</h3>
    <table>
        <tr><th>Batch no.</th><th>Date uploaded</th>
            <th>Records</th><th>Errors</th><th colspan="2">Output</th>
            <th>Delete batch</th></tr>
    <?php foreach ($DataSets as $set): ?>
        <tr>
            <td><?=$set['BatchNo']?></td><td><?=$set['DateUploaded']?></td>
            <td><?=$set['NumRecords']?></td><td><a href="<?=site_url()?>/gpi/show_errors/batch/<?=$set['BatchNo']?>"
                title="Show errors"><?=$set['NumErrors']?></a></td>
            <td><a href="<?=site_url()?>/gpi/get_xml/<?=$set['BatchNo']?>/gpi"
                title="get XML (GPI Schema)">XML (GPI)</a></td>
            <td><a href="<?=site_url()?>/gpi/get_xml/<?=$set['BatchNo']?>/biocase"
                title="get XML (with BioCASe wrapper)">XML (BioCASe)</a></td>
            <td><a href="<?=site_url()?>/gpi/get_xml/<?=$set['BatchNo']?>/csv"
                title="get CSV">CSV</a></td>
                <td><a href="<?=site_url()?>/gpi/delete_batch/<?=$set['BatchNo']?>">delete</a></td>
        </tr>
    <?php endforeach; ?>
    </table>
<?php endif; ?>

<?php require_once('footer.php'); ?>
