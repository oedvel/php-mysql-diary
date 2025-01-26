<?php
include '../inc/__settings_and_functions.php';

$sql = "SELECT 'y' FROM diary_users WHERE fld_role = 'admin'";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$row) {
	
	$html = "<h1>Admin User Password Reset</h1>";
	$html .= "<div class='alert alert-danger'>Admin user does not exist.</div>";
	
} else {
	
	// generate random password.
	// generateRandomString function is in the `inc\__settings.php` folder
	$password = generateRandomString();
		
	$fld_hash = password_hash($password, PASSWORD_BCRYPT, array("cost" => 11));

	$sql = "UPDATE diary_users
			   SET fld_pwd = :fld_pwd 
				 , fld_update_date = now()
				 , fld_password_reset_date = now()
			 WHERE fld_username = 'diaryadminuser'
			   AND fld_role = 'admin'
			 LIMIT 1;";

	$stmt = $pdo->prepare($sql);
	$stmt->bindParam(':fld_pwd', $fld_hash);
	$stmt->execute();
	
	$html = "
	
	<h1>Admin User Password Reset</h1>
	
	<div class='alert alert-success'>Admin user password reset successfully. Make a note of login details below.</div>
	
	<ul>
		<li>Username: <code>diaryadminuser</code></li>
		<li>Password: <code>$password</code></li>
	</ul>
	
	<hr>
	
	<p>You can now log in as the admin user on the <a href='../login.php'>login page</a>.</p>
	
	";
		
}

include '../inc/_inc1.php';
?>
<title>Admin User Password Reset</title>
<?php include '../inc/_inc2.php';?>
<div class="<?php echo $container ?>">

	<?php
	echo $html;
	?>
				
</div>
<?php include '../inc/_inc3.php';?>
</body>
</html>