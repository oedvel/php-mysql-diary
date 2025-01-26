<?php
include 'inc/__settings_and_functions.php';
include 'inc/_validation.php';

$page_title = "Day Range";

// validate the range number and handle invalid values
// also set min and max range values
if (isset($_GET['range'])) {
	$range = $_GET['range'];
	if (is_numeric($range) == false) {
		$range = 10;
	} elseif ($range > 100) {
		$range = 10;
	} elseif ($range < 0) {
		$range = 10;
	}
	// round to deal with decimal point
	$range = ceil($range);
	$range_txt_min = "-$range day";
	$range_txt_max = "+$range day";
} else {
	$range = 10;
	$range_txt_min = '';
	$range_txt_max = '';
}

// validate the date parameter
if (isset($_GET['date'])) {
	$check_date = $_GET['date'];
	$newdate1 = strtotime ( $range_txt_min , strtotime ( $check_date ) ) ;
	$newdate1 = date ( 'Y-m-d' , $newdate1 );
	$newdate2 = strtotime ( $range_txt_max , strtotime ( $check_date ) ) ;
	$newdate2 = date ( 'Y-m-d' , $newdate2 );
} else {
	$check_date = date('Y-m-d');
}

// format

if (isset($_GET['format'])) {
	$format = $_GET['format'];
} else {
	$format = '';
}

if ($format == "y") {
	$format_check = "checked";
} else {
	$format_check = '';
}

// hide category

if (isset($_GET['hide_category'])) {
	$hide_category = $_GET['hide_category'];
} else {
	$hide_category = 'n';
}

if ($hide_category == "y") {
	$hide_category_check = "checked";
} else {
	$hide_category_check = '';
}
$hide_category_check = htmlspecialchars($hide_category_check);

// mode

if (isset($_GET['mode'])) {
	$mode = $_GET['mode'];
} else {
	$mode = '';
}

$format_check = htmlspecialchars($format_check);
$range = htmlspecialchars($range);
$check_date = htmlspecialchars($check_date);

// define HTML for form
$html = "

<h1>Day Range</h1>

<form method='get' action='dayrange.php' class='alert alert-secondary'>
	<input type='hidden' name='mode' value='range'>
	<div class='mb-3'>
		<label for='d' class='form-label'>Date (yyyy-mm-dd)</label>
		<input type='text' class='form-control datepicker' id='date' name='date' value='$check_date' placeholder='📅 Date in YYYY-MM-DD format' required>
	</div>
	<div class='mb-3'>
		<label for='r' class='form-label'>Day Range (number)</label>
		<input type='text' class='form-control' id='range' name='range' value='$range' placeholder='Range in days between 0 and 100' required>
	</div>
	<div class='form-check'>
		<input class='form-check-input' type='checkbox' value='y' id='format' name='format' $format_check>
		<label class='form-check-label' for='format'>Hide Action Links</label>
	</div>
	<div class='form-check'>
		<input class='form-check-input' type='checkbox' value='y' id='hide_category' name='hide_category' $hide_category_check>
		<label class='form-check-label' for='hide_category'>Hide Category</label>
	</div>
	<hr>
	<button type='submit' class='btn btn-success'><i class='fa fa-calendar'></i> Day Range Search</button>
	<button class='btn btn-danger' type='reset'><i class='fa fa-rotate-right'></i> Reset</button>
</form>

";

// run query based on date from querystring

if (isset($_GET['date'])) {
	
	$sql = "SELECT d.fld_id
				 , d.fld_date
				 , d.fld_content
				 , d.fld_cat_id
				 , c.fld_cat
			  FROM diary_days d
				 , diary_categories c
			 WHERE d.fld_cat_id = c.fld_id
			   AND d.fld_date BETWEEN :d1 AND :d2
		  ORDER BY fld_date DESC;";
	
	$date = $_GET['date'];
	
	if(!validateDate($date)) { // check if date is valid - if not - display message
	
		$html = "<p>Date is not valid. <a href='dayrange.php'>Return to day range page</a>.</p>";
		
	} else { // otherwise run SQL query
	
		$title_date0 = date_create($date);
		$title_date0 = htmlspecialchars(date_format($title_date0,"d.m.Y"));
		
		$stmt = $pdo->prepare($sql);
		$stmt->bindParam(':d1', $newdate1);
		$stmt->bindParam(':d2', $newdate2);
		$stmt->execute();
		$results = $stmt->fetchAll();
		
		$title_date1 = date_create($newdate1);
		$title_date1 = date_format($title_date1,"d.m.Y");
		
		$title_date2 = date_create($newdate2);
		$title_date2 = htmlspecialchars(date_format($title_date2,"d.m.Y"));
		
		if ($newdate1 && $newdate2 && $range) {
			$page_title = "Range [" . $title_date1 . " > " . $title_date2 . "]";#
		} elseif ($newdate1 && $newdate2 && !$range) {
			$page_title = " [" . $title_date0 . "] - Day Range";
		} else {
			$page_title = "Day Range";
		}
		
		$html_results = null;
		// get results from the database via buildDiaryRecordsOutput function
		$diary_data = buildDiaryRecordsOutput($results, $date, $format, $str = null, $line_breaks = null, "alert", "format1", $hide_category);
		$data_exists = $diary_data[0];
		$html_results .= $diary_data[1];
		$result_count = $diary_data[2];

		// if no results found, feed back to user
		if ($data_exists === "no" && strlen($mode) > 0) {
			$html .= "<p class='text-danger'>No records found</p>";
		} else {
			$html .= $html_results;
		} // end if checking for results
	
	} // end if checking for valid date
	
} else {
	$page_title = "Day Range";
}

include 'inc/_inc1.php';
?>
<title><?php echo htmlspecialchars($page_title) ?></title>
<?php include 'inc/_inc2.php';?>
<?php include 'inc/_inc_nav.php';?>
<div class="<?php echo htmlspecialchars($container); ?>">

	<?php
	echo $html;
	?>
				
</div>
<?php include 'inc/_inc3.php';?>
<script>
$('.datepicker').datepicker({
    format: 'yyyy-mm-dd',
	autoclose: true,
	todayHighlight: true
});
</script>
</body>
</html>