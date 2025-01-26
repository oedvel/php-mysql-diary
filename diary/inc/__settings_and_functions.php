<?php
// variables
// #####################################################################

// full width or narrower width page container (narrower width: container, full width: container-fluid)
$container = "container-fluid";

// server
$pageName = $_SERVER['PHP_SELF'];
$server_name = strtolower($_SERVER['SERVER_NAME']);

// if server name is localhost, set tls flag as false, assuming SSL certificate is not installed on local computer
// flag is passed to diaryCookie function
// if your local server is not called localhost, change local server name below to relevant value
if($server_name === "localhost"){
	$tls_flag = false;
} else {
	$tls_flag = true;
}

// Database
// #####################################################################

$config['db'] = array(
    'host'      => '127.0.0.1',
    'username'  => 'your-user',
    'password'  => 'your-password',
    'dbname'    => 'diary'
);

// Define PDO connection
// https://phpdelusions.net/pdo
// https://phpdelusions.net/delusion/try-catch

$pdo = new PDO('mysql:host=' .$config['db']['host']. ';dbname=' .$config['db']['dbname'], $config['db']['username'], $config['db']['password']);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
$pdo->setAttribute(PDO::NULL_EMPTY_STRING, true);
$pdo->exec("SET CHARACTER SET utf8mb4");
$pdo->exec("set names utf8mb4");

// Functions
// #####################################################################

// https://www.php.net/manual/en/function.checkdate.php
// Is date valid?
function validateDate($date, $format = 'Y-m-d') {
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) == $date;
}

// http://php.net/manual/en/function.substr.php
function py_slice($input, $slice) {
    $arg = explode(':', $slice);
    $start = intval($arg[0]);
    if ($start < 0) {
        $start += strlen($input);
    }
    if (count($arg) === 1) {
        return substr($input, $start, 1);
    }
    if (trim($arg[1]) === '') {
        return substr($input, $start);
    }
    $end = intval($arg[1]);
    if ($end < 0) {
        $end += strlen($input);
    }
    return substr($input, $start, $end - $start);
}

// https://stackoverflow.com/a/11845955/4532066
// for search results
function highlighter_text($text, $words) {
    $split_words = explode( " " , $words );
    foreach($split_words as $word)
    {
        $text = preg_replace("|($word)|Ui" ,
            "<span style='background:yellow'>$1</span>" , $text );
    }
    return $text;
}

// https://stackoverflow.com/questions/4356289/php-random-string-generator/31107425#31107425
// generate random string for admin password
function generateRandomString($length = 15) {
	$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	$charactersLength = strlen($characters);
	$randomString = '';
	for ($i = 0; $i < $length; $i++) {
		$randomString .= $characters[random_int(0, $charactersLength - 1)];
	}
	return $randomString;
}

// basic function to output the value of a variable
function sillyDebug($varname, $var) {
	$output = "<div class='alert alert-danger'><strong>$varname</strong>: $var</div>";
	echo $output;
}

// https://stackoverflow.com/a/37641037/4532066
function safeOutput($str) {
	$output = htmlspecialchars($str, ENT_QUOTES, 'utf8mb4');
	return $output;
}

function beforeAndAfter($date){
	
	$date_before = new DateTime($date);
	$date_before->modify('-1 day');
	$date_before = $date_before->format('Y-m-d');

	$date_after = new DateTime($date);
	$date_after->modify('+1 day');
	$date_after = $date_after->format('Y-m-d');

	return array($date_before, $date_after);

}

// loop through database results and display diary entries

function buildDiaryRecordsOutput($results, $date, $format, $str, $hide_line_breaks, $style, $date_format) {
	
	$result_count = 0;
	$data_exists = "no";
	$output_html = '';
	$actions_html = '';
	
	/*
	Date Format Parameter
	
	format1: l F jS, Y = 	Tuesday February 6th, 2024
	format2: Y-m-d = 		2024-02-06
	format3: dd-mm-yyyy = 	02-06-2024
	format4: mm-dd-yyyy = 	06-02-2024	
	*/
	
	if($date_format === "format1") {
		$display_format = "l F jS, Y";
	} elseif($date_format === "format2") {
		$display_format = "Y-m-d";			
	} elseif($date_format === "format3") {
		$display_format = "d-m-Y";
	} elseif($date_format === "format4") {
		$display_format = "m-d-Y";
	} else {
		$display_format = "l F jS, Y";
	}
	
	foreach ($results as $diary_entry => $day) {
		
		$data_exists = 		"yes";
		$fld_id = 			htmlspecialchars($day['fld_id']);
		$fld_date = 		$day['fld_date'];
		$fld_date_display = htmlspecialchars(date($display_format, strtotime($fld_date)));
		$fld_date_day = 	htmlspecialchars(date('d',strtotime($fld_date)));
		$fld_date_month = 	htmlspecialchars(date('m',strtotime($fld_date)));
		$fld_content = 		htmlspecialchars($day['fld_content']);
		$fld_cat = 			htmlspecialchars($day['fld_cat']);
		$fld_day_num = 		date('N', strtotime($fld_date));
		$fld_word_ct = 		htmlspecialchars(str_word_count($fld_content));
		
		// add line breaks to content for displaying, unless option set to hide line breaks
		if(isset($hide_line_breaks) && $hide_line_breaks != 'y') {
			$fld_content = nl2br($fld_content);
		}	
		
		// highlight words if search string is populated
		if(isset($str) && strlen($str) > 0) {
			$fld_content = highlighter_text($fld_content, $str);
		}
		
		// logic to set the colour of the alert box depending on the day of the week
		if ($fld_day_num == 6) {
			$card_class = "success";
		} elseif ($fld_day_num == 7) {
			$card_class = "info";
		} elseif ($fld_day_num < 6) {
			$card_class = "light";
		}

		// logic to mark the selected day for dayrange search
		if($date === $fld_date) {
			$dayrange_current_day_id = " id='selected' ";
			$dayrange_current_day_marker = " 🔴 ";
		} else {
			$dayrange_current_day_id = null;
			$dayrange_current_day_marker = null;
		}
		
		// html for actions (edit / delete etc)
		if($format != 'y') {
			
			$result_count_display = $result_count + 1;
			$actions_html = "
			#$result_count_display / <abbr title='Diary Entry Word Count'>$fld_word_ct</abbr> / 
			<a title='Edit' href='index.php?id=$fld_id&mode=edit'>Edit</a> / 
			<a title='Delete' href='index.php?id=$fld_id&mode=delete'>Delete</a> / 
			<a title='Edit' href='index.php?date=$fld_date&mode=single'>Single</a> / 
			<a title='Same Day' href='sameday.php?d=$fld_date_day&m=$fld_date_month'>Sameday</a> / 
			<a title='Date Range' href='dayrange.php?date=$fld_date#$fld_date'>Day Range</a><br>
			";
			
		} else {
			
			$actions_html = '';
			
		}
			
		if($style === "alert"){
			
			$output_html .= "

			<div class='alert alert-$card_class diary-entry' $dayrange_current_day_id>
				<p>
				<span style='font-weight:bold;'>$fld_date_display</span>
				<br>
				$actions_html
				Category: $fld_cat
				<br>
				$dayrange_current_day_marker $fld_content
				</p>
			</div>

				";
				
		} elseif($style === "card") {
			
			$fld_content = nl2br($fld_content);
			
			$output_html .= "
			
			<div class='card text-bg-primary mb-6' $dayrange_current_day_id>
				<div class='card-header'>Category: $fld_cat</div>
				<div class='card-body'>
					<p class='card-text'>$dayrange_current_day_marker $fld_content</p>
				</div>
			</div>
			
			<hr>
			
				";
		
		}

		$result_count ++;
		
	}
	
	$result_count = htmlspecialchars(number_format($result_count));
	
	return array($data_exists,$output_html,$result_count);
	
}

function diaryCookie($name, $value, $expire, $server_name, $secure){

	setcookie(
		 $name, // name
		 $value, // value
		 $expire, // expire
		 '/', // path
		 $server_name, // domain
		 $secure, // TLS-only
		 true  // http-only
	);

}

?>
