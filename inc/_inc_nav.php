<?php

// top-level

$v_enter = 		htmlspecialchars(strpos($pageName,"index.php"));
$v_categories = htmlspecialchars(strpos($pageName,"categories.php"));
$v_sameday = 	htmlspecialchars(strpos($pageName,"sameday.php"));
$v_dayrange = 	htmlspecialchars(strpos($pageName,"dayrange.php"));
$v_search = 	htmlspecialchars(strpos($pageName,"search.php"));
$v_group = 		htmlspecialchars(strpos($pageName,"groupby.php"));

if ($v_enter > 0) 		{ $c_enter = " active"; } 		else { $c_enter = '';  }
if ($v_categories > 0) 	{ $c_categories = " active"; } 	else { $c_categories = '';  }
if ($v_sameday > 0) 	{ $c_sameday = " active"; } 	else { $c_sameday = ''; }
if ($v_dayrange > 0) 	{ $c_dayrange = " active"; } 	else { $c_dayrange = ''; }
if ($v_search > 0) 		{ $c_search = " active"; } 		else { $c_search = ''; }
if ($v_group > 0) 		{ $c_group = " active"; } 		else { $c_group = ''; }
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-primary" style="margin-bottom:20px;">
	<div class="<?php echo htmlspecialchars($container); ?>">
		<a class="navbar-brand" href="index.php">Diary</a>
		<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation"><span class="navbar-toggler-icon"></span></button>
		<div class="collapse navbar-collapse" id="navbarNav">
			<ul class="navbar-nav me-auto mb-2 mb-md-0">
				<li class="nav-item"><a class="nav-link<?php echo htmlspecialchars($c_enter); ?>" href="index.php">Home</a></li>
				<li class="nav-item"><a class="nav-link<?php echo htmlspecialchars($c_categories); ?>" href="categories.php">Categories</a></li>
				<li class="nav-item"><a class="nav-link<?php echo htmlspecialchars($c_group); ?>" href="groupby.php">Group By</a></li>
				<li class="nav-item"><a class="nav-link<?php echo htmlspecialchars($c_dayrange); ?>" href="dayrange.php">Range</a></li>
				<li class="nav-item"><a class="nav-link<?php echo htmlspecialchars($c_sameday); ?>" href="sameday.php">Same Day</a></li>
				<li class="nav-item"><a class="nav-link<?php echo htmlspecialchars($c_search); ?>" href="search.php">Search</a></li>
				<li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
			</ul>
			<form class="d-flex" role="search" action="search.php" method="post">
				<input type="hidden" name="method" value="search">
				<input class="form-control me-2" type="search" placeholder="Search" aria-label="Search" name="str">
			</form>
		</div>
	</div>
</nav>
