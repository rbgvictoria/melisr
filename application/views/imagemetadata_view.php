<?php require_once('header.php'); ?>

<div class="container">
    <div class="row">
        <div class="col-md-12">
            <h2>Attachment metadata workbench</h2>
            <form action="<?=site_url()?>imagemetadata" method="post"
                  enctype="multipart/form-data" class="form-horizontal">

            <div class="form-group">
                <label class="col-md-3 control-label" for="user">User name:</label>
                <div class="col-md-9">
                    <select id="user" name="user" class="form-control">
                        <option value="">(select a user)</option>
                        <?php foreach($Users as $user): ?>
                        <?php
                            $selected = false;
                            if ($this->input->post('user') && $user['AgentID'] 
                                    == $this->input->post('user'))
                                $selected = ' selected="true"'
                        ?>
                        <option value="<?=$user['AgentID']?>"
                                    <?=$selected?>><?=$user['Name']?></option>
                        <?php endforeach;?>
                    </select>
                </div>
            </div> <!-- /.form-group -->
            
            <div class="form-group">
                <label class="col-md-3 control-label" for="startdate">Attachment added since:</label>
                <div class="col-md-9">
                    <input type="text" name="startdate" id="startdate" 
                            class="form-control" 
                            value="<?=$this->input->post('startdate')?>"
                            placeholder="yyyy-mm-dd" />
                </div>
            </div> <!-- /.form-group -->
            
            <div class="form-group">
                <label class="col-md-3 control-label" for="enddate">Attachments added before:</label>
                <div class="col-md-9">
                    <input type="text" name="enddata" id="startdate" 
                            class="form-control" 
                            value="<?=$this->input->post('enddate')?>"
                            placeholder="yyyy-mm-dd" />
                </div>
            </div> <!-- /.form-group -->
            
            <div class="checkbox">
                <label>
                    <input type="checkbox" name="insufficient_metadata"
                            value="1" 
                            <?=($this->input->post('insufficient_metadata')) 
                                ? 'checked="true"' : ''?>/>
                    Check for all attachment records with insufficient metadata
                </label>
            </div>
            <br/>
            
            <p><b>Filter by attachment records with missing values in:</b></p>
            <?php 
                $missing = [
                    'att.CopyRightHolder' => 'Copyright holder',
                    'att.CopyRightDate' => 'Copyright date',
                    'att.License' => 'Restrictions',
                    'att.Credit' => 'Credit',
                    'aia.Text2' => 'Photographer',
                    'aia.Text1' => 'Context',
                    'aia.CreativeCommons' => 'Licence',
                ];
            ?>
            <?php foreach ($missing as $key => $value): ?>
                <div class="checkbox">
                    <label>
                        <input type="checkbox" value="key"
                                <?=($this->input->post('missing') && 
                                    in_array($key, $this->input->post('missing'))) 
                                    ? 'checked="true"' : ''?> />
                        <?=$value?>
                    </label>
                </div>
            <?php endforeach; ?>
            
            <br/>
            
            <?php
                $options = [
                    'taxonname'  => 'taxon name',
                    'collector'    => 'collector',
                    'collectingnumber'    => 'collecting number',
                    'collectingdate'    => 'collecting date',
                    'geography' => 'geography'
                ];
            ?>
            <div class="form-group">
                <label class="col-md-3 control-label" for="extrafields">Extra fields:</label>
                <div class="col-md-9">
                    <select name="extrafields[]" id="extrafields" 
                            multiple="true" class="form-control">
                        <?php foreach ($options as $key => $value): ?>
                            <option value="<?=$key?>"><?=$value?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            
            <div class="form-group">
                <label class="col-md-3 control-label" for="format">Output format:</label>
                <div class="col-md-9">
                    <select name="format" id="format" class="form-control">
                        <option value="html">HTML table</option>
                        <option value="txt">Tab-delimited text</option>
                    </select>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <input type="submit" name="submit" value="Get attachment metadata"
                           class="btn btn-primary btn-block" />
                </div>
                <div class="col-md-6">
                    <a class="btn btn-primary btn-block" href="<?=site_url()?>imagemetadata/upload">
                        Upload file with attachment metadata
                    </a>
                </div>
            </div> <!-- /.row -->
            <br/>
            
            <?php if (isset($imagerecords) && $imagerecords): ?>
            <br/>
            <div class="image-records">
                <table class="table table-condensed table-bordered table-responsive">
                    <thead>
                        <tr>
                            <?php
                                $headerrow = array_keys($imagerecords[0]);
                                array_shift($headerrow);
                                foreach ($headerrow as $value): 
                            ?>
                            <th><?=$value?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($imagerecords as $row): ?>
                        <?php array_shift($row); ?>
                        <tr>
                            <?php foreach ($row as $key => $value): ?>
                            <td><?=$value?></td>
                            <?php endforeach; ?>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div>&nbsp;</div>
            <?php endif; ?>
        </div> <!-- /.col-md-12 -->
    </div> <!-- /.row -->
</div> <!-- /.container -->

<?php require_once('footer.php'); ?>
