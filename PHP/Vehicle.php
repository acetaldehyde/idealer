<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Vehicle
 *理想一致率を算出するための車両データの格納クラス
 * @author sigma
 */
class Vehicle{
    private $idVehicle;
    private $attributeList = array();
    
    //コンストラクタ　車両IDを格納するだけ
    public function Vehicle($idVehicle){
        $this->idVehicle = $idVehicle;
    }
    
    //
    public function getIdVehicle(){
        return $this->idVehicle;
    }
    
    //車両の属性リストを格納する
    public function addAttribute($idAttribute, $count){
        array_push($this->attributeList, array($idAttribute, $count));
    }
    
    public function getAttributes(){
        return $this->attributeList;
    }
    
    //理想一致率を算出して返却
    public function IdealConcordanceRate($idealList){
        $denomi = count($this->attributeList) + count($idealList);
        $nume = 0;
        
        foreach ($idealList as $idIdeal){
            foreach ($this->attributeList as $attribute){
                if($idIdeal == $attribute[0]){
                    $nume += $attribute[1];
                }
            }
        }
        
        $IdealConcordanceRate = $nume / $denomi;
        return $IdealConcordanceRate;
    }
}
/*
$vehicle = new Vehicle(1);
$vehicle->addAttribute(1, 2);
$vehicle->addAttribute(2, 1);

$idealList = array();
array_push($idealList, 1);
array_push($idealList, 2);
array_push($idealList, 3);

echo $vehicle->IdealConcordanceRate($idealList);
*/