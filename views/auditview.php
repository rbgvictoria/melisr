<?php require_once('header.php'); ?>

<h2>What did you do to our database?</h2>

<?=form_open('audit')?>

<?php 
    $options = array();
    $options[0] = '(select a table)';
    foreach ($tables as $table)
        $options[$table['id']] = $table['name'];
    echo form_label('Table:', 'table', array('style' => 'width: auto'));
    echo form_dropdown('table', $options, $this->session->userdata('table'), 'id="table" style="width: 140px"');
?>

<?php
    $options = array(
       '0'    => '(select an action)',
       '1'    => 'insert',
      '2'   => 'update',
      '3' => 'delete',
    );
    
    echo form_label('Action:', 'action', array('style' => 'margin-left: 20px; width: auto;'));
    echo form_dropdown('action', $options, $this->session->userdata('action'), 'id="action"');
?>

<?php
    $options = array();
    $options[0] = '(select a user)';
    foreach ($Users as $user)
        $options[$user['AgentID']] = $user['Name'];
    echo form_label('Specify user: ', 'user', array('style' => 'width: auto; margin-left: 20px;'));
    echo form_dropdown('user', $options, $this->session->userdata('user'), 'id="user"');
?>

<br/>

<?php
    $data = array(
        'id' => 'startdate',
        'name' => 'startdate',
        'style' => 'width: 100px;',
        'value' => $this->session->userdata('startdate')
    );
    echo form_label('Start date (yyyy-mm-dd):', 'startdate', array('style' => 'width: auto;'));
    echo form_input($data);
    
    echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
    
?>

<?php
    $data = array(
        'id' => 'enddate',
        'name' => 'enddate',
        'style' => 'width: 100px;',
        'value' => $this->session->userdata('enddate')
    );
    echo form_label('End date (yyyy-mm-dd):', 'enddate', array('style' => 'width: auto; margin-left: 20px;'));
    echo form_input($data);
    
    echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
    
?>
&nbsp;&nbsp;
<?=form_submit('submit', 'Submit')?>

<?=form_close()?>

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
<table>
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

<?php require_once('footer.php'); ?>

