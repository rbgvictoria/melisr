<?php

require_once('header.php');

echo '<h2>Insect damage reporter</h2>';

?>

<p>Uploaded file has to be CSV and have the following column structure:</p>
<table>
    <tr>
        <th>Column A</th><td>MEL Number</td><td>&nbsp;</td>
    </tr>
    <tr>
        <th>Column B</th><td>Preparation</td><td>Default: Sheet</td>
    </tr>
    <tr>
        <th>Column C</th><td>Severity</td><td>minor/moderate/severe</td>
    </tr>
    <tr>
        <th>Column D</th><td>Damage to</td><td>flowers/fruit/leaves/roots/stems, etc</td>
    </tr>
    <tr>
        <th>Column E</th><td>Comments</td><td>&nbsp;</td>
    </tr>
    <tr>
        <th>Column F</th><td>Date noticed</td><td>&nbsp;</td>
    </tr>
    <tr>
        <th>Column G</th><td>Cause of damage</td><td>&nbsp;</td>
    </tr>
    <tr>
        <th>Column H</th><td>Treatment report</td><td>e.g. freezing</td>
    </tr>
    <tr>
        <th>Column I</th><td>Treated by</td><td>e.g. Gebert, W.A.</td>
    </tr>
    <tr>
        <th>Column J</th><td>Date treatment completed</td><td>e.g. 02/08/2011</td>
    </tr>
</table>
<p>Make sure that the MEL number is formatted exactly as the MEL barcodes, that the values for preparation (column B) are values from the pick list 
and that the agent name and the date in columns F, I and J are formatted exactly as in the examples.</p>
<p>Column headers are optional and you can have as many as you want.</p>

<?php

echo form_open_multipart('insectdamage');

echo '<p>';
echo form_label('Who is uploading the file?', 'agent', array('style'=>'width: auto;'));
echo form_dropdown('agent', $agents, $this->input->post('agent'));
echo '</p>';

$data = array(
    'name' => 'uploadedfile',
    'id' => 'uploadedfile',
    'value' => FALSE,
);
echo '<p>';
echo form_label('Load file: ', 'uploadedfile');
echo form_upload($data);
echo '</p>';

echo '<p>';
echo form_submit('submit', 'Submit');
echo '</p>';

echo form_close();

if (isset($errors)) {
    echo '<ul>';
    foreach ($errors as $error)
        echo "<li style=\"font-weight:bold;color:#ff0000;\">$error</li>";
    echo '</ul>';
}
if (isset($messages)) {
    echo '<ul>';
    foreach ($messages as $message)
        echo "<li style=\"font-weight:bold;color:#009900;\">$message</li>";
    echo '</ul>';
}

require_once('footer.php');

?>
