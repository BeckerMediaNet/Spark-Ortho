<?php

namespace Level;

class Helper
{
	/**
	 * Get the excerpt by word count
	 *
	 * @param string $content
	 * @param int $word_count
	 * @return string
	 */
	public static function excerpt_by_word_count($content, $word_count = 55)
	{
		$content = strip_tags($content);
		$content = explode(' ', $content);
		$content = array_slice($content, 0, $word_count);
		$content = implode(' ', $content);
		return $content;
	}
}