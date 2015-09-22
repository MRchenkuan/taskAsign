<?php
if(!$dormChecker_Data){var_dump('dormsChecker.php - 当前页数 $dormChecker_Data 未设置');return;}

?>

<?php foreach($dormChecker_Data as $dorm){
    /*每个宿舍分数*/
    $dc_grad = $dormChecker_FinalRes->{$dorm['id']}; ?>
    <label class="<?php echo $dc_grad!=''?'bg-info':'bg-default';?>" style="border:1px solid grey;margin: 1px;padding: 5px;;" data-res="<?php echo $dc_grad?>">
        <?php if($dormChecker_Selectable){ ?>
            <input style="position:relative;margin: 0" type="checkbox" class="dormTag" data-dormid="<?php echo $dorm['id']?>" value="<?php echo ' '.$dorm['dormname']?>">
        <?php }
        echo $dorm['dormname'];
        ?>
    </label>
<?php } ?>
<script>
    var dorms = document.querySelectorAll('.dormTag');
    console.log(dorms);
    /*分栋*/
    for(var i=0;i<dorms.length;i++){
        var dorm = dorms[i];
        var floor = dorm.value.split('-')[0];
        var level = dorm.value.split('-')[1][0];
        var floorBox = document.getElementById('floorblock-'+floor);
        var levelBox = document.getElementById('levelblock-'+floor+'-'+level);
        var levellegend,slctlvl;
        if(floorBox){
            if(levelBox){
                levelBox.appendChild(dorm.parentNode);
            }else{
                /*分楼层*/
                levelBox =document.createElement('fieldset');
                floorBox.appendChild(levelBox);//栋加层
                levelBox.id='levelblock-'+floor+'-'+level;
                levelBox.setAttribute('style','margin-top:20px');
                levellegend = document.createElement('legend');
                levellegend.innerHTML=floor+'栋 - '+level+' 楼 ';
                levelBox.appendChild(levellegend);//层加标题

                levelBox.appendChild(dorm.parentNode);//层加房间

                /*楼层全选*/
                slctlvl = document.createElement('input');
                slctlvl.type='checkbox';
                levellegend.appendChild(slctlvl);
                slctlvl.addEventListener('change',function(){
                    var thischeck = this;
                    console.log(thischeck.checked);
                    $(thischeck.parentNode.parentNode).find('.dormTag').each(function(){
                        this.checked=thischeck.checked;
                    });
                });



            }
        }else{
            var board = dorms[i].parentNode.parentNode;

            /*分楼栋*/
            var fieldset = document.createElement('fieldset');
            fieldset.id='floorblock-'+floor;
            fieldset.setAttribute('style','margin-top:50px');
            var legend = document.createElement('legend');
            legend.innerHTML=floor+' 栋宿舍 ';

            /*分楼层*/
            levelBox = document.createElement('fieldset');
            levelBox.id='levelblock-'+floor+'-'+level;
            levelBox.setAttribute('style','margin-top:20px');
            levellegend = document.createElement('legend');
            levellegend.innerHTML=floor+'栋 - '+level+' 楼 ';

            /*楼栋全选*/
            var slctAll = document.createElement('input');
            slctAll.type='checkbox';
            legend.appendChild(slctAll);
            slctAll.addEventListener('change',function(){
               var thischeck = this;
                console.log(thischeck.checked);
                $(thischeck.parentNode.parentNode).find('.dormTag').each(function(){
                    this.checked=thischeck.checked;
                });
            });

            /*楼层全选*/
            slctlvl = document.createElement('input');
            slctlvl.type='checkbox';
            levellegend.appendChild(slctlvl);
            slctlvl.addEventListener('change',function(){
                var thischeck = this;
                console.log(thischeck.checked);
                $(thischeck.parentNode.parentNode).find('.dormTag').each(function(){
                    this.checked=thischeck.checked;
                });
            });

            board.appendChild(fieldset);//总面板加栋
            fieldset.appendChild(legend);//栋加标题

            fieldset.appendChild(levelBox);//栋加层
            levelBox.appendChild(levellegend);//层加标题

            levelBox.appendChild(dorm.parentNode);//层加房间
        }
    }


</script>