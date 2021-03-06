<?php
require 'message.php';
require_once '../config.php';
require_once 'bot.config.php';

// set db connection variables
$first_user = NULL;
$second_user = NULL;

$mysqli = new mysqli($host, $user, $password, $dbname);

do {
    do {
        echo "Checking for new entry...\n";
        $query = "SELECT * FROM matches WHERE active=1";

        $result = $mysqli->query($query);

        if (!$result) {
            echo "mysqli error: " . $mysqli->error . "\n";
            break;
        }

        if ($result->num_rows > 0) {
            $row = $result->fetch_object();
            $first_user = $row->first_user;
            $second_user = $row->second_user;
            $id = $row->id;
            $created_at = $row->created_at;
            $counted = unserialize($row->users_voted);
            $first_user_count = $row->first_user_count;
            $second_user_count = $row->second_user_count;
            $prev_count = [
                "first_user" => $row->first_user_count,
                "second_user" => $row->second_user_count
            ];
            break;
        } else {
            sleep(5);
        }
    } while (true);

    echo "\n" . $first_user . " vs " . $second_user . "\n";
    $diff = time() - strtotime($created_at . " UTC");
    if ($diff >= 86400) {
        echo "\nOOPS! THE MATCH HAS ALREADY FINISHED.\n\n";
        $query = "UPDATE matches SET active=0 WHERE id=$id";
        $result = $mysqli->query($query);
        if (!$result) {
            echo "mysqli error: " . $mysqli->error . "\n";
        }
        continue;
    }
    echo "\nConnecting to twitch channel...\n";
    $fp = fsockopen("irc.chat.twitch.tv", 80, $errno, $errstr, 30);

    if (!$fp) {
        echo "$errstr ($errno)<br />\n";
    } else {
        $buff = "";
        fwrite($fp, $CAP);
        fwrite($fp, $PASS);
        fwrite($fp, $NICK);
        fwrite($fp, $JOIN);
        echo "\nJoined channel.\n";
        $start_time = strtotime($created_at . " UTC");
        $last_update_time = time();
        $last_post_time = time();

        while (!feof($fp)) {
            if (time() - $start_time >= 86400) {
                fclose($fp);
                echo "DISCONNECTED FROM CHANNEL.\n\n";
                $query = "UPDATE matches SET active=0 WHERE id=$id";
                $result = $mysqli->query($query);
                if (!$result) {
                    echo "mysqli error: " . $mysqli->error . "\n";
                }
                break;
            }
            socket_set_timeout($fp, 1);
            $buff = fgets($fp);
            $messageObj = new Message($buff);
            $message = $messageObj->getMessage();
            $username = $messageObj->getUsername();

            if (!is_null($message)) {
                if (strpos($message, "PING") === 0) {
                    echo $message;
                    $reply = str_replace("PING", "PONG", $message);
                    fwrite($fp, $reply);
                    echo "bot: " . $reply;
                } else {
                    if (!is_null($username)) {
                        $message = strtolower($message);
                        echo $username . ": " . $message;
                        $first_user_index = strpos($message, "#" . strtolower($first_user));
                        $second_user_index = strpos($message, "#" . strtolower($second_user));
                        if (!isset($counted[$username])) {
                            if ($first_user_index !== false) {
                                $first_user_count++;
                                $counted[$username] = $first_user;
                                echo $first_user . ": " . $first_user_count . "\n";
                            } elseif ($second_user_index !== false) {
                                $second_user_count++;
                                $counted[$username] = $second_user;
                                echo $second_user . ": " . $second_user_count . "\n";   
                            }
                        }
                    }
                }
            }

            unset($message);

            if(time() - $last_post_time > 300) {
 
                if($second_user_count == 0 && $first_user_count == 0) {
                    $first_user_percent = 0.50;
                    $second_user_percent = 0.50;
                } else {
                    if($first_user_count == 0) {
                        $first_user_percent = 0.01;
                        $second_user_percent = 1 - $first_user_percent;
                    } else if($second_user_count == 0 ) {
                        $second_user_percent = 0.01;
                        $first_user_percent = 1 - $second_user_percent;
                    } else {
                        $first_user_percent = $first_user_count / ($first_user_count + $second_user_count);
                        $second_user_percent = 1 - $first_user_percent;
                    }
                }
                $last_post_time = time();
                $first_user_percent = round($first_user_percent * 100, 2);
                $second_user_percent = round($second_user_percent * 100, 2);

                fwrite($fp, "PRIVMSG $CHANNEL_NAME :Greetings, use #$first_user or #$second_user to vote, current score is $first_user_percent% and $second_user_percent%\r\n");
            }

            if (time() - $last_update_time > 1) {
                $serialized = serialize($counted);
                $query = "UPDATE matches SET first_user_count=$first_user_count, second_user_count=$second_user_count, users_voted='$serialized' WHERE id=$id";
                $result = $mysqli->query($query);
                if (!$result) {
                    echo "mysqli error: " . $mysqli->error . "\n";
                }
                $last_update_time = time();
                $prev_count['first_user'] = $first_user_count;
                $prev_count['second_user'] = $second_user_count;

                $query = "SELECT * FROM matches WHERE active=1";
                $result = $mysqli->query($query);
                if (!$result) {
                    echo "mysqli error: " . $mysqli->error . "\n";
                    break;
                }
                if($result->num_rows > 0) {
                    $row = $result->fetch_object();
                    if($id != $row->id) {
                        fclose($fp);
                        break;
                    }
                } else {
                    fclose($fp);
                    break;
                }
            }
        }
    }
} while(true);
$mysqli->close();
fclose($fp);
?>