<?php
header("Content-Type: text/html; charset=UTF-8");
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
function getChoises(){
    $choices = "";

    $link = mysql_connect("localhost", "root", "sigma1810");
    if (!$link) {
        // "Error. MySQL connect failed.";
    }
    //超重要！　文字化け対策
    mysql_selectdb("idealer", $link);
    mysql_query('SET NAMES utf8', $link);

    $sql = "SELECT title FROM idealer.attributes;";

    $result = mysql_query($sql);

    while ($row = mysql_fetch_row($result)) {
        $choices .= "<span id=\"choice\" onClick=\"document.form.attributes.value = document.form.attributes.value + '".$row[0]." ';\">".$row[0]."</span>";
        
    }
    return $choices;
}