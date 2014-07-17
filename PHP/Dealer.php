<?php

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
    }

    //診断ファンクション　引数は理想IDリストで型はArrayIterator
    /*
     * 返却値は車種IDリスト
     * 
     */
    public function diagnose($idealIdList) {
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
        for($i = 0; $i < 3; $i++){
            array_push($recommendedVehicleList, $vehicles[$i]->getIdVehicle());
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

    //学習ファンクション　引数は興味を持った車両IDと理想リスト
    public function hearing($vehicleId, $idealList) {
        
    }

}
/*
$dealer = new Dealer;

$atlist = array(1, 3);
$reccomends = $dealer->diagnose($atlist);
foreach ($reccomends as $idVehicle){
    echo $idVehicle . "\n";
}
*/