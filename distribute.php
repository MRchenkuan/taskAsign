<?php
error_reporting(0);
session_start();
$pageID='distribute';
include './widgets/head.php';
require_once('./tools/Kodbc.class.php');
$Kodbc = new Kodbc('./Database/CheckTask.xml');
$taskid = $_GET['taskid'];
if(!$taskid){
    $taskid=$Kodbc->getNewestId();
}
$tasks = $Kodbc->getById($taskid);



$Kodbc2 = new Kodbc('./Database/Dorms.xml');
$Kodbc3 = new Kodbc('./Database/subTask.xml');
$dorms = $tasks['dorms'];//dorms 的id集合 string 1,2,3,4,5
$dorms = explode(",",$dorms);//dorms 的id集合 array

/*合并查寝结果集*/
$res = $Kodbc3->getByAttr('taskid',$taskid);
$finalRes=json_decode('{}');
foreach($res as $result){
    $_res = json_decode($result['result']);
    foreach($_res as $key=>$val){
        if($val=='0'){
            /*此处要额外设置0，因为‘0’按照下述方式截取为空*/
            $finalRes->{$key}='0';
        }else{
            $finalRes->{$key} = substr($val,0,stripos($val,"::"));
        }
    }
}

/*组成结果数据*/
for($i=0;$i<count($dorms);$i++){
    $dorms[$i]= $Kodbc2->getById($dorms[$i]);
} ?>
    <div class="page-header" style="width: 960px;margin: 60px auto 0 auto">
        <h1>任务分配 <small>分配查寝任务到学生 <span class="label label-warning"><?php echo '('.$tasks['id'].') '.$tasks['update'] ?></span></small></h1>
    </div>
    <!-- 寝室提示 -->
    <div class="panel panel-default center-block" style="width: 960px;">
        <div class="panel-heading">待查寝室</div>
        <div class="panel-body">
            <?php
            $dormChecker_Selectable = false;//是否可选
            $dormChecker_Data = $dorms;//数据
            $dormChecker_FinalRes = $finalRes;
            include './widgets/dormsChecker.php'
            ?>
        </div>
    </div>
    <!-- 寝室提示 -结束 -->

    <!--新建任务-->
    <div class="panel panel-default center-block" style="width: 960px;margin-top: 10px;">
        <div class="panel-heading">待查寝室</div>
        <div class="panel-body">
            <p>下面是分配的查寝者</p>
            <button type="button" class="btn btn-success" data-toggle="modal" data-placement="top" title="新增子任务" data-target="#taskEditor">新增子任务</button>

        </div>
        <script src="js/tools.js"></script>
        <table class="table">
            <tr><td>日期</td><td>责任人</td><td>查寝结果</td><td>备注</td><td>操作</td></tr>
            <?php
            $subTasks = $Kodbc3->getByAttr('taskid',$taskid);
            /*排序*/
            usort($subTasks, function($a, $b) {
                $al = (int)$a['id'];
                $bl = (int)$b['id'];
                if ($al == $bl)
                    return 0;
                return ($al > $bl) ? -1 : 1;
            });
            foreach($subTasks as $items){?>
                <tr>
                    <td><?php echo $items['update']?></td>
                    <td><?php echo $items['trustee']==''?'暂未分配':$items['trustee'] ?></td>
                    <td><?php switch($items['stat']){
                            case '1':echo "<span class=\"label label-success\">正在进行中...</span>";break;
                            case '0':echo "<span class=\"label label-info\">已经完成</span>";break;
                            default:echo '其他';break;
                        }?></td>
                    <td style="color: #e54137;"><?php echo $items['remark'] ?></td>
                    <td>
                        <a tabindex="0" role="button" data-trigger="focus" type="button" class="btn btn-info" data-container="body" data-toggle="popover" data-placement="top"
                           data-id="<?php echo $items['id']?>"
                           data-title="<?php echo $items['trustee']?> 的任务"
                           data-html="true"
                           data-qrsrc = "excuteTask.php?subtaskid=<?php echo $items['id']?>"
                           data-content="">
                            <div class="qrcode"></div>
                            <span class="glyphicon glyphicon-picture"></span>&nbsp;生成二维码
                        </a>
                        <a tabindex="0" role="button" type="button" class="btn btn-info" data-container="body" data-toggle="popover" data-placement="top"
                           data-id="<?php echo $items['id']?>"
                           data-title="<?php echo $items['trustee']?> 的任务链接"
                           data-html="true"
                           data-qrsrc = "excuteTask.php?subtaskid=<?php echo $items['id']?>"
                           data-content=""
                            onmouseover="var clpLinkSrc=location.href.split('distribute.php')[0]+'excuteTask.php?subtaskid=<?php echo $items['id']?>';this.setAttribute('data-content','<a _target=\'blank\' style=\'word-break: break-all\' href=\''+clpLinkSrc+'\'>'+clpLinkSrc+'</a>')">
                            <span class="glyphicon glyphicon-link"></span>&nbsp;生成任务连接
                        </a>
                    </td>
                </tr>

            <?php }?>
        <script>
            $('[data-toggle="popover"]').popover();

            /*******二维码生成过程*******/
            $('.qrcode').each(function(){
                var that = this;
                var $this = $(this);
                $this.qrcode({
                    render: "canvas", //table方式
                    width: 150, //宽度
                    height:150, //高度
                    text: location.href.split('distribute.php')[0]+that.parentNode.getAttribute('data-qrsrc') //父元素的qrsrc属性
                });
                var qrstring = that.getElementsByTagName('canvas')[0].toDataURL("image/png");
                that.parentNode.setAttribute('data-content',"<img src='"+qrstring+"'>");//替换为弹层
                that.parentNode.removeChild(that);//移除原图片
            });
            /*******二维码生成过程*******/
        </script>
        </table>
    </div>

    <!-- ************* 寝室选择模块 ************* -->
    <div class="modal fade" id="taskEditor" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" draggable="false" >
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true"></span></button>
                    <h4 class="modal-title" id="myModalLabel"><span class="glyphicon glyphicon-home"></span> 分配一条查寝任务</h4>
                </div>
                <div class="modal-body">
                    <div class="input-group">
                        <span class="input-group-addon"><span class="glyphicon glyphicon-eye-open"></span></span>
                        <input id="excuteNames" type="text" class="form-control" placeholder="责任人，填学生干部的名字" aria-describedby="sizing-addon2">
                    </div>

                    <div class="input-group">
                        <span class="input-group-addon"><span class="glyphicon glyphicon-hand-up"></span></span>
                        <input id="noticeText" type="text" class="form-control" placeholder="注意事项，查寝时要注意的事" aria-describedby="sizing-addon2">
                    </div>
                    该同学的寝室选择
                    <div class="panel-body">
                        <div id="dormChecker">
                            <?php
                            $dormChecker_Selectable = true;//是否可选
                            $dormChecker_Data = $dorms;//数据
                            include './widgets/dormsChecker.php'
                            ?>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
                    <button id="editimg" type="button" class="btn btn-success" onclick="subTaskSubmit('<?php echo $taskid?>',this)">保存</button>
                    <script>
                        function subTaskSubmit(taskid,thisnode){
                            var excuteNames = document.querySelector('#excuteNames').value;
                            var noticeText = document.querySelector('#noticeText').value;
                            if(!excuteNames){alert('没有指定查寝学生!');return false}
                            if(!excuteNames){if(!confirm('没有需要'+excuteNames+'注意的事项吗？')){return false}}
                            var dormChecker = document.querySelectorAll('#dormChecker');
                            var dorms = $(dormChecker).find('.dormTag');
                            var checkedDorms = [];
                            for(var i=0;i<dorms.length;i++){
                                if(dorms[i].checked)checkedDorms.push(dorms[i].getAttribute('data-dormid'));
                            }
                            if(checkedDorms.length<=0){alert('没有选择任何寝室');return false}
                            thisnode.setAttribute('disabled','disabled');

                            $.ajax({
                                url:'Data.php?id=createSubTask',
                                type:'GET',
                                data:{
                                    'dorms':checkedDorms.toString(),
                                    'taskid':taskid,
                                    'remark':noticeText,
                                    'trustee':excuteNames
                                },
                                success:function(data){
                                    data = eval('(' + data + ')');
                                    console.log(data);
                                    if (data.stat == 200) {
                                        alert(data.msg);
                                        location.reload();
                                    }
                                },
                                error:function(data){
                                    data = eval('(' + data + ')');
                                    console.log(data);
                                }
                            });
                            setTimeout(function(){
                                thisnode.removeAttr('disabled');
                            },30000)
                        }
                    </script>
                </div>
            </div>
        </div>
    </div>
<?php
include './widgets/foot.php';
?>