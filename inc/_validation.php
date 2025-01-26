<?php
// https://stackoverflow.com/questions/3128985/php-login-system-remember-me-persistent-cookie
// https://stackoverflow.com/questions/3128985/php-login-system-remember-me-persistent-cookie/30135526#30135526
// https://stackoverflow.com/questions/12091951/php-sessions-login-with-remember-me

session_start();

if (empty($_SESSION['userid']) && empty($_COOKIE['remember'])) { // No Userid Session and no Cookie, go back to homepage

    header('Location:login.php?issue=no-user-id-session-and-no-cookie-login-1');
    exit;
    
} elseif (empty($_SESSION['userid']) && !empty($_COOKIE['remember'])) { // No Userid Session, but Remember Cookie exists - regenerate everything and let user in
    
    list($selector, $authenticator) = explode(':', $_COOKIE['remember']);
    
    $sql = "SELECT fld_id, fld_userid, fld_token FROM diary_auth_tokens WHERE fld_selector = :sel";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':sel', $selector);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
	
	if($row) { // if record found
	
		$token = $row['fld_token'];
		
		if($token) {
		
			if (hash_equals($token, hash('sha256', base64_decode($authenticator)))) { // token in cookie = token in database
				
				// clear out old session data for this user
				$sql = "DELETE FROM diary_auth_tokens WHERE fld_selector = :sel";
				$stmt = $pdo->prepare($sql);
				$stmt->bindParam(':sel', $selector);
				$stmt->execute();
				
				// Set the session fld_id since this has been successful
				$_SESSION['userid'] = $row['fld_userid'];
				
				// regenerate login token
				$user_id = $row['fld_userid'];
				$selector = base64_encode(openssl_random_pseudo_bytes(9));
				$authenticator = openssl_random_pseudo_bytes(33);
				$token_auth = hash('sha256', $authenticator);
				$exp = date('Y-m-d H:i:s', time() + 864000);

				// recreate cookie
				$cookie_value = $selector.':'.base64_encode($authenticator);
				$cookie_expire = time() + 864000;				
				diaryCookie("remember",$cookie_value,$cookie_expire,$server_name,$tls_flag);

				// create a new session row for this user
				$sql = "INSERT INTO diary_auth_tokens (fld_selector, fld_token, fld_userid, fld_expiration_date, fld_creation_date, fld_tag) VALUES (:selector, :token_auth, :userid, :exp, now(), 'val')";
				
				$stmt = $pdo->prepare($sql);
				
				$stmt->bindParam(':selector', $selector);
				$stmt->bindParam(':token_auth', $token_auth);
				$stmt->bindParam(':userid', $user_id);
				$stmt->bindParam(':exp', $exp);
				
				//r($selector);
		   
				$stmt->execute();
				
			} else { // token in cookie != token in database
				
				header('Location:login.php?issue=token-in-cookie-does-not-match-token-in-database');
				exit;
				
			}
			
		}
		
	} else { // no record in database found for selector in cookie
		
		header('Location:login.php?issue=selector-in-cookie-not-in-database');
		exit;
		
	}
    
}

// now check if user has valid session?

if (empty($_SESSION['userid'])) { // No Userid Session and no Cookie, go back to homepage

    header('Location:login.php?issue=no-user-id-session-and-no-cookie-login-2');
    exit;
    
}

?>