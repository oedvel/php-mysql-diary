<?php
include 'inc/__settings_and_functions.php';
include 'inc/_validation.php';

$html = null;
$mode = null;
$edited_feedback = null;
$okay_flag = "no";
$page_title = "Diary Home";

if (!isset($_GET['mode']) && ($_SERVER['REQUEST_METHOD'] != 'POST')) {

	// ####################################################################################################################################################################################
	// BASIC ENTRY FORM
	// ####################################################################################################################################################################################

	// if edited appears in querystring which is included after a post has been edited.
	if(isset($_GET['edited'])) {
		
		$fld_edited = htmlspecialchars($_GET['edited']);
		
		if(is_numeric($fld_edited)) { // check if ID is a number
			
			$okay_flag = "yes";
			
			$sql = "SELECT d.fld_id
						 , d.fld_date
						 , d.fld_content
						 , d.fld_cat_id
						 , c.fld_cat 
					  FROM diary_days d
						 , diary_categories c 
					 WHERE d.fld_cat_id = c.fld_id
					   AND d.fld_id = :fld_id";
					   
			$stmt = $pdo->prepare($sql);       
			$stmt->bindParam(':fld_id', $fld_edited);
			$stmt->execute();
			$results = $stmt->fetchAll();
			
			$diary_data = buildDiaryRecordsOutput($results, $date = null, $format = "n", $str = null, $hide_line_breaks = "y", "alert", $format_of_date = "format1", $hide_category = null);
			$edited_feedback_post = $diary_data[1];
			
			$edited_feedback = "
			<div class='alert alert-success'>✅ Success - post ID $fld_edited edited</div>
			$edited_feedback_post
			";
		
		}
		
	}

	// get max date from database
	// if no date found use today's date
	$sql = "SELECT COALESCE(MAX(fld_date), DATE(NOW())) max_post_date FROM diary_days";
	$stmt = $pdo->prepare($sql);
	$stmt->execute();
	$row = $stmt->fetch(PDO::FETCH_ASSOC);

	// get max date from database and format in yyyy-mm-dd format
	$max_post_date = $row['max_post_date'];
	$today = date('Y-m-d');
	$max_post_date = date('Y-m-d', strtotime($max_post_date));
	
	// if max date is in the past then add 1 day to the max date otherwise use today's date in yyyy-mm-dd format
	if ($max_post_date < $today) {
		$date=date_create($max_post_date);
		// add 1 to max post date
		date_add($date,date_interval_create_from_date_string("1 days"));
		$new_date = date_format($date,"Y-m-d");
	} else {
		$new_date = $today;
	}
	
	$page_title = "New Diary Entry";
	$new_date = htmlspecialchars($new_date);
	
	// category select options HTML
	$cat_html = "";
	$sql = "SELECT fld_id, fld_cat FROM diary_categories ORDER BY fld_cat";
	$stmt = $pdo->prepare($sql);
	$stmt->execute();

	foreach ($stmt as $row) {
		$cat_title = htmlspecialchars($row['fld_cat']);
		$cat_id = htmlspecialchars($row['fld_id']);
		$cat_html .= "<option value='$cat_id'>$cat_title</option>";
	}
	
	// define HTML

	$html = "<h1>$page_title</h1>
	
	$edited_feedback
	
	<nav aria-label='breadcrumb'>
		<ol class='breadcrumb'>
			<li class='breadcrumb-item'><a href='index.php'>Diary Home</a></li>
			<li class='breadcrumb-item active' aria-current='page'>Diary Entry Mode</li>
		</ol>
	</nav>

	<form method='post' action='index.php' class='alert alert-secondary'>
		<input type='hidden' name='mode' value='newprocess'>
		<div class='mb-3'>
			<label for='fld_date' class='form-label'>Date (yyyy-mm-dd)</label>
			<input class='form-control datepicker' type='text' id='fld_date' name='fld_date' placeholder='📅 Date in YYYY-MM-DD format' value='$new_date' required>
		</div>
		<div class='mb-3'>
			<label for='fld_content' class='form-label'>Info</label>
			<textarea style='height:250px;' class='form-control' name='fld_content' id='fld_content' placeholder='✏️ Diary entry' required></textarea>
		</div>
		<div class='mb-3'>
			<label for='fld_category' class='form-label'>Category</label>
			<select name='fld_category' id='fld_category' class='form-select'>
				$cat_html
			</select>
		</div>
		<div class='mb-3'>
			<button type='submit' class='btn btn-success'><i class='fa-solid fa-check'></i> Create New</button>
		</div>
	</form>
	
	";
	
}

// ####################################################################################################################################################################################
// 	QUERYSTRING
// ####################################################################################################################################################################################

if (isset($_GET['mode'])) {
	
	$mode = $_GET['mode'];
	
	// #####################################################################################################
	// SHOW EDIT FORM
	// #####################################################################################################
	
	// @@@@@@@@@@@@@@@@@@@@@@@@ -> Edit View
	
	if ($mode == 'edit') {
        		
		if(isset($_GET['id'])) {
			
			$fld_id = $_GET['id'];
			
			if(!is_numeric($fld_id)) { // check if ID is a number - if not - feed back to user
			
				$html = "<p>ID is not numeric. <a href='index.php'>Return to home page</a>.</p>";
				
			} else {
				
				$okay_flag = "yes";
				$sql = "SELECT d.fld_id
							 , d.fld_date
							 , d.fld_content
							 , d.fld_cat_id
						  FROM diary_days d
						 WHERE d.fld_id = :fld_id";
						   
				$stmt = $pdo->prepare($sql);
				$stmt->bindParam(':fld_id', $fld_id);
				$stmt->execute();
				
			}
			
		} 	elseif(!isset($_GET['id'])) { // fld_id parameters not included in querystring - feed back to user
		
			$html = "<p>ID missing. <a href='index.php'>Return to home page</a>.</p>";
			
		}
		
		if($okay_flag == "yes") {
			
			$row = $stmt->fetch(PDO::FETCH_ASSOC);
		
			if(!$row) { // no data found
			
				$html = "<p>No data found. <a href='index.php'>Return to home page</a>.</p>";
				
			} else { // data found - proceed
			
				$fld_id = htmlspecialchars($row['fld_id']);
				$fld_date = htmlspecialchars($row['fld_date']);
				$fld_content = htmlspecialchars($row['fld_content']);
				$fld_cat_id = htmlspecialchars($row['fld_cat_id']);
				
				// category select options HTML
				$cat_html = "";
				
				$sql = "SELECT fld_id, fld_cat FROM diary_categories ORDER BY fld_cat";
				$stmt = $pdo->prepare($sql);
				$stmt->execute();

				foreach ($stmt as $row) {
					
					$cat_title = htmlspecialchars($row['fld_cat']);
					$cat_id = htmlspecialchars($row['fld_id']);
					
					if ($fld_cat_id == $cat_id) {
						$cat_html_selected = "selected='selected'";
					} else {
						$cat_html_selected = NULL;
					}

					$cat_html .= "<option $cat_html_selected value='$cat_id'>$cat_title</option>";
					
				}
				
				// define HTML
				
				$page_title = "Edit Diary Entry [ID: $fld_id]";
				
				$html = "<h1>$page_title</h1>
				
				<nav aria-label='breadcrumb'>
					<ol class='breadcrumb'>
						<li class='breadcrumb-item'><a href='index.php'>Diary Home</a></li>
						<li class='breadcrumb-item active' aria-current='page'>Edit: <span class='text-primary-emphasis'>$fld_id ($fld_date)</span></li>
					</ol>
				</nav>
						
				<form method='post' action='index.php' class='alert alert-secondary'>
					<input type='hidden' name='mode' value='editprocess'>
					<input type='hidden' name='fld_id' value='$fld_id'>
					<div class='mb-3'>
						<label for='fld_date' class='form-label'>Date (yyyy-mm-dd)</label>
						<input class='form-control datepicker' type='text' id='fld_date' name='fld_date' value='$fld_date' placeholder='📅 Date in YYYY-MM-DD format' required>
					</div>
					<div class='mb-3'>
						<label for='fld_content' class='form-label'>Info</label>
						<textarea style='height:250px;' class='form-control' name='fld_content' id='fld_content' placeholder='✏️ Diary entry' required>$fld_content</textarea>
					</div>
					<div class='mb-3'>
						<label for='fld_category' class='form-label'>Category</label>
						<select name='fld_category' id='fld_category' class='form-select'>
							$cat_html
						</select>
					</div>
					<div class='mb-3'>
						<button type='submit' class='btn btn-success'><i class='fa-solid fa-floppy-disk'></i> Save Changes</button>
						<a href='index.php?id=$fld_id&mode=delete' class='btn btn-danger'><i class='fa-solid fa-trash'></i> Delete</a>
					</div>
				</form>
				
				";

			}
		
		}
		
	// @@@@@@@@@@@@@@@@@@@@@@@@ -> Single Day View
	
	} elseif ($mode == 'single') {
	
		if(isset($_GET['date'])) {
			
			$date = $_GET['date'];
			
			if(!validateDate($date)) { // check if date is valid - if not - display message
			
				$html = "<p>Date is not valid. <a href='index.php'>Return to home page</a>.</p>";
				
			} else {
				
				$date_param = $date;
				$okay_flag = "yes";
				$sql = "SELECT d.fld_id
							 , d.fld_date
							 , d.fld_content
							 , c.fld_cat
						  FROM diary_days d
							 , diary_categories c
						 WHERE d.fld_cat_id = c.fld_id
						   AND d.fld_date = :fld_date";
						   
				$stmt = $pdo->prepare($sql);       
				$stmt->bindParam(':fld_date', $date);
				$stmt->execute();
				$results = $stmt->fetchAll();
				
				// get results from the database via buildDiaryRecordsOutput function
				$diary_data = buildDiaryRecordsOutput($results, $date_param = null, $format = null, $str = null, $hide_line_breaks = null, "card", "format1", $hide_category = null);
				
			}
			
		} elseif(!isset($_GET['date'])) { // date parameters not included in querystring - feed back to user
		
			$html = "<p>Date missing. <a href='index.php'>Return to home page</a>.</p>";
			
		}
		
		if($okay_flag == "yes") {
			
			$data_exists = $diary_data[0];
			
			if($data_exists === "yes"){
				
				$page_title = "Single Day [$date]";
				
				// Pagination Start ##########################
				
				// pagination - get min max dates
				$sql = "select min(fld_date) min_date, max(fld_date) max_date FROM diary_days d, diary_categories c where  d.fld_cat_id = c.fld_id";
				$stmt = $pdo->prepare($sql);       
				$stmt->execute();
				$row = $stmt->fetch(PDO::FETCH_ASSOC);
				$min_date = htmlspecialchars($row['min_date']);
				$max_date = htmlspecialchars($row['max_date']);
				
				// get before and after dates
				$date_array = beforeAndAfter($date);
				$date_before = $date_array[0];
				$date_after = $date_array[1];
				
				// does data exist for before date?
				$sql = "SELECT 'y' data_exists
						  FROM diary_days d
							 , diary_categories c
						 WHERE d.fld_cat_id = c.fld_id
						   AND d.fld_date = :fld_date";
						   
				$stmt = $pdo->prepare($sql);       
				$stmt->bindParam(':fld_date', $date_before);
				$stmt->execute();
				$row_before = $stmt->fetch(PDO::FETCH_ASSOC);
				
				if($row_before){
					$backward_disabled = null;
					$backward_link = "index.php?date=$date_before&mode=single";
				} else {
					$backward_disabled = " disabled ";
					$backward_link = "#";
				}
				
				// does data exist for after date?
				$sql = "SELECT 'y' data_exists
						  FROM diary_days d
							 , diary_categories c
						 WHERE d.fld_cat_id = c.fld_id
						   AND d.fld_date = :fld_date";
						   
				$stmt = $pdo->prepare($sql);       
				$stmt->bindParam(':fld_date', $date_after);
				$stmt->execute();
				$row_after = $stmt->fetch(PDO::FETCH_ASSOC);
				
				if($row_after){
					$forward_disabled = null;
					$forward_link = "index.php?date=$date_after&mode=single";
				} else {
					$forward_disabled = " disabled ";
					$forward_link = "#";
				}
			
				$title_date = date_create($date);
				$title_date = date_format($title_date,"l F jS, Y");
				
				// 
				if($date === $min_date){
					$min_disabled = " disabled ";
				} else {
					$min_disabled = null;
				}

				if($date === $max_date){
					$max_disabled = " disabled ";
				} else {
					$max_disabled = null;
				}

				if($date === $max_date){
					$max_disabled = " disabled ";
				} else {
					$max_disabled = null;
				}
				
				// Pagination End ##########################
				
				// build up HTML for the page

				$html .= "
				
				<h1>$title_date</h1>

				<nav aria-label='breadcrumb'>
					<ol class='breadcrumb'>
						<li class='breadcrumb-item'><a href='index.php'>Diary Home</a></li>
						<li class='breadcrumb-item active' aria-current='page'>Single Day</li>
					</ol>
				</nav>

				<nav aria-label='Page navigation'>
				  <ul class='pagination'>
					<li class='page-item $min_disabled'><a class='page-link' href='index.php?date=$min_date&mode=single'><i class='fa-solid fa-backward-fast'></i></a></li>
					<li class='page-item $backward_disabled'><a class='page-link' href='$backward_link'><i class='fa-solid fa-backward'></i></a></li>
					<li class='page-item active' aria-current='page'><a class='page-link' href='#'>$date</a></li>
					<li class='page-item $forward_disabled'><a class='page-link' href='$forward_link'><i class='fa-solid fa-forward'></i></a></li>
					<li class='page-item $max_disabled'><a class='page-link' href='index.php?date=$max_date&mode=single'><i class='fa-solid fa-forward-fast'></i></a></li>
				  </ul>
				</nav>
				
				$diary_data[1]
				
				";
				
			} else {
				
				$html .= "<p>No data found. <a href='index.php'>Return to home page</a>.</p>";
				
			}
		
		}
		
	// @@@@@@@@@@@@@@@@@@@@@@@@ -> Delete View
	
	} elseif ($mode == 'delete') {
		
		$sql = "SELECT d.fld_id
					 , d.fld_date
					 , d.fld_content
				  FROM diary_days d
				 WHERE d.fld_id = :fld_id";

        $fld_id = $_GET['id'];
		$stmt = $pdo->prepare($sql);
        $stmt->bindParam(':fld_id', $fld_id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
		
		if(!$row) { // no data found
		
			$html = "<p>No data found. <a href='index.php'>Return to home page</a>.</p>";
			
		} else { // data found, proceed
		
			$fld_id = htmlspecialchars($row['fld_id']);
			$fld_date = htmlspecialchars($row['fld_date']);
			$fld_date = date('D d-M-Y', strtotime($fld_date));
			$fld_content = htmlspecialchars($row['fld_content']);
			
			$page_title = "Delete Diary Entry";
			
			// define HTML
			
			$html = "
			
			<h1>$page_title</h1>
			
			<nav aria-label='breadcrumb'>
				<ol class='breadcrumb'>
					<li class='breadcrumb-item'><a href='index.php'>Diary Home</a></li>
					<li class='breadcrumb-item active' aria-current='page'>Delete <span class='text-primary-emphasis'>$fld_id ($fld_date)</span></li>
				</ol>
			</nav>

			<p>Are you sure you want to delete this diary entry?</p>
			
			<div class='alert alert-danger'>
			
				<div>
					<strong>$fld_date</strong>
				</div>
				
				<div>$fld_content</div>
				
				<hr>
				
				<form method='post' id='theForm' action='index.php'>
					<input type='hidden' name='id' value='$fld_id'>
					<input type='hidden' name='mode' value='deleteprocess'>
					<button type='submit' class='btn btn-danger'><i class='fa-solid fa-trash'></i> Delete</button>
					<a href='index.php' class='btn btn-success'><i class='fa-solid fa-times'></i> Cancel</a>
				</form>
				
			</div>
			
			";
			
		}
		
	// @@@@@@@@@@@@@@@@@@@@@@@@ -> Deal with other issues
	
	} else {
	
		$page_title = "Unexpected Error";
		$html = "<h1>Unexpected Error</h1><p>Unexpected Error. <a href='index.php'>Return to home page</a>.</p>";
		
	}
	
}

// ####################################################################################################################################################################################
// 	PROCESS FORM DATA
// ####################################################################################################################################################################################

// check if form posted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

	$mode = $_POST['mode'];
	
	// @@@@@@@@@@@@@@@@@@@@@@@@ -> Edit
	
	if ($mode == 'editprocess') {
		
		$fld_id = $_POST['fld_id'];
		$fld_date = $_POST['fld_date'];
		$fld_category = $_POST['fld_category'];
		$fld_content = $_POST['fld_content'];
		
		if(!$fld_date || !$fld_category || !$fld_content) { // if any of the key fields are empty - feed back to user
		
			$html = "<p>Not all key values are populated. <a href='index.php'>Return to home page</a>.</p>";
			
		} elseif(!is_numeric($fld_id)) { // check if ID is a number - if not - feed back to user
		
			$html = "<p>ID not valid. <a href='index.php'>Return to home page</a>.</p>";
			
		} elseif(!validateDate($fld_date)) {
			
			$html = "<p>Date is not valid. <a href='index.php'>Return to home page</a>.</p>";
			
		} else { // all okay, proceed

			$sql = "UPDATE diary_days 
					   SET fld_date = :fld_date
						 , fld_cat_id = :fld_category
						 , fld_content = :fld_content
						 , fld_update_date = now()
					 WHERE fld_id = :fld_id LIMIT 1";
			
			$stmt = $pdo->prepare($sql);
			$stmt->bindParam(':fld_date', $fld_date);
			$stmt->bindParam(':fld_category', $fld_category);
			$stmt->bindParam(':fld_content', $fld_content);
			$stmt->bindParam(':fld_id', $fld_id);
			$stmt->execute();
		
			header("Location:index.php?edited=$fld_id");
			exit;
			
		}
		
	// @@@@@@@@@@@@@@@@@@@@@@@@ -> New
	
	} elseif ($mode == 'newprocess') { // new record mode
		
		$fld_date = $_POST['fld_date'];
		$fld_category = $_POST['fld_category'];
		$fld_content = $_POST['fld_content'];
		
		// if any of the key fields are empty display message
		
		if(!$fld_date || !$fld_category || !$fld_content) {
			
			$html = "<p>Not all values provided. <a href='index.php'>Return to home page</a>.</p>";
			
		} elseif(!validateDate($fld_date)) { // check if date is valid - if not - display message
		
			$html = "<p>Date is not valid.</p>";
			
		} else {
			
				 $sql =  "INSERT INTO diary_days (fld_date
											 , fld_cat_id
											 , fld_content
											 , fld_creation_date) 
									   VALUES (:fld_date
											 , :fld_category
											 , :fld_content
											 , now())";
			
			$stmt = $pdo->prepare($sql);
			$stmt->bindParam(':fld_date', $fld_date);
			$stmt->bindParam(':fld_category', $fld_category);
			$stmt->bindParam(':fld_content', $fld_content);
			$stmt->execute();
		
			header("Location:index.php");
			exit;
		
		}
		
	}
	
	// @@@@@@@@@@@@@@@@@@@@@@@@ -> Delete
	
	if ($mode == 'deleteprocess') {
		
		$fld_id = $_POST['id'];
		$sql = "DELETE FROM diary_days WHERE fld_id = :fld_id LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':fld_id', $fld_id);
        $stmt->execute();

		header("Location:index.php");
		exit;
		
	}

}

// ########################################################################
// Recent Entries
// ########################################################################

$sql = "SELECT d.fld_id
             , d.fld_date
			 , d.fld_content
			 , c.fld_cat
		  FROM diary_days d
		     , diary_categories c
		 WHERE d.fld_cat_id = c.fld_id
	  ORDER BY fld_date DESC
	     LIMIT 365";
		 
$stmt = $pdo->prepare($sql);
$stmt->execute();
$results = $stmt->fetchAll();
$recent_html = "<h2>Recent Entries</h2>";
// get results from the database via buildDiaryRecordsOutput function
$diary_data = buildDiaryRecordsOutput($results, $date = null, $format = null, $str = null, $hide_line_breaks = "n", "alert", "format1", $hide_category = "n");
$recent_exists = $diary_data[0];
$recent_html .= $diary_data[1];

if($recent_exists === "no") {
	$recent_html = "<h2>Recent Entries</h2><p>No recent entries exist yet.</p>";
}

include 'inc/_inc1.php';
?>
<title><?php echo htmlspecialchars($page_title) ?></title>
<?php include 'inc/_inc2.php';?>
<?php include 'inc/_inc_nav.php';?>
<div class="<?php echo htmlspecialchars($container); ?>">
	<div class="row">
		<div class="col-md-6">
			<?php
			echo $html;
			?>
		</div>
		<?php
		if($mode <> "single") {
		?>
		<div class="col-md-6">
			<div style="overflow: auto; height:590px; padding:10px; margin:20px; border:1px solid blue; border-radius:5px;">
				<?php
				echo $recent_html;
				?>
			</div>
		</div>
		<?php
		}
		?>
	</div>
</div>
<?php include 'inc/_inc3.php';?>
<script>
$('.datepicker').datepicker({
    format: 'yyyy-mm-dd',
	autoclose: true,
	todayHighlight: true
});

// focus text on fld_content field
function getFocus(){
	if(document.getElementById("fld_content")){
		document.getElementById("fld_content").focus();
		return;
	}
	setTimeout(function(){
		getFocus();
	},100);
}
getFocus();

// prevent user from leaving the page and losing unsaved data-target
// https://stackoverflow.com/questions/7317273/warn-user-before-leaving-web-page-with-unsaved-changes/48238659#48238659
// 04-SEP-2022

"use strict";
(() => {
const modified_inputs = new Set;
const defaultValue = "defaultValue";
// store default values
addEventListener("beforeinput", (evt) => {
	const target = evt.target;
	if (!(defaultValue in target || defaultValue in target.dataset)) {
		target.dataset[defaultValue] = ("" + (target.value || target.textContent)).trim();
	}
});
// detect input modifications
addEventListener("input", (evt) => {
	const target = evt.target;
	let original;
	if (defaultValue in target) {
		original = target[defaultValue];
	} else {
		original = target.dataset[defaultValue];
	}
	if (original !== ("" + (target.value || target.textContent)).trim()) {
		if (!modified_inputs.has(target)) {
			modified_inputs.add(target);
		}
	} else if (modified_inputs.has(target)) {
		modified_inputs.delete(target);
	}
});
// clear modified inputs upon form submission
addEventListener("submit", (evt) => {
	modified_inputs.clear();
	// to prevent the warning from happening, it is advisable
	// that you clear your form controls back to their default
	// state with evt.target.reset() or form.reset() after submission
});
// warn before closing if any inputs are modified
addEventListener("beforeunload", (evt) => {
	if (modified_inputs.size) {
		const unsaved_changes_warning = "Changes you made may not be saved.";
		evt.returnValue = unsaved_changes_warning;
		return unsaved_changes_warning;
	}
});
})();
</script>
</body>
</html>