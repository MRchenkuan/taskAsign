<?php
/**
 * Created by PhpStorm.
 * User: mac
 * Date: 15/6/1
 * Time: 下午5:59
 *
 *
 * 对应的xml格式如下
 * database( NOWID 必须,不建议增加其他属性)
 * - item (id必须，其余各式各样的属性都可以，表示字段，建议新增的属性在DTD中声明)
 */
error_reporting(0);
class Kodbc {
    function __construct($xmlPath) {
        $this->xmlPath=$xmlPath;
        if(!$this->xmlDoc = simplexml_load_file($xmlPath))echo $xmlPath.'不存在';
    }

    /*初始化列数*/
    function initCol(){
        //do something
    }

    /*根据id查找节点*/
    function getById($id){
        try{
            return $this->xmlDoc->xpath('//database/item[@id=\''.$id.'\']')[0];
        }catch(Exception $e){
            return null;
        }

    }

    /*根据ID删除节点*/
    function delById($id){
        $xmldoc = new DOMDocument();
        $xmldoc->load($this->xmlPath);

        /*getid获取不到就只能用xpath了*/
        $xpath = new DOMXPath($xmldoc);
        $preDel = $xpath->query("//database/item[@id='".$id."']")->item(0);
        if(!$preDel){
            return json_encode(array(
                'stat'=>201,
                'msg'=>$id.' is not found'
            ));
        }

        $preDel->parentNode->removeChild($preDel);
        $xmldoc->save($this->xmlPath);

        if($xpath->query("//database/item[@id='".$id."']")->item(0)){
            return json_encode(array(
                'stat'=>202,
                'msg'=>$id.' del failed!'
            ));
        }else{
            return json_encode(array(
                'stat'=>200,
                'msg'=>$id.' del success!'
            ));
        }
    }

    /*根据属性查找结果集*/
    function getByAttr($attr,$val){
        $arr = $this->xmlDoc->xpath("//database/item[@".$attr."='".$val."']");
        return $arr;
    }

    /*得到所有item列表*/
    function getAllItems(){
        $args = func_get_args();
        $offset = $args[0];
        $length= $args[1];

        $_Arr = $this->xmlDoc->xpath('//database/item');

        if(!$offset&&!$length){
            return $_Arr;
        }

        /*----按order进行排序-----*/
//        $this->sort($_Arr,'order','DESC');
        usort($_Arr, function($a, $b) {
            $al = (int)$a['id'];
            $bl = (int)$b['id'];
            if ($al == $bl)
                return 0;
            return ($al > $bl) ? -1 : 1;
        });
        /*----排序结束-----------*/

        return array_slice($_Arr,$offset,$length);
    }

    /**
     *
     * ------暂时不能用 待调试
     *
     * 排序方法
     * @$arr 被排序的结果集 - 数组
     * @$by 被排序的字段
     * @$rule 排序规则 DESC为反序，其他为正序
     * */
    function sort($arr,$by,$rule){
        usort($arr, function($a, $b)use($by,$rule) {
            $al = (int)$a[$by];
            $bl = (int)$b[$by];
            if ($al == $bl)
                return 0;
            if($rule=='DESC'){
                return ($al < $bl) ? -1 : 1;
            }else{
                return ($al > $bl) ? -1 : 1;
            }
        });
    }

    function count(){
        return count($this->xmlDoc->xpath('//database/item'));
    }

    /*根据起止时间查找ID*/
    function getByPeriod($start,$end){

    }

    /*根据属性插入新节点*/
    function insertItem($attrs){
        $NowId= $this->xmlDoc->attributes()['NOWID'];
        $item = $this->xmlDoc->addChild('item');
        $item->addAttribute('id',$NowId);
        foreach($attrs as $k=>$v){
            $item->addAttribute($k,$v);
        };
        /*最大ID数+1*/
        $selfattr = $this->xmlDoc->attributes();
        $selfattr['NOWID'] = $NowId+1;
        $this->xmlDoc->asXML($this->xmlPath);
        return $selfattr['NOWID'];
    }

    /*根据id和对象更新节点*/
    function updateItem($id,$info){
        $item = $this->getByid($id);
        foreach($info as $k=>$v){
            $item->attributes()->$k=$v;
        }
        $this->xmlDoc->asXML($this->xmlPath);

    }
    function getNewestId(){
        return $this->xmlDoc->attributes()['NOWID']-1;
    }
}

//$kodbc = new Kodbc('../Database/photolib/photobase.xml');
//$kodbc->getByAttr('albumid','14');