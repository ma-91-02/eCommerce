<?php 
  ob_start();
  session_start();
  $pageTitle = 'Show Items';
  include 'init.php';//get the file init
  // Check If Get Request itemid Is Numeric &Get The Integer Value Of It
  $itemid = isset($_GET['itemid']) && is_numeric($_GET['itemid']) ? intval($_GET['itemid']) : 0;
  // Select All Data Depend On This ID
  $stmt = $con->prepare("SELECT
                              items.* , categories.Name AS category_name, users.Username 
                          FROM
                              items
                          INNER JOIN
                              categories 
                          ON
                              categories.ID = items.Cat_ID
                          INNER JOIN
                              users 
                          ON
                              users.userID = items.Member_ID 
                          WHERE
                              Item_ID = ?
                          AND 
                              Approve = 1");
  // Execute Query 
  $stmt->execute(array($itemid));
  $count = $stmt->rowCount();
  if ($count >0 ){
    // Fetch The Data
    $item = $stmt->fetch();?>
    <h1 class="text-center"><?php echo $item['Name'];?></h1>
    <div class="container">
      <div class="row">
        <div class="col-md-3">
          <img class="img-fluid img-thumbnail" src="layout/images/img1.png" alt="">
        </div>
        <div class="col-md-9 item-info">
          <h2 class=""><?php echo $item['Name'];?></h2>
          <p class=""><?php echo $item['Description'];?></p>
          <ul class="list-unstyled">
            <li><i class="fa fa-money fa-fw"></i> <span>Price</span> : $<?php echo $item['Price'];?></li>
            <li><i class="fa fa-calendar fa-fw"></i> <span>Added Date</span> : <?php echo $item['Add_Date'];?></li>
            <li><i class="fa fa-building fa-fw"> </i><span>Made IN</span> : <?php echo $item['Country_Made'];?></li>
            <li><i class="fa fa-tags fa-fw"></i> <span>Category</span> : <a href="cotegories.php?pageid=<?php echo $item['Cat_ID'] ;?>" class=""> <?php echo $item['category_name'];?></a></li>
            <li><i class="fa fa-user fa-fw"></i> <span>Added By</span> : <a href="#" class=""> <?php echo $item['Username'];?></a></li>
            <li class ="tags-items"><i class="fa fa-user fa-fw"></i> <span>Tags</span> :  
              <?php $allTags = explode(",",$item['tags']);
              foreach ($allTags as $tag){
                $tag = str_replace(' ', '',$tag);
                $lowertag = strtolower($tag);
                if(! empty($tag)){
                echo '<a href="tags.php?name='. $lowertag .'" class="">'.$tag .'</a>';}}?>
            </li>
          </ul>
        </div>
      </div>
      <hr class="custm-hr">
      <!------------------------------------------------- Start Add Comment ------------------------------->
      <?php if (isset($_SESSION['user'])){?>
              <div class="row">
                <div class="col-md-9 offset-3">
                  <div class="add-comment">
                    <h3>Add Your Comment</h3>
                    <form action="<?php echo $_SERVER['PHP_SELF'].'?itemid=' .$item['Item_ID'];?>" method="POST">
                      <textarea  required name="comment" id="" ></textarea>
                      <input class="btn btn-primary"type="submit" value="Add Comment">
                    </form>
                    <?php 
                      if ($_SERVER['REQUEST_METHOD'] == 'POST'){
                        $comment = filter_var($_POST['comment'],FILTER_SANITIZE_STRING);
                        $userid  = $_SESSION['uid'];
                        $itemid  = $item['Item_ID'];
                        if (! empty($comment)){
                          $stmt = $con->prepare("INSERT INTO 
                                                      comments(comment, status, comment_date, item_id, user_id)
                                                  VALUE(:zcomment, 0, NOW(), :zitemid, :zuserid)");
                          $stmt->execute(array(
                            'zcomment'=> $comment,
                            'zitemid' => $itemid,
                            'zuserid' => $userid
                          ));
                          if ($stmt){
                            $theMsg= "<div class='alert alert-success'> Comment Added </div>";
                            redirectHome($theMsg, 'Back', 0);
                            exit();
                          }
                        }
                      }
                    ?>
                  </div>
                </div>
              </div>
      <?php }else{
          echo "Login Or Register To Add Comment";
              }?>
      <!------------------------------------------------- End Add Comment ------------------------------->
      <hr class="custm-hr">
      <?php 
            // Select All Uesrs Except Admin
            $stmt = $con->prepare("SELECT
                                        comments.*, users.Username AS Member
                                    FROM
                                        comments
                                    INNER JOIN 
                                        users
                                    ON
                                        users.userID = comments.user_id
                                    WHERE
                                        item_id = ?
                                    AND 
                                        status = 1
                                    ORDER BY 
                                        c_id DESC");

            // Execute The Statement
            $stmt->execute(array($item['Item_ID']));

            // Assign To Variable
            $comments = $stmt->fetchAll();
            ?>
        <?php
          foreach ($comments as $comment){?>
            <div class="comment-box">
              <div class="row">
                <div class="col-sm-2 text-center">
                <img class="img-fluid img-thumbnail rounded-circle mx-auto" src="layout/images/img1.png" alt="">  
                <?php echo $comment['Member']; ?>
                
                </div>
                <div class="col-sm-10"><p class="lead"><?php echo $comment['comment']; ?></p></div>
              </div>
            </div\
            
            >
            <hr>
        <?php }
        ?>
    </div>
<?php
  }else{
      echo "There's No Such ID or This Item Is Waiting Approval";
    }
  include $tpl . 'footer.php';//get footer
  ob_end_flush();
?>