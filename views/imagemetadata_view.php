<?php require_once('header.php'); ?>

<h2>Image metadata workbench</h2>
<?=form_open('imagemetadata',array('enctype'=>'multipart/form-data'))?>

<p>
<?=form_label('User name:', 'user', array('style' => 'width: auto')); ?>
<select id="user" name="user">
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

<?php
    $data = array(
        'id' => 'startdate',
        'name' => 'startdate',
        'style' => 'width: 100px;',
        'value' => ($this->input->post('startdate')) ? $this->input->post('startdate') : date('Y-m-d')
    );
    echo form_label('Image added between (yyyy-mm-dd):', 'startdate', array('style' => 'width: auto; margin-left: 20px'));
    echo form_input($data);
    
    echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
    
?>
<?php
    $data = array(
        'id' => 'enddate',
        'name' => 'enddate',
        'style' => 'width: 100px;',
        'value' => (isset($enddate) && $enddate) ? $enddate : FALSE
    );
    echo form_label('and (yyyy-mm-dd):', 'enddate', array('style' => 'width: auto; margin-left: 20px'));
    echo form_input($data);
?>
</p>
<p>
    <?php 
        $missing = array(
            'att.CopyRightHolder' => 'Copyright holder',
            'att.CopyRightDate' => 'Copyright date',
            'att.License' => 'Restrictions',
            'att.Credit' => 'Credit',
            'aia.Text2' => 'Photographer',
            'aia.Text1' => 'Context',
            'aia.CreativeCommons' => 'Licence',
        );
        
        echo '<b>Filter by attachment records with missing values in:</b><br/>';
        foreach ($missing as $key => $value) {
            $options = array(
                'name' => 'missing[]',
                'id' => 'missing_' . substr($key, strpos($key, '.')+1),
                'value' => $key,
                'checked' => ($this->input->post('missing') && in_array($key, $this->input->post('missing'))) ? TRUE : FALSE
            );
            
            echo form_checkbox($options);
            echo form_label($value, $options['id'], array('style' => 'width:auto;'));
            echo '<br/>';
        }
    ?>
</p>
<p>
    <?php
    $options = array(
        '' => '(select)',
        'taxonname'  => 'taxon name',
        'collector'    => 'collector',
        'collectingnumber'    => 'collecting number',
        'collectingdate'    => 'collecting date',
        'geography' => 'geography'
      );
    echo form_label('Extra fields:', 'extrafields', array('style' => 'width: auto;'));
    echo form_multiselect('extrafields[]', $options, $this->input->post('extrafields'), 'id="extrafields"');
    ?>

</p>
<p>
    <?php
        $options = array(
            'html'  => 'HTML table',
            'txt'    => 'TXT',
            'csv'    => 'CSV',
          );
        echo form_label('Output format:', 'format', array('style' => 'width: auto;'));
        echo form_dropdown('format', $options, 'html', 'id="format"');
    
    ?>
    <?=form_submit('submit', 'Get image metadata')?></p>
<?=form_close(); ?>
<p><?=anchor('imagemetadata/upload', 'Upload CSV file with image metadata')?></p>

<?php if (isset($message)): ?>
<div class="message"><?=$message?></div>
<?php endif; ?>

<?php if (isset($imagerecords) && $imagerecords): ?>
<div class="image-records">
    <table>
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

<?php require_once('footer.php'); ?>