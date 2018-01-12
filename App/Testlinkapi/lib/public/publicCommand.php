<?php
header("Content-Type: text/html;charset=utf-8");
require_once("publicDefine.php");
/*
*公共方法处理类
*包括：枚举值名称获取/枚举值ID获取
*/
class publicCommand{
    public  $importance=array('1'=>'低','2'=>'中','3'=>'高');
    public  $execution_type=array('1'=>'手工','2'=>'自动化','3'=>'手工但已自动化');
    public  $complexity =array('1'=>'简单','2'=>'比较简单','3'=>'一般','4'=>'复杂','5'=>'很复杂');
    public  $reviewed_status=array('1'=>'待评审','2'=>'评审通过','3'=>'评审未通过');

    
    /*函数功能：获取枚举值的名称
     * 参数：$EnumType 枚举值类型
     *     $EnumName 枚举值名称
     * */
    public function getEnumName($EnumType,$EnumID){
        $curValue="";
        //枚举类型为优先级
        if($EnumType==ENUM_importance){
            $curValue=$this->importance[$EnumID];
        }
        //枚举类型为执行类型
        if($EnumType==ENUM_execution_type){
            $curValue=$this->execution_type[$EnumID];
        }
        //枚举类型为复杂度
        if($EnumType==ENUM_complexity){
            $curValue=$this->complexity[$EnumID];
        }
        //枚举类型为执行状态
        if($EnumType==ENUM_reviewed_status){
            $curValue=$this->reviewed_status[$EnumID];
        }
        
        return $curValue;
    }
       
    /*函数功能：获取枚举值的值
     * 参数：$EnumType 枚举值类型
     *     $EnumID 枚举值名称
     * */
    public function getEnumID($EnumType,$EnumName){
        $curValue="";
        //枚举类型为优先级
        if($EnumType==ENUM_importance){
            $curValue=array_search($EnumName,$this->importance);
        }
        //枚举类型为执行类型
        if($EnumType==ENUM_execution_type){
            $curValue=array_search($EnumName,$this->execution_type);
        }
        //枚举类型为复杂度
        if($EnumType==ENUM_complexity){
            $curValue=array_search($EnumName,$this->complexity);
        }
        //枚举类型为执行状态
        if($EnumType==ENUM_reviewed_status){
            $curValue=array_search($EnumName,$this->reviewed_status);
        }
        return $curValue;
    }
}

