<?php
    require("config.php");
    $mysqli = new mysqli($host, $user, $password, $dbname);

    $query = "UPDATE matches SET active=0 WHERE active = 1;";

    $result = $mysqli->query($query);
    $mysqli->close();
?>
<script>
    window.close();
</script>