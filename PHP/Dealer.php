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

    //診断ファンクション　引数は理想IDリストで型はArrayIterator
    /*
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
    
    //costomized quick sort using only this class.
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
    
    private function attirbuteTitle2Id($title){
         $sql = "select idAttribute from idealer.attributes where title = '" . $title . "'";
         $result = mysql_query($sql);
         $row = mysql_fetch_assoc($result);
         return $row['idAttribute'];
    }
    
    private function vehicleId2Name($vehicleId){
        $sql = "select name from idealer.vehicles where idVehicle = '" . $vehicleId . "'";
         $result = mysql_query($sql);
         $row = mysql_fetch_assoc($result);
         return $row['name'];
    }

    //学習ファンクション　引数は興味を持った車両IDと理想リスト
    public function hearing($vehicleId, $idealList) {
        
    }

}
/*
$dealer = new Dealer;
$atlist = array("大人", "重い");
echo $atlist[0] . $atlist[1];
$reccomends = $dealer->diagnose($atlist);
foreach ($reccomends as $idVehicle){
    echo $idVehicle . "\n";
}*/