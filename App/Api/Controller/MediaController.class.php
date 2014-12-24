<?php
namespace api\Controller;
use Api\Controller;
include_once(dirname(__FILE__).'/BaseController.class.php');
/**
--媒体管理--
*/
class MediaController extends BaseController {
	
	 protected $_module_name = 'media';
     protected $id;              #id
     protected $dict_sn;         #字典编号
     protected $media_url;       #媒体相对路径
     protected $width;           #图片宽度
     protected $height;          #图片高度

	#上传文件
	/**
	*@@input
	*@param $media_data  媒体数据
	*@param $media_type  媒体数据类型
	*@param $media_ext   媒体后缀名
	*@param $module_name 模块名称 
	*@@output
	*
	*
	*/
	public function upload($content, $handler)
	{
		//反解析
    	#格式化并检查参数
		$format_params = json_decode($content, true);
		extract($format_params);
		if(!isset($field_name)
		|| !isset($file_name)
		|| !isset($file_ext)
		|| !isset($module_sn)
		)
		{
			return array(500,array(urlencode('参数不合法')));
		}

		switch($module_sn)
		{
			case '015001':#商品模块
			{
				$file_dir  = 'media/'.'goods'.'/'.date("Y-m-d").'/';
			}
			break;
			case '015002':#会员模块
			{
				$file_dir  = 'media/'.'user'.'/'.date("Y-m-d").'/';
			}
			break;
		}
		if(!is_dir(__PUBLIC__.$file_dir))
		{
			mkdir(__PUBLIC__.$file_dir);
		}
		switch($file_ext)
		{
			case 'jpg':
				{
					$file_name = $file_dir.time().".jpg";
				}
				break;
		}
		$data_file_name = $file_name;
		$file_name = __PUBLIC__.$file_name;
		
		if(move_uploaded_file($_FILES[$field_name]['tmp_name'], 
			                 $file_name))
        {
        	#添加图片到数据库
        	$data = array(
        		'dict_sn'   => $module_sn,
        		'media_url' => $data_file_name,
        		);
        	list($status_code, $content) = $this->add(json_encode($data));
        	if(200 == $status_code
        	&& 0   == $content['is_success'])
        	{
        		$id = M()->getLastInsID();
        		return array(200, 
        		         array('is_success' => 0,
        		         	   'message'    => urlencode('上传成功'),
        		         	   'img'        => C('media_url_pre').$data_file_name,
        		         	   'id'         => $id,
        		         	   ),
        		);
        	}        	
        	else
        	{
        		return array(200, 
        		         array('is_success' => -1,
        		         	   'message'    => urlencode('数据写入数据库失败'),
        		         	   ),
        		);
        	}
        }
        else
        {
        	return array(200,
        				 array(
        				 	'is_success'=>-1,
        				 	'message'   =>urlencode('图片上传失败'),
        				 ),
        		          );
        }
        /*
                        echo 'failt';
                    list($this->status_code, $this->out_content) = $obj->{$method}($this->in_content, $handler);#处理带有资源的数据信息
                    echo 'end<br/>';

		var_dump($handler);
		*/
	}

}