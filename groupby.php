<?php
include 'inc/__settings_and_functions.php';
include 'inc/_validation.php';

// initialise variables
$form = '';
$html = '';
$snip = '';
$sql_str = '';
$page_title = "Group By Search";

if (isset($_POST['group'])) {
	$group = $_POST['group'];
	$group_length = strlen($group);
} else {
	$group = '';
	$group_length = 0;
}

$group = htmlspecialchars($group);

// define HTML for form
$form .= "

<h1>Group By Search</h1>

<form method='post' action='groupby.php' class='alert alert-secondary'>
		<div class='mb-3'>
			<label for='group' class='form-label'>Group By Search Text</label>
			<input class='form-control' type='text' id='group' name='group' value='$group'>
		</div>
	</fieldset>
	<button type='submit' class='btn btn-success'><i class='fa fa-search'></i> Group By</button>
	<button class='btn btn-danger' type='reset'><i class='fa fa-rotate-right'></i> Reset</button>
</form>

";

// if group variable found in posted form
if (isset($group) && $group_length > 0) {
	
	// build sql etc
	// run query

	// search text

	if ($group_length > 0) {
		// split words in search string into an array
		$keywords = preg_split('/[\s]+/', $group);
		// count how many words in the search string
		$totalKeywords = count($keywords);
		// loop through array to build SQL statement to handle LIKE statement
		for($i=0 ; $i < $totalKeywords; $i++){
			$search_bit = ":search" . $i;
			$sql_str .= " AND fld_content LIKE $search_bit ";
		}
	} else {
		$group = '';
	}

	$sql = "SELECT YEAR(d.fld_date) post_year
				 , COUNT(*) ct
			  FROM diary_days d
			     , diary_categories c 
			 WHERE d.fld_cat_id = c.fld_id AND d.fld_content RLIKE :exact_text $sql_str
		  GROUP BY YEAR(d.fld_date)
		  ORDER BY 1 DESC";
	
	$stmt = $pdo->prepare($sql);

	if (!empty($sql_str)) {
	    for ($x = 0; $x<$totalKeywords; $x++) {
			// add the percent signs, or make a new copy of the array first if you want to keep the parameters
			$keywords[$x] = "%" . $keywords[$x] . "%";
			$stmt->bindParam(':search' . $x, $keywords[$x]);
	    }
	}
	
	$stmt->bindValue(':exact_text', '[[:<:]]' . $group . '[[:>:]]');
	$stmt->execute();

	// loop through results and built HTML table rows
	
	while ($row = $stmt->fetch()){
		
		$data = true;
		$page_title = "Group By: [$group]";
		$fld_year = htmlspecialchars($row['post_year']);
		$fld_count = number_format(htmlspecialchars($row['ct']));
		
		$snip .= "
		
		<tr>
			<td>
				<form action='search.php' method='post'>
					<input type='hidden' name='method' value='search'>
					<input type='hidden' name='yr' value='$fld_year'>
					<input type='hidden' name='exact_phrase' value='y'>
					<input type='hidden' name='str' value='$group'>
					<button type='submit' class='btn btn-primary'>$fld_year</button>
				</form>
			</td>
			<td>$fld_count</td>
		</tr>
			
		";

	}
	
	// if results found, build HTML table to hold results
	
    if (isset($data)) {
        
		$page_title = "Group By [$group]";
		
		$html .= "
		
		<p>Click the grey button to drill down to see the results for the word you have searched for, for that particular year.</p>
		
		<div class='col-md-6'>
			<div class='table-responsive'>
				<table class='table table-bordered table-hover table-condensed table-striped'>
					<tr>
					<th>Year</th>
					<th>Diary Entry Count</th>
					</tr>
					$snip
				</table>
			</div>
		</div>
		
		";
        
    } else { // otherwise confirm no records found
        
        $html .= "<p class='text-danger'>No records found</p>";
		
    } // end if checking for results
	
}

include 'inc/_inc1.php';
?>
<title><?php echo htmlspecialchars($page_title) ?></title>
<?php include 'inc/_inc2.php';?>
<?php include 'inc/_inc_nav.php';?>
<div class="<?php echo htmlspecialchars($container); ?>">

	<?php
	echo $form;
	echo $html;
	?>
				
</div>
<?php include 'inc/_inc3.php';?>

<script>
// focus text on groupp field
function getFocus(){
	if(document.getElementById("group")){
		document.getElementById("group").focus();
		return;
	}
	setTimeout(function(){
		getFocus();
	},100);
}
getFocus();
</script>
</body>
</html>