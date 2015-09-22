<?php
if(!$pageNow){var_dump('pageSliceBar.php - 当前页数 $pageNow 未设置');return;}
if(!$pageCount){var_dump('pageSliceBar.php - 页面总数 $pageCount 未设置');return;}
if(!$sliceParam){var_dump('pageSliceBar.php - 分页参数 $page 未设置');return;}
?>
<nav style="margin: 0 auto;text-align: center">
    <ul class="pagination">
        <li class="<?php echo $pageNow<=1?'disabled':'default'?>">
            <a <?php echo $pageNow<=1?'':"href='?".$sliceParam."=".($pageNow-1)."'"; ?> aria-label='Previous'>
                <span aria-hidden="true">«</span>
            </a>
        </li>
        <?php
        for($i=0;$i<$pageCount;$i++){
            echo ($pageNow==($i+1))?"<li class='active'>":"<li class='default'>";
            echo "<a href='?".$sliceParam."=".($i+1)."'>".($i+1)."</a></li>";
        }
        ?>
        <li class="<?php echo $pageNow>=$pageCount?'disabled':'default'?>" >
            <a <?php echo $pageNow>=$pageCount?'':"href='?".$sliceParam."=".($pageNow+1)."'"; ?> aria-label="Next">
                <span aria-hidden="true">»</span>
            </a>
        </li>
    </ul>
</nav>