<?php
include 'inc/__settings_and_functions.php';
include 'inc/_validation.php';

$page_title = "Search";
$form = '';
$html = '';
$sql_str = '';
$start_text = '';

$title = "

<h1>Diary Search</h1>

<nav aria-label='breadcrumb'>
	<ol class='breadcrumb'>
		<li class='breadcrumb-item'><a href='search.php'>Search</a></li>
	</ol>
</nav>

";

if (isset($_POST['str']) && strlen($_POST['str']) > 0) {
	$str = $_POST['str'];
	$str_length = strlen($str);
} else {
	$str = '';
	$str_length = 0;
}

if (isset($_POST['start'])) {
	$start_length = strlen($_POST['start']);
} else {
	$start_length = '';
}

// ################################################################
// CHECK FORM POST VALUES
// ################################################################

// get the year field
if (isset($_POST['yr'])) {
	$vyr = $_POST['yr'];
} else {
	$vyr = '';
}

// category

if (isset($_POST['cat']) && $_POST['cat'] != "n") {
	$cat = $_POST['cat'];
	$sql_cat = " AND c.fld_id = :cat ";
} else {
	$cat = '';
	$sql_cat = '';
}

// month

if (isset($_POST['mn']) && $_POST['mn'] != "n") {
	$mn = $_POST['mn'];
	$sql_month = " AND MONTH(fld_date) = :mn ";
} else {
	$mn = '';
	$sql_month = '';
}

// day

if (isset($_POST['dy']) && $_POST['dy'] != "n") {
	$dy = $_POST['dy'];
	$sql_day = " AND DAY(fld_date) = :dy ";
} else {
	$dy = '';
	$sql_day = '';
}

// year

if (isset($_POST['yr']) && $_POST['yr'] != "n") {
	$yr = $_POST['yr'];
	$sql_yr = " AND YEAR(fld_date) = :yr ";
} else {
	$yr = '';
	$sql_yr = '';
}

// date format

if (isset($_POST['format_of_date'])) {
	$format_of_date = $_POST['format_of_date'];
} else {
	$format_of_date = '';
}

// results count

if (isset($_POST['results_limit']) && $_POST['results_limit'] != "n") {
	$results_limit = $_POST['results_limit'];
	$sql_limit = " LIMIT :limit ";
} else {
	$results_limit = '';
	$sql_limit = null;
}

// format

if (isset($_POST['format'])) {
	$format = $_POST['format'];
} else {
	$format = '';
}

// week


if (isset($_POST['day_of_week']) && $_POST['day_of_week'] != "n") {
	$day_of_week = htmlspecialchars($_POST['day_of_week']);
	if ($day_of_week === "monday") {
		$sql_week = " AND WEEKDAY(fld_date) = 0 ";
	} elseif ($day_of_week === "tuesday") {
		$sql_week = " AND WEEKDAY(fld_date) = 1 ";
	} elseif ($day_of_week === "wednesday") {
		$sql_week = " AND WEEKDAY(fld_date) = 2 ";
	} elseif ($day_of_week === "thursday") {
		$sql_week = " AND WEEKDAY(fld_date) = 3 ";
	} elseif ($day_of_week === "friday") {
		$sql_week = " AND WEEKDAY(fld_date) = 4 ";
	} elseif ($day_of_week === "saturday") {
		$sql_week = " AND WEEKDAY(fld_date) = 5 ";
	} elseif ($day_of_week === "sunday") {
		$sql_week = " AND WEEKDAY(fld_date) = 6 ";
	} elseif ($day_of_week === "week") {
		$sql_week = " AND WEEKDAY(fld_date) < 5 ";
	} elseif ($day_of_week === "weekend") {
		$sql_week = " AND WEEKDAY(fld_date) > 4 ";
	}
} else {
	$day_of_week = NULL;
	$sql_week = NULL;
}

// line breaks

if (isset($_POST['hide_line_breaks'])) {
	$hide_line_breaks = $_POST['hide_line_breaks'];
} else {
	$hide_line_breaks = '';
}

// category

if (isset($_POST['hide_category'])) {
	$hide_category = $_POST['hide_category'];
} else {
	$hide_category = 'n';
}

// exact phrase

if (isset($_POST['exact_phrase'])) {
	
	$exact_phrase = $_POST['exact_phrase'];
	
	if ($exact_phrase == "y") {
		$sql_exact = " AND fld_content RLIKE :exact_text ";
	} elseif ($exact_phrase == "no") {
		$sql_exact = '';
	}
	
} else {
	$exact_phrase = '';
	$sql_exact = '';
}

// order by

if (isset($_POST['ob']) && $_POST['ob'] != "n") {
	
	$ob = $_POST['ob'];
	
	if ($ob === "a") {
		$sql_ob = " fld_date asc ";
	} elseif ($ob === "b") {
		$sql_ob = " fld_date desc ";
	} elseif ($ob === "c") {
		$sql_ob = " length(fld_content) asc ";
	} elseif ($ob === "d") {
		$sql_ob = " length(fld_content) desc ";
	} else {
		$ob = "b";
		$sql_ob = " fld_date desc ";
	}
	
} else {
	$ob = "b";
	$sql_ob = " fld_date desc ";
}

// range

if ($start_length > 0) {
	$start = $_POST['start'];
	$end = $_POST['end'];
	$sql_range = " AND fld_date BETWEEN :start AND :end ";
} else {
	$start = '';
	$end = '';
	$sql_range = '';
}

// search text

if (isset($str) && $str_length > 0) {
	// split words in search string into an array
	$keywords = preg_split('/[\s]+/', $str);
	// count how many words in the search string
	$totalKeywords = count($keywords);
	// loop through array to build SQL statement to handle LIKE statement
	for($i=0 ; $i < $totalKeywords; $i++){
	    $search_bit = ":search" . $i;
	    $sql_str .= " AND fld_content LIKE $search_bit ";
	}
} else {
	$str = '';
}

// ################################################################
// SELECT - CATEGORIES
// ################################################################

$select_cat = "<select class='form-select' name='cat' id='cat'>";
$select_cat .= "<option value='n'>Any</option><option value='' disabled>or choose:</option>";
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

// ################################################################
// SELECT - YEARS
// ################################################################

$select_year = "<select class='form-select' name='yr' id='yr'>";
$select_year .= "<option value='n'>Any</option><option value='' disabled>or choose:</option>";

//year to start with
$startdate = 1995;
 
//year to end with - this is set to current year. You can change to specific year
$enddate = date("Y");
 
$years = range($enddate, $startdate);
 
//print years
foreach($years as $year){
	if ($year == $yr) {
		$year_html = "selected='selected'";
	} else {
		$year_html = '';
	}
	$year_html = htmlspecialchars($year_html);
	$year = htmlspecialchars($year);
	$select_year .= "<option $year_html value='$year'>$year</option>";
}
$select_year .= "</select>";

// ################################################################
// SELECT - MONTHS
// ################################################################

$select_months = "<select name='mn' id='mn' class='form-select'>";
$select_months .= "<option value='n'>Any</option><option value='' disabled>or choose:</option>";

for($m = 1;$m <= 12; $m++){ 
    $month = date("F", mktime(0, 0, 0, $m, 1)); 
	if ($m == $mn) {
		$mn_html = "selected='selected'";
	} else {
		$mn_html = '';
	}
	$mn_html = htmlspecialchars($mn_html);
	$month = htmlspecialchars($month);
    $select_months .= "<option $mn_html value='$m'>$month</option>"; 
}
$select_months .="</select>";

// ################################################################
// SELECT - DAYS
// ################################################################

$select_days = "<select name='dy' id='dy' class='form-select'>";
$select_days .= "<option value='n'>Any</option><option value='' disabled>or choose:</option>";

for($x = 1;$x <= 31; $x++){ 
	if ($x == $dy) {
		$dy_html = "selected='selected'";
	} else {
		$dy_html = '';
	}
	$dy_html = htmlspecialchars($dy_html);
	$x = htmlspecialchars($x);
    $select_days .= "<option $dy_html value='$x'>$x</option>"; 
} 
$select_days .="</select>";

// ################################################################
// SELECT - RESULTS LIMIT
// ################################################################

$select_results_limit = "<select name='results_limit' id='results_limit' class='form-select'>";
$select_results_limit .= "<option value='n'>Any</option><option value='' disabled>or choose:</option>";

for($x = 1;$x <= 365; $x++){ 
	if ($x == $results_limit) {
		$results_limit_html = "selected='selected'";
	} else {
		$results_limit_html = '';
	}
	$results_limit_html = htmlspecialchars($results_limit_html);
	$x = htmlspecialchars($x);
    $select_results_limit .= "<option $results_limit_html value='$x'>$x</option>"; 
} 
$select_results_limit .="</select>";

// ################################################################
// SELECT - FORMAT OF DATE
// ################################################################

$select_format_of_date = "<select name='format_of_date' id='format_of_date' class='form-select'>";

if ($format_of_date == "format1") {
	$sel1 = "selected='selected'";
} else {
	$sel1 = '';
}

if ($format_of_date == "format2") {
	$sel2 = "selected='selected'";
} else {
	$sel2 = '';
}

if ($format_of_date == "format3") {
	$sel3 = "selected='selected'";
} else {
	$sel3 = '';
}

if ($format_of_date == "format4") {
	$sel4 = "selected='selected'";
} else {
	$sel4 = '';
}

$d1 = date('l F jS, Y');
$d2 = date('Y-m-d');
$d3 = date('d-m-Y');
$d4 = date('m-d-Y');

$select_format_of_date .= "<option " . htmlspecialchars($sel1) . " value='format1'> $d1</option>";
$select_format_of_date .= "<option " . htmlspecialchars($sel2) . " value='format2'> $d2 (yyyy-mm-dd)</option>";
$select_format_of_date .= "<option " . htmlspecialchars($sel3) . " value='format3'> $d3 (dd-mm-yyyy)</option>";
$select_format_of_date .= "<option " . htmlspecialchars($sel4) . " value='format4'> $d4 (mm-dd-yyyy)</option>";
$select_format_of_date .="</select>";

// ################################################################
// SELECT - ORDER BY
// ################################################################

$select_ob = "<select name='ob' id='ob' class='form-select'>";

if ($ob == "a") {
	$sel1 = "selected='selected'";
} else {
	$sel1 = '';
}

if ($ob == "b") {
	$sel2 = "selected='selected'";
} else {
	$sel2 = '';
}

if ($ob == "c") {
	$sel3 = "selected='selected'";
} else {
	$sel3 = '';
}

if ($ob == "d") {
	$sel4 = "selected='selected'";
} else {
	$sel4 = '';
}

$select_ob .= "<option " . htmlspecialchars($sel1) . " value='a'>Date Asc</option>";
$select_ob .= "<option " . htmlspecialchars($sel2) . " value='b'>Date Desc</option>";
$select_ob .= "<option " . htmlspecialchars($sel3) . " value='c'>Shortest</option>";
$select_ob .= "<option " . htmlspecialchars($sel4) . " value='d'>Longest</option>";
$select_ob .="</select>";

// ################################################################
// SELECT - WEEKEND OR WEEKDAYS
// ################################################################

$select_week = "<select name='day_of_week' id='day_of_week' class='form-select'>\n";
$select_week .= "<option value='n'>Any</option><option value='' disabled>or choose:</option>";

if ($day_of_week == "sunday") {
	$sel0 = "selected='selected'";
} else {
	$sel0 = NULL;
}

if ($day_of_week == "monday") {
	$sel1 = "selected='selected'";
} else {
	$sel1 = NULL;
}

if ($day_of_week == "tuesday") {
	$sel2 = "selected='selected'";
} else {
	$sel2 = NULL;
}

if ($day_of_week == "wednesday") {
	$sel3 = "selected='selected'";
} else {
	$sel3 = NULL;
}

if ($day_of_week == "thursday") {
	$sel4 = "selected='selected'";
} else {
	$sel4 = NULL;
}

if ($day_of_week == "friday") {
	$sel5 = "selected='selected'";
} else {
	$sel5 = NULL;
}

if ($day_of_week == "saturday") {
	$sel6 = "selected='selected'";
} else {
	$sel6 = NULL;
}

if ($day_of_week == "weekend") {
	$sel7 = "selected='selected'";
} else {
	$sel7 = NULL;
}

if ($day_of_week == "week") {
	$sel8 = "selected='selected'";
} else {
	$sel8 = NULL;
}

$select_week .= "<option $sel1 value='monday'>Monday</option>\n";
$select_week .= "<option $sel2 value='tuesday'>Tuesday</option>\n";
$select_week .= "<option $sel3 value='wednesday'>Wednesday</option>\n";
$select_week .= "<option $sel4 value='thursday'>Thursday</option>\n";
$select_week .= "<option $sel5 value='friday'>Friday</option>\n";
$select_week .= "<option $sel6 value='saturday'>Saturday</option>\n";
$select_week .= "<option $sel0 value='sunday'>Sunday</option>\n";
$select_week .= "<option $sel7 value='week'>Week Days</option>\n";
$select_week .= "<option $sel8 value='weekend'>Weekend</option>\n";
$select_week .="</select>\n";

// ################################################################
// CHECKBOX - FORMAT
// ################################################################

if ($format == "y") {
	$format_check = "checked";
} else {
	$format_check = '';
}
$format_check = htmlspecialchars($format_check);

// ################################################################
// CHECKBOX - LINE BREAKS
// ################################################################

if ($hide_line_breaks == "y") {
	$hide_line_breaks_check = "checked";
} else {
	$hide_line_breaks_check = '';
}
$hide_line_breaks_check = htmlspecialchars($hide_line_breaks_check);

// ################################################################
// CHECKBOX - EXACT PHRASE OR NOT
// ################################################################

if ($exact_phrase == "y") {
	$exact_check = "checked";
} else {
	$exact_check = '';
}
$exact_check = htmlspecialchars($exact_check);

// ################################################################
// CHECKBOX - CATEGORY
// ################################################################

if ($hide_category == "y") {
	$hide_category_check = "checked";
} else {
	$hide_category_check = '';
}
$hide_category_check = htmlspecialchars($hide_category_check);

// ################################################################
// FORM
// ################################################################

$str = htmlspecialchars($str);
$start = htmlspecialchars($start);
$end = htmlspecialchars($end);

$form .= "

<div class='row'>
	<div class='col-md-6'>
		<form action='search.php#results' method='post' id='form_search' class='alert alert-secondary'>
			<input type='hidden' name='method' value='search'>
			
				
					<div class='mb-3'>
						<label for='str' class='form-label'>🔤 Search Words</label>
						<input type='text' class='form-control' id='str' name='str' value='$str' placeholder = '✏️ Search' style='border:1px solid red;'>
					</div>
				
				
					<div class='mb-3'>
						<label for='cat' class='form-label'>📂 Category</label>
						$select_cat
					</div>
				
				
					<div class='mb-3'>
						<label for='day_of_week' class='form-label'>📅 Day of Week etc.</label>
						$select_week
					</div>
					
					<hr>
					
					<div class='row'>
						<div class='col-md-4'>
							<div class='mb-3'>
								<label for='yr' class='form-label'>🟢 Year</label>
								$select_year
							</div>
						</div>
						<div class='col-md-4'>
							<div class='mb-3'>
								<label for='yr' class='form-label'>🔵 Month</label>
								$select_months
							</div>
						</div>
						<div class='col-md-4'>
							<div class='mb-3'>
								<label for='dy' class='form-label'>🟣 Day</label>
								$select_days
							</div>
						</div>
					</div>
					
					<hr>
					
					<div class='row'>
						<div class='col-md-6'>
							<div class='mb-3'>
									<label for='start' class='form-label'>🗓️ From Date</label>
									<input type='text' class='form-control datepicker' id='start' name='start' value='$start' placeholder='Range From'>
							</div>
						</div>
						<div class='col-md-6'>
							<div class='mb-3'>
									<label for='end' class='form-label'>🗓️ To Date</label>
									<input type='text' class='form-control datepicker' id='end' name='end' value='$end' placeholder='Range To'>
							</div>
						</div>
					</div>
					
					<hr>

					<div class='mb-3'>
						<label for='ob' class='form-label'>⬇️ Sort By</label>
						$select_ob
					</div>

					<div class='mb-3'>
						<label for='format_of_date' class='form-label'>📆 Date Format</label>
						$select_format_of_date
					</div>

					<div class='mb-3'>
						<label for='results_limit' class='form-label'>🔢 Results Limit</label>
						$select_results_limit
					</div>

			
			<div class='form-check'>
				<input class='form-check-input' type='checkbox' value='y' id='exact_phrase' name='exact_phrase' $exact_check>
				<label class='form-check-label' for='exact_phrase'>Exact Phrase</label>
			</div>
			<div class='form-check'>
				<input class='form-check-input' type='checkbox' value='y' id='format' name='format' $format_check>
				<label class='form-check-label' for='format'>Hide Action Links</label>
			</div>
			<div class='form-check'>
				<input class='form-check-input' type='checkbox' value='y' id='hide_line_breaks' name='hide_line_breaks' $hide_line_breaks_check>
				<label class='form-check-label' for='hide_line_breaks'>Hide Line Breaks</label>
			</div>
			<div class='form-check'>
				<input class='form-check-input' type='checkbox' value='y' id='hide_category' name='hide_category' $hide_category_check>
				<label class='form-check-label' for='hide_category'>Hide Category</label>
			</div>
			<hr>
			<div class='mb-3'>
				<button type='submit' class='btn btn-success'><i class='fa fa-search'></i> Search</button>
				<button class='btn btn-danger' type='reset'><i class='fa fa-rotate-right'></i> Reset</button>
			</div>
		</form>
	</div>
</div>

<a name='results'></a>
		
";

// ################################################################
// BUILD RESULTS
// ################################################################

if (isset($_POST['method'])) {
	
	$sql = "SELECT d.fld_id
	             , d.fld_date
				 , d.fld_content
				 , d.fld_cat_id
				 , c.fld_cat 
			  FROM diary_days d
			     , diary_categories c 
			 WHERE d.fld_cat_id = c.fld_id $sql_str $sql_cat $sql_month $sql_yr $sql_range $sql_week $sql_exact $sql_day 
    	  ORDER BY $sql_ob
				   $sql_limit";

	$stmt = $pdo->prepare($sql);
	
	if (!empty($sql_str)) {
	    for ($x = 0; $x<$totalKeywords; $x++) {
			// add the percent signs, or make a new copy of the array first if you want to keep the parameters
			$keywords[$x] = "%" . $keywords[$x] . "%";
			$stmt->bindParam(':search' . $x, $keywords[$x]);
	    }
	}
	
	if (!empty($sql_cat)) {
		$stmt->bindParam(':cat', $cat);
	}
	
	if (!empty($sql_month)) {
		$stmt->bindParam(':mn', $mn);
	}

	if (!empty($sql_day)) {
		$stmt->bindParam(':dy', $dy);
	}
	
	if (!empty($sql_yr)) {
		$stmt->bindParam(':yr', $yr);
	}
	
	if (!empty($sql_range)) {
		$stmt->bindParam(':start', $start);
		$stmt->bindParam(':end', $end);
	}

	if (!empty($sql_exact)) {
		$stmt->bindValue(':exact_text', '[[:<:]]' . $str . '[[:>:]]');
	}

	if (!empty($sql_limit)) {
		$stmt->bindParam(':limit', $results_limit);
	}

	$stmt->execute();
	$results = $stmt->fetchAll();

	$html_results = null;
	// get results from the database via buildDiaryRecordsOutput function
	$diary_data = buildDiaryRecordsOutput($results, $date = null, $format, $str, $hide_line_breaks, "alert", $format_of_date, $hide_category);
	$data_exists = $diary_data[0];
	$html .= $diary_data[1];
	$result_count = htmlspecialchars($diary_data[2]);
	
	// if no results found, feed back to user
	if ($data_exists === "no" && $str) {
		
		$page_title = "Search [$str] :: $result_count";
		
		$title = "
		
		<h1>Diary Search</h1>
		
		<nav aria-label='breadcrumb'>
			<ol class='breadcrumb'>
				<li class='breadcrumb-item'><a href='search.php'>Search</a></li>
				<li class='breadcrumb-item active' aria-current='page'><span class='text-danger'>No Records Found</span></li>
			</ol>
		</nav>
		
		
		";
		
		$html .= "<p>Search for <code>$str</code> - no data returned</p>";
		
	} elseif ($data_exists === "no" && !$str) {
		
		$page_title = "Search :: $result_count";
		
		$title = "
		
		<h1>Diary Search</h1>
		
		<nav aria-label='breadcrumb'>
			<ol class='breadcrumb'>
				<li class='breadcrumb-item'><a href='search.php'>Search</a></li>
				<li class='breadcrumb-item active' aria-current='page'><span class='text-danger'>No Records Found</span></li>
			</ol>
		</nav>
		
		
		";
		
		$html .= "<p class='text-danger'>No data returned</p>";

	} else {

		if($str <> '' && $result_count) {
			
			$title = "
			
			<h1>Diary Search</h1>
			
			<nav aria-label='breadcrumb'>
				<ol class='breadcrumb'>
					<li class='breadcrumb-item'><a href='search.php'>Search</a></li>
					<li class='breadcrumb-item active' aria-current='page'>Results: <span class='text-primary-emphasis'>$str</span> (<span class='text-danger-emphasis'>$result_count</span>)</li>
				</ol>
			</nav>
			
			
			";
			
		} elseif($str == '' && $result_count) {
			
			$page_title = "Search :: $result_count";
			
			$title = "
			
			<h1>Diary Search</h1>
			
			<nav aria-label='breadcrumb'>
				<ol class='breadcrumb'>
					<li class='breadcrumb-item'><a href='search.php'>Search</a></li>
					<li class='breadcrumb-item active' aria-current='page'>Results: <span class='text-danger-emphasis'>$result_count</span></li>
				</ol>
			</nav>
			
			";
			
		}
		
		if($str_length > 0) {
			$page_title = "Search [$str] :: $result_count";
			$start_text = "<form action='groupby.php' method='post' id='results'><input type='hidden' name='group' value='$str'><button type='submit' class='btn btn-primary'><i class='fa-solid fa-filter'></i> Group Results By Year</button></form>";
		} else {
			$page_title = "Search";
			$start_text = null;
		}
		
		$start_text .= "<hr>";
		
	}
	
} else {
	
	$start_text = '';
	$page_title = "Search";
	
}

include 'inc/_inc1.php';
?>
<title><?php echo htmlspecialchars($page_title) ?></title>
<?php include 'inc/_inc2.php';?>
<?php include 'inc/_inc_nav.php';?>
<div class="<?php echo htmlspecialchars($container); ?>">

	<?php
	echo $title;
	echo $form;
	echo $start_text;
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