<nav class="navbar navbar-default navbar-fixed-top">
    <div class="container-fluid">
        <!--品牌图标-->
        <div class="navbar-header">
            <a class="navbar-brand" href="#">
                <span class="glyphicon glyphicon-cog"></span>
                <span class="glyphicon glyphicon-book"></span>
                <span class="glyphicon glyphicon-list-alt"></span>
                <span class="glyphicon glyphicon-user"></span>
            </a>
        </div>
        <!--导航菜单-->
        <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
            <ul class="nav navbar-nav">
                <!--导航选项-->
                <li class="<?php echo $pageID=='home'?'active':'default' ?>"><a href="./home.php">首页</a></li>
                <li class="<?php echo $pageID=='distribute'?'active':'default' ?>"><a href="./distribute.php">任务分配</a></li>
                <li class="<?php echo $pageID=='report'?'active':'default' ?>"><a href="./report.php">结果报告</a></li>
            </ul>
        </div>
    </div>
</nav>

