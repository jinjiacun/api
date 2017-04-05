<?php
namespace Hqapi\Controller;
use Think\Controller;
class IndexController extends Controller {

    protected $type        = "" ; //数据类型
    protected $method      = "" ; //方法名称
    protected $in_content  = "" ; //输入参数
    protected $token       = "" ; //加密验证
    protected $handler     = null;//资源处理句柄
    protected $is_mul      = false;
    protected $debug       = 0;//0-非调试模式,1-调试模式

    public function __constract__()
    {

    }

	public function __constract($method, $in_content)
    {
    	$this->method     = $method;
    	$this->in_content = $in_content;
    }    

    public function __get($propery_name)
    {
    	if(isset($this->$propery_name))
    	{
    		return $this->$propery_name;
    	}
    	else
    		return null;
    }

    public function __set($propery_name, $value)
    {
    	if(isset($this->$propery_name))
    	{
    		$this->$propery_name = $value;
    	}
    }


    public function index(){//($type, $method=null, $content=null, $handler=null){
      header("Content-Type: text/html;charset=utf-8");      
      $obj_des = new \Org\Util\DES();
      $stime=microtime(true);
        ##get
    	if(I('get.method'))
    	{
    		$this->method = I('get.method');
    	}
    	if(I('get.content'))
    	{
    		$this->in_content = I('get.content');
    	}
    	if(I('get.token'))
    	{
			$this->token = I('get.token');
		  }
      if(I('get.debug'))
      {
        $this->debug = I('get.debug');
      }

        ##post
        if(I('post.type'))
        {
            $this->type = I('post.type');
        }
        if(I('post.method'))
        {
            $this->method = I('post.method');
        }
        if(I('post.content'))
        {
            $this->in_content = urldecode(I('post.content'));
        }
        if(I('post.handler'))
        {
            $this->handler = I('post.handler');
        }
        if(I('post.token'))
        {
			   $this->token = I('post.token');
		    }
        if(I('post.debug'))
        {
          $this->debug = I('post.debug');
        }
        if(!isset($this->method)
        || !isset($this->in_content)
        || !isset($this->token))
        {
           return $this->call_back(500, 
                            array('message'=>urlencode('参数输入不合法'))
                            );
            return;
        }
        

        //切换表前缀
        if(1 == $this->debug)
        {
          C('DB_NAME','hr_bug_test');
          C('IS_DEBUG',1);
        }
        elseif(2== $this->debug)
        {
		  C('DB_NAME','hr_bug_test_ex');
          C('IS_DEBUG',2);
		}

        /*
        if(empty($this->token))
        {
			return $this->call_back(500, 
                            array('message'=>urlencode('token为空'))
                            );
            return;
		}
		*/
        
        
        if(I('post.is_mul'))
        {
          $this->is_mul = true;
        }
        if(I('get.is_mul'))
        {
          $this->is_mul = true;
        }

        if('' == $this->method)
        {
           return $this->call_back(500,
                            array('message'=>urlencode('方法名不为空'))
                            );
        }

        if($this->method)
        {
           $this->method = str_replace("\\", '', $this->method);
           $this->method = str_replace("&quot;",'"', $this->method);
           $this->method = str_replace("&amp;", '', $this->method);
           $this->method = str_replace("'", '"', $this->method);
        }

        $_arr = array(
          'Test.get_des',
    //      'Inexposal.dynamic',
        );

        //验证参数合法性
	/*
        if($this->token !=  $obj_des->encrypt($this->method.date("Y-m-d")) && !in_array($this->method, $_arr))
        {
		  file_put_contents(__PUBLIC__."log/error".date("Y-m-d").'_'.$this->getIP().".log", sprintf("method:%s,content:%s,token:%s\r\n",$this->method,$this->in_content,$this->token), FILE_APPEND);	
          return $this->call_back(501, 
                            array('message'=>urlencode('no authorization'))
                            );
            return; 
        }
	*/

        if($this->in_content)
        {
           $this->in_content = str_replace("\\", '', $this->in_content);
           $this->in_content = str_replace("&quot;",'"', $this->in_content);
           $this->in_content = str_replace("&amp;", '', $this->in_content);
           $this->in_content = str_replace("'", '"', $this->in_content);
           $this->in_content = str_replace("$", '%', $this->in_content);
           //$this->in_content = str_replace(" ", '', $this->in_content);
        }     

        


        #访问日志
        $log_str = sprintf("begin   ip:%s   date:%s method:%s  content:%s   type:%s	debug:%s\r\n", 
                          $this->getIP(),
                          date("Y-m-d H:i:s"), 
                          $this->method,
                          $this->in_content,
                          $this->type,
                          $this->debug);
        file_put_contents(__PUBLIC__."log/request_bug_".date("Y-m-d").'_'.$this->getIP().".log", $log_str, FILE_APPEND);
    	  if(!in_array($this->type,array('text','resource')))
    	  {
			     $this->type = 'text';
		    }
        switch($this->type)
        {
            case 'resource':
                {
                    /*
                    $data = $GLOBALS[HTTP_RAW_POST_DATA];
                    if(empty($data))
                    {
                        $data = file_get_contents("php://input");
                    }
                    */
                    list($class_name, $method)= explode('.', $this->method);
                    $class_name = 'Hqapi/'.$class_name;        
                    $obj = A($class_name);
                    list($status_code, $out_content) = $obj->{$method}($this->in_content, $handler);#处理带有资源的数据信息
                    $etime=microtime(true);//获取程序执行结束的时间
                    $total=$etime-$stime;   //计算差值
                    #访问日志
                    $log_str = sprintf("end   ip:%s   date:%s	use_time:%s秒 method:%s  content:%s   type:%s   status_code:%s, data:%s\r\n\r\n", 
                                      $this->getIP(),
                                      date("Y-m-d H:i:s"), 
                                      $total,
                                      $this->method,
                                      $this->in_content,
                                      $this->type,
                                      $status_code,
                                      urldecode(json_encode($out_content))
                                      );
                    file_put_contents(__PUBLIC__."log/request_bug_".date("Y-m-d").'_'.$this->getIP().".log", $log_str, FILE_APPEND);                    
					           /*
					           A('Soapi/Apistat')->add(json_encode(array(
							         'name'=>$this->method,
							         'run_time'=>$total,
							         'type'=>1
					           )));
                     */
                    self::call_back($status_code, $out_content);
                }
                  break;
            case 'text':
                {
                    if($this->is_mul)
                    {
                      $this->method = json_decode($this->method);
                    }                    
                    if($this->is_mul)
                    {                      
                      $status_code_list = $out_content_list = array();
                      $in_content_list = json_decode($this->in_content, true);
                      foreach($this->method as $k=>$cur_method)
                      {
                         $stime=microtime(true); 
                         $tmp_content =  $in_content_list[$k];
                         $cur_content = json_encode($tmp_content);
                         unset($tmp_content);
                         list($class_name, $method)= explode('.', $cur_method);
                         $class_name = 'Hqapi/'.$class_name;        
                         $obj = A($class_name);
                         list($status_code, $out_content) = $obj->{$method}($cur_content);//,&$this->status, &$this->out_content);#处理普通数据
                         $etime=microtime(true);//获取程序执行结束的时间
                         $total=$etime-$stime;   //计算差值
                         #访问日志
                         $log_str = sprintf("end   ip:%s   date:%s use_time:%s秒 method:%s  content:%s   type:%s   status_code:%s, data:%s\r\n\r\n", 
                                        $this->getIP(),
                                        date("Y-m-d H:i:s"), 
                                        $total,
                                        $cur_method,
                                        $cur_content,
                                        $this->type,
                                        $status_code,
                                        urldecode(json_encode($out_content))
                        );
                        #file_put_contents(__PUBLIC__."log/request".date("Y-m-d").'_'.$this->getIP().".log", $log_str, FILE_APPEND);
                        //self::call_back($status_code, $out_content);
                        $status_code_list[] = $status_code;
                        $out_content_list[] = $out_content;
                      }
                      self::call_back($status_code_list, $out_content_list);
                    }
                    else
                    {
                      list($class_name, $method)= explode('.', $this->method);
                      $class_name = 'Hqapi/'.$class_name;        
                      $obj = A($class_name);
                      list($status_code, $out_content) = $obj->{$method}($this->in_content);//,&$this->status, &$this->out_content);#处理普通数据
                      $etime=microtime(true);//获取程序执行结束的时间
                      $total=$etime-$stime;   //计算差值
                      #访问日志
                      $log_str = sprintf("end   ip:%s   date:%s use_time:%s秒 method:%s  content:%s   type:%s   status_code:%s, data:%s\r\n\r\n", 
                                        $this->getIP(),
                                        date("Y-m-d H:i:s"), 
                                        $total,
                                        $this->method,
                                        $this->in_content,
                                        $this->type,
                                        $status_code,
                                        urldecode(json_encode($out_content))
                      );
                      file_put_contents(__PUBLIC__."log/request_bug_".date("Y-m-d").'_'.$this->getIP().".log", $log_str, FILE_APPEND);
                      self::call_back($status_code, $out_content);
                    }                    
                }
                break;
            default:
                {
                    echo sprintf("type:%s", 'unkown');
                }
                break;
        }
        #访问日志
        $log_str = sprintf("end   ip:%s   date:%s method:%s  content:%s   type:%s   status_code:%s, data:%s\r\n", 
                          $this->getIP(),
                          date("Y-m-d H:i:s"), 
                          $this->method,
                          json_encode($this->in_content),
                          $this->type,
                          $status_code,
                          json_encode($out_content)
                          );
        file_put_contents(__PUBLIC__."log/request_bug_".date("Y-m-d").'_'.$this->getIP().".log", $log_str, FILE_APPEND);
        $etime=microtime(true);//获取程序执行结束的时间
		$total=$etime-$stime;   //计算差值
		
		A('Soapi/Apistat')->add(json_encode(array(
							'name'=>$this->method,
							'run_time'=>$total,
							'type'=>2
					)));
    }

    public function call_back($status_code, $out_content)
    {
        $re_list = array('status_code'=>$status_code,
                         'content'    =>$out_content,
            );
        echo urldecode(json_encode($re_list));
        exit();
    }

    public static function getIP() { 
        if (@$_SERVER["HTTP_X_FORWARDED_FOR"]) 
        $ip = $_SERVER["HTTP_X_FORWARDED_FOR"]; 
        else if (@$_SERVER["HTTP_CLIENT_IP"]) 
        $ip = $_SERVER["HTTP_CLIENT_IP"]; 
        else if (@$_SERVER["REMOTE_ADDR"]) 
        $ip = $_SERVER["REMOTE_ADDR"]; 
        else if (@getenv("HTTP_X_FORWARDED_FOR"))
        $ip = getenv("HTTP_X_FORWARDED_FOR"); 
        else if (@getenv("HTTP_CLIENT_IP")) 
        $ip = getenv("HTTP_CLIENT_IP"); 
        else if (@getenv("REMOTE_ADDR")) 
        $ip = getenv("REMOTE_ADDR"); 
        else 
        $ip = "Unknown"; 
        return $ip; 
    }
}
