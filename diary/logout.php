<?php
include 'inc/__settings_and_functions.php';

session_start();

if (empty($_SESSION['userid']) && empty($_COOKIE['remember'])) { // No Userid Session and no Cookie, go back to homepage

    header('Location:login.php?issue=no-user-id-session-and-no-cookie-logout');
    exit;
    
}

if (!empty($_COOKIE['remember'])) { // remember cookie exists

    list($selector, $authenticator) = explode(':', $_COOKIE['remember']);
	
	//clear out old session data for this user
	$sql = "DELETE FROM diary_auth_tokens WHERE fld_selector = :sel";
	$stmt = $pdo->prepare($sql);
	$stmt->bindParam(':sel', $selector);
	$stmt->execute();
    
	// unset cookies
	unset($_COOKIE['remember']);
	// 2nd parameter passes empty cookie value and 3rd parameter (expiration value) is date in the past
	// Both of those will remove the cookie
	$cookie_expire = time() - 864000;
	diaryCookie("remember",'',$cookie_expire,$server_name,$tls_flag);

}

if (!empty($_SESSION['userid'])) { // userid session cookie exists

	session_destroy(); // destroy session
	setcookie("PHPSESSID","",time()-3600,"/"); // delete session cookie
	
}

header('Location:login.php?ref=logged-out');
exit;

?>