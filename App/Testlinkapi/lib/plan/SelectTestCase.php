<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head profile="http://gmpg.org/xfn/11">
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />

	<title>Senamion.com - multiselect2side (multiple select double side) plugin: documentation and demo page</title>
	<link rel="stylesheet" href="../../gui/themes/multiselectside/css/jquery.multiselect2side.css" type="text/css" media="screen" />
	<script type="text/javascript" src="../../gui/themes/multiselectside/js/jquery.js" ></script>
	<script type="text/javascript" src="../../gui/themes/multiselectside/js/jquery.multiselect2side.js" ></script>
	<script type="text/javascript">
		$().ready(function() {
			$('#searchable').multiselect2side({
				search: "待选区: ",	
				labelsx: '',
				labeldx: '已选择'
			});
			$("#liOption").multiselect2side({
				selectedPosition: 'right',
				moveOptions: false,
				labelsx: '待选区',
				labeldx: '已选区'
			}); 
			$('#first').multiselect2side({
				optGroupSearch: "Group: ",
				search: "<img src='img/search.gif' />"
			});

			var dk = $(window.parent.document).find("#tclistFrame").attr("src");
             
			 var arrfirst =dk.split('?');
			 var srclist=arrfirst[1].split('&');

             var tcidstr=srclist[4].split('=');
             var tctxtstr=srclist[3].split('=');
			 
             var tcidlist=tcidstr[1].split(',');
             var tctxtlist=tctxtstr[1].split(',');
             
             if(tcidlist.length>0){
            	 for(var i=0;i<tcidlist.length;i++){
                	 var curtcid = tcidlist[i].replace(/\s+/g,"");
                	 if(curtcid!=""){
                		 $("#searchablems2side__dx").append('<option title='+tctxtlist[i]+' value='+tcidlist[i]+'>'+tctxtlist[i]+'</option>');
                    	 }
                	}
             }
			
		});
		
	</script>
</head>
<body>
<div id="tclist" style="width:800px;height:300px" >
<select name="searchable[]" id='searchable' multiple='multiple' >
  <?php  

    require('../../config.inc.php');
    require_once("common.php");
    
    testlinkInitPage($db,true,false,null);
  
    if (isset($_REQUEST['cpid']) && isset($_REQUEST['cstatus']) &&
        isset($_REQUEST['cbid']) && $_REQUEST['cbid'] != "" && $_REQUEST['cbid'] != 0)
    {
        $cpid = $_REQUEST['cpid'];                //proj id
        $cbid = $_REQUEST['cbid'];                //build id
        $cstatus = $_REQUEST['cstatus'];          //status id
        
        
      
        $tcArrayMsg=array();
        $curArray=array('tcid'=>'','tcname'=>'');
        $curbuildSql="";
        $tcresult="";
      
        if($cstatus != "n")
        {
            $build_ex_sql = "select ex.tcversion_id as id, ex.status, ex.execution_ts, nh2.name from " .
    	      $db->get_table('executions') . " ex " .
              " inner join " . $db->get_table('nodes_hierarchy') . " nh on nh.id = ex.tcversion_id " .
              " inner join " . $db->get_table('nodes_hierarchy') . " nh2 on nh2.id = nh.parent_id " .
              " where ex.build_id = " . $cbid . 
              " order by id desc, execution_ts asc ";
            
            $result = $db->fetchRowsIntoMap($build_ex_sql, 'id');
            
            $cur_tc_id = 0;
            if (count($result, COUNT_NORMAL) > 0)
            {
                foreach ($result as $id => $row)
                {
                    if ($id == $cur_tc_id || $row['status'] != $cstatus)
                    {
                        // muti executions, ignore earlier
                        $cur_tc_id = $id;
                        continue;
                    }
                    
                    
                    $cur_tc_id = $row['id'];
                    $cur_tc_name = $row['name'];
                     
                    if ($cur_tc_name != "")
                    {
                        $curArray['tcid'] = $cur_tc_id;
                        $curArray['tcname'] = $cur_tc_name;
                        $tcArrayMsg[]=$curArray;
                    }
                }
            }
        }
        else
        {
            $no_ex_sql = "select tcv.tcversion_id as id, nh2.name from " . 
                $db->get_table('testplan_tcversions') . " tcv " .
                " inner join " . $db->get_table('nodes_hierarchy') . " nh on nh.id = tcv.tcversion_id " .
                " inner join " . $db->get_table('nodes_hierarchy') . " nh2 on nh2.id = nh.parent_id " .
                " where (tcv.build_id, tcv.tcversion_id) not in " .
                " (select build_id, tcversion_id from " . 
                $db->get_table('executions') . ") " .
                " and tcv.build_id = " . $cbid;
                
            $result = $db->fetchRowsIntoMap($no_ex_sql, 'id');
            $cur_tc_id = 0;
            if (count($result, COUNT_NORMAL) > 0)
            {
                foreach ($result as $id => $row)
                {
                    $cur_tc_id = $row['id'];
                    $cur_tc_name = $row['name'];
                         
                    if ($cur_tc_name != ""){
                        $curArray['tcid'] = $cur_tc_id;
                        $curArray['tcname'] = $cur_tc_name;
                        $tcArrayMsg[]=$curArray;
                    }
                }
            }
        }  
    
        if (count($tcArrayMsg) > 0)
        {
            foreach($tcArrayMsg as $k=>$val)
            {
                $tcname=$val['tcname'];
                $tcid=$val['tcid'];
                echo "<option title=$tcname value=$tcid>$tcname</option>";
            }
        }
        else
        {
            echo "<option value=$cpid>无</option>";
        } 
    }
  ?>
</select>
</div>
	
</body>
</html>
