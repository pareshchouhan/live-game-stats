<?php
require_once 'config.php';
?>
<html>
<head>
	<title></title>
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
	<link href="https://fonts.googleapis.com/css?family=Libre+Franklin:400,100,100italic,200,200italic,300,300italic,400italic,500,600,500italic,600italic,700,700italic,800,900,800italic,900italic" rel="stylesheet" type="text/css">
	<link href="https://fonts.googleapis.com/css?family=PT+Sans+Caption:400,700" rel="stylesheet" type="text/css">
	<link href="https://fonts.googleapis.com/css?family=Patrick+Hand+SC" rel="stylesheet" type="text/css">
	<script
  src="https://code.jquery.com/jquery-3.2.1.min.js"
  integrity="sha256-hwg4gsxgFZhOsEEamdOYGBf13FyQuiTwlAQgxVSNgt4="
  crossorigin="anonymous"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/spectrum/1.8.0/spectrum.min.js"></script>
<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/spectrum/1.8.0/spectrum.min.css">
	<style>
	body {
		background-color : #232323;
		color : white;
		font-family: 'PT Sans Caption', Helvetica, Arial, sans-serif;
	}
		a {
			text-decoration: none;
		}
		#player-1 {
			background-color: yellow;
			height : 100px;
			display : block;
			float : left;
		}
		#player-2 {
			background-color: blue;
			height : 100px;
			display : block;
			float : right;
		}
		#player-1 {
			color : grey;
  			align-items: center;
			text-align : center;
			font-size : 3.5em;
			padding-top : 10px;
		}
		#player-2 {
			color : grey;
  			align-items: center;
			text-align  : center;
			padding-top : 10px;
			font-size : 3.5em;
		}
		#user-title{
			font-size : 7em;
		}
	</style>
</head>
<body>
	<!-- <svg id="draw-shapes" height="25%" width="100%"> -->


	<!-- </svg> -->
	<?php
	$matchTitle = "N/A";
	if (isset($_GET['id'])) {
		try {
			$mysqli = new mysqli($host, $user, $password, $dbname);

			$query = "SELECT match_title, first_user, second_user, date, first_user_count, second_user_count, users_voted FROM matches WHERE public_id='" . $_GET['id'] . "'";

			$result = $mysqli->query($query);

			if ($result->num_rows > 0) {
				$row = $result->fetch_object();
				$matchTitle = $row->match_title;
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
	} else {
		try {
			$mysqli = new mysqli($host, $user, $password, $dbname);

			$query = "SELECT match_title, first_user, second_user, date, first_user_count, second_user_count, users_voted FROM matches WHERE active=1 LIMIT 1";

			$result = $mysqli->query($query);

			if ($result->num_rows > 0) {
				$row = $result->fetch_object();
				$matchTitle = $row->match_title;
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
	if($matchTitle == 0) {
		$matchTitle = "N/A";
	}
	?>
	<div class="container">
	<h1 class="text-center" id="user-title"><?php echo $row->first_user; ?> Vs <?php echo $row->second_user; ?></h1>
	<h3 class="text-center" id="match-title"><?php echo $matchTitle; ?></h3>
	</div>
		<div class="match-details container">
			<span id="player-1"></span>
			<span id="player-2"></span>
		</div>
	<script>
	$(document).ready(function() {
		console.log(data);
		updateMatchDetails();
		setInterval(function updateUI() {
			var url = "get_data.php";
			var id = "<?php echo isset($_GET["id"]) ? $_GET["id"] : -1; ?>";
			if(id != -1) {
				url += "?id=" + id;
			}
			$.get(url, function(matchData) {
				data = JSON.parse(matchData);
				console.log(data);
				updateMatchDetails();
			});
		}, 1000);
	});

	function updateMatchDetails() {
		if (+data.first_user_count === 0 && +data.second_user_count === 0) {
			first_user_fraction = 0.5;
			second_user_fraction = 0.5
		} else {
			var first_user_fraction = +data.first_user_count / (+data.first_user_count + +data.second_user_count);
			var second_user_fraction = 1 - first_user_fraction;
			if(first_user_fraction == 0) {
				first_user_fraction = 0.01;
				second_user_fraction = 0.99;
			}
			if(second_user_fraction == 0) {
				first_user_fraction = 0.99;
				second_user_fraction = 0.01;
			}
		}
		$("#player-1").animate({
			width : first_user_fraction * $('.match-details').width(),
		}, 500);
		$("#player-1").text( (first_user_fraction * 100).toFixed(2) + "%");
		$("#player-2").animate({
			width : second_user_fraction * $('.match-details').width()
		}, 500);
		$("#player-2").text( (second_user_fraction * 100).toFixed(2) + "%");
		$("#user-title").text(data.first_user + " Vs " + data.second_user);
		if(data.match_title == 0) {
			data.match_title = "N/A";
		}
		$("#match-title").text(data.match_title);
	}

		
	</script>
	<br/>
	<br/>
	<div class="container voters">
	<table class="table">
	<thead>
		<tr>
			<th class="text-center"><?php echo $row->first_user?></th>
			<th class="text-center"><?php echo $row->second_user?></th>
		</tr>
	</thead>
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
	</div>
	<input id='player-1-color' />
	<input id='player-2-color' />
	<input id='player-1-font-color' />
	<input id='player-2-font-color' />
</body>

<script>
$("#player-1-color").spectrum({
    color: "#f00",
	flat: true,
    showInput: true,
	showAlpha: true,
	move : function(color) {
		$("#player-1").css({
			"background-color" : color.toHexString()
		});
	}
});
$("#player-2-color").spectrum({
    color: "#0f0",
	flat: true,
    showInput: true,
	showAlpha: true,
	move : function(color) {
		$("#player-2").css({
			"background-color" : color.toHexString()
		});
	}
});
$("#player-1-font-color").spectrum({
    color: "#f00",
	flat: true,
    showInput: true,
	showAlpha: true,
	move : function(color) {
		$("#player-1").css({
			"color" : color.toHexString()
		});
	}
});
$("#player-2-font-color").spectrum({
    color: "#0f0",
	flat: true,
    showInput: true,
	showAlpha: true,
	move : function(color) {
		$("#player-2").css({
			"color" : color.toHexString()
		});
	}
});

</script>
</html>
