<?php require_once('header.php'); ?>

<div class="container">
    <div class="row">
        <div class="col-md-12">
            <h2>Upload attachment metadata</h2>
            <form action="<?=site_url()?>imagemetadata/upload" method="post"
                  enctype="multipart/form-data" class="form-horizontal">

                <div class="form-group">
                    <label class="control-label col-md-3" for="user">Username:</label>
                    <div class="col-md-9">
                        <select id="user" name="user" class="form-control">
                            <option value="">(select a user)</option>
                            <?php foreach($Users as $user): ?>
                            <?php
                                $selected = false;
                                if ($this->input->post('user') && $user['AgentID'] == $this->input->post('user'))
                                    $selected = ' selected="selected"'
                            ?>
                            <option value="<?=$user['AgentID']?>"<?=$selected?>><?=$user['Name']?></option>
                            <?php endforeach;?>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="control-label col-md-3" 
                          for="image_metadata_upload">Load file:</label>
                    <div class="col-md-9">
                        <div class="input-group">
                            <span class="btn btn-primary btn-file input-group-addon">
                                Browse...
                                <input type="file" name="image_metadata_upload"
                                    id="image_metadata_upload"/>
                            </span>
                            <input type="text" class="form-control" />
                        </div>
                    </div>
                </div>

                <div>
                    <button type="submit" name="submit" class="btn btn-primary btn-block" value="submit">Submit</button>
                </div>
            </form>

        </div>
    </div>
</div>

<?php require_once('footer.php'); ?>
