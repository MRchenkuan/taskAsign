<?php
error_reporting(0);

////引入PHPExcel相关文件

require_once "./phpexcel/PHPExcel.php";
require_once './phpexcel/PHPExcel/IOFactory.php';
require_once './phpexcel/PHPExcel/Writer/Excel5.php';

function getCol($num){
    $arr=array(0=>'Z',1=>'A',2=>'B',3=>'C',4=>'D',5=>'E',6=>'F',7=>'G',8=>'H',9=>'I',10=>'J',11=>'K',12=>'L',13=>'M',14=>'N',15=>'O',16=>'P',17=>'Q',18=>'R',19=>'S',20=>'T',21=>'U',22=>'V',23=>'W',24=>'X',25=>'Y',26=>'Z');
    if($num==0) return '';
    return getCol((int)(($num-1)/26)).$arr[$num%26];
}

/*****************数据准备*******************************/
/*合并查寝结果*/
$taskid=$_GET['taskid'];
if(!$taskid)$taskid='19';

require_once "./Kodbc.class.php";
$kodbc = new Kodbc('../Database/CheckTask.xml');
$kodbc2 = new Kodbc('../Database/subTask.xml');
$kodbc3 = new Kodbc('../Database/Dorms.xml');
$mainTask = $kodbc->getById($taskid);//总任务
$subTasks = $kodbc2->getByAttr('taskid',$taskid);//所有分任务

$allDroms = $mainTask['dorms'];//所有宿舍 1，2，3，4
$allDroms = explode(',',$allDroms);//所有宿舍 [1,2,3,4]


/*合并结果集为数组 [dormid => res]*/
$_finalRes=[];// distinct ['153' => ['3::一般','查寝者']]
foreach($subTasks as $subTask){
    $subTask_res = json_decode($subTask['result']);
    foreach($subTask_res as $key=>$val){
        if($val==0)$val='0::';
        $_finalRes[$key]=[$val,(string)$subTask['trustee']];
    }
}

/*构建大清单*/
$finalRes = [];//总导出集合
foreach($allDroms as $dormid){
    $dorm = $kodbc3->getById($dormid);
    $dormname = $dorm['dormname'];
    $dormGrad = $_finalRes[$dormid][0];//总评分 3::一般
    $checker = $_finalRes[$dormid][1];// 查寝者 '查寝者'


    $Grad= substr($dormGrad,0,stripos($dormGrad,'::'));
    switch($Grad){
        case '1':$Grad='优秀';break;
        case '2':$Grad='良好';break;
        case '3':$Grad='中等';break;
        case '4':$Grad='差劲';break;
        default:$Grad='未检查';break;
    }
    $Remark = substr($dormGrad,stripos($dormGrad,'::')+2);

    $dormRes = array(
        substr($dormname,0,stripos($dormname,'-')),//楼栋
        substr($dormname,stripos($dormname,'-')+1),//宿舍
        '',//学院
        '',//班级
        '',//成员
        $dorm['type']==1?'男':'女',//性别
        '',//辅导员
        $Remark,//寝室问题
        $Grad,//评分
        $checker//查寝者
    );
    array_push($finalRes,$dormRes);
}
/*****************数据准备 --- 结束*******************************/

//新建
$resultPHPExcel	= new PHPExcel();

//设置参数
$head = ['楼栋','宿舍','学院','班级','成员','性别','辅导员','寝室问题','评分','查寝人'];
//设置标题
$tableTitle='标题';
$resultPHPExcel->getActiveSheet()->mergeCells('A1:'.getCol(count($head)).'1');
$resultPHPExcel->getActiveSheet()->setCellValue('A1', $tableTitle);
$resultPHPExcel->getActiveSheet()->getStyle('A1')
    ->getAlignment()
    ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

//设置表头
for($i=0;$i<count($head);$i++){
    $resultPHPExcel->getActiveSheet()->setCellValue(getCol($i+1).'2', $head[$i]);
}

$i = 3;

foreach($finalRes as $item){
    for($j=0;$j<count($item);$j++){
        $resultPHPExcel->getActiveSheet()->setCellValue(getCol($j+1).$i, $item[$j]);
    }
    $i ++;
}
//设置导出文件名
$outputFileName = 'checkResult.xls';
$xlsWriter = new PHPExcel_Writer_Excel5($resultPHPExcel);
header("Content-Type: application/force-download");
header("Content-Type: application/octet-stream");
header("Content-Type: application/download");
header('Content-Disposition:inline;filename="'.$outputFileName.'"');
header("Content-Transfer-Encoding: binary");
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Pragma: no-cache");
//
$xlsWriter->save( "php://output" );
