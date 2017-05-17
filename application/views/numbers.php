<?php require_once('header.php'); ?>

<div class="container">
    <div class=""row>
        <div class="col-md-12">
            <h2>MEL numbers</h2>
            <?=form_open('numbers/melnumber', ['class' => 'form-horizontal']); ?>
            <div class="form-group">
                <div class="col-md-3">
                    <input type="submit" name="submit_melnumber" value="MEL numbers" class="btn btn-primary"/>
                </div>
                <div class="col-md-3">
                    <?php
                        if(!isset($howmany)) $howmany = 100;
                        echo form_input(['name' => 'howmany', 'value' => $howmany, 'class' => 'form-control']);
                    ?>
                </div>
            </div>
            <?=form_close(); ?>

            <?php if (isset($startnumber)): ?>
            <form action="<?=site_url()?>numbers/melnumber_insert" method="post" class="form-inline">
                <label>MEL</label>
                <?=form_input(['name' => 'startnumber', 'value' => $startnumber, 'readonly' => true, 'class' => 'form-control'])?>
                -
                <?php if (isset($endnumber)): ?>
                <?=form_input(['name' => 'endnumber', 'value' => $endnumber, 'readonly' => true, 'class' => 'form-control'])?>
                <?php endif; ?>
                <?php if (!isset($print)): ?>
                <label>Name
                    <?=form_input(['name' => 'username', 'value' => isset($username) ? $username : '', 'class' => 'form-control'])?>
                    <?=form_input(['type' => 'submit', 'name' => 'accept', 'value' => 'Accept', 'class' => 'btn btn-primary'])?>
                </label>
                <?php else: ?>
                <span>
                    <a href="<?=site_url()?>numbers/printcsv/start/<?=$startnumber?>/end/<?=$endnumber?>">print list</a>
                </span>
                <?php endif; ?>
            </form>
            <?php endif; ?>
            <p><?=anchor('numbers/melnumbers/', 'Overview of assigned MEL numbers', 'title="Overview of assigned MEL numbers"');?></p>

            
            <h2>Loan and exchange numbers</h2>
            <form action="<?=site_url()?>numbers/loan" method="post" class="form-horizontal">
                <div class="form-group">
                    <div class="col-md-3">
                        <input type="submit" name="submit_loan" value="Loans" class="btn btn-primary"/>
                    </div>
                    <?php if (isset($loannumber)): ?>
                    <div class="col-md-3">
                        <?=form_input(['name' => 'loannumber', 'value' => $loannumber, 'readonly' => true, 'class' => 'form-control'])?>
                    </div>
                    <?php endif; ?>
                </div>
            </form>

            <form action="<?=site_url()?>numbers/exchange" method="post" class="form-horizontal">
                <div class="form-group">
                    <div class="col-md-3">
                        <input type="submit" name="submit_exchange" value="Exchange" class="btn btn-primary"/>
                    </div>
                    <?php if (isset($exchangenumber)): ?>
                    <div class="col-md-3">
                        <?=form_input(['name' => 'exchangenumber', 'value' => $exchangenumber, 'readonly' => true, 'class' => 'form-control'])?>
                    </div>
                    <?php endif; ?>
                </div>
            </form>

        </div> <!-- /.col- -->
    </div> <!-- /.row -->
</div> <!-- /.container -->

<?php require_once('footer.php'); ?>

