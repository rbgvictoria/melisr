 <?php require_once('header.php'); ?>

<h2>VRS records no longer in MEL collection</h2>

<?php if ($records): ?>
<?=form_open('vrs/not_in_mel'); ?>
<table>
    <tr>
        <th>VRS number</th>
        <th>MEL number</th>
        <th>Perp</th>
        <th>Created</th>
        <th>Delete?</th>
    </tr>
    <?php foreach ($records as $row): ?>
    <tr>
        <td><?=$row['VRSNumber']?></td>
        <td><?=$row['MELNumber']?></td>
        <td><?=$row['Perp']?></td>
        <td><?=$row['TimestampCreated']?></td>
        <td><?=form_checkbox(array('name' => 'colobj[]', 'value' => $row['CollectionObjectID']))?></td>
    </tr>
    <?php endforeach; ?>
</table>
<p>
    <?=form_submit('delete', 'Delete'); ?>
</p>

<?=form_close(); ?>
    
    
<?php else: ?>
<p style="font-weight:bold;color:green;">There are no records without MEL vouchers in the VRS collection.</p>
<?php endif; ?>

<?php require_once('footer.php'); ?>
