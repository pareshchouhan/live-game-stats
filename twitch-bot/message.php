<?php
class Message {
	private $message;
	private $tags;
	private $command;
	private $original;
	private $channel;
	private $username;

	public function __construct($message) {
		$this->parseMessage($message);
	}

	public function getMessage() {
		return $this->message;
	}

	public function getTags() {
		return $this->tags;
	}

	public function getCommand() {
		return $this->command;
	}

	public function getOriginal() {
		return $this->original;
	}

	public function getChannel() {
		return $this->channel;
	}

	public function getUsername() {
		return $this->username;
	}

	private function parseMessage($message) {
		if (strpos($message, "PING") === 0) {
			$this->message = $message;
		} else {
			$startIndex = strpos($message, "@");

			if ($startIndex === 0) {
				$tagIndex = strpos($message, " ", $startIndex);
				$userIndex = strpos($message, " ", $tagIndex + 1);
				$commandIndex = strpos($message, " ", $userIndex + 1);
				$channelIndex = strpos($message, " ", $commandIndex + 1);
				$messageIndex = strpos($message, " ", $channelIndex + 1);

				$this->original = substr($message, $startIndex);
				$this->tags = substr($message, $startIndex, $startIndex - $tagIndex - 1);
				$this->username = substr($message, $tagIndex + 2, strpos($message, "!", $tagIndex + 2) - $tagIndex - 2);
				$this->command = substr($message, $userIndex + 1, $commandIndex - $userIndex - 1);
				$this->channel = substr($message, $commandIndex + 1, $channelIndex - $commandIndex - 1);
				$this->message = substr($message, $channelIndex + 2);
			}

			if (strcmp($this->command, "PRIVMSG")) {
				$this->message = NULL;
			}
		}
		
	}
}
?>