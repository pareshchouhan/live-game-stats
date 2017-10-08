<?php
require("config.php");

if (isset($_GET['id'])) {
		try {
			$mysqli = new mysqli($host, $user, $password, $dbname);

			$query = "SELECT match_title, first_user, second_user, date, first_user_count, second_user_count, users_voted FROM matches WHERE public_id='" . $_GET['id'] . "'";

			$result = $mysqli->query($query);

			if ($result->num_rows > 0) {
				$row = $result->fetch_object();
				$matchTitle = $row->match_title;
        
        		echo json_encode($row);

			} else {
				echo "invalid game id";
			}
			$mysqli->close();
		} catch (Exception $e) {
			echo "mysqli exception: ", $e->getMessage(), "\n";
		}
	} else {
		try {
			$mysqli = new mysqli($host, $user, $password, $dbname);

			$query = "SELECT match_title, first_user, second_user, date, first_user_count, second_user_count, users_voted FROM matches WHERE active=1 LIMIT 1";

			$result = $mysqli->query($query);

			if ($result->num_rows > 0) {
				$row = $result->fetch_object();
        $matchTitle = $row->match_title;
        
		echo json_encode($row);
		$mysqli->close();

			} else {
				echo "invalid game id";
			}

		} catch (Exception $e) {
			echo "mysqli exception: ", $e->getMessage(), "\n";
		}
	}