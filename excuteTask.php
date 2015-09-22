<?php
/**
 * Created by PhpStorm.
 * User: mac
 * Date: 15/9/19
 * Time: 下午10:59
 */
$subTaskId=$_GET['subtaskid'];
//$key=$_GET['key'];

/*--连接数据库--*/
require_once('./tools/Kodbc.class.php');
?>
<!DOCTYPE html>
<html>
<head lang="zh-CN">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width">
    <link rel="stylesheet" href="./bootstrap/bootstrap.min.css">
    <link rel="stylesheet" href="./bootstrap/bootstrap-theme.min.css">
    <script src="./js/jquery.min.js"></script>
    <script src="./bootstrap/bootstrap.min.js"></script>
    <style>
        body{width:100%;font-size: 3em}
    </style>
    <title>任务详情</title>
</head>
<body>
<?php
$Kodbc = new Kodbc('./Database/subTask.xml');
$Kodbc2 = new Kodbc('./Database/Dorms.xml');
$subTask = $Kodbc->getById($subTaskId);
$dorms = $subTask['dorms'];
$dorms = explode(',',$dorms);
$result = json_decode($subTask['result']); ?>

<div class="list-group">
    <span class="list-group-item list-group-item-info" style="font-weight: bolder">注意事项：<br><?php echo $subTask['remark']?></span>
    <hr style="margin: 5px 0;">

    <?php
    for($i=0;$i<count($dorms);$i++) {
        $_dorm = $Kodbc2->getById($dorms[$i]);
        switch($result->{$dorms[$i]}){
            case 1: ?>
                <a href="javascript:void(0)" class="list-group-item list-group-item-success"><?php $_dorm = $Kodbc2->getById($dorms[$i]);echo $_dorm['dormname'] ?> <span class="label label-success" style="float: right">优秀</span> </a>
                <?php ;break;
            case 2: ?>
                <a href="javascript:void(0)" class="list-group-item list-group-item-success"><?php $_dorm = $Kodbc2->getById($dorms[$i]);echo $_dorm['dormname'] ?> <span class="label label-info" style="float: right">良好</span> </a>
                <?php ;break;
            case 3: ?>
                <a href="javascript:void(0)" class="list-group-item list-group-item-success"><?php $_dorm = $Kodbc2->getById($dorms[$i]);echo $_dorm['dormname'] ?> <span class="label label-warning" style="float: right">中等</span> </a>
                <?php ;break;
            case 4: ?>
                <a href="javascript:void(0)" class="list-group-item list-group-item-success"><?php $_dorm = $Kodbc2->getById($dorms[$i]);echo $_dorm['dormname'] ?> <span class="label label-danger" style="float: right">差劲</span> </a>
                <?php ;break;
            default:?>
                <a href="javascript:void(0)" class="list-group-item list-group-item-warning" data-toggle="modal" data-target="#remarkeditor" data-dromid="<?php echo $dorms[$i]?>" onclick="updateRemarkTar(this);" data-dormname=" <?php echo $_dorm['dormname'] ?> ">给 <?php echo $_dorm['dormname'] ?> 寝室打分</a>
                <?php ;break;
        };
    }
    ?>

    <script>
        function updateRemarkTar(node){
            var tar_title = node.getAttribute('data-dormname');
            var tar_dromid = node.getAttribute('data-dromid');
            var board = document.getElementById('remarkBordTitle');
            board.innerHTML =' 给 '+tar_title+' 寝室打分';
            board.setAttribute('data-dromid',tar_dromid);
        }
    </script>
</div>

<div class="modal fade" id="remarkeditor" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" draggable="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true"></span></button>
                <h4 class="modal-title" id="myModalLabel"><span class="glyphicon glyphicon-send"></span><span id="remarkBordTitle" data-dormid=""></span></h4>
            </div>
            <div class="modal-body">
                <div class="input-group input-group-lg">
                    <span class="input-group-addon"><span class="glyphicon glyphicon-hand-right"></span></span>
                    <input id="remarktext" type="text" class="form-control" placeholder="特殊情况请汇报" aria-describedby="sizing-addon2">
                </div>
                <hr style="margin: 10px 0;">
                <div id="remarkgrad" data-grad="2" class="btn-group btn-group-lg" role="group" aria-label="...">
                    <button type="button" onclick="conmitDromRemark('<?php echo $subTaskId?>',1,this.innerHTML);" class="btn-lg btn btn-success">优秀</button>
                    <button type="button" onclick="conmitDromRemark('<?php echo $subTaskId?>',2,this.innerHTML);" class="btn-lg btn btn-info">良好</button>
                    <button type="button" onclick="conmitDromRemark('<?php echo $subTaskId?>',3,this.innerHTML);" class="btn-lg btn btn-warning">中等</button>
                    <button type="button" onclick="conmitDromRemark('<?php echo $subTaskId?>',4,this.innerHTML);" class="btn-lg btn btn-danger">差劲</button>
                </div>


            </div>
            <div class="modal-footer">
                <script>
                    function conmitDromRemark(subtaskid,grad,gradText){
                        var res = document.getElementById('remarkBordTitle');
                        var remark =document.getElementById('remarktext').value;
                        var notice = '你确定'+res.innerHTML+" >>"+gradText+'<< ?\n\n报告：'+((remark=='')?'无':remark);
                        if(confirm(notice)){
                            $.ajax({
                                url:'Data.php?id=subTaskRemark',
                                type:'GET',
                                data:{
                                    'subtaskid':subtaskid,
                                    'dromid':res.getAttribute('data-dromid'),
                                    'grad':grad,
                                    'remark':remark
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
                            })
                        }else{
                            return false;
                        }
                    }
                </script>
            </div>
        </div>
    </div>
</div>
</body>
</html>