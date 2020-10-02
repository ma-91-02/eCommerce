<?php
ob_start();
session_start();
include 'init.php'; //Get The File init ?>
<div class="container">
  
  <?php
    if (isset($_GET['name'])){
    $tag = $_GET['name'];?>
    <h1 class="text-center"><?php echo $tag;?></h1>
    <div class="row">
    <?php
    $tagitems = getAllFrom("*", "items", "where tags like '%$tag%'","AND Approve = 1", "Item_ID");
    foreach($tagitems as $item){?>
    <div class="col-sm-6 col-md-3">
      <div class="img-thumbnail item-box">
        <span class="price-tag">$<?php echo $item['Price'];?></span>
        <img class="img-fluid" src="layout/images/img1.png" alt="">
        <div class="caption">
          <h3><a href="items.php?itemid=<?php echo $item['Item_ID'];?>"><?php echo $item['Name'];?></a></h3>
          <p><?php echo $item['Description'];?></p>
          <div class="date"><?php echo $item['Add_Date'];?></div>
        </div>
      </div>
    </div>
    <?php }
    }else{
      echo "You Must Enter Tag Name";
    }
    ?>
  </div>
</div>

  <?php include $tpl . 'footer.php';//Get Footer
  ob_end_flush();
   ?>
