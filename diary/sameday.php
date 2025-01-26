<?php
include 'inc/__settings_and_functions.php';
include 'inc/_validation.php';

// if date values from query string
if (isset($_GET['d']) && isset($_GET['m'])) {
	$str_d = $_GET['d'];
	$str_m = $_GET['m'];
	if (is_numeric($str_d) == false) {
		$str_d = "10";
	}
	if (is_numeric($str_m) == false) {
		$str_m = "10";
	}
	$fld_date = $str_m . "-" . $str_d;
} else {
	$str_d = '';
	$str_m = '';
}

// format

if (isset($_POST['format'])) {
	$format = $_POST['format'];
} else {
	$format = '';
}

if ($format == "y") {
	$format_check = "checked";
} else {
	$format_check = '';
}
$format_check = htmlspecialchars($format_check);

// hide category

if (isset($_POST['hide_category'])) {
	$hide_category = $_POST['hide_category'];
} else {
	$hide_category = 'n';
}

if ($hide_category == "y") {
	$hide_category_check = "checked";
} else {
	$hide_category_check = '';
}
$hide_category_check = htmlspecialchars($hide_category_check);

// if form posted

// date

if (isset($_POST['fld_date'])) {
	$fld_date = $_POST['fld_date'];
}

// category

if (isset($_POST['cat']) && $_POST['cat'] != "n") {
	$cat = $_POST['cat'];
	$sql_cat = " AND c.fld_id = :cat ";
} else {
	$cat = '';
	$sql_cat = '';
}

// if no date from query string or form, use today's date

if (!isset($fld_date)) {
	$fld_date = date('m') . "-" . date('d');
}

// ################################################################
// SELECT - CATEGORIES
// ################################################################

$select_cat = "<select class='form-select' name='cat' id='cat'>";
$select_cat .= "<option value='n'>Category</option>";
$sql = "SELECT fld_id, fld_cat FROM diary_categories ORDER BY fld_cat";
$stmt = $pdo->prepare($sql);
$stmt->execute();

while ($row = $stmt->fetch()){
	$fld_id = htmlspecialchars($row['fld_id']);
	$fld_cat = htmlspecialchars($row['fld_cat']);
	if ($fld_id == $cat) {
		$cat_html = "selected='selected'";
	} else {
		$cat_html = '';
	}
	$cat_html = htmlspecialchars($cat_html);
	$select_cat .= "<option $cat_html value='$fld_id'>$fld_cat</option>";
}
$select_cat .= "</select>";

$fld_date = htmlspecialchars($fld_date);
$str_d = htmlspecialchars($str_d);
$str_m = htmlspecialchars($str_m);

$html = "

<h1>Sameday Search</h1>

<form method='post' action='sameday.php' class='alert alert-secondary'>
	<div class='mb-3'>
		<label for='fld_date' class='form-label'>Sameday Date (mm-dd)</label>
		<input type='text' class='form-control datepicker' id='fld_date' name='fld_date' value='$fld_date' placeholder='Date in MM-DD format' required>
	</div>
	<div class='mb-3'>
		<label for='cat' class='form-label'>Category</label>
		$select_cat
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
	<button type='submit' class='btn btn-success'><i class='fa fa-calendar'></i> Sameday Search</button>
	<button class='btn btn-danger' type='reset'><i class='fa fa-rotate-right'></i> Reset</button>
</form>

";

if (isset($_POST['fld_date'])) {
	
	$sameday_date = $_POST['fld_date'];
	
	$m = py_slice($sameday_date, ':2');
	$d = py_slice($sameday_date, '-2:');
	
	$sql = "SELECT d.fld_id
				 , d.fld_date
				 , d.fld_content
				 , c.fld_cat
			  FROM diary_days d
				 , diary_categories c
			 WHERE d.fld_cat_id = c.fld_id
			   AND MONTH(d.fld_date) = :m
			   AND DAY(d.fld_date) = :d
			   $sql_cat
		  ORDER BY fld_date DESC;";
			 
	$stmt = $pdo->prepare($sql);
	$stmt->bindParam(':m', $m);
	$stmt->bindParam(':d', $d);
	
	if (!empty($sql_cat)) {
		$stmt->bindParam(':cat', $cat);
	}
	
	$stmt->execute();
	$results = $stmt->fetchAll();
	
	if ($d && $m) {
		$page_title = "Same Day [" . $d . "/" . $m . "]";
	} else {
		$page_title = "Same Day";
	}
	
	$html_results = null;
	// get results from the database via buildDiaryRecordsOutput function
	$diary_data = buildDiaryRecordsOutput($results, $date = null, $format, $str = null, $hide_line_breaks = null, "alert", "format1", $hide_category);
	$data_exists = $diary_data[0];
	$html_results .= $diary_data[1];
	$result_count = $diary_data[2];
	
	// if no results found, feed back to user
	if ($data_exists === "no") {
		$html .= "<p class='text-danger'>No records found</p>";
	} else {
		$html .= "<p>Same Day Count: <strong>$result_count</strong>";			
		$html .= $html_results;			
	} // end if checking for results

} else {
	$page_title = "Same Day";
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
    format: 'mm-dd',
	autoclose: true,
	todayHighlight: true
});
</script>
</body>
</html>