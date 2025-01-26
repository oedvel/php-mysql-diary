<?php
include 'inc/__settings_and_functions.php';
include 'inc/_validation.php';

$html = "<h1>Categories</h1>";
$page_title = "Categories";

if (!isset($_GET['mode']) && ($_SERVER['REQUEST_METHOD'] != 'POST')) {
	
// ####################################################################################################################################################################################
// ADMIN TABLE
// ####################################################################################################################################################################################

	// ##############################################
	// count categories
	// ##############################################

	$sql_pages = "SELECT count(*) total_category_count FROM diary_categories c";
					 
	$stmt = $pdo->prepare($sql_pages);
	$stmt->execute();
	$row = $stmt->fetch(PDO::FETCH_ASSOC);
	$total_category_count = number_format($row['total_category_count']);

    $snip = NULL;
	
	$sql = "SELECT fld_id
				 , fld_cat
			  FROM diary_categories 
		  ORDER BY fld_cat";
		  
	$stmt = $pdo->prepare($sql);
	$stmt->execute();
    
    // loop through results, building a table row for each category
	foreach ($stmt as $row) {
        
        $data = true;
        $fld_id = htmlspecialchars($row['fld_id']);
        $fld_cat = htmlspecialchars($row['fld_cat']);
        
        $snip .=  "<tr><td><a href='categories.php?fld_id=$fld_id&mode=edit'>$fld_cat</a></td>";
        
        // ##############################################
        // count child posts start
        // ##############################################
        
        $sql_pages = "SELECT count(*) ct
    				    FROM diary_days d
						   , diary_categories c 
					   WHERE d.fld_cat_id = c.fld_id
                         AND c.fld_id = :fld_id";
                         
        $stmt = $pdo->prepare($sql_pages);
        $stmt->bindParam(':fld_id', $fld_id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $ct = number_format(htmlspecialchars($row['ct']));

        $snip .= "
		
		<td>$ct</td>
		<td><a href='categories.php?fld_id=$fld_id&mode=edit'>Edit</a></td>
		
		";

		if ($ct > 0) {
			$snip .=  "<td><span class='link-secondary'>Cannot delete active category</td>";
		} elseif ($total_category_count == 1) {
			$snip .=  "<td>Cannot delete only existing category</td>";
		} else {
			$snip .=  "<td><a href='categories.php?fld_id=$fld_id&mode=delete'>Delete</a></td>";
		}
        
		if ($ct > 0) {
			$snip .= "
			
			<td>
				<form action='search.php' method='post'>
					<input type='hidden' name='method' value='search'>
					<input type='hidden' name='cat' value='$fld_id'>
					<button type='submit' class='btn btn-primary btn-sm'><i class='fa-solid fa-magnifying-glass'></i> Find diary entries</button>
				</form>
			</td>
						
			";
		}
		
		$snip .=  "</tr>";
		
	}        
    // if category data found, built HTML for table to display categories
	
	if (isset($data)) {

		$html .= "

		<nav aria-label='breadcrumb'>
			<ol class='breadcrumb'>
				<li class='breadcrumb-item'><a href='categories.php'>Categories Home</a></li>
			</ol>
		</nav>
		
		<div class='table-responsive'>
			<table class='table table-light table-striped table-hover table-bordered'>
				<tr>
					<thead>
						<th>Category</th>
						<th>Entry Count</th>
						<th>Edit</th>
						<th>Delete</th>
						<th>Lookup</th>
					</thead>
				</tr>
				$snip
			</table>
		</div>
		
		";

    } else {
        $html .= "<p>No Categories found. <a href='categories.php'>Return to Categories home</a>.</p>";
    }
        
    $html .=  "<a href='categories.php?mode=new' class='btn btn-success'><i class='fa fa-plus'></i> New Category</a>";

}

// ####################################################################################################################################################################################
// 	QUERYSTRING
// ####################################################################################################################################################################################

if (isset($_GET['mode'])) {
	
	// if mode is populated in querystring

	$mode = $_GET['mode'];
	
	// if in edit mode, search for category to edit
	
	if ($mode == 'edit') {
		
		  $sql = "SELECT c.fld_id
					   , c.fld_cat 
					FROM diary_categories c
				   WHERE c.fld_id = :cat_id";

        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':cat_id', $_GET['fld_id']);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
		
		if(!$row) {
			
			$html .= "<p>No category found to edit. <a href='categories.php'>Return to Categories home</a>.</p>";
			
		} else {
		
			$cat_title = htmlspecialchars($row['fld_cat']);
			$cat_id = htmlspecialchars($row['fld_id']);
			$page_title = "Edit: $cat_title / Categories";
			
			$html = "

			<h1>Edit Category</h1>

			<nav aria-label='breadcrumb'>
				<ol class='breadcrumb'>
					<li class='breadcrumb-item'><a href='categories.php'>Categories Home</a></li>
					<li class='breadcrumb-item active' aria-current='page'>Edit: <span class='text-primary-emphasis'>$cat_title</span></li>
				</ol>
			</nav>

			<form  method='post' class='alert alert-secondary' action='categories.php'>
				<input type='hidden' name='mode' value='editprocess'>
				<input type='hidden' name='cat_id' value='$cat_id'>
				<div class='mb-3'>
					<label for='cat_title'>Title</label>
					<input type='text' class='form-control' id='cat_title' name='cat_title' value='$cat_title' placeholder='✏️ Category' required>
				</div>
				<div class='mb-3'>
					<button type='submit' class='btn btn-success'><i class='fa-solid fa-floppy-disk'></i> Save Changes</button>
					<a href='categories.php' class='btn btn-primary'><i class='fa fa-times'></i> Cancel</a>
				</div>	
			</form>

			";

		}
		
	} // end edit mode

	// new record mode
	
	if ($mode == 'new') {
		
		// HTML for new category form
		
		$html = "
		
		<h1>New Category</h1>
		
		<nav aria-label='breadcrumb'>
			<ol class='breadcrumb'>
				<li class='breadcrumb-item'><a href='categories.php'>Categories Home</a></li>
				<li class='breadcrumb-item active' aria-current='page'>New</li>
			</ol>
		</nav>

		<form  method='post' class='alert alert-secondary' action='categories.php'>
			<input type='hidden' name='mode' value='newprocess'>
			<div class='mb-3'>
				<label for='cat_title'>Title</label>
				<input type='text' class='form-control' id='cat_title' name='cat_title' placeholder='✏️ Category' required>
			</div>
			<div class='mb-3'>
				<button type='submit' class='btn btn-success'><i class='fa-solid fa-check'></i> Create Category</button> <a href='categories.php' class='btn btn-primary'><i class='fa fa-times'></i> Cancel</a>
			</div>	
		</form>
		
		";
		
		$page_title = "New / Categories";

	} // end new mode
	
	// delete mode
	
	if ($mode == 'delete') {
		
		$fld_id = $_GET['fld_id'];
		
		if (!isset($fld_id)) {
			header('Location:categories.php#delete-id-not-set');
			exit;
		}
		
		// ##############################################
		// get count of categories from database
		// ##############################################

		$sql_pages = "SELECT count(*) total_category_count
						FROM diary_categories c";
						 
		$stmt = $pdo->prepare($sql_pages);
		$stmt->execute();
		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		$total_category_count = number_format(htmlspecialchars($row['total_category_count']));
		
		// Get Category Details ##################################################################
		
		  $sql = "SELECT c.fld_id
					   , c.fld_cat 
					FROM diary_categories c
				   WHERE c.fld_id = :cat_id";

        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':cat_id', $_GET['fld_id']);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
		
		if(!$row) {
			$html .= "<p>No record found to delete. <a href='categories.php'>Return to Categories home</a>.</p>";
		} else {
			$cat_title = htmlspecialchars($row['fld_cat']);
			$cat_id = htmlspecialchars($row['fld_id']);
			
			// List Posts ##################################################################################
				   
			$sql_pages = "SELECT count(*) ct
							FROM diary_days d
							   , diary_categories c 
						   WHERE d.fld_cat_id = c.fld_id
							 AND c.fld_id = :cat_id";
							 
			$stmt = $pdo->prepare($sql_pages);
			$stmt->bindParam(':cat_id', $cat_id);
			$stmt->execute();
			$row = $stmt->fetch(PDO::FETCH_ASSOC);
			$ct = htmlspecialchars($row['ct']);
			
			if ($ct > 0) {
				$html .= "<p>Cannot delete category as it contains <code>$ct</code> records. <a href='categories.php'>Return to Categories home</a>.</p>";
			} elseif ($total_category_count == 1) {
				$html .= "<p>Cannot delete only existing category. <a href='categories.php'>Return to Categories home</a>.</p>";
			} else {
				$sql = "DELETE FROM diary_categories WHERE fld_id = :fld_id LIMIT 1";
				$stmt = $pdo->prepare($sql);
				$stmt->bindParam(':fld_id', $_GET['fld_id']);
				$stmt->execute();
				header("Location:categories.php#deleted");
			}
		}

	} // end delete mode

} else {
	
	$page_title = "New / Categories";
	
}

// ####################################################################################################################################################################################
// 	PROCESS FORM DATA
// ####################################################################################################################################################################################

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

	$mode = $_POST['mode'];
	
	// process edit mode
	
	if ($mode == 'editprocess') {
		
		$cat_id = $_POST['cat_id'];
		
		/* 404 for no results */
		if (!isset($cat_id)) {
			
			$html = "<p>No category found. <a href='categories.php'>Return to Categories home</a>.</p>";
			
		} else {
			
			$cat_title = $_POST['cat_title'];
			
			// if category not provided in form alert user
			if(!$cat_title){
				
				$html = "<p>Category value not provided. <a href='categories.php'>Return to Categories home</a>.</p>";
				
			} else { // otherwise update category in database
			
				$sql = "UPDATE diary_categories SET fld_cat = :cat_title
											   , fld_update_date = now()
										   WHERE fld_id = :cat_id
										   LIMIT 1";
										
				$stmt = $pdo->prepare($sql);
				$stmt->bindParam(':cat_title', $cat_title);
				$stmt->bindParam(':cat_id', $cat_id);
				$stmt->execute();
										
				header("Location:categories.php#editprocess");
				
			} // end checking to see if category provided
			
		} // end if for no record exists in database
		
	} // end edit mode
	
	// new record mode
	
	if ($mode == 'newprocess') {
		
		$cat_title =  $_POST['cat_title'];
		
		// if category not provided in form alert user
		if(!$cat_title){
			
			$html = "<p>Category value not provided. <a href='categories.php'>Return to Categories home</a>.</p>";
			
		} else { // otherwise insert record into database
  		
			 $sql = "INSERT INTO diary_categories (fld_cat
											 , fld_creation_date) 
									  VALUES (:cat_title
											, now())";
										  
			$stmt = $pdo->prepare($sql);
			$stmt->bindParam(':cat_title', $cat_title);
			$stmt->execute();
									
			header("Location:categories.php#newprocess");
			
		} // end checking to see if category provided
		
	} // end new record mode

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
</body>
</html>