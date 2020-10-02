<?php 
ob_start();
/*
 ==========================================================
==== Manage Comments Page                               ====
==== You Can Edit | Delete | Approve Comments From Here ====
 ==========================================================
*/
  session_start();
  $pageTitle = 'Comments';
  
  if(isset($_SESSION['Username'])) {

    include 'init.php';
    $do = isset($_GET['do']) ? $_GET['do'] : 'Manage';
/*
=====================================
==== **** Start Manage Page **** ====
=====================================
*/
    // 
    if($do == 'Manage'){
      // Select All Uesrs Except Admin
      $stmt = $con->prepare("SELECT
                                  comments.*, items.Name AS Item_Name, users.Username AS Member
                              FROM
                                  comments
                              INNER JOIN
                                  items
                              ON 
                                  items.Item_ID = comments.item_id
                              INNER JOIN 
                                  users
                              ON
                                  users.userID = comments.user_id
                              ORDER BY 
                                  c_id DESC");

      // Execute The Statement
      $stmt->execute();

      // Assign To Variable
      $rows = $stmt->fetchAll();
      if (! empty($rows)){
  ?>
    
    <h1 class="text-center">Manage Comments</h1>
    <div class="container">
      <div class="table-responsive">
        <table class="main-table text-center table table-bordered">
          <tr>
            <td>ID</td>
            <td>Comment</td>
            <td>Item Name</td>
            <td>User Name</td>
            <td>Added Date</td>
            <td>Control</td>
          </tr>
          <?php
            foreach($rows as $row){
              echo "<tr>";
                echo "<td>" . $row['c_id'] . "</td>";
                echo "<td>" . $row['comment'] . "</td>";
                echo "<td>" . $row['Item_Name'] . "</td>";
                echo "<td>" . $row['Member'] . "</td>";
                echo "<td>" . $row['comment_date'] . "</td>";
                echo '<td>
                       <a href="comments.php?do=Edit&comid='.$row['c_id'].'" class="btn btn-success"><i class="fa fa-edit"></i> Edit</a>
                       <a href="comments.php?do=Delete&comid='.$row['c_id'].'" class="btn btn-danger confirm"><i class="fa fa-close"></i> Delete </a>';
                       if($row['status'] == 0){
                        echo '<a href="comments.php?do=Approve&comid='.$row['c_id'].'" class="btn btn-info activate"><i class="fa fa-check"></i> Approve</a>';
                       }
                       
               echo '</td>';
              echo "</tr>";
            }
          ?>
        </table>
      </div><?php }else{?>
        <div class="container">
          <div class="alert alert-info">There's No Record To Show</div>
        </div>
    <?php }?>
    </div>
    <?php
/*
=====================================
==== **** Edit Comment Page **** ====
=====================================
*/
  }elseif($do == "Edit"){
    // Check If Get Request Comment Is Numeric &Get The Integer Value Of It
    $comid = isset($_GET['comid']) && is_numeric($_GET['comid']) ? intval($_GET['comid']) : 0;
    // Select All Data Depend On This ID
    $stmt = $con->prepare("SELECT * FROM comments WHERE c_id = ?");
    // Execute Query 
    $stmt->execute(array($comid));
    // Fetch The Data
    $row = $stmt->fetch();
    // The Row Count
    $count = $stmt->rowCount();
    // If There's Such ID The Form
    if($count > 0){ ?>
      <h1 class="text-center">Edit Comment</h1>
      <div class="container">
        <form class="form-horizontal" action="?do=Update" method="POST">
          <input type="hidden" name="comid" value="<?php echo $comid?>"/>
          <!-- Start Comment Field -->
          <div class="form-group">
            <label for="" class="col-sm-2 control-label">Comment</label>
            <div class="col-sm-4 col-md-4">
              <textarea class="form-control" name="comment" id="" cols="30" rows="10"><?php echo $row['comment'];?></textarea>
            </div>
          </div>
          <!-- End Comment Field -->
          <!-- Start Save Field -->
          <div class="form-group">
            <div class="col-sm-4">
              <input type="submit" value="Save" class="btn btn-primary"/>
            </div>
          </div>
          <!-- End Save Field -->
        </form>
      </div>
    <?php 
  // If There's No Such ID Show Error Message
  }else{
    echo '<div class="container">';
    $theMsg = '<div class="alert alert-danger"> Theres No Such Id</div>';
    redirectHome($theMsg);
    echo '</div>';
  }
/*
=======================================
==== **** Update Comment Page **** ====
=======================================
*/
}elseif($do == 'Update'){?>
  <h1 class="text-center">Update Comment</h1>
  <div class="container">
<?php
  if($_SERVER['REQUEST_METHOD'] == 'POST'){

    // Get Variables From The Form
    $comid         = $_POST['comid'];
    $comment    = $_POST['comment'];

          // Validate The Form
          $formErrors = array();
          if(empty($comment)){
            $formErrors[] = "Comment Can't Be Empty";
          }
          // Loop Into Errors Array And Echo It
          foreach($formErrors as $error){
            echo "<div class='alert alert-danger'>" . $error ."</div>" ;
          }

    // Check If There's No Error Proceed The Update Operation
    if(empty($formErrors)){
    // Update The Database With This Info 
    $stmt = $con->prepare("UPDATE comments SET comment = ? WHERE c_id = ?") ;
    $stmt->execute(array($comment, $comid));

    //Echo Success Massage
    $theMsg = "<div class='alert alert-success'>" . $stmt->rowCount() . 'Record Updated' . "</div>";
    redirectHome($theMsg, 'Back');
    }

  }else{
    $theMsg = "<div class='alert alert-danger'>Sorry You Con't Browse This Page Directly</div>";
    redirectHome($theMsg);
  }
  echo '</div>';
/*
=======================================
==== **** Delete Comment Page **** ====
=======================================
*/
}elseif($do == 'Delete'){
  echo '<h1 class="text-center">Delete Comment</h1>
  <div class="container"> ';
  
  // Check If Get Request Comment Is Numeric &Get The Integer Value Of It
  $comid = isset($_GET['comid']) && is_numeric($_GET['comid']) ? intval($_GET['comid']) : 0;

  // Select All Data Depend On This ID
  $check = checkItem('c_id', 'comments', $comid);
  // If There's Such ID The Form
  if($check > 0){

    $stmt = $con->prepare("DELETE FROM comments WHERE c_id = :zcom ");
    $stmt->bindParam(":zcom", $comid);
    $stmt->execute();
    $theMsg = "<div class='alert alert-success'>" . $stmt->rowCount() . 'Record Deleted' . "</div>";
    redirectHome($theMsg,'back');
  }else{
    $theMsg = "<div class='alert alert-danger'> This Id is not Exist</div>";
    redirectHome($theMsg);}
    echo "</div>";
/*
=======================================
==== **** Approve Comment Page **** ====
=======================================
*/
}elseif($do == 'Approve'){
  echo '<h1 class="text-center">Approve Comment</h1>
  <div class="container"> ';
  
  // Check If Get Request comment Is Numeric &Get The Integer Value Of It
  $comid = isset($_GET['comid']) && is_numeric($_GET['comid']) ? intval($_GET['comid']) : 0;

  // Select All Data Depend On This ID
  $check = checkItem('c_id', 'comments', $comid);
  // If There's Such ID The Form
  if($check > 0){

    $stmt = $con->prepare("UPDATE comments SET status =1 WHERE c_id = ? ");
    $stmt->execute(array($comid));
    $theMsg = "<div class='alert alert-success'>" . $stmt->rowCount() . 'Record Approved' . "</div>";
    redirectHome($theMsg, 'back');
  }else{
    $theMsg = "<div class='alert alert-danger'> This Id is not Exist</div>";
    redirectHome($theMsg);}
    echo "</div>";
}
  include $tpl . 'footer.php';
}else{
  header('Location: index.php');
  exit();
}
ob_end_flush();
?>