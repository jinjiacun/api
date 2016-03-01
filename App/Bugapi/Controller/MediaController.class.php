<?php
namespace Bugapi\Controller;
use Bugapi\Controller;
use Think\Exception;
include_once(dirname(__FILE__).'/BaseController.class.php');

/**
--媒体管理--
*/
/*
##--------------------------------------------------------##
public function get_swf_by_doc($content)             doc生成swf接口
@@input
@@param $doc_id
@@output
@param $swf_url
*/

class MediaController extends BaseController {
	/**	
	CREATE TABLE IF NOT EXISTS `hr_media` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `dict_sn` varchar(11) DEFAULT '0' COMMENT '媒体类型(1-商品)',
  `media_url` varchar(255) DEFAULT NULL COMMENT '媒体相对路径',
  `width` int(11) DEFAULT '0' COMMENT '图片宽度',
  `height` int(11) DEFAULT '0' COMMENT '图片高度',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='媒体表' AUTO_INCREMENT=1 ;
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
			case '011001':#resume <400k
			{	
				$file_dir  = 'media/'.'resume'.'/'.date("Y-m-d").'/';
			}
		}
		if(!is_dir(__PUBLIC__.$file_dir))
		{
			mkdir(__PUBLIC__.$file_dir);
		}
		if(!in_array($file_ext, array('jpg', 'gif', 'jpeg', 'png','pdf','doc','docx','xls','pdf','xlsx','zip','rar')))
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
			case 'pdf':
			      {
				       $file_name = $file_dir.time().'_'.rand(1000,2000).".pdf";
			      }
			      break;
			case 'doc':
			     {
			              $file_name = $file_dir.time().'_'.rand(1000,2000).".doc";
			     }
			     break;
			case 'docx':
			    {
			              $file_name = $file_dir.time().'_'.rand(1000,2000).".docx";
			    }
			break;
			case 'xls':
			   {
			              $file_name = $file_dir.time().'_'.rand(1000,2000).".xls";
			   }
			break;
			case 'pdf':
				{
						 $file_name = $file_dir.time().'_'.rand(1000,2000).".pdf";
				}
			break;
			case 'xlsx':
				{
						$file_name = $file_dir.time().'_'.rand(1000,2000).".xlsx";
				}
			break;
			case 'zip':
				{
						$file_name = $file_dir.time().'_'.rand(1000,2000).".zip";
				}
			break;
			case 'rar':
				{
						$file_name = $file_dir.time().'_'.rand(1000,2000).".rar";
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
	
	
	/**
	 *
	 @@input
	 @@param $doc_id
	 @@output
	 @param $swf_url
	 * */
	public function get_swf_by_doc($content)                 #doc生成swf接口
	{
		$data = $this->fill($content);
	
		if(!isset($data['doc_id']))
		{
			return C('param_err');
		}
	
		$data['doc_id'] = intval(trim($data['doc_id']));
	
		if(0> $data['doc_id'])
		{
			return C('param_fmt_err');
		}
	
		$swf_http_url = '';//要返回的url
		$tmp_one = null;
		#检查文档是否存在
		if(!($tmp_one = M('media')->field("media_url")->find($data['doc_id'])))
		{
		return array(
				200,
				array(
						'is_success'=>-4,
						'message'=>urlencode('不存在此媒体'),
					)
				);
		}
		
		#物理路径
		$old_media_physical_path = C('media_physical_path').$tmp_one['media_url'];
		$pdf_media_physical_path = '';
		$swf_media_physical_path = '';
		
		#转换pdf,swf路径
		$tmp_file_info = pathinfo($old_media_physical_path);
		if(!in_array($tmp_file_info['extension'],array('doc', 'docx')))
		{
			return array(
					200,
					array(
							'is_success'=>-3,
							'message'=>urlencode('文件格式不正确'),
					)
			);
		}
		else
		{
			$path_pre = substr($old_media_physical_path,0,-1*strlen($tmp_file_info['extension']));
			$pdf_media_physical_path = $path_pre.'pdf';
			$swf_media_physical_path = $path_pre.'swf';
		}
		
		#判定pdf文件是否存在
		if(!file_exists($pdf_media_physical_path))
		{
			#生成pdf
			try{
				word2pdf('file:///'.$old_media_physical_path, 'file:///'.$pdf_media_physical_path);
			}
			catch(Exception $e)
			{
				return array(
					200,
					array(
					'is_success'=>-2,
					'message'=>urlencode($e->getMessage()),
					),
				);
			}
		}
		
		#判定swf文件是否存在
		if(!file_exists($swf_media_physical_path))
		{
			#生成swf
			//使用pdf2swf转换命令
			$command = C('swf_tool_path')."  -t \"" . $pdf_media_physical_path . "\" -o  \"" . $swf_media_physical_path . "\" -s flashversion=9 ";
			//创建shell对象
			if(!pdf2swf($command))
			{
				return array(
					200,
					array(
					'is_success'=>-1,
					'message'=>urlencode('生成swf失败'),
					),
				);
			}
		}
	
		#处理swf物理路径到网络路径转换
		$swf_media_physical_path = substr($swf_media_physical_path, strlen(C('media_physical_path')));
	
		return array(
			200,
			array(
				'is_success'=>0,
				'url'=>C('media_url_pre').$swf_media_physical_path
			)		
		);
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
}
