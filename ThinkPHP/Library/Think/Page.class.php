<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2009 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// |         lanfengye <zibin_5257@163.com>
// +----------------------------------------------------------------------
namespace Think;
class Page {
    
    // 分页栏每页显示的页数
    public $rollPage = 10;
    // 页数跳转时要带的参数
    public $parameter  ;
    // 分页URL地址
    public $url     =   '';
    // 默认列表每页显示行数
    public $listRows = 20;
    // 起始行数
    public $firstRow    ;
    // 分页总页面数
    protected $totalPages  ;
    // 总行数
    protected $totalRows  ;
    // 当前页数
    protected $nowPage    ;
    // 分页的栏的总页数
    protected $coolPages   ;
    // 可进行设置的每页显示条数
    protected $pageSizes  =  array(20,50,100);
    
	// 默认分页变量名
	protected $config  =    array('header'=>'共 %totalRow% 条记录','prev'=>'<<','next'=>'>>','first'=>'1...','last'=>'...%totalPages%','theme'=>'<div class="pull-left"><form action="" method="get">%header%  %nowPage%/%totalPage%页  %findPage%</form></div>  <div class="pull-right"><ul class="pagination">%upPage%  %linkPage% %downPage% </div>');
    protected $varPage;

    /**
     * 架构函数
     * @access public
     * @param array $totalRows  总的记录数
     * @param array $listRows  每页显示记录数
     * @param array $parameter  分页跳转的参数
     */
    public function __construct($totalRows,$listRows='',$parameter='',$url='') {
        $this->totalRows    =   $totalRows;
        $this->parameter    =   $parameter;
        $this->varPage      =   C('VAR_PAGE') ? C('VAR_PAGE') : 'p' ;
        if(!empty($listRows)) {
            $this->listRows =   intval($listRows);
        }
        $this->totalPages   =   ceil($this->totalRows/$this->listRows);     //总页数
        $this->coolPages    =   ceil($this->totalPages/$this->rollPage);
        $this->nowPage      =   !empty($_GET[$this->varPage])?intval($_GET[$this->varPage]):1;
        if($this->nowPage<1){
            $this->nowPage  =   1;
        }elseif(!empty($this->totalPages) && $this->nowPage>$this->totalPages) {
            $this->nowPage  =   $this->totalPages;
        }
        $this->firstRow     =   $this->listRows*($this->nowPage-1);
        if(!empty($url))    $this->url  =   $url; 
    }

    public function setConfig($name,$value) {
        if(isset($this->config[$name])) {
            $this->config[$name]    =   $value;
        }
    }

    /**
     * 分页显示输出
     * @access public
     */
    public function show() {

        if(0 == $this->totalRows) return '';
        $p              =   $this->varPage;
        $nowCoolPage    =   ceil($this->nowPage/$this->rollPage);

        // 分析分页参数
        if($this->url){
            $depr       =   C('URL_PATHINFO_DEPR');
            $url        =   rtrim(U('/'.$this->url,'',false),$depr).$depr.'__PAGE__';
        }else{
            if($this->parameter && is_string($this->parameter)) {
                parse_str($this->parameter,$parameter);
            }elseif(is_array($this->parameter)){
                $parameter      =   $this->parameter;
            }elseif(empty($this->parameter)){
                unset($_GET[C('VAR_URL_PARAMS')]);
                $var =  !empty($_POST)?$_POST:$_GET;
                if(empty($var)) {
                    $parameter  =   array();
                }else{
                    $parameter  =   $var;
                }
            }
            $parameter[$p]  =   '__PAGE__';
            $url            =   U('',$parameter);
        }

        //上下翻页字符串
        $upRow          =   $this->nowPage-1;
        $downRow        =   $this->nowPage+1;
        if ($upRow>0){
            $upPage     =    "<li><a href='".str_replace('__PAGE__',$upRow,$url)."'>".$this->config['prev']."</a></li>";
        }else{
            $upPage     =    '';
        }

        if ($downRow <= $this->totalPages){
            $downPage   =   "<li><a href='".str_replace('__PAGE__',$downRow,$url)."'>".$this->config['next']."</a></li>";
        }else{
            $downPage   =   '';
        }
        // << < > >>
        if($nowCoolPage == 1){
            $theFirst   =   '';
            $prePage    =   '';
			
			$findPage = '';
        }else{
            $preRow     =   $this->nowPage-$this->rollPage;
            $prePage    =   "<li><a href='".str_replace('__PAGE__',$preRow,$url)."' >上".$this->rollPage."页</a></li>";
            $theFirst   =   "<li><a href='".str_replace('__PAGE__',1,$url)."' >".$this->config['first']."</a></li>";
			
			
        }
        if($nowCoolPage == $this->coolPages){
            $nextPage   =   '';
            $theEnd     =   '';
			
			
        }else{
            $nextRow    =   $this->nowPage+$this->rollPage;
            $theEndRow  =   $this->totalPages;
            $nextPage   =   "<li><a href='".str_replace('__PAGE__',$nextRow,$url)."' >下".$this->rollPage."页</a></li>";
            $theEnd     =   "</li><a href='".str_replace('__PAGE__',$theEndRow,$url)."' >".$this->config['last']."</a></li>";

        }
        // 1 2 3 4 5
        $linkPage = "";
        for($i=1;$i<=$this->rollPage;$i++){
            $page       =   ($nowCoolPage-1)*$this->rollPage+$i;
            if($page!=$this->nowPage){
                if($page<=$this->totalPages){
                    $linkPage .= "<li><a href='".str_replace('__PAGE__',$page,$url)."'>".$page."</a></li>";
                }else{
                    break;
                }
            }else{
                if($this->totalPages != 1){
                    $linkPage .= "<li><span class='active'>".$page."</span></li>";
                }
            }
        }
		
		if(1 == $this->totalPages){
			$findPage = '';
		}else{
			$findPage = '跳转至：&nbsp;<input type="text" name="p" onkeyup="value=value.replace(/[^\d\x\X]/g,\'\')" maxlength="5" size="3" value=""/>&nbsp;&nbsp;&nbsp;&nbsp;<input type="submit"  value="确定" />';
		}
		// +--------------------------------------------------------------
		//设置每页显示条数
		/*
		$setPageSizeStr = '<li><span>每页显示<select onchange="var data = {\'pagesize\': $(this).val()};$.get(\'/Area/setPageSize\', data, function(){window.location.href=window.location.href;window.location.reload;});" name="pageSize" id="pageSize">';
		
		foreach($this->pageSizes as $val){
			$setPageSizeStr .= '<option value="'.$val.'" '.($val==$this->listRows?'selected=selected':'').'>'.$val.'</option>';
		}
		
		$setPageSizeStr .= '</select></span></li>';
        */
		// +---------------------------------------------------------------
		
        $pageStr     =   str_replace(
            array('%header%','%nowPage%','%totalRow%','%totalPage%','%upPage%','%downPage%','%first%','%prePage%','%linkPage%','%nextPage%','%end%','%findPage%'),
            array($this->config['header'],$this->nowPage,$this->totalRows,$this->totalPages,$upPage,$downPage,$theFirst,$prePage,$linkPage,$nextPage,$theEnd,$findPage),$this->config['theme']);
			
        return "{$pageStr}";
    }

}