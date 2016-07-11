<?php
namespace Azureapi\Controller;
use Azureapi\Controller;
include_once(dirname(__FILE__).'/BaseController.class.php');

class ThemeController extends BaseController
{
    /**
       sql script:
       create table theme(ThemeId int primary key auto_increment,
       AppVersion int,
       ThemePath varchar(200),
       ThemeName varchar(50),
       ThemeImg text,
       ThemeState int,
       ThemeTime timestamp,
       UpTime timestamp
       )charset=utf8;
     */
    
    protected $_module_name = 'theme';
    protected $_key = 'ThemeId';

    protected $ThemeId;
    protected $AppVersion;
    protected $ThemePath;
    protected $ThemeName;
    protected $ThemeImg;
    protected $ThemeState;
    protected $ThemeTime;
    protected $UpTime;

    public function get_list($content)
    {
        list($data, $record_count) = parent::get_list($content);
        $list = array();
        if($data)
            {
                foreach($data as $v)
                    {
                        $list[] = array(
                            'ThemeId'    => urlencode($v['ThemeId']),
                            'AppVersion' => urlencode($v['AppVersion']),
                            'ThemePath'  => urlencode($v['ThemePath']),
                            'ThemeName'  => urlencode($v['ThemeName']),
                            'ThemeImg'   => urlencode($v['ThemeImg']),
                            'ThemeState' => urlencode($v['ThemeState']),
                            'ThemeTime'  => urlencode($v['ThemeTime']),
                            'UpTime'     => urlencode($v['UpTime'])
                        );
                    }
            }

        return array(200,
        array(
            'list'=>$list,
            'record_count'=>$record_count)
        );
    }
}