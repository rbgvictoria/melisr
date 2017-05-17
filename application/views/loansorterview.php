<?php require_once('header_1.php'); ?>

<div class="container">
    <div class="row">
        <div class="col-md-12">
            <h2>Messy loan sorter</h2>
            <p>You can enter barcodes of both MEL and non MEL specimens in the text area below.</p>
            <?=form_open('loansorter/sort'); ?>
            <form action="<?=site_url()?>" method="post" class="form">
                
                <label class="control-label" for="melnumbers">Barcodes</label>
                <textarea class="form-control" name="melnumbers" 
                    id="melnumbers" rows="10"
                    ><?=(isset($melnumbers)) ? $melnumbers : ''?></textarea>

                <div class="text-right">
                    <button type="submit" name="submit" class="btn btn-primary">
                        Sort
                    </button>
                    <button type="reset" name="reset" class="btn btn-primary">
                        Reset
                    </button>
                </div>
            </form>


            <?php if (isset($loans) && $loans): ?>
            <h2>MEL loans</h2>
            <?php foreach ($loans as $key => $loan): ?>
            <form action="<?=site_url()?>loanreturn/prepare" class="form" method="post"
                  target="_blank">
                <h3><?=$loan['LoanNumber']?></h3>
                <input type="hidden" name="loannumber" value="<?=$loan['LoanID']?>" />
                <textarea class="form-control" name="melnumbers" rows="10"
                          ><?=implode("\n", $loan['MelNumber'])?></textarea>
                <div class="text-right">
                    <button class="btn btn-primary" type="submit" name="loan_return">
                        Send to loan return
                    </button>
                    <button class="btn btn-primary" type="submit" name="record_set">
                        Send to record set creator
                    </button>
                </div>
            </form>
            <div>&nbsp;</div>
            <?php endforeach; ?>
            <?php endif; ?>
            
            
            <?php if (isset($nonmelloans) && $nonmelloans): ?>
            <h2>Non-MEL loans</h2>
            <?php foreach ($nonmelloans as $key => $loan): ?>
            <form action="<?=site_url()?>borrower" class="form"
                  target="_blank">
                <input type="hidden" name="melrefno" value="<?=$loan['LoanID']?>"/>
                <h3><?=$loan['LoanNumber']?></h3>
                <textarea class="form-control" name="stickybarcodes" rows="10"
                          ><?=implode("\n", $loan['MelNumber'])?></textarea>
                <div class="text-right">
                    <button class="btn btn-primary" type="submit" name="loan_return">
                        Send to non-MEL loan return
                    </button>
                </div>
            </form>
            <div>&nbsp;</div>
            <?php endforeach; ?>
            <?php endif; ?>
            
        </div> <!-- /.col-md-12 -->
    </div> <!-- /.row -->
</div> <!-- /.container -->

<?php require_once('footer.php'); ?>

