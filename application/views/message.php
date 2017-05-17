<?php require_once('header.php'); ?>

<div class="container">
    <div class="row">
        <div class="col-md-12">
            <h2><?=$message; ?></h2>
            <?php if(isset($message_table)): ?>
                <?=$message_table?>
            <?php endif; ?>
        </div>
    </div>
</div>
		
<?php require_once('footer.php'); ?>