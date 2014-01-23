<?php require_once('header.php'); ?>

<h4>Edit genus and higher taxon storage</h4>
<h2><?=$name?></h2>
<?php if ($classification): ?>
    <?php foreach ($classification as $item): ?>
        <div><?=$item['Rank']?>: <b><?=$item['Name']?></b></div>
    <?php endforeach; ?>
<?php endif; ?>
        <br/><br/>
<?=form_open('genusstorage/insert/')?>
    <?=form_hidden('taxonid', $taxonid)?>
    <?=form_hidden('name', $name)?>
    <?=form_label('Stored under', 'storedunder', array('style' => 'width: 100px'))?>
    <select name="storedunder" id="storedunder">
        <?=$options?>
    </select>
    <?=form_submit('insert', 'Insert')?>
        
<?php if($colobjects): ?>
        <h3>Specimen records</h3>
        <p>Storage of the following records will be updated when the storage family is set.</p>
        <table>
            <tr>
                <th>Catalogue number</th>
                <th>Taxon name</th>
                <th>Storage type</th>
                <th>Modification date</th>
                <th>Modified by</th>
            </tr>
            <?php foreach ($colobjects as $colobj): ?>
            <tr>
                <td><?=$colobj['CatalogNumber']?><?=form_hidden('colobj[]', $colobj['CollectionObjectID'])?></td>
                <td><?=$colobj['FullName']?></td>
                <td><?=$colobj['StorageType']?><?=form_hidden('storagetype[]', $colobj['StorageType'])?></td>
                <td><?=$colobj['DateModified']?></td>
                <td><?=$colobj['ModifiedBy']?></td>
            </tr>
            <?php endforeach; ?>
        </table>
<?php endif; ?>
<?=form_close()?>
        

<?php require_once('footer.php'); ?>

