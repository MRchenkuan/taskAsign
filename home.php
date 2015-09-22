<?php
//error_reporting(0);
session_start();
$pageID='home';
include './widgets/head.php';
?>



<?php
/*--连接数据库--*/
require_once('./tools/Kodbc.class.php');
$Kodbc = new Kodbc('./Database/CheckTask.xml');
$pageNow = $_GET['page'];//当前分页
if(!$pageNow){$pageNow=1;}
$sliceParam = 'page'; //分页参数
$pagesize = 10;//页面条数

$adCollection = $Kodbc->getAllItems();
/*排序*/
usort($adCollection, function($a, $b) {
    $al = (int)$a['id'];
    $bl = (int)$b['id'];
    if ($al == $bl)
        return 0;
    return ($al > $bl) ? -1 : 1;
});
$count = $Kodbc->count();//总共条目数
$pageCount = ceil($count/$pagesize);//总页数
?>


    <div class="page-header" style="width: 960px;margin: 60px auto 0 auto">
        <h1>安排查寝 <small>查寝记录列表</small></h1>
    </div>

    <div class="panel panel-default center-block" style="width: 960px">
        <!-- Default panel contents -->
        <div class="panel-heading">历史查寝记录</div>
        <div class="panel-body">
            <p>下表中是之前所有查过寝的记录</p>
            <button type="button" class="btn btn-success" data-toggle="modal" data-placement="top" title="编辑图片" data-target="#taskEditor">新增查寝任务</button>

        </div>

        <!-- Table -->
        <table class="table">
            <tr><td>编号</td><td>日期</td><td>责任人</td><td>查寝结果</td><td>操作</td></tr>
        <?php
        foreach($adCollection as $items){?>
            <tr>
                <td>(<?php echo $items['id']?>)</td>
                <td><?php echo $items['update']?></td>
                <td><?php echo $items['trustee']==''?'暂未分配':$items['trustee'] ?></td>
                <td><?php switch($items['stat']){
                        case '1':echo "<span class=\"label label-success\">正在进行中...</span>";break;
                        case '0':echo "<span class=\"label label-warning\">已经结束</span>";break;
                        default:echo '其他';break;
                    }?></td>
                <td>
                <?php if($items['stat']=='1'){ ?>
                        <button type="button"
                                class="btn btn-success"
                                onclick="location='./distribute.php?taskid=<?php echo $items['id']?>'"
                                data-id="<?php echo $items['id']?>">
                            <span class="glyphicon glyphicon-list-alt"></span>&nbsp;分配查寝任务
                        </button>
                <?php } ?>
                    <button type="button"
                            class="btn btn-primary"
                            onclick="location='./report.php?taskid=<?php echo $items['id']?>'">
                        <span class="glyphicon glyphicon-list-alt"></span>&nbsp;查看结果报告
                    </button>
                </td>
            </tr>

        <?php }?>

        </table>
    </div>


    <!-- ************* 寝室选择模块 ************* -->
    <?php
    $Kodbc2 = new Kodbc('./Database/Dorms.xml');
    $adCollection2 = $Kodbc2->getAllItems();
    ?>
    <div class="modal fade" id="taskEditor" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" draggable="false" >
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true"></span></button>
                    <h4 class="modal-title" id="myModalLabel"><span class="glyphicon glyphicon-home"></span> 选择需要检查的寝室</h4>
                </div>
                <div class="modal-body" id="dormChecker">
                    <?php
                    $dormChecker_Selectable = true;
                    $dormChecker_Data = $adCollection2;
                    include './widgets/dormsChecker.php'
                    ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
                    <button id="editimg" type="button" class="btn btn-success" onclick="submitDorms(this)">保存</button>
                    <script>
                        function submitDorms(thisnode){
                            var dormChecker = document.querySelectorAll('#dormChecker');
                            var dorms = $(dormChecker).find('.dormTag');
                            var checkedDorms = [];
                            for(var i=0;i<dorms.length;i++){
                                if(dorms[i].checked)checkedDorms.push(dorms[i].getAttribute('data-dormid'));
                            }
                            if(checkedDorms.length<=0){alert('没有选择任何寝室');return false}
                            thisnode.setAttribute('disabled','disabled');
                            $.ajax({
                                url:'Data.php?id=createTask',
                                type:'GET',
                                data:{
                                    'dorms':checkedDorms.toString()
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