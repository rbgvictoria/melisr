<?php require_once('header.php'); ?>

<h2>Victorian Reference Set</h2>
<?php if (isset($message)): ?>
<p style="font-weight:bold;color:red;"><?=$message?></p>
<?php endif; ?>

<?php if($records): ?>
<?=form_open('vrs'); ?>
<table>
    <tr>
        <th>Catalogue number</th>
        <th>VRS number</th>
        <th>Modified by</th>
        <th>Modified</th>
        <th>Flowers</th>
        <th>Fruit</th>
        <th>Buds</th>
        <th>Leafless</th>
        <th>Fertile</th>
        <th>Sterile</th>
        <th>Curation notes</th>
    </tr>
    <?php foreach ($records as $index=>$row): ?>
    <?php if ($index%2==0): ?>
    <tr class="odd">
    <?php else: ?>
    <tr class="even"?>
    <?php endif; ?>
        <td><?=$row['CatalogNumber']?><?=form_hidden("collectionobjectid[$index]", $row['CollectionObjectID']);?><?=form_hidden("catalognumber[$index]", $row['CatalogNumber']);?></td>
        <td><?=$row['SampleNumber']?><?=form_hidden("vrsnumber[$index]", $row['SampleNumber']);?></td>
        <td><?=$row['MiddleInitial']?><?=form_hidden("agentid[$index]", $row['ModifiedByAgentID']);?></td>
        <td><?=$row['TimestampModified']?></td>
        <td><?=form_checkbox("flowers[$index]", 1, ($row['Flowers']) ? TRUE : FALSE);?></td>
        <td><?=form_checkbox("fruit[$index]", 1, ($row['Fruit']) ? TRUE : FALSE);?></td>
        <td><?=form_checkbox("buds[$index]", 1, ($row['Buds']) ? TRUE : FALSE);?></td>
        <td><?=form_checkbox("leafless[$index]", 1, ($row['Leafless']) ? TRUE : FALSE);?></td>
        <td><?=form_checkbox("fertile[$index]", 1, ($row['Fertile']) ? TRUE : FALSE);?></td>
        <td><?=form_checkbox("sterile[$index]", 1, ($row['Sterile']) ? TRUE : FALSE);?></td>
        <td><?=form_textarea(array('name'=>"curationnotes[$index]",'value'=> '','rows'=>'1','cols'=>'18'));?></td>
    </tr>
    <?php endforeach; ?>
</table>
<div><?=form_submit('updatevrs', 'Update VRS collection');?></div>

<?=form_close(); ?>
<?php endif; ?>
<?php require_once('footer.php'); ?>

