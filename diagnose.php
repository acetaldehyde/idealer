<!DOCTYPE html>
<!--
To change this license header, choose License Headers in Project Properties.
To change this template file, choose Tools | Templates
and open the template in the editor.
-->
<?php
    header("Content-Type: text/html; charset=UTF-8");
    require_once("./PHP/AttributeShower.php");
    $choices = getChoises();
?>
<html>
    <head>
        <title>iDealer Diagnose</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
                    <h1>Choose your attribute.</h1>
                    <form name="form" action="./diagnose_result.php" method="get">
                        <input type="text" name="attributes" value="" size="100" id="input_form">
                        <span id="clear_button" onClick="document.form.attributes.value = '';">Clear</span>
                        <div id="attribute_choices">
                            <!-- 選択肢は自動生成する予定 -->
                            <h2>Choose from here!</h2>
                            <?= $choices ?>
                        </div>
                        <input type="submit" name="submit" id="submit_button" title="SUBMIT">
                    </form>
                </section>
                <section>
                    <p id="ads">ads</p>
                </section>
            </div>
            
        </article>
    </body>
</html>
