<?php
header("Content-Type: text/html; charset=UTF-8");
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Dealer
 *
 * @author sigma
 * iDealerの中核である車種診断および学習機能のコア
 */
require_once 'Vehicle.php';
class Dealer {

    private $link;

    //コンストラクタ　おもにDB接続関連
    public function Dealer() {
        $this->link = mysql_connect("localhost", "root", "sigma1810");
        if (!$this->link) {
            // "Error. MySQL connect failed.";
        }
        //超重要！　文字化け対策
        mysql_selectdb("idealer", $this->link);
        mysql_query('SET NAMES utf8', $this->link);
    }

    /*
     * 診断ファンクション　引数は理想リストで型はArrayIterator
     * 返却値は車種IDリスト
     * 
     */
    public function diagnose($idealList) {  
        //理想リストを理想IDリストに変換
        $idealIdList = array();
        foreach ($idealList as $ideal) {
            array_push($idealIdList, $this->attirbuteTitle2Id($ideal));
        }
        
        //理想IDリストをArrayItaratorに変換
        $iterableIdealList = new ArrayIterator();
        foreach ($idealIdList as $ideal) {
            $iterableIdealList->append($ideal);
        }

        //SQLを作る
        $sql = "SELECT idVehicle, idAttribute, count FROM idealer.vehicle_image where idAttribute in(";
        $flag = true;
        while ($flag) {
            $sql = $sql . $iterableIdealList->current();
            $iterableIdealList->next();
            if ($iterableIdealList->valid()) {
                $sql = $sql . ", ";
            } else {
                $sql = $sql . ") order by idVehicle";
                $flag = false;
            }
        }
         
        //echo $sql . "\n";
        //SQLを実行して取得した結果セットをVehicleオブジェクト化
        $result = mysql_query($sql);
        $vehicles = array();
        $iterator = 0;
        while ($row = mysql_fetch_assoc($result)) {
            if (count($vehicles) == null) {
                //echo $row["idVehicle"]. " create new first vehicle " . $iterator . "\n";
                $vehicle = new Vehicle($row["idVehicle"]);
                $vehicle->addAttribute($row["idAttribute"], $row["count"]);
                array_push($vehicles, $vehicle);
                $iterator++;
            } else if ($vehicles[$iterator - 1]->getIdVehicle() != $row["idVehicle"]) {
                //echo $row["idVehicle"]. " create new vehicle " . $iterator . "\n";
                $vehicle = new Vehicle($row["idVehicle"]);
                $vehicle->addAttribute($row["idAttribute"], $row["count"]);
                array_push($vehicles, $vehicle);
                $iterator++;
            } else {
                $vehicles[$iterator - 1]->addAttribute($row["idAttribute"], $row["count"]);
            }
        }
        
        //ICRでソート
        //echo "Sort\n";
        $vehicles = $this->quicksort($vehicles, $idealIdList);
        
        /*
        foreach ($vehicles as $vehicle){
            echo "idVehicle = " . $vehicle->getIdVehicle(). "\n";
            foreach ($vehicle->getAttributes() as $idAttirbute){
                echo "*" . $idAttirbute[0] . " count = " . $idAttirbute[1]. "\n";
            }
            echo "ICR = " . $vehicle->IdealConcordanceRate($idealIdList) . "\n";
        }
         */
        
        //上位三つを配列にする
        $recommendedVehicleList = array();
        if(count($vehicles) >= 3){
            for($i = 0; $i < 3; $i++){
                array_push($recommendedVehicleList, $this->vehicleId2Name($vehicles[$i]->getIdVehicle()));
            }
        }else{
            foreach ($vehicles as $vehicle) {
                array_push($recommendedVehicleList, $this->vehicleId2Name($vehicle->getIdVehicle()));
            }
        }
        
        return $recommendedVehicleList;
    }
    
    /*
     * 理想IDリストをcountでクイックソートする
     */
    private function quicksort($array, $idealIdList){
        if (count($array) <= 1){
            //return only
            return $array;
        }
        $pivot = array_shift($array); // ピボットの選択

        $left = $right = array();
        foreach ($array as $value) {
            if ($value->IdealConcordanceRate($idealIdList) > $pivot->IdealConcordanceRate($idealIdList)) {
             $left[]  = $value; //ピボットより小さい数は左
            } else {
                $right[] = $value;  // ピボットより大きい数は右
            }
        }
        // 左右のデータを再帰的にソートする
        return array_merge($this->quicksort($left, $idealIdList), array($pivot), $this->quicksort($right, $idealIdList));
    }
    
    /*
     *属性タイトルリストを属性IDに変換する 
     */
    private function attirbuteTitle2Id($title){
        $sql = "select idAttribute from idealer.attributes where title = '" . $title . "'";
        $result = mysql_query($sql);
        $row = mysql_fetch_assoc($result);
        return $row['idAttribute'];
    }
    
    /*
     * 車両IDを車両の名前に変換する
     */
    private function vehicleId2Name($vehicleId){
        $sql = "select name from idealer.vehicles where idVehicle = '" . $vehicleId . "'";
        $result = mysql_query($sql);
        $row = mysql_fetch_assoc($result);
        return $row['name'];
    }
    
    /*
     * 
     */
    private function vehicleName2Id($vehicleName){
        $sql = "select idVehicle from idealer.vehicles where name = '" . $vehicleName . "'";
        $result = mysql_query($sql);
        $row = mysql_fetch_assoc($result);
        return $row['idVehicle'];
    }

    /*
     * 学習ファンクション　引数は興味を持った車両名と理想リスト
     * 返却値はなし
     */
    public function hearing($vehicleName, $idealList) {
        //データ変換
        $idVehicle = $this->vehicleName2Id($vehicleName);
        $idealIdList = array();
        foreach ($idealList as $ideal) {
            array_push($idealIdList, $this->attirbuteTitle2Id($ideal));
        }
        
        //各理想IDごとに追加あるいは更新処理
        foreach ($idealIdList as $idIdeal) {
            //その車両にその属性があるかの確認
            $sql = "select * from idealer.vehicle_image where idVehicle = '" . $idVehicle . "' and idAttribute = '" . $idIdeal . "'";
            $result = mysql_query($sql);
            $row = mysql_fetch_row($result);
            if($row == null){
                //該当する属性なし：追加処理
                $sql = sprintf("insert into idealer.vehicle_image (idVehicle, idAttribute, count) values (%d, %d, %d)", $idVehicle, $idIdeal, 1);
                if(!mysql_query($sql)){
                    echo 'ERROR: Quering failed.';
                }
            }else{
                //該当する属性あり:まずはcountの値を取得
                $sql = sprintf("select count from idealer.vehicle_image where idVehicle = %d and idAttribute = %d", $idVehicle, $idIdeal);
                $result = mysql_query($sql);
                $row = mysql_fetch_assoc($result);
                $count = $row["count"];
                
                //countの値をインクリメント
                $count++;
                
                //インクリメントした結果をDBに反映：更新処理
                $sql = sprintf("update idealer.vehicle_image set count = %d where idVehicle = %d and idAttribute = %d", $count, $idVehicle, $idIdeal);
                if(!mysql_query($sql)){
                    echo 'ERROR: Update failed.';
                }
            }
        }
    }

}
