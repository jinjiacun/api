<?php
namespace Azureapi\Controller;
use Think\Controller;
class IndexController extends Controller {

    protected $type        = "" ; //数据类型
	  protected $method      = "" ; //方法名称
    protected $in_content  = "" ; //输入参数
    protected $handler     = null;//资源处理句柄

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
        ##get
    	if(I('get.method'))
    	{
    		$this->method = I('get.method');
    	}
    	if(I('get.content'))
    	{
    		$this->in_content = I('get.content');
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
            $this->in_content = I('post.content');
        }
        if(I('post.handler'))
        {
            $this->handler = I('post.handler');
        }

        if(!isset($this->method)
        || !isset($this->in_content))
        {
           return $this->call_back(500,
                            array('message'=>urlencode('参数输入不合法'))
                            );
            return;
        }

        if('' == $this->method)
        {
           return $this->call_back(500,
                            array('message'=>urlencode('方法名不为空'))
                            );
        }

        if($this->in_content)
        {
           $this->in_content = str_replace("\\", '', $this->in_content);
           $this->in_content = str_replace("&quot;",'"', $this->in_content);
           $this->in_content = str_replace("&amp;", '', $this->in_content);
           $this->in_content = str_replace("'", '"', $this->in_content);
           $this->in_content = str_replace("$", "%", $this->in_content);
        }

        $ip = $this->getIP().'_';
        #访问日志
        $log_str = sprintf("begin   ip:%s   date:%s method:%s  content:%s   type:%s\r\n",
                          $this->getIP(),
                          date("Y-m-d H:i:s"),
                          $this->method,
                          $this->in_content,
                          $this->type);
        file_put_contents(__PUBLIC__."log/request_azure_".$ip.date("Y-m-d").".log", $log_str, FILE_APPEND);

        list($class_name, $method)= explode('.', $this->method);
        $class_name = 'Azureapi/'.$class_name;
    	  $obj = A($class_name);
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
                    list($status_code, $out_content) = $obj->{$method}($this->in_content, $handler);#处理带有资源的数据信息
                    #访问日志
                    $log_str = sprintf("end   ip:%s   date:%s method:%s  content:%s   type:%s   status_code:%s, data:%s\r\n\r\n",
                                      $this->getIP(),
                                      date("Y-m-d H:i:s"),
                                      $this->method,
                                      $this->in_content,
                                      $this->type,
                                      $status_code,
                                      urldecode(json_encode($out_content))
                                      );
                    file_put_contents(__PUBLIC__."log/request_azure_".$ip.date("Y-m-d").".log", $log_str, FILE_APPEND);
                    self::call_back($status_code, $out_content);
                }
                  break;
            case 'text':
                {
                    $stime = microtime(true);
                    list($status_code, $out_content) = $obj->{$method}($this->in_content);//,&$this->status, &$this->out_content);#处理普通数据
                    $etime = microtime(true);
                    $total = $etime - $stime;
                    #访问日志
                    $log_str = sprintf("end   ip:%s   date:%s use:%s秒 method:%s  content:%s   type:%s   status_code:%s, data:%s\r\n\r\n",
                                      $this->getIP(),
                                      date("Y-m-d H:i:s"),
                                      $total,
                                      $this->method,
                                      $this->in_content,
                                      $this->type,
                                      $status_code,
                                      urldecode(json_encode($out_content))
                                      );
                    file_put_contents(__PUBLIC__."log/request_azure_".$ip.date("Y-m-d").".log", $log_str, FILE_APPEND);
                    self::call_back($status_code, $out_content);
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
        file_put_contents(__PUBLIC__."log/request_azure_".$ip.date("Y-m-d").".log", $log_str, FILE_APPEND);
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
