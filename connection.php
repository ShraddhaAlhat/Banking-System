<?php
$servername="localhost";
$username ="root";
$password = "";
$databse ="bank";

$conn= mysqli_connect($servername,$username,$password,$databse);

if(!$conn){
    die("Sorry we failed to connect:". mysqli_connect_error());
}

function check_name($from,$to,$amount){
  

     $user       = 'root';
     $password   = '';
     $dns ='mysql:host=localhost;dbname=bank';

    
    try {
        
        $db = new PDO($dns, $user, $password);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $db->begintransaction();

 
        $sql = 'SELECT amount FROM customers WHERE Name=:from';
        $stmt = $db->prepare($sql);
        $stmt->execute(array(":from" => $from));
        $availableAmount = (int) $stmt->fetchColumn();
        $stmt->closeCursor();

        if ($availableAmount < $amount) {
            throw new Exception('Insufficient amount to transfer!');
        }
        
    
        $st1 = $db->prepare(
            "update customers set
            `Amount`= Amount - :amt where Name = :fro "
        );
        $st2 = $db->prepare(
            "update customers set
            `Amount`= Amount + :amt where Name = :to "
        );
    
        $st3 = $db->prepare(
            "INSERT INTO `transaction` ( `From`, `To`, `Amount`) VALUES ( :from, :to, :amount)"
        );
        
    
        $st1->bindValue(':amt', $amount, PDO::PARAM_INT);
        $st1->bindValue(':fro', $from, PDO::PARAM_STR);
        $st2->bindValue(':amt', $amount, PDO::PARAM_INT);
        $st2->bindValue(':to', $to, PDO::PARAM_STR);
        $st3->bindValue(':amount', $amount, PDO::PARAM_INT);
        $st3->bindValue(':to', $to, PDO::PARAM_STR);
        $st3->bindValue(':from', $from, PDO::PARAM_STR);

        $st1->execute();
        $st2->execute();
        $st3->execute();
        
        if(!$st2->rowCount()||!$st1->rowCount()){ 
            throw new Exception('User not Found!');
        }

        if(!$st3->rowCount()) throw new Exception('Could not insert data into Transaction Entry!');    
        if($db->commit()) 
            notification("success","Success","Transaction successfull.");
        else 
            notification("danger","Error","Transaction failed!");
        
    } catch (PDOException $e) { 
        notification("danger","Error",$e->getMessage()); 
    }catch(Exception $e){   
        notification("danger","Error",$e->getMessage());
        $db->rollBack();   
    }
}
 
function notification($type,$strong,$message){
    echo '<div class="alert alert-'.$type.' alert-dismissible fade show" role="alert">
    <strong>'.$strong.'</strong> '.$message.'
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>';
}

?>