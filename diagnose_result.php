<!DOCTYPE html>
<!--
To change this license header, choose License Headers in Project Properties.
To change this template file, choose Tools | Templates
and open the template in the editor.
-->
<?php
    //GET引数を分解し配列として整形する。
    if(isset($_GET)){
        $attributeString = $_GET["attributes"];
        $attributeString = trim($attributeString);
        $attributes = array();
        $word = "";
        for($i = 0; $i < strlen($attributeString); $i++){
            if($attributeString[$i] != " "){
                $word .= $attributeString[$i];
            }else{
                if($word != " "){
                    array_push($attributes, $word);
                }
                $word = "";
            }
        }
        array_push($attributes, $word);
    }
?>
<html>
    <head>
        <meta charset="UTF-8">
        <title>iDealer result</title>
        <link href="./CSS/common.css" rel="stylesheet" type="text/css">
    </head>
    <body>
        <article>
            <header>
                <h1><a href="./top.html">iDealer</a></h1>
                <nav id="header_menu">
                    <a href="">About</a>
                    <a href="">Contact us</a>
                </nav>
            </header>
            <footer>
                <p>©2014 iDealer All rights reserved.</p>
            </footer>
            
            <div id="body">
                <section>
                    <p id="ads">ads</p>
                </section>
                <section id="diagnose">
                    <h1>Result</h1>
                    <?= $attributes[0] ?>
                </section>
                <section>
                    <p id="ads">ads</p>
                </section>
            </div>
            
        </article>
    </body>
</html>
