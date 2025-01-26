<?php
include 'inc/__settings_and_functions.php';

if (!isset($_GET['mode']) && ($_SERVER['REQUEST_METHOD'] != 'POST')) {

	// ####################################################################################################################################################################################
	// LOGIN TABLE
	// ####################################################################################################################################################################################

	$form = "
	
		<h1>Login</h1>
		<hr>		
		<div class='row'>
			<div class='col-md-3'>
				<form method='post' id='theForm' action='login.php' class='alert alert-secondary'>
					<input type='hidden' name='mode' value='login'>
					<div class='mb-3'>
						<label for='fld_username' class='form-label'>Username</label>
						<input class='form-control' type='text' id='fld_username' name='fld_username' required>
					</div>
					<div class='mb-3'>
						<label for='fld_pwd' class='form-label'>Password</label>
						<input class='form-control' type='password' id='fld_pwd' name='fld_pwd' required>
					</div>
					<div class='form-check'>
						<label class='form-check-label' for='fld_rem'>
							<input class='form-check-input' type='checkbox' value='y' name='fld_rem' id='fld_rem'>
							Remember Me
						</label>
					</div>
					<hr>
					<div class='mb-3'>
						<button type='submit' class='btn btn-success'><i class='fa-solid fa-unlock'></i> Login</button>
					</div>
				</form>
			</div>
		</div>
		
		";

}

// ####################################################################################################################################################################################
// 	PROCESS FORM DATA
// ####################################################################################################################################################################################

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

	$mode = $_POST['mode'];
	
	if ($mode == 'login') {
        
		$fld_username = $_POST['fld_username'];
		$fld_pwd_form = $_POST['fld_pwd'];
        
        if (isset($_POST['fld_rem'])) {
            $fld_rem = $_POST['fld_rem'];
        }
        
        $stmt = $pdo->prepare('SELECT fld_id, fld_pwd FROM diary_users WHERE fld_username = :fld_username LIMIT 1');
        $stmt->bindParam(':fld_username', $fld_username);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
		
			// if record found for user
			
			if($row) {

			$check_password = password_verify($fld_pwd_form, $row['fld_pwd']);
			
			if ($check_password == true) { // password is valid
			
				$user_id = $row['fld_id'];
			
				if (isset($fld_rem)) { // remember me ticked
				
					// does cookie already exist?
					// if so, delete auth_token record for it
					if (!empty($_COOKIE['remember'])) { // remember cookie exists

						list($selector, $authenticator) = explode(':', $_COOKIE['remember']);
						
						// clear out old session data for this user
						$sql = "DELETE FROM diary_auth_tokens WHERE fld_selector = :sel";
						$stmt = $pdo->prepare($sql);
						$stmt->bindParam(':sel', $selector);
						$stmt->execute();
						
						// delete remember cookie
						if (isset($_COOKIE['remember'])) {
							unset($_COOKIE['remember']);
							setcookie('remember', '', time() - 3600, '/'); // empty value and old timestamp
						}
						
					}
			
					$selector = base64_encode(openssl_random_pseudo_bytes(9));
					$authenticator = openssl_random_pseudo_bytes(33);
					$token_auth = hash('sha256', $authenticator);
					$exp = date('Y-m-d H:i:s', time() + 864000);
					
					// set new cookie up
					$cookie_value = $selector.':'.base64_encode($authenticator);
					$cookie_expire = time() + 864000;				
					diaryCookie("remember",$cookie_value,$cookie_expire,$server_name,$tls_flag);
					
					// create a new session row for this user
					$sql = "INSERT INTO diary_auth_tokens (fld_selector, fld_token, fld_userid, fld_expiration_date, fld_creation_date, fld_tag) VALUES (:selector, :token_auth, :userid, :exp, now(), 'log')";
					$stmt = $pdo->prepare($sql);
					$stmt->bindParam(':selector', $selector);
					$stmt->bindParam(':token_auth', $token_auth);
					$stmt->bindParam(':userid', $user_id);
					$stmt->bindParam(':exp', $exp);
					$stmt->execute();
					
					session_start();
					$_SESSION['userid'] = $user_id;
					
					header('Location:index.php#remember-ticked');
					exit;
				
				} else { // remember me NOT ticked
					
					session_start();
					$_SESSION['userid'] = $user_id;
					
					header('Location:index.php#remember-not-ticked');
					exit;
					
				}
		
			} else { // password is not valid
				
				$html = "<h1>Login</h1><hr><p class='text-danger'>Login failed. <a href='login.php'>Return to login page</a>.</p>";
				
			}
			
		} else {

			$html = "<h1>Login</h1><hr><p class='text-danger'>User not found. <a href='login.php'>Return to login page</a>.</p>";
		
		} // end if for no row in database
		
	}

}

include 'inc/_inc1.php';
?>
<title>Login</title>
<?php include 'inc/_inc2.php';?>

    <div class='<?php echo htmlspecialchars($container); ?>'>
    
    <?php
    if (isset($form)) {
        echo $form;
    }
	
    if (isset($html)) {
        echo $html;
    }
    ?>
        
    </div>

<?php include 'inc/_inc3.php';?>

</body>
</html>