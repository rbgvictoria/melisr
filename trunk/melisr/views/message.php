<?php require_once('header.php'); ?>

    <h2><?=$message; ?></h2>
    <?php if(isset($message_table)): ?>
        <?=$message_table?>
    <?php endif; ?>
		
<?php require_once('footer.php'); ?>