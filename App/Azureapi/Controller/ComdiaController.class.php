<?php
namespace Azureadmin\Controller;
use Azureadmin\Controller;
include_once(dirname(__FILE__)."/BaseController.class.php");

class ComdiaController extends BaseController
{
    /**
       --管理--
     */

    /**
       sql script:
       create table comdia(DiaId int primary key auto_increment,
       UserId int,
       UserOffer varchar(50),
       AdminId int,
       AdminName varchar(50),
       ComId int,
       DiaState int,
       DiaPath varchar(200),
       DiaCon text,
       DiaTime timestamp,
       DiaFinTime timestamp
       )charset=utf8;
     */
}
