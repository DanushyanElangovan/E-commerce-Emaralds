<?php
session_start();
include "../includes/db_connect.php";
$user_id = $_SESSION['user_id'];

$firstname = $_POST["firstname"];
$lastname = $_POST["lastname"];
$email = $_POST["email"];
$phonenumber = $_POST["phonenumber"];
$shippingaddress = $_POST["shippingaddress"];
$current_password = $_POST["current_password"];
$new_password = $_POST["new_password"];
$confirm_password = $_POST["confirm_password"];

try {
  if ($new_password == NULL) {
    // Update without password change
    $sql = "UPDATE user SET email=?, firstname=?, lastname=?, Phonenumber=? WHERE userID=?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$email, $firstname, $lastname, $phonenumber, $user_id]);

    $sql1 = "UPDATE customer SET email=?, firstname=?, lastname=?, Phonenumber=?, shippingaddress=? WHERE username=?";
    $stmt1 = $pdo->prepare($sql1);
    $stmt1->execute([$email, $firstname, $lastname, $phonenumber, $shippingaddress, $_SESSION['cust_username']]);

    $_SESSION["success"] = 1;
    echo "<script>window.history.back();</script>";
  } else {
    // Password change requested
    $stmt = $pdo->prepare("SELECT * FROM USER WHERE userID = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($new_password == $confirm_password) {
      if (password_verify($current_password, $user['password'])) {
        // Update user info
        $sql = "UPDATE user SET email=?, firstname=?, lastname=?, Phonenumber=? WHERE userID=?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$email, $firstname, $lastname, $phonenumber, $user_id]);

        // Update customer info
        $sql1 = "UPDATE customer SET email=?, firstname=?, lastname=?, Phonenumber=?, shippingaddress=? WHERE username=?";
        $stmt1 = $pdo->prepare($sql1);
        $stmt1->execute([$email, $firstname, $lastname, $phonenumber, $shippingaddress, $_SESSION['cust_username']]);

        $_SESSION["success"] = 1;
        echo "<script>window.history.back();</script>";
      } else {
        $_SESSION["error"] = 1;
        echo "<script>window.history.back();</script>";
      }
    } else {
      $_SESSION["error"] = 2; // Different error for password mismatch
      echo "<script>window.history.back();</script>";
    }
  }
} catch (PDOException $e) {
  $_SESSION["error"] = "Database error: " . $e->getMessage();
  echo "<script>window.history.back();</script>";
}
