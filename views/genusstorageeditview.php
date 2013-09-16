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
<?=form_close()?>

<?php require_once('footer.php'); ?>

