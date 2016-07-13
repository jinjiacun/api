<?php
namespace Azureapi\Controller;
use Azureapi\Controller;
include_once(dirname(__FILE__).'/BaseController.class.php');
/**
--媒体管理--
*/
class MediaController extends BaseController {
	/**	
	CREATE TABLE IF NOT EXISTS `yms_media` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `dict_sn` varchar(11) DEFAULT '0' COMMENT '媒体类型(1-商品)',
  `media_url` varchar(255) DEFAULT NULL COMMENT '媒体相对路径',
  `width` int(11) DEFAULT '0' COMMENT '图片宽度',
  `height` int(11) DEFAULT '0' COMMENT '图片高度',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='媒体表' AUTO_INCREMENT=70 ;
	*/
	
	 protected $_module_name = 'media';
     protected $id;              #id
     protected $dict_sn;         #字典编号
     protected $media_url;       #媒体相对路径
     protected $width;           #图片宽度
     protected $height;          #图片高度

	#上传文件
	/**
	*@@input
    *@param field_name   
	*@param file_name
	*@param file_ext
	*@param module_sn 
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
		
		if(!isset($_FILES[$field_name])
		|| empty($_FILES[$field_name]))
		{
			return array(
				200,
				array(
					'is_success'=> -2,
					'message'=>urlencode('没有文件上传'),
				),
			);
		}
		
		if(0< $_FILES[$field_name]['error'])
		{
			return array(
				200,
				array(
					'is_success'=> -3,
					'message'=>urlencode('文件上传错误'),
					'err'=>$_FILES[$field_name]['error']
				),
			);
		}

		switch($module_sn)
		{
			case '001001':#
			{
				if(400 < ($_FILES[$field_name]["size"] / 1024))
				{
					return array(
						200,
						array('is_success'=>-4,
							  'message'=> urlencode('超过了400k')
						),
					);
				}
				
				$file_dir  = 'media/'.'expression'.'/'.date("Y-m-d").'/';
			}
			break;
			case '001002':#监管机构 <20k
			{
				if(20 < ($_FILES[$field_name]["size"] / 1024))
				{
					return array(
						200,
						array('is_success'=>-4,
							  'message'=> urlencode('超过了20k')
						),
					);
				}
				$file_dir  = 'media/'.'regulators'.'/'.date("Y-m-d").'/';
			}
			break;
			case '001003':#营业执照 <400k
			{
				if(400 < ($_FILES[$field_name]["size"] / 1024))
				{
					return array(
						200,
						array('is_success'=>-4,
							  'message'=> urlencode('超过了400k')
						),
					);
				}
				$file_dir  = 'media/'.'license'.'/'.date("Y-m-d").'/';
			}
			break;
			case '001004':#机构代码证 <400k
			{
				if(400 < ($_FILES[$field_name]["size"] / 1024))
				{
					return array(
						200,
						array('is_success'=>-4,
							  'message'=> urlencode('超过了400k')
						),
					);
				}
				$file_dir  = 'media/'.'code'.'/'.date("Y-m-d").'/';
			}
			break;
			case '001005':#资质证明 <400k
			{
				if(400 < ($_FILES[$field_name]["size"] / 1024))
				{
					return array(
						200,
						array('is_success'=>-4,
							  'message'=> urlencode('超过了400k')
						),
					);
				}
				$file_dir  = 'media/'.'certificate'.'/'.date("Y-m-d").'/';
			}
			break;
			case '001006':#新闻图片(pc)
			{
				$file_dir  = 'media/'.'news_pc'.'/'.date("Y-m-d").'/';
			}
			break;
			case '001007':#新闻图片（app)
			{
				$file_dir  = 'media/'.'news_app'.'/'.date("Y-m-d").'/';
			}			
			break;
			case '001008':#logo
			{
				$file_dir  = 'media/'.'logo'.'/'.date("Y-m-d").'/';
			}			
			break;
			case '001009':#评论图片<400k
			{
				if(400 < ($_FILES[$field_name]["size"] / 1024))
				{
					return array(
						200,
						array('is_success'=>-4,
							  'message'=> urlencode('超过了400k')
						),
					);
				}
				$file_dir  = 'media/'.'comment'.'/'.date("Y-m-d").'/';
			}
			break;
			case '001010':#意见反馈<400k
			{
				if(400 < ($_FILES[$field_name]["size"] / 1024))
				{
					return array(
						200,
						array('is_success'=>-4,
							  'message'=> urlencode('超过了400k')
						),
					);
				}
				$file_dir  = 'media/'.'idea'.'/'.date("Y-m-d").'/';
			}
			break;
			case '001011':#广告
			{
				if(400 < ($_FILES[$field_name]["size"] / 1024))
				{
					return array(
						200,
						array('is_success'=>-4,
							  'message'=> urlencode('超过了400k')
						),
					);
				}
				$file_dir  = 'media/'.'bander'.'/'.date("Y-m-d").'/';
			}
			break;
            case '003001':#友情链接
            {
                /*
                if(400 < ($_FILES[$field_name]["size"] / 1024))
				{
					return array(
						200,
						array('is_success'=>-4,
							  'message'=> urlencode('超过了400k')
						),
					);
				}
                */
				$file_dir  = 'media/'.'link'.'/'.date("Y-m-d").'/';
            }
            break;
            case '003002':#登陆图片
            {
				$file_dir  = 'media/'.'login'.'/'.date("Y-m-d").'/';
            }
            break;
            case '003003':#banner图片
            {
				$file_dir  = 'media/'.'banner'.'/'.date("Y-m-d").'/';
            }
            break;
		}
		if(!is_dir(__PUBLIC__.$file_dir))
		{
			mkdir(__PUBLIC__.$file_dir);
		}
		if(!in_array($file_ext, array('jpg', 'gif', 'jpeg', 'png')))
		{
			return array(
				200,
				array(
					'is_success'=>-5,
					'message'=> urlencode('图片格式错误')
				),
			);
		}
		switch($file_ext)
		{
			case 'jpg':
				{
					$file_name = $file_dir.time().'_'.rand(1000,2000).".jpg";					
				}
				break;
			case 'gif':
				{
					$file_name = $file_dir.time().'_'.rand(1000,2000).".gif";
				}
				break;
			case 'jpeg':
				{
					$file_name = $file_dir.time().'_'.rand(1000,2000).".jpeg";
				}
				break;
			case 'png':
				{
					$file_name = $file_dir.time().'_'.rand(1000,2000).".png";
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


	public function get_list($content)
	{
		list($data, $record_count) = parent::get_list($content);
        
        $list = array();
        if($data)
        {
            foreach($data as $v)
            {
                $list[] = array(
                        'id'               => intval($v['id']),
                        'dict_sn'          => urlencode($v['dict_sn']),
                        'media_url'        => urlencode($v['media_url']),
                        'http_url'         => C('media_url_pre').$v['media_url'],
                    );  
            }
            unset($v);
        }

        return array(200, array('list'=>$list, 
                                'record_count'=>$record_count));
	}

	#
	/**
	*@@input
	*@param $media_id 
	*@@output
	*@param $media_id
	*@param $dict_sn
	*@param $media_url
	*@param $http_url
	*/
	public function get_by_id($content)
	{
		$data = $this->fill($content);

		if(!isset($data['media_id']))
		{
			return C('param_err');
		}

		$data['media_id'] = intval($data['media_id']);

		if(0>= $data['media_id'])
		{
			return C('param_fmt_err');
		}

		$list = array();
		$tmp_one = M('Media')->find($data['media_id']);
		if($tmp_one)
		{
			$list = array(
				'media_id'  => intval($tmp_one['id']),
				'dict_sn'   => urlencode($tmp_one['dict_sn']),
				'media_url' => urlencode($tmp_one['media_url']),
				'http_url'  => C('media_url_pre').$tmp_one['media_url'],
				);
		}

		return array(
			200,
			$list,
		);
	}
}
