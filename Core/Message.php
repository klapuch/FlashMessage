<?php
declare(strict_types = 1);
namespace Klapuch\FlashMessage;

interface Message {
	/**
	 * Flash the message
	 * @param string $content
	 * @param string $type
	 * @return void
	 */
	public function flash(string $content, string $type): void;

	/**
	 * Print the message
	 * @return string
	 */
	public function print(): string;
}
