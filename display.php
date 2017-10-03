<?php
require_once 'config.php';
?>
<html>
<head>
	<title></title>
	<script src="./third-party/two.min.js"></script>
	<style>
		table, th, td {
    		border: 1px solid black;
		}
		table {
			width: 40%;
			margin-left: auto;
			margin-right: auto;
		}
		a {
			text-decoration: none;
		}
	</style>
</head>
<body>
	<svg id="draw-shapes" height="25%" width="100%">
		
	</svg>
	<?php
	if (isset($_GET['id'])) {
		try {
			$mysqli = new mysqli($host, $user, $password, $dbname);

			$query = "SELECT first_user, second_user, date, first_user_count, second_user_count, users_voted FROM matches WHERE public_id='" . $_GET['id'] . "'";

			$result = $mysqli->query($query);

			if ($result->num_rows > 0) {
				$row = $result->fetch_object();
				
				?>

				<script>
					var data = <?php echo json_encode($row)?>;
				</script>
				
				<?php

			} else {
				echo "invalid game id";
			}

		} catch (Exception $e) {
			echo "mysqli exception: ", $e->getMessage(), "\n";
		}
	}
	?>
	
	<script>
		var elem = document.getElementById('draw-shapes');
		var params = { width: '100%' , height: '100%' };
		var two = new Two(params).appendTo(elem);

		// console.log(data);
		var fixedWidth = 500;
		var fixedHeight = 60;

		if (+data.first_user_count === 0 && +data.second_user_count === 0) {
			first_user_fraction = 0.5;
			second_user_fraction = 0.5
		} else {
			var first_user_fraction = +data.first_user_count / (+data.first_user_count + +data.second_user_count);
			var second_user_fraction = 1 - first_user_fraction;
		}

		var first_user_width = first_user_fraction * fixedWidth;
		// console.log(first_user_width);

		var second_user_width = fixedWidth - first_user_width
		// console.log(second_user_width);

		var diffX = (fixedWidth - first_user_width) / 2;

		// console.log(window.innerWidth / 2);
		// console.log(window.innerHeight / 2);
		var first_user = new Two.Text(data.first_user, window.innerWidth / 2 - fixedWidth / 2 - 20 - data.first_user.length / 2 * 5, fixedHeight);
		first_user.fill = 'rgb(0, 200, 0)';
		first_user.noStroke();

		var first_rect = two.makeRectangle(0, 0, first_user_width, fixedHeight);
		first_rect.fill = 'rgb(0, 200, 0)';
		first_rect.opacity = 0.75;
		first_rect.noStroke();

		var second_user = new Two.Text(data.second_user, window.innerWidth / 2 + fixedWidth / 2 + 20 + data.second_user.length / 2 * 5, fixedHeight);
		second_user.fill = 'rgb(200, 0, 0)';
		second_user.noStroke();

		var second_rect = two.makeRectangle((first_user_width + second_user_width) / 2, 0, second_user_width, fixedHeight);
		second_rect.fill = 'rgb(200, 0, 0)';
		second_rect.opacity = 0.75;
		second_rect.noStroke();

		var group = two.makeGroup(first_rect, second_rect);
		two.add(first_user, second_user);

		group.translation.set(window.innerWidth / 2 - diffX, fixedHeight);
		two.update();
	</script>

	<table>
		<tr>
			<th><?php echo $row->first_user?></th>
			<th><?php echo $row->second_user?></th>
		</tr>
		<tr>
			<td>
				<?php
					$users_voted = unserialize($row->users_voted);
					$i = 0;
					$count = count($users_voted);
					foreach($users_voted as $user => $vote) {
						if ($vote === $row->first_user) {
							if (++$i == $count) {
								echo '<a href="https://www.twitch.tv/' . $user . '" target="_blank">' . $user . ' </a>';
							} else {
								echo '<a href="https://www.twitch.tv/' . $user . '" target="_blank">' . $user . ', </a>';
							}
						}
					}
				?>
			</td>
			<td>
				<?php
					$users_voted = unserialize($row->users_voted);
					$i = 0;
					$count = count($users_voted);
					foreach($users_voted as $user => $vote) {
						if ($vote === $row->second_user) {
							if (++$i == $count) {
								echo '<a href="https://www.twitch.tv/' . $user . '" target="_blank">' . $user . ' </a>';
							} else {
								echo '<a href="https://www.twitch.tv/' . $user . '" target="_blank">' . $user . ', </a>';
							}
						}
					}
				?>
			</td>
		</tr>
	</table>
	
</body>
</html>
