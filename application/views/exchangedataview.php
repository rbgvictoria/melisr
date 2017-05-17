<?php require_once('header_1.php'); ?>

<div class="container">
    <div class="row">
        <div class="col-md-12">

            <h2>Exchange metadata</h2>
            <form action="<?=site_url()?>exchangedata/getdata" method="post" 
                  target="_blank" class="form-horizontal">
                
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
                                <option value="<?=$gift['giftid'] ?>"<?=$grey ?>><?=$gift['giftnumber'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="control-label col-md-2" for="recordset">
                        Record set: 
                    </label>
                    <div class="col-md-4">
                        <select name="recordset" id="recordset" class="form-control">
                            <option value="">(select record set)</option>
                            <?php foreach($recordsets as $set): ?>
                                <option value="<?=$set['RecordSetID'] ?>"><?=$set['Name'] ?> [<?=$set['SpecifyUser'] ?>]</option>
                            <?php endforeach; ?>
                        </select>
                    </div>                        
                </div>
            
                <p><b>Output format:</b></p>
            
                <div class="radio">
                    <label>
                        <input type="radio" name="format" value="hispid" checked="true"/>
                        AVH data
                    </label>
                </div>
                <div class="radio">
                    <label>
                        <input type="radio" name="format" value="csv" checked="true"/>
                        CSV
                    </label>
                </div>
                <div class="radio">
                    <label>
                        <input type="radio" name="format" value="biocase" checked="true"/>
                        BioCASe
                    </label>
                </div>
                <br/>
                <p>
                    <button type="submit" name="submit" class="btn btn-primary">
                        Submit
                    </button>
                </p>
            </form>
            
            <form action="<?=site_url()?>exchangedata/updateBiocase" method="post" class="form-horizontal">
                <input type="hidden" name="lastupdated" value="<?=$biocaseLastUpdated?>" />
                <p>If any of the records in the exchange or record set have been created or modified today, you may have to update
                    the BioCASe tables in order to get the latest changes. The biocase tables are up-to-date to: <?=$biocaseLastUpdated?>.</p>
                <p>
                    <button class="btn btn-primary" type="submit" name="submit">
                        Update BioCASe
                    </button>
                </p>

            </form>
            
        </div> <!-- /.col-md-12 -->
    </div> <!-- /.row -->
</div> <!-- /.container -->

<?php require_once 'footer.php'; ?>


