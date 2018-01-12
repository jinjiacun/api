<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head profile="http://gmpg.org/xfn/11">
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />

	<title>Senamion.com - multiselect2side (multiple select double side) plugin: documentation and demo page</title>
	<link rel="stylesheet" href="css/jquery.multiselect2side.css" type="text/css" media="screen" />
	<script type="text/javascript" src="js/jquery.js" ></script>
	<script type="text/javascript" src="js/jquery.multiselect2side.js" ></script>
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
  $cur_db_host=$_GET['db_host'];
  $cur_db_user=$_GET['db_user'];
  $cur_db_pass=$_GET['db_pass'];
  $cur_db_name=$_GET['db_name'];

  $conn=mysqli_Connect($cur_db_host,$cur_db_user,$cur_db_pass,$cur_db_name);
  //$conn=mysqli_connect("200.31.155.63:3306", "root", "Cfets@123","testlink");
  $conn->query("SET NAMES utf8");
 
  $cpid=$_GET["cpid"];//proj id
  $cbid=$_GET["cbid"];//build id
  $cstatus=$_GET["cstatus"];//status id
  
  $tcArrayMsg=array();
  $curArray=array('tcid'=>'','tcname'=>'');
  $curbuildSql="";
  $tcresult="";
  
  if($cstatus!="n"){
//        $curbuildSql="SELECT DISTINCT executions.tcversion_id as tcid FROM executions
        $curbuildSql="SELECT DISTINCT executions.tcversion_id as tcid FROM ".$conn->get_table('executions')."
        WHERE executions.build_id='$cbid' AND executions.status='$cstatus'";
        $tcresult= $conn->query($curbuildSql);
        if(!mysqli_num_rows($tcresult) < 1){
            while(($row=mysqli_fetch_array($tcresult))==true){
                $curtcid = $row['tcid'];
                 
                $acttcid=$curtcid-1;
          
   //             $tcNameSql="SELECT nodes_hierarchy.name as tcname FROM nodes_hierarchy WHERE nodes_hierarchy.id ='$acttcid' ";
                $tcNameSql="SELECT nodes_hierarchy.name as tcname FROM ".$conn->get_table('nodes_hierarchy')." WHERE nodes_hierarchy.id ='$acttcid' ";
                $tcnameresult= $conn->query($tcNameSql);
                $rowName=mysqli_fetch_array($tcnameresult);
                $tcName=$rowName['tcname'];
                if($tcname!==""){
                    $curArray['tcid']=$curtcid;
                    $curArray['tcname']=$tcName;
                    $tcArrayMsg[]=$curArray;
                }
                    
              
            }  
        }
  }else{
      
//       $curbuildSql="SELECT tcv.tcversion_id as tcid
//                     from testplan_tcversions tcv
//                     where 
//                     (tcv.build_id, tcv.tcversion_id) not in
//                     (select build_id, tcversion_id from executions)
//                     and tcv.build_id = '$cbid'";
      $curbuildSql="SELECT tcv.tcversion_id as tcid
      from ".$conn->get_table('testplan_tcversions')." tcv
      where
      (tcv.build_id, tcv.tcversion_id) not in
      (select build_id, tcversion_id from ".$conn->get_table('executions').")
      and tcv.build_id = '$cbid'";
      $tcresult= $conn->query($curbuildSql);
      if(!mysqli_num_rows($tcresult) < 1){
          while(($row=mysqli_fetch_array($tcresult))==true){
              $curtcid = $row['tcid'];
              $acttcid=$curtcid-1;
  //            $tcNameSql="SELECT nodes_hierarchy.name as tcname FROM nodes_hierarchy WHERE nodes_hierarchy.id ='$acttcid' ";
              $tcNameSql="SELECT nodes_hierarchy.name as tcname FROM ".$conn->get_table('nodes_hierarchy')." WHERE nodes_hierarchy.id ='$acttcid' ";
              $tcnameresult= $conn->query($tcNameSql);
              $rowName=mysqli_fetch_array($tcnameresult);
              $tcName=$rowName['tcname'];

               if($tcname!==""){
                    $curArray['tcid']=$curtcid;
                    $curArray['tcname']=$tcName;
                    $tcArrayMsg[]=$curArray;
                } 
             
          }
      }
  }  
  if(count($tcArrayMsg)!=0 ||count($tcArrayMsg)!=1)
  {
      foreach($tcArrayMsg as $k=>$val){
          $tcname=$val['tcname'];
          $tcid=$val['tcid'];
          echo "<option title=$tcname value=$tcid>$tcname</option>";
      }
  }else{
      echo "<option value=$cpid>无</option>";
  } 
  ?>
</select>
</div>
	
</body>
</html>
