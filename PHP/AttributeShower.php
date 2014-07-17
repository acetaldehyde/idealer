<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

$choices = "";

$link = mysql_connect("localhost", "root", "sigma1810");
if (!$this->link) {
    // "Error. MySQL connect failed.";
}

$sql = "SELECT title FROM idealerDB.Attributes;";

$result = mysql_query($sql);

$rows = mysql_fetch_row($result);

foreach ($rows as $row) {
    $choices = "<span id=\"choice\" onClick=\"document.form.attributes.value = document.form.attributes.value + '" .$row . " ';\">" .$row . "</span>";
}

echo $choices;