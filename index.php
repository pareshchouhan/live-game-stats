<?php
require_once 'config.php';
?>
<html>
<head>
	<title></title>
</head>
<body>
	<?php
		if (!isset($_POST['first_user']) || !isset($_POST['second_user'])) {
	?>
	<form action="<?php echo $_SERVER['PHP_SELF']?>" method="post">
		<input type="text" name="first_user" placeholder="First User">
		<input type="text" name="second_user" placeholder="Second User">
		<input type="submit" name="submit" value="Submit">
	</form>
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

			for ($i = 0; $i < $num_of_rows; $i++) {
				$row = $result->fetch_object();

				if ($row->active == 0) {
					echo "$row->first_user vs $row->second_user <span style=\"color: red\">inactive </span><a href='display.php?id=$row->public_id'>view</a><br>";
				} else {
					echo "$row->first_user vs $row->second_user <span style=\"color: blue\">active </span><a href=\"display.php?id=$row->public_id\"> view</a><br>";
				}
			}
			
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
</body>
</html>