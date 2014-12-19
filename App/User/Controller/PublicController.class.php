<?php
namespace User\Controller;
use Think\Controller;
class PublicController extends Controller {
    public function index(){
        $this->display();
    }

	public function top(){
        $this->display();
    }


    public function left(){
        $this->display();
    }

    public function main(){
        $this->display();
    }
}