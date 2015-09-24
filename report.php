<?php
error_reporting(0);
session_start();
$pageID='report';
include './widgets/head.php';
require_once('./tools/Kodbc.class.php');
$Kodbc = new Kodbc('./Database/CheckTask.xml');
$taskid = $_GET['taskid'];
if(!$taskid){
    $taskid=$Kodbc->getNewestId();
}
$tasks = $Kodbc->getById($taskid);

?>
    <div class="page-header" style="width: 960px;margin: 60px auto 0 auto">
        <h1>结果报告 <small>查寝情况展示<span class="label label-warning"><?php echo '('.$tasks['id'].') '.$tasks['update'] ?></span></small></h1>
    </div>
<div class="panel center-block panel-default" style="width: 960px;">
    <div class="panel-heading">
        查寝情况报告
    </div>
    <div class="panel-body" id="checkStatus">
        <p>下表是当前查寝情况报告</p>
        <?php
        $Kodbc2 = new Kodbc('./Database/subTask.xml');
        $Kodbc3 = new Kodbc('./Database/Dorms.xml');


        /*合并查寝结果集*/
        $finalRes=json_decode('{}');//总结果集
        $dormsOfMainTask = explode(',',$tasks['dorms']);//[1,2,3,4]
        foreach($dormsOfMainTask as $val){//初始化结果集
            $finalRes->{$val} = '0::';
        }

        $res = $Kodbc2->getByAttr('taskid',$taskid);
        foreach($res as $result){
            $_res = json_decode($result['result']);//已分配子任务的评价结果集合
            foreach($_res as $key=>$val){//更新已打分的结果集
                $finalRes->{$key} = $val;
            }
        }

        if($tasks['stat']!=0){/*表示查寝还可能还在进行，则需要刷新结果集*/
            $_temp_status='ok';//表示已经好了
            foreach($finalRes as $key => $val){
                if(substr($val,0,stripos($val,'::'))==''||substr($val,0,stripos($val,'::'))=='0'){
                    /*检测到有未完成项就跳出*/
                    $_temp_status = 'bug';//设置标志位
                    break;
                }
            }
            if($_temp_status=='ok'){
                /*全部完成则更新状态*/
                $Kodbc->updateItem($taskid, array('stat' => '0'));
            }

        }


        /*展示查寝结果*/
        foreach($finalRes as $key => $val){
            $dorm = $Kodbc3->getById($key);
            /*判断得分并分割评价*/
            if($val=='0'){
                $grad = 0;
                $remark = '';
            }else{
                $grad = substr($val,0,stripos($val,'::'));
                $remark = substr($val,stripos($val,'::')+2);
            }
            ?>
            <span data-grad="<?php echo $grad ?>" data-rmk="<?php echo $remark ?>"
            <?php switch($grad){
                case '1': ?> class="label label-success" <?php ;break;
                case '2': ?> class="label label-info" <?php ;break;
                case '3': ?> class="label label-warning" <?php ;break;
                case '4': ?> class="label label-danger" <?php ;break;
                default : ?> class="label label-default" <?php ;break;
            } ?> style="float:left;border:1px solid grey;margin: 5px;padding: 15px;">
                <?php echo $dorm['dormname'] ?>
            </span>
        <?php
        }
        ?>
        <script>
            var field = [];
            var title = ['还未检查','优秀','良好','中等','差劲'];
            var grads = ['0','1','2','3','4'];
            for(var i=0;i<5;i++){
                var unchecked = document.createElement('fieldset');
                unchecked.setAttribute('style','margin-top:40px;');
                unchecked.appendChild(document.createElement('legend'));
                unchecked.querySelector('legend').innerHTML=title[i];
                $('[data-grad=\"'+grads[i]+'\"]').each(function(){
                    unchecked.appendChild(this);
                });
                document.querySelector('#checkStatus').appendChild(unchecked);
            }
        </script>
    </div>
    <div class="panel-body">
        <button type="button" class="btn btn-success pull-right" onclick="if(confirm('是否确认导出？'))location = './tools/fileExport.php?taskid=<?php echo $taskid?>'">导出查寝结果报告</button>
    </div>
</div>

<?php
include './widgets/foot.php';
?>