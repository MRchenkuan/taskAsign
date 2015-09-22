<?php
error_reporting(0);
session_start();

require_once('./tools/Kodbc.class.php');

$APIID = $_GET['id'] ? $_GET['id'] : 'defaultMethod';
$DATABASEURL = './Database/ADVTSDATA.xml';


/*****************************************************
 *
 *                  转发路由
 *
 *****************************************************/
$config = array(
    'defaultMethod' => defaultMethod,
    'getNews' => getNews,
    'uploadImg' => uploadImg,
    'userLogin' => userLogin,
    'userVerify' => userVerify,
    'createAdvt' => createAdvt,
    'delAdvt' => delAdvt,
    'createNews' => createNews,
    'delNews' => delNews,
    'uploadImgAjax' => uploadImgAjax,
    'moveImage' => moveImage,
    'removeImage' => removeImage,
    'createAlbum' => createAlbum,
    'delAlbum' => delAlbum,
    'getNewsContent' => getNewsContent,


    'createTask' => createTask,
    'createSubTask' => createSubTask,
    'subTaskRemark' => subTaskRemark,
);
$config[$APIID]();

/*****************************************************
 *
 *                  通用的处理函数
 *
 *****************************************************/
/**
 * 默认返回的方法
 * */
function defaultMethod()
{
    echo 'api unformated!';
}


/**
 * 用户登陆的方法
 * */

function userLogin()
{

    $username = $_GET['username'];
    $password = $_GET['password'];
    $trylimit = 10;//最大登录尝试次数

//    if (!$_COOKIE['_auth']) return;

    if ($_SESSION['trycount'] && $_SESSION['trycount'] >= $trylimit) {
        echo json_encode(array(
            'stat' => 205,
            'msg' => 'login failed! too frequently you try!'
        ));
        return;
    }
    if ($username == 'huying' && $password == '890824') {
        /*记录session值并写入cookie*/
        setcookie('SSID', session_id(),time()+43200);
        $_SESSION['stat'] = 'login';
        $_SESSION['Verifyed'] = true;
        $_SESSION['trycount'] = 1;
        echo json_encode(array(
            'stat' => 200,
            'msg' => 'login sucessed!'
        ));
    } else {
        if (!$_SESSION['trycount']) {
            $_SESSION['trycount'] = 0;
        }
        $_SESSION['trycount'] += 1;

        echo json_encode(array(
            'stat' => 201,
            'msg' => 'login failed!'
        ));
    }
}

/**
 * 用户登陆态验证的方法
 * */
function userVerify()
{
    if ($_SESSION['stat'] == 'login') {
        return true;
    } else {
        return false;
    }
}

/*****************************************************
 *
 *                  总任务的处理函数
 *
 *****************************************************/
/**
 * 总任务创建
 */

function createTask(){
    $id = $_GET['taskid'];
    $dorms = $_GET['dorms'];
    $remark = $_GET['remark'] or '';
    $trustee = $_GET['trustee'] or '';

    if (!userVerify()) {
        /*验证用户登陆*/
        echo json_encode(array(
            'stat' => 201,
            'msg' => 'login failed!'
        ));
        echo false;
    }

    $Kodbc = new Kodbc('./Database/CheckTask.xml');
    $dataitem = array(
        'stat' => '1',
        'update' => date('Y-m-d H:i'),
        'remark' => $remark,
        'dorms' =>$dorms,
        'trustee' => $trustee
    );

    /*更新或者新增取决于ID是否存在*/
    if ($id && $id != '') {
        $Kodbc->updateItem($id, $dataitem);
    } else {
        $Kodbc->insertItem($dataitem);
    }
    echo json_encode(array(
        'stat' => 200,
        'msg' => 'add sucess！'
    ));
}

/**
 * 子任务创建
 */
function createSubTask(){
    $id=$_GET['subtaskid'];
    $taskid = $_GET['taskid'];
    $dorms = $_GET['dorms'];
    $taskid = $_GET['taskid'];
    $remark = $_GET['remark'];
    $trustee = $_GET['trustee'];
    $result = [];
    foreach(explode(',',$dorms) as $dormid){
        $result[$dormid] = 0;
    }
    $result = json_encode($result);
    if (!userVerify()) {
        /*验证用户登陆*/
        echo json_encode(array(
            'stat' => 201,
            'msg' => 'login failed!'
        ));
        echo false;
    }

    $Kodbc = new Kodbc('./Database/subTask.xml');
    $dataitem = array(
        'stat' => '1',
        'taskid' => $taskid,
        'update' => date('Y-m-d H:i'),
        'remark' => $remark,
        'dorms' =>$dorms,
        'trustee' => $trustee,
        'result' => $result
    );

    /*维护主任务*/
    $Kodbc2 = new Kodbc('./Database/CheckTask.xml');
    $mainTask = $Kodbc2->getById($taskid);
    $allTrustee = $mainTask['trustee'];
    if($allTrustee!=''){
        $allTrustee .= '、'.$trustee;
    }else{
        $allTrustee = $trustee;
    }
    $dataitem2 = array('trustee' => $allTrustee);
    $Kodbc2->updateItem($taskid,$dataitem2);

    /*更新或者新增取决于ID是否存在*/
    if ($id && $id != '') {
        $Kodbc->updateItem($id, $dataitem);
    } else {
        $Kodbc->insertItem($dataitem);
    }
    echo json_encode(array(
        'stat' => 200,
        'msg' => 'add sucess！'
    ));
}

/**
 * 评价接口
 */
function subTaskRemark(){
    $id=$_GET['subtaskid'];
    $dromid = $_GET['dromid'];//结果序号
    $grad = $_GET['grad'];//结果等级
    $remark = $_GET['remark'];//结果评价

    $Kodbc = new Kodbc('./Database/subTask.xml');
    $task = $Kodbc->getById($id);
    $result = $task['result'];
    $result = json_decode($result);
    $result->{$dromid} = $grad.'::'.$remark;

    /*维护任务执行结果*/
    $stat = '0';
    foreach($result as $key=>$val){
        if($val=='0'||$val==0){
            $stat='1';
            break;
        }
    }

    $dataitem = array(
        'stat' => $stat,
        'result' => json_encode($result)
    );

    /*更新任务*/
    $Kodbc->updateItem($id, $dataitem);
    echo json_encode(array(
        'stat' => 200,
        'msg' => 'add sucess！'
    ));
}






















/*****************************************************
 *
 *                  广告的处理函数
 *
 *****************************************************/

/**
 * 用户创建广告的方法
 * */
function createAdvt()
{
    $id = $_GET['adid'];
    $order = $_GET['order'];
    $title = $_GET['title'];
    $imgsrc = $_GET['imgsrc'];
    $update = $_GET['update'];
    $dndate = $_GET['dndate'];
    $remark = $_GET['remark'];

    if (!userVerify()) {
        /*验证用户登陆*/
        echo json_encode(array(
            'stat' => 201,
            'msg' => 'login failed!'
        ));
        echo false;
    }

    $Kodbc = new Kodbc('./Database/ADVTSDATA.xml');
    $dataitem = array(
        'order' => $order,
        'stat' => 'disable',
        'title' => $title,
        'imgsrc' => $imgsrc,
        'update' => $update,
        'dndate' => $dndate,
        'remark' => $remark
    );

    /*更新或者新增取决于ID是否存在*/
    if ($id && $id != '') {
        $Kodbc->updateItem($id, $dataitem);
    } else {
        $Kodbc->insertItem($dataitem);
    }
    echo json_encode(array(
        'stat' => 200,
        'msg' => 'add sucess！'
    ));
}

/**
 * 用户删除广告方法
 * */
function delAdvt()
{
    $id = $_GET['adid'];
    global $DATABASEURL;
    $Kodbc = new Kodbc($DATABASEURL);
    echo $Kodbc->delById($id);
}



/**
 * 图片上传的方法
 * */
function uploadImg()
{
    $uploaddir = './image/' . date('Ymd') . '/';
    if (!file_exists($uploaddir)) {
        if (mkdir($uploaddir)) {
            chmod($uploaddir, 0777);
        } else {
            echo 'faile to create ' . $uploaddir . 'maybe the path you have no permit!<br>';
        };
    }
    $uploadfileUrl = $uploaddir . time() . '.jpg';

    if ($_FILES['userfile']['error'] !== 0) {
        echo 'upload failed! error code:' . $_FILES['userfile']['error'];
        var_dump($_FILES['userfile']);
    } else {
        if (move_uploaded_file($_FILES['userfile']['tmp_name'], $uploadfileUrl)) {
            /*********
             * 记录入库
             ********/
            $Kodbc = new Kodbc('./Database/photolib/photobase.xml');
            $Kodbc->insertItem(array(
                'albumid'=>'0',
                'remark'=>'from uploadImg',
                'imgsrc'=>$uploadfileUrl,
                'pubdata'=>date('Y-m-d\TH:i')
            ));

            /*********
             * 页面输出
             ********/
            echo "<body style='padding: 0;margin: 0'>";
            echo "<form style='padding: 0;margin: 0;' enctype='multipart/form-data' action='./Data.php?id=uploadImg' method='POST' name='form'>";
            echo "<img id='uploadCallBack-ImgSrc' style='height:100%;max-width: 300px;' src='" . $uploadfileUrl . "'>";
            echo "<input style='float: right' id='userfile' name='userfile' type='file' onchange=\"document.getElementById('uploadform').submit()\">";
//                echo "<input style='float: right' type='submit' value='上传图片'>";
            echo "</body>";
            echo "</form>";
            header($uploadfileUrl);
        } else {
            header('#');
        }
    }
}



/*****************************************************
 *
 *                  新闻的处理函数
 *
 *****************************************************/

function createNews(){

    $newsid     =   $_POST['newsid'];
    $title     =   $_POST['title'];
    $auth       =   $_POST['auth'];
    $origin     =   $_POST['origin'];
    $pubdata    =   $_POST['pubdata'];
    $stat    =   $_POST['stat'];
    $cover    =   $_POST['cover'];

    $text       =   htmlspecialchars($_POST['text']);

    $Kodbc = new Kodbc('./Database/NEWSDATA.xml');
    /*************
     *
     * 储存大文本
     *
     ************/
    $newsDir = './news/'.date('Ymd').'/';
    if (!file_exists($newsDir)) {
        if (mkdir($newsDir)) {
            chmod($newsDir, 0777);
        } else {
            echo 'faile to create ' . $newsDir . 'maybe the path you have no permit!<br>';
        };
    }

    /*判断是修改文件还是新增文件*/
    if ($newsid && $newsid != ''){
        $item = $Kodbc->getById($newsid);
        $newsFileUrl = $item['text'];
    }else{
        $newsFileUrl = $newsDir . time() . '.news';
    }

    $fp = fopen($newsFileUrl, "w+");
    if(!fwrite($fp,$text)){
        fclose($fp);
        echo json_encode(array(
            'stat' => 201,
            'msg' => 'login failed!'
        ));
        throw new Exception("文件保存异常");
    }
    fclose($fp);
    /**************
     * 文件储存结束
     **************/

    if (!userVerify()) {
        /*验证用户登陆*/
        echo json_encode(array(
            'stat' => 201,
            'msg' => 'login failed!'
        ));
    }

    $dataitem = array(
        'stat' => $stat,
        'title' => $title,
        'auth' => $auth,
        'origin' => $origin,
        'pubdata' => $pubdata,
        'text' =>$newsFileUrl,
        'cover' => $cover
    );

    /*更新或者新增取决于ID是否存在*/
    if ($newsid && $newsid != '') {
        $Kodbc->updateItem($newsid, $dataitem);
    } else {
        $newsid = $Kodbc->insertItem($dataitem);
    }
    echo json_encode(array(
        'stat' => 200,
        'msg' => $newsid.' add sucess！',
        'articId'=>$newsid[0]
    ));
}

/**
 * 用户删除新闻方法
 * */
function delNews()
{
    $id = $_GET['newsid'];
    $Kodbc = new Kodbc('./Database/NEWSDATA.xml');
    echo $Kodbc->delById($id);
}

/**
 * 获得新闻内容的方法
 * */
function getNewsContent(){
    $id=$_GET['newsid'];
    $Kodbc = new Kodbc('./Database/NEWSDATA.xml');
    $item= $Kodbc->getById($id);
    $contentsrc = $item['text'];
    $newsfile = fopen($contentsrc,'r') or die('can not find news,because no news file found');
    echo json_encode(array(
        'stat' => 200,
        'msg' => $id.' get sucess！',
        'content'=>htmlspecialchars_decode(fread($newsfile,filesize($contentsrc)))
    ));
    fclose($newsfile);

}


/*****************************************************
 *
 *                  图库的处理函数
 *
 *****************************************************/

/**
 * 异步上传图片
 */
function uploadImgAjax()
{

    $imgdatastring = $_POST['imgDataString'] or null;
    $uploaddir = './image/' . date('Ymd') . '/';
    if (!file_exists($uploaddir)) {
        if (mkdir($uploaddir)) {
            chmod($uploaddir, 0777);
        } else {
            echo 'faile to create ' . $uploaddir . 'maybe the path you have no permit!<br>';
        };
    }

    /*base64保存为图片，并写入数据库*/
    if($imgdatastring){
        //do someting for 保存图片
        if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $imgdatastring, $result)){
            $type = $result[2];
            $uploadfileUrl = $uploaddir. time().'.'.$type;
            if (file_put_contents($uploadfileUrl, base64_decode(str_replace($result[1], '', $imgdatastring)))){
                //写入数据库
                $Kodbc = new Kodbc('./Database/photolib/photobase.xml');
                $Kodbc->insertItem(array(
                        'albumid'=>$_POST['albumid'],
                        'stat'=>'active',
                        'remark'=>$_POST['remark'],
                        'imgsrc'=>$uploadfileUrl,
                        'pubdata'=> date('Y-m-d\TH:i')
                    )
                );

                echo json_encode(array(
                    'stat'=>200,
                    'imgurl'=>$uploadfileUrl,
                    'msg'=>'图片上传成功',
                ));
            }
        }else if($_POST['onlineurl']){
            /*如果没有图片但是有imgurl时*/
            $Kodbc = new Kodbc('./Database/photolib/photobase.xml');
            $Kodbc->insertItem(array(
                    'albumid'=>$_POST['albumid'],
                    'stat'=>'active',
                    'remark'=>$_POST['remark'],
                    'imgsrc'=>$_POST['onlineurl'],
                    'pubdata'=> date('Y-m-d\TH:i')
                )
            );
            echo json_encode(array(
                'stat'=>200,
                'imgurl'=>$_POST['onlineurl'],
                'msg'=>'网络URL,图片添加成功',
            ));

        }else{
            echo json_encode(array(
                'stat'=>202,
                'imgurl'=>null,
                'msg'=>'图片字符串匹配失败！也未填写图片URL',
            ));
        }

    }else{
        echo json_encode(array(
            'stat'=>202,
            'msg'=>'后端未收到前端图片数据',
        ));
    }
}

/**
 * 相册间移动图片
 */
function moveImage(){
    $albumid=$_GET['albumid'];
    $Kodbc = new Kodbc('./Database/photolib/photobase.xml');
    if($_GET['imgid']){
        $imgid=$_GET['imgid'];
        $Kodbc->updateItem($imgid,array(
            'albumid'=>$albumid
        ));
        echo json_encode(array(
            'stat'=>200,
            'msg'=>"{$imgid}移动到{$albumid}",
        ));
    }elseif($_GET['imgsrc']){
        $imgsrc=$_GET['imgsrc'];
        $Kodbc->insertItem(array(
            'albumid'=>'0',
           'imgsrc'=>$imgsrc,
            'pubdata'=>date('Y-m-d\TH:i'),
            'remark'=>'from image binding'
        ));
        echo json_encode(array(
            'stat'=>200,
            'msg'=>"{$imgsrc}绑定到{$albumid}",
        ));
    }else{
        echo json_encode(array(
            'stat'=>202,
            'msg'=>"既没有图片ID也没有图片地址"
        ));
    }

}


/**
 * 物理删除图片
 */
function removeImage(){
    $imgsrc = $_GET['imgsrc'];
    $filename=end(explode('/',$imgsrc));
    /*新建回收站*/
    $dashbindir = './dashbin/'.date('Ymd').'/';
    if (!file_exists($dashbindir)) {
        if (mkdir($dashbindir)) {
            chmod($dashbindir, 0777);
        } else {
            echo 'faile to create ' . $dashbindir . 'maybe the path you have no permit!<br>';
        };
    }

    $Kodbc = new Kodbc('./Database/photolib/photobase.xml');
    if($_GET['imgid']){
        $imgid=$_GET['imgid'];
        $Kodbc->delById($imgid);
    }

    if(rename($imgsrc,$dashbindir.$filename )){
        echo json_encode(array(
            'stat'=>200,
            'msg'=>"{$_GET['imgid']}在数据库中删除，{$filename}移动到服务器回收站",
        ));
        return true;
    }else{
        echo json_encode(array(
            'stat'=>200,
            'msg'=>"数据库删除成功，但服务器无此文件",
            '$imgsrc'=>$imgsrc,
            '$dashbindir.$filename'=>$dashbindir.$filename,
        ));
        return true;
    }
}


/**
 * 创建相册
 */
function createAlbum(){
    try{

        $albumname = $_GET['albumname'];
        $stat = $_GET['stat'];

        $Kodbc = new Kodbc('./Database/photolib/photoAlbum.xml');
        if($_GET['albumid']&&$_GET['albumid']!==''){
            $Kodbc->updateItem($_GET['albumid'],array(
                'stat'=>$stat,
                'remark'=>$albumname,
                'pubdata'=>date('Y-m-d\TH:i')
            ));
            echo json_encode(array(
                'stat'=>200,
                'msg'=>"《{$albumname}》修改成功",
            ));
        }else{
            $Kodbc->insertItem(array(
                'stat'=>$stat,
                'remark'=>$albumname,
                'editable'=>1,
                'count'=>0,
                'pubdata'=>date('Y-m-d\TH:i')
            ));
            echo json_encode(array(
                'stat'=>200,
                'msg'=>"《{$albumname}》创建成功",
            ));
        }
    }catch (Exception $e){
        echo json_encode(array(
            'stat'=>202,
            'msg'=>"出现异常:{$e}",
        ));
    }

}

/**
 * 删除相册
 * */
function delAlbum(){
    if($_GET['albumid']){
        $albumid = $_GET['albumid'];
        $Kodbc = new Kodbc('./Database/photolib/photoAlbum.xml');
        $item = $Kodbc->getById($albumid);
        if($item['count']>0){
            echo json_encode(array(
                'stat'=>200,
                'msg'=>"相册内有照片，需要先清空相册！",
            ));
        }else{
            $Kodbc->delById($albumid);
            echo json_encode(array(
                'stat'=>200,
                'msg'=>"{$albumid}:成功删除",
            ));
        }
    }else{
        echo json_encode(array(
            'stat'=>202,
            'msg'=>"并没有找到什么卵ID",
        ));
    }
}