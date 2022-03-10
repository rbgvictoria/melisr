<?php require_once('header_1.php'); ?>

<div class="container">
    <div class="row">
        <div class="col-md-12">
            <h2>Exchange metadata</h2>
            <form action="<?=site_url()?>exchangedata" method="post" class="form-horizontal">
                
                <div class="form-group">
                    <label class="control-label col-md-2" for="giftnumber">
                        Exchange: 
                    </label>
                    <div class="col-md-4">
                        <select name="giftnumber" id="giftnumber" class="form-control">
                            <option value="">(select exchange number)</option>
                            <?php foreach($gifts as $gift): ?>
                                <?php
                                    $grey = null;
                                    if ($gift['haspreps'] == 0) $grey = ' style="color: #999999"';
                                ?>
                                <option value="<?=$gift['giftno']?>"
                                        <?=$gift['giftno'] == $giftnumber ? 'selected' : '' ?>
                                        <?=$grey ?>><?=$gift['giftnumber'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <p>
                        <button type="submit" name="submit_exchange" value="1" class="btn btn-primary">
                            Get exchange data
                        </button>
                    </p>
                </div>
                
                <div class="form-group">
                    <label class="control-label col-md-2" for="recordset">
                        Record set: 
                    </label>
                    <div class="col-md-4">
                        <select name="recordset" id="recordset" class="form-control">
                            <option value="<?=$recordset?>">(select record set)</option>
                            <?php foreach($recordsets as $set): ?>
                                <option value="<?=$set['RecordSetID']?>"
                                   <?=$set['RecordSetID'] == $recordset ? 'selected' : '' ?>
                                ><?=$set['Name'] ?> [<?=$set['SpecifyUser'] ?>]</option>
                            <?php endforeach; ?>
                        </select>
                    </div>                        
                    <p>
                        <button type="submit" name="submit_recordset" value="1" class="btn btn-primary">
                            Get data for record set
                        </button>
                    </p>
                </div>
            
                <p><b>Output format:</b></p>
                <p><b>Note:</b> We now have only one output format: Darwin Core Archive (DwCA). A 
                Darwin Core Archive is a Zip archive containing one or more CSV files with data 
                and a manifest (meta.xml) that tells what is in the files and what the columns are.</p>
                
                <p>ABCD or HISPID 5 is not used anymore at MEL, or at other herbaria.</p>
                
            
            </form>
            
        </div> <!-- /.col-md-12 -->
    </div> <!-- /.row -->
</div> <!-- /.container -->

<?php require_once 'footer.php'; ?>


