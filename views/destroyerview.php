<?php

require_once('header.php');

echo '<h2>Destructive damage reporter</h2>';

?>

<p>Uploaded file has to be CSV and have the following column structure:</p>
<table>
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

<?php

echo form_open_multipart('destroyer');

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
