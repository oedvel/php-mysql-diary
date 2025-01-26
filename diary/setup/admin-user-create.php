<?php
include '../inc/__settings_and_functions.php';

$sql = "SELECT fld_id, fld_pwd FROM diary_users WHERE fld_role = 'admin'";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if($row) {
	
	$html = "<h1>Create Admin User</h1>";
	$html .= "<div class='alert alert-danger'>Admin user already exists.</div>";
	
} else {
	
	// generate random password.
	// generateRandomString function is in the `inc\__settings_and_functions.php` folder
	$password = generateRandomString();
		
	$fld_hash = password_hash($password, PASSWORD_BCRYPT, array("cost" => 11));

	$sql = "INSERT INTO diary_users (fld_username
					  , fld_pwd
					  , fld_role
					  , fld_creation_date) 
				 VALUES ('diaryadminuser'
					  , :fld_pwd
					  , 'admin'
					  , now())";

	$stmt = $pdo->prepare($sql);
	$stmt->bindParam(':fld_pwd', $fld_hash);
	$stmt->execute();
	
	$html = "
	
	<h1>Create Admin User</h1>
	
	<div class='alert alert-success'>Admin user created successfully. Make a note of login details below.</div>
	
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
<title>Admin User Setup</title>
<?php include '../inc/_inc2.php';?>
<div class="<?php echo $container ?>">

	<?php
	echo $html;
	?>
				
</div>
<?php include '../inc/_inc3.php';?>
</body>
</html>