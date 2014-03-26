<?php require_once('header.php'); ?>

<h2>Genus and higher taxon storage</h2>
<table>
    <tr><th>TaxonID</th><th>Name</th><th>Created by</th><th>Add</th></tr>
    <?php foreach ($taxa as $item): ?>
        <tr>
            <td><?=$item['TaxonID']?></td>
            <td><?=$item['Name']?></td>
            <td><?=$item['CreatedBy']?></td>
            <td><a href="<?=site_url()?>/genusstorage/edit/<?=$item['TaxonID']?>">Add...</a></td>
        </tr>
    <?php endforeach; ?>
</table>

<?php require_once('footer.php'); ?>

