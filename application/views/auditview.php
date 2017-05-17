<?php require_once('header_1.php'); ?>

<div class="container">
    <div class="row">
        <div class="col-md-12">
            <h2>What did you do to our database?</h2>

            <form action="<?=site_url()?>audit" method="post" class="form-horizontal">
                
                <div class="form-group">
                    <label class="control-label col-md-2" for="table">
                        Table:
                    </label>
                    <div class="col-md-2">
                        <?php 
                            $options = array();
                            $options[0] = '(select a table)';
                            foreach ($tables as $table)
                                $options[$table['id']] = $table['name'];
                        ?>
                        <?=form_dropdown('table', $options, $this->session->userdata('table'), 'id="table" class="form-control"')?>
                    </div>
                    <label class="control-label col-md-2" for="action">
                        Action:
                    </label>
                    <div class="col-md-2">
                        <?php
                            $options = array(
                               '0'    => '(select an action)',
                               '1'    => 'insert',
                              '2'   => 'update',
                              '3' => 'delete',
                            );
                        ?>
                        <?=form_dropdown('action', $options, $this->session->userdata('action'), 'id="action" class="form-control"')?>
                    </div>
                    <label class="control-label col-md-2" for="user">
                        Specify user:
                    </label>
                    <div class="col-md-2">
                        <?php
                            $options = array();
                            $options[0] = '(select a user)';
                            foreach ($Users as $user)
                                $options[$user['AgentID']] = $user['Name'];
                        ?>
                        <?=form_dropdown('user', $options, $this->session->userdata('user'), 'id="user" class="form-control"')?>
                    </div>
                </div> <!-- /.from-group -->
                
                <div class="form-group">
                    <label class="control-label col-md-2" for="startdate">
                        Start date:
                    </label>
                    <div class="col-md-2">
                        <input type="text" name="startdate" id="startdate"
                               value="<?=$this->session->userdata('startdate')?>"
                               placeholder="yyyy-mm-dd" class="form-control" />
                    </div>
                    <label class="control-label col-md-2" for="enddate">
                        End date:
                    </label>
                    <div class="col-md-2">
                        <input type="text" name="enddate" id="enddate"
                               value="<?=$this->session->userdata('enddate')?>"
                               placeholder="yyyy-mm-dd" class="form-control" />
                    </div>
                    <div class="col-md-4">
                        <button type="submit" name="submit" value="1" 
                                class="btn btn-primary btn-block">
                            Submit
                        </button>
                        
                    </div>
                </div> <!-- /.from-group -->
            </form>

            <br/>


            <?php if (isset($changes)): ?>
            <p><?=$NumberOfChanges?></p>
            <p><?=$pagelinks?></p>
            <?php
                $userids = array();
                foreach ($Users as $user) $userids[] = $user['AgentID'];

                $tableids = array();
                foreach ($tables as $table) $tableids[] = $table['id'];

            ?>
            <table class="table table-bordered table-condensed table-responsive">
                <tr>
                    <th>Time</th>
                    <th>Action</th>
                    <th>Table</th>
                    <th>Record</th>
                    <th>ParentTable</th>
                    <th>ParentRecord</th>
                    <th>Agent</th>
                </tr>
                <?php foreach ($changes as $row): ?>
                <tr>
                    <td><?=$row->TimestampCreated;?></td>
                    <?php
                        $actions = array('insert', 'update', 'delete');
                        if ($row->Action !== FALSE)
                            echo '<td>' . $actions[$row->Action] . '</td>';
                        else
                            echo '<td>&nbsp;</td>';
                    ?>
                    <?php 
                        $key = array_search($row->TableNum, $tableids);
                        if ($key !== FALSE)
                            echo '<td>' . $tables[$key]['name'] . '</td>';
                    ?>
                    <td><?=$row->RecordID;?></td>
                    <?php 
                        $key = array_search($row->ParentTableNum, $tableids);
                        if ($key !== FALSE)
                            echo '<td>' . $tables[$key]['name'] . '</td>';
                        else 
                            echo '<td>&nbsp;</td>';
                    ?>
                    <td><?=$row->ParentRecordID;?></td>

                    <?php 
                        $key = array_search($row->CreatedByAgentID, $userids);
                        if ($key !== FALSE)
                            echo '<td>' . $Users[$key]['Name'] . '</td>';
                        else 
                            echo '<td>&nbsp;</td>';
                    ?>
                </tr>
                <?php endforeach; ?>
            </table>
            <?php endif; ?>
        </div> <!-- /.col-md-12 -->
    </div> <!-- /.row -->
</div> <!-- /.container -->

<?php require_once('footer.php'); ?>

