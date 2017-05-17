<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

require_once('collectionobjectcl.php');

@$db = new mysqli('203.55.15.78', 'admin', 'admpwd', 'specify_development');
if (mysqli_connect_errno()) {
    echo 'Error: Could not connect to database' . "\n";
    exit;
}

$select = "SELECT PreparationID, CollectionObjectID
    FROM preparation
    LIMIT 100";
$query = $db->query($select);
while ($row = $query->fetch_assoc()) {
    $colobj = new CollectionObjectCl($db, $row['CollectionObjectID']);
    echo $colobj->getStorageID() . "/n";
}



?>
