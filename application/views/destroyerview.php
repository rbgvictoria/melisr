<?php require_once 'header_1.php'; ?>

<div class="container">
    <div class="row">
        <div class="col-md-12">
            <h2>Destructive damage reporter</h2>

            <p>Uploaded file has to be CSV and have the following column structure:</p>
            <table class="table table-bordered table-condensed table-responsive">
                <tr>
                    <th width="10%">Column A</th><td width="15%">MEL Number</td><td>&nbsp;</td>
                </tr>
                <tr>
                    <th>Column B</th><td>Preparation</td><td><b>Pick list value:</b> <?=implode('|', $preparationItems)?><br/>Default: Sheet</td>
                </tr>
                <tr>
                    <th>Column C</th><td>Event type</td><td><b>Pick list value:</b> <?=implode('|', $eventTypeItems)?></td>
                </tr>
                <tr>
                    <th>Column D</th><td>Researcher</td><td><b>Agent name:</b> &lt;<i>last name</i>&gt;, &lt;<i>initials</i>&gt;[; &lt;<i>last name</i>&gt;, &lt;<i>initials</i>&gt;][...] or &lt;<i>institution name</i>&gt;</td>
                </tr>
                <tr>
                    <th>Column E</th><td>Sampling date</td><td><b>Date:</b> Complete date: dd/mm/yyyy; year/month: 00/dd/yyyy; year: 00/00/yyyy</td>
                </tr>
                <tr>
                    <th>Column F</th><td>Purpose</td><td>&nbsp;</td>
                </tr>
                <tr>
                    <th>Column G</th><td>Results</td><td>&nbsp;</td>
                </tr>
                <tr>
                    <th>Column H</th><td>Cause of damage</td><td><b>Pick list value:</b> <?=implode('|', $causeOfDamageItems)?></td>
                </tr>
                <tr>
                    <th>Column I</th><td>Severity</td><td><b>Pick list value:</b> <?=implode('|', $severityOfDamageItems)?></td>
                </tr>
                <tr>
                    <th>Column J</th><td>Date noticed</td><td><b>Date:</b> Complete date: dd/mm/yyyy; year/month: 00/dd/yyyy; year: 00/00/yyyy</td>
                </tr>
                <tr>
                    <th>Column K</th><td>Assessed by</td><td><b>Agent name:</b> &lt;<i>last name</i>&gt;, &lt;<i>initials</i>&gt;[; &lt;<i>last name</i>&gt;, &lt;<i>initials</i>&gt;][...] or &lt;<i>institution name</i>&gt;</td>
                </tr>
                <tr>
                    <th>Column L</th><td>Date assessed</td><td><b>Date:</b> Complete date: dd/mm/yyyy; year/month: 00/dd/yyyy; year: 00/00/yyyy</td>
                </tr>
                <tr>
                    <th>Column M</th><td>Treatment report</td><td><i>e.g.</i> freezing</td>
                </tr>
                <tr>
                    <th>Column N</th><td>Part of specimen</td><td>flowers, fruit, leaves, roots, stems, etc</td>
                </tr>
                <tr>
                    <th>Column O</th><td>Comments</td><td>&nbsp;</td>
                </tr>
            </table>
            <p>Make sure that the MEL number is formatted exactly as the MEL barcodes, that the values for preparation (column B) are values from the pick list 
            and that the agent name and the dates are formatted exactly as in the examples.</p>
            <p>Column headers are optional and you can have as many as you want.</p>

            <form action="<?=site_url()?>destroyer" method="post"
                  enctype="multipart/form-data" class="form-horizontal">
            
                <div class="form-group">
                    <label class="control-label col-md-2" for="agent">
                        Who is uploading?
                    </label>
                    <div class="col-md-10">
                        <?=form_dropdown('agent', $agents, $this->input->post('agent'), 'class="form-control" id="agent"')?>
                    </div>
                </div> <!-- /.form-group -->
                
                <div class="form-group">
                    <label class="control-label col-md-2" 
                          for="uploadedfile">Choose file:</label>
                    <div class="col-md-10">
                        <div class="input-group">
                            <span class="btn btn-primary btn-file input-group-addon">
                                Browse...
                                <input type="file" name="uploadedfile"
                                    id="uploadedfile"/>
                            </span>
                            <input type="text" class="form-control" />
                        </div>
                    </div>
                </div> <!-- /.form-group -->
                
                <p>
                    <input type="submit" name="submit" value="Upload"
                           class="btn btn-primary" />
                </p>
            </form>

        </div> <!-- /.col-md-12 -->
    </div> <!-- /.row -->
</div> <!-- /.container -->

<?php require_once 'footer.php'; ?>
