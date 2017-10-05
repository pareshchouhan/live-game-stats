<?php
require_once 'config.php';
?>
<html>
<head>
	<title></title>
	<!-- Latest compiled and minified CSS -->
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
	<link href="https://fonts.googleapis.com/css?family=Libre+Franklin:400,100,100italic,200,200italic,300,300italic,400italic,500,600,500italic,600italic,700,700italic,800,900,800italic,900italic" rel="stylesheet" type="text/css">
	<link href="https://fonts.googleapis.com/css?family=PT+Sans+Caption:400,700" rel="stylesheet" type="text/css">
	<link href="https://fonts.googleapis.com/css?family=Patrick+Hand+SC" rel="stylesheet" type="text/css">
	<style>
		h1 {
			text-align: center;
			font-family: 'PT Sans Caption', Helvetica, Arial, sans-serif;
			font-size: 4em;
			font-weight: 700;
			margin-bottom: 40px;
		}
		h2 {
			font-weight: 700;
			font-family: 'PT Sans Caption', Helvetica, Arial, sans-serif;
			margin-bottom: 30px;
		}
		hr {
			border-top: 1px solid #e2e2e2;
		}
		.form-control {
			width: 30%;
			margin-left: auto;
			margin-right: auto;
			height: 40px;
			font-size: 16px;
		}
		.btn {
			font-size: 16px;
		}
	</style>
</head>
<body>
	<?php
	if (!isset($_POST['first_user']) || !isset($_POST['second_user'])) {
		?>
		<div class="container">
			<h1>Homepage</h1>
			<form action="<?php echo $_SERVER['PHP_SELF']?>" method="post">
			<!-- <input type="text" name="first_user" placeholder="First User">
			<input type="text" name="second_user" placeholder="Second User">
			<input type="submit" name="submit" value="Submit"> -->
			<div class="form-group">
				<input type="text" class="form-control" name="first_user" placeholder="First player's name">
			</div>
			<div class="form-group">
				<input type="text" class="form-control" name="second_user" placeholder="Second player's name">
			</div>
			<div class="form-group">
				<button type="submit" name="submit" class="btn btn-primary form-control" style="
				display: block;">Submit</button>
			</div>
		</form>
		<hr>
		<h2>Summary</h2>
		<?php
	} else {
		$first_user = $_POST['first_user'];
		$second_user = $_POST['second_user'];
		$public_id = SHA1($first_user . $second_user . time());

		if (validate($first_user, $second_user)) {
			$first_user_count = getCount($first_user);
			$second_user_count = getCount($second_user);

				// setup db connection

			try {
				$mysqli = new mysqli($host, $user, $password, $dbname);

					// check if the row exists in db
					// $query = "SELECT * FROM matches WHERE date=CURDATE() AND first_user='" . $first_user . "' AND second_user='" . $second_user . "'";

					// $result = $mysqli->query($query);

					// // if row doesn't exist, insert new row
					// if ($result->num_rows < 1) {
						// set other rows to be inactive
				$inactiveQuery = "UPDATE `matches` SET `active`=0 WHERE `active`=1";
				$res = $mysqli->query($inactiveQuery);
				if (!$res) {
					echo "mysqli error: " . $mysqli->error . "\n";
				}

					// insert new row
				$users_voted = array();
				$serialized = serialize($users_voted);
				$query = "INSERT INTO `matches`(`date`, `first_user`, `second_user`, `first_user_count`, `second_user_count`, `created_at`, `from_date`, `to_date`, `public_id`, `active`, `users_voted`) VALUES (CURDATE(),'$first_user', '$second_user', $first_user_count, $second_user_count, UTC_TIMESTAMP() + 0, CURDATE(), CURDATE() + 1, '$public_id', 1, '$serialized')";

				$mysqli->query($query);
					// }
			} catch (Exception $e) {
				echo "mysqli exception: ", $e->getMessage(), "\n";
			}

		} else {
				// display error
		}
	}

		// display existing matches

	try {
		$mysqli = new mysqli($host, $user, $password, $dbname);

		$query = "SELECT * FROM matches ORDER BY active DESC";

		$result = $mysqli->query($query);

		$num_of_rows = $result->num_rows;

		?>
		<table class="table table-striped">
			<thead>
				<tr>
					<th>#</th>
					<th>First Player</th>
					<th>Second Player</th>
					<th>Match Status</th>
					<th>Results</th>
				</tr>
			</thead>
			<?php

			for ($i = 0; $i < $num_of_rows; $i++) {
				$row = $result->fetch_object();

				if ($row->active == 0) {
					// echo "$row->first_user vs $row->second_user <span style=\"color: red\">inactive </span><a href='display.php?id=$row->public_id'>view</a><br>";
					?>
					<tbody>
						<tr>
							<th scope="row"><?php echo $i?></th>
							<td><?php echo $row->first_user?></td>
							<td><?php echo $row->second_user?></td>
							<td style="color: red;">Inactive</td>
							<td>
								<?php echo "<a href='display.php?id=$row->public_id'>"?>
									<button class="btn btn-primary" style="display: block;font-size: 14px;">View Results</button>
								<?php echo "</a>"?>
							</td>
						</tr>
					</tbody>
					<?php
				} else {
					// echo "$row->first_user vs $row->second_user <span style=\"color: blue\">active </span><a href=\"display.php?id=$row->public_id\"> view</a><br>";
					?>
					<tbody>
						<tr>
							<th scope="row"><?php echo $i?></th>
							<td><?php echo $row->first_user?></td>
							<td><?php echo $row->second_user?></td>
							<td style="color: green;">Active</td>
							<td>
								<?php echo "<a href='display.php?id=$row->public_id'>"?>
									<button class="btn btn-primary" style="display: block;font-size: 14px;">View Results</button>
								<?php echo "</a>"?>
							</td>
						</tr>
					</tbody>
					<?php
				}
			}
			?>
		</table>
		<?php

		$mysqli->close();
	} catch (Exception $e) {
		echo "mysqli exception: ", $e->getMessage(), "\n";
	}

		// Stub
	function validate($first_user, $second_user) {
		return true;
	}

		// Stub
	function getCount($user) {
		return 0;
	}
	?>
</div>
</body>
</html>