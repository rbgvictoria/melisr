<?php require_once('header_1.php'); ?>
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <h2>Herbarium transaction paperwork</h2>
            
            <form action="<?=site_url()?>transactions/loanpaperwork"
                  method="post" class="form-horizontal">
            <?=form_fieldset('<b>Loans</b>')?>
            
                <div class="form-group">
                    <div class="col-md-3">
                        <select name="loannumber" class="form-control">
                            <option value="">(select loan number)</option>
                            <?php foreach($loans as $loan): ?>
                                <?php
                                    $grey = null;
                                    if ($loan['haspreps'] == 0) $grey = ' style="color: #999999"';
                                ?>
                                <option value="<?=$loan['loanid'] ?>"<?=$grey ?>><?=$loan['loannumber'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-9">
                        <input type="submit" name="deleteduplicates"
                               value="Delete &apos;duplicate&apos; loan preparations"
                               class="btn btn-primary"/>
                    </div>
                </div>
                
                <div class="checkbox">
                    <label>
                        <input type="radio" name="output" value="1" checked="true"/>
                        Loan paperwork
                    </label>
                </div>
                <div class="checkbox">
                    <label>
                        <input type="radio" name="output" value="2"/>
                        List of preparations
                    </label>
                </div>
                <div class="checkbox">
                    <label>
                        <input type="radio" name="output" value="3"/>
                        Envelope
                    </label>
                </div>
                <div class="checkbox">
                    <label>
                        <input type="radio" name="output" value="16"/>
                        Envelope (mailroom printer)
                    </label>
                </div>
                <div class="checkbox">
                    <label>
                        <input type="radio" name="output" value="4"/>
                        Parcel label
                    </label>
                </div>
                <div class="checkbox">
                    <label>
                        <input type="radio" name="output" value="19"/>
                        Parcel label (mailroom printer)
                    </label>
                </div>
                <div class="checkbox">
                    <label>
                        <input type="radio" name="output" value="5"/>
                        Conditions of loan
                    </label>
                </div>
            <?=form_fieldset_close()?>
                
                
            <?=form_fieldset('<b>Exchange</b>')?>
                <div class="form-group">
                    <div class="col-md-3">
                        <select name="exchangeoutnumber" class="form-control">
                            <option value="">(select exchange number)</option>
                            <?php foreach($exchange_out as $exchange): ?>
                                <?php
                                    $grey = null;
                                    if ($exchange['haspreps'] == 0) $grey = ' style="color: #999999;"';
                                ?>
                                <option value="<?=$exchange['giftid'] ?>"<?=$grey ?>><?=$exchange['giftnumber'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-9">
                        <input type="submit" name="fixexchangenumbers"
                               value="Fix exchange numbers"
                               class="btn btn-primary"/>
                    </div>
                </div>
                
                <div class="checkbox">
                    <label>
                        <input type="radio" name="output" value="6"/>
                        Exchange paperwork
                    </label>
                </div>
                <div class="checkbox">
                    <label>
                        <input type="radio" name="output" value="7"/>
                        List of preparations
                    </label>
                </div>
                <div class="checkbox">
                    <label>
                        <input type="radio" name="output" value="8"/>
                        Envelope
                    </label>
                </div>
                <div class="checkbox">
                    <label>
                        <input type="radio" name="output" value="17"/>
                        Envelope (mailroom printer)
                    </label>
                </div>
                <div class="checkbox">
                    <label>
                        <input type="radio" name="output" value="9"/>
                        Parcel label 
                    </label>
                </div>
                <div class="checkbox">
                    <label>
                        <input type="radio" name="output" value="20"/>
                        Parcel label (mailroom printer)
                    </label>
                </div>
            <?=form_fieldset_close()?>
                
                
            <?=form_fieldset('<b>Non-MEL loans</b>')?>
                <div class="form-group">
                    <div class="col-md-3">
                        <?=form_dropdown('nonmelloan', $non_mel_loans, FALSE, 
                                'class="form-control"');?>
                    </div>
                </div>
                
                <div class="checkbox">
                    <label>
                        <input type="radio" name="output" value="12"/>
                        Non-MEL loan paperwork
                    </label>
                </div>
                <div class="checkbox">
                    <label>
                        <input type="radio" name="output" value="14"/>
                        Envelope
                    </label>
                </div>
                <div class="checkbox">
                    <label>
                        <input type="radio" name="output" value="22"/>
                        Envelope (mailroom printer)
                    </label>
                </div>
                <div class="checkbox">
                    <label>
                        <input type="radio" name="output" value="15"/>
                        Parcel label
                    </label>
                </div>
                <div class="checkbox">
                    <label>
                        <input type="radio" name="output" value="23"/>
                        Parcel label (mailroom printer)
                    </label>
                </div>
            <?=form_fieldset_close()?>
                
                
            <?=form_fieldset('<b>Address labels</b>')?>
                <div class="form-group">
                    <div class="col-md-3">
                        <select name="institution" class="form-control">
                            <option value="">(select institution or person name)</option>
                            <?php foreach($institutions as $inst): ?>
                                <option value="<?=$inst['agentid'] ?>"><?=$inst['agentname'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="checkbox">
                    <label>
                        <input type="radio" name="output" value="10"/>
                        Envelope
                    </label>
                </div>
                <div class="checkbox">
                    <label>
                        <input type="radio" name="output" value="18"/>
                        Envelope (mailroom printer)
                    </label>
                </div>
                <div class="checkbox">
                    <label>
                        <input type="radio" name="output" value="11"/>
                        Parcel label
                    </label>
                </div>
                <div class="checkbox">
                    <label>
                        <input type="radio" name="output" value="21"/>
                        Parcel label (mailroom printer)
                    </label>
                </div>

            <?=form_fieldset_close()?>
                <br/>
                <div>
                    <input type="submit" value="Submit" class="btn btn-primary" />
                </div>
                
            </form>
        </div> <!-- /.col-md-12 -->
    </div> <!-- /.row -->
</div> <!-- /.container -->
    <div style="clear: both;">&nbsp;</div>
<?php require_once('footer.php')?>


