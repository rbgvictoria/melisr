<?php require_once('header_1.php'); ?>

<div class="container">
    <div class="row">
        <div class="col-md-12">
            <h2>Dehispidator</h2>
            <h3>Convert HISPID3 to CSV</h3>
            
            <form action="<?=site_url()?>dehispidator/hispid3_convert" method="post"
                  enctype="multipart/form-data" class="form-horizontal">
                
                <label class="control-label">Load HISPID file</label>
                <div class="form-group">
                    <div class="col-md-9">
                        <div class="input-group">
                            <span class="btn btn-primary btn-file input-group-addon">
                                Browse...
                                <input type="file" name="upload"
                                    id="upload"/>
                            </span>
                            <input type="text" class="form-control" 
                                   placeholder="Please make sure name of uploaded file has an extension"/>
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <div class="col-md-2">
                        <div class="checkbox">
                            <label>
                                <input type="checkbox" name="cleanup" value="cleanup" checked="true">
                                Make it sing
                            </label>
                        </div>
                    </div>
                    <div class="col-md-7 form-inline">
                        <label class="control-label" for="outputfields">Output fields</label>
                        <?php
                            $options = array(
                                '' => '',
                                'allfields.csv'  => 'All fields (HISPID + extras)',
                                'hispidonly.csv'    => 'Just the HISPID fields thanks',
                                'specifywb.csv'   => 'Specify Workbench fields',
                                //'alison.csv' => "Alison's custom output",
                                //'perth.csv' => "PERTH data",
                            );
                        ?>
                        <?=form_dropdown('outputfields', $options, 'allfields.csv', 'class="form-control"')?>
                    </div>
                </div>
                
                <div>
                    <input type="submit" name="submit" value="Load" class="btn btn-primary" />
                </div>
                
                

            </form>

            <h3>Convert HISPID5 to CSV</h3>
            <form action="<?=site_url()?>dehispidator/hispid5_convert" method="post"
                  enctype="multipart/form-data" class="form-horizontal">
                <label class="control-label" for="upload2">Load HISPID file</label>
                <div class="form-group">
                    <div class="col-md-9">
                        <div class="input-group">
                            <span class="btn btn-primary btn-file input-group-addon">
                                Browse...
                                <input type="file" name="upload2"
                                    id="upload"/>
                            </span>
                            <input type="text" class="form-control" />
                        </div>
                    </div>
                </div>
                <div class="checkbox">
                    <label>
                        <input type="checkbox" name="friendlycolumnnames" value="1" checked="true" />
                        Use friendly column names
                    </label>
                </div>
                <br/>
                <div>
                    <input type="submit" name="submit" value="Load" class="btn btn-primary" />
                </div>
            </form>
            
        </div> <!-- /.col-md-12 -->
    </div> <!-- /.row -->
</div> <!-- /.container -->


<?php require_once('footer.php'); ?>

