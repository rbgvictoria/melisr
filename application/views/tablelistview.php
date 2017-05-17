<?php require_once('header.php'); ?>

<h2>List of tables</h2>
<?php if(isset($tables)):?>
<table>
    <tr>
        <th style="text-align: right;">Id</th>
        <th>Name</th>
        <th>Abbreviation</th>
        <th>Workbench</th>
        <th>Searchable</th>
        <th>System table</th>
    </tr>
    <?php foreach ($tables as $table): ?>
    <tr>
        <td style="text-align: right;"><?=$table['id']?></td>
        <td><?=$table['name']?></td>
        <td><?=$table['abbreviation']?></td>
        <td><?=$table['workbench']?></td>
        <td><?=$table['searchable']?></td>
        <td><?=$table['system']?></td>
    </tr>
    <?php endforeach; ?>
</table>
<?php endif;?>


<?php require_once('footer.php'); ?>

