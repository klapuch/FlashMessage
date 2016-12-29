<?php
declare(strict_types = 1);
namespace Klapuch\FlashMessage;

/**
 * Message in XML format
 */
final class XmlMessage implements Message {
	private const MESSAGE = 'flashMessage';
	private $sessions;

	public function __construct(array &$sessions) {
		$this->sessions = &$sessions;
	}

	public function flash(string $content, string $type): void {
		$this->sessions[self::MESSAGE][] = [$type => $content];
	}

	public function print(): string {
		if(!isset($this->sessions[self::MESSAGE]))
			return $this->wrap(sprintf('<%s/>', self::MESSAGE));
		$messages = $this->wrap(
			implode(array_map([$this, 'toXml'], $this->sessions[self::MESSAGE]))
		);
		unset($this->sessions[self::MESSAGE]);
		return $messages;
	}

	/**
	 * The message in XML format
	 * @param array $message
	 * @return string
	 */
	private function toXml(array $message): string {
		[$type, $content] = [key($message), current($message)];
		return sprintf(
			'<%1$s><type>%2$s</type><content>%3$s</content></%1$s>',
			self::MESSAGE,
			$this->escape($type),
			$this->escape($content)
		);
	}

	/**
	 * Escaped value from XSS
	 * @param string $value
	 * @return string
	 */
	private function escape(string $value): string {
		return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
	}

	/**
	 * Wrap the XML with higher element
	 * @return string
	 */
	private function wrap(string $xml): string {
		return sprintf('<flashMessages>%s</flashMessages>', $xml);
	}
}