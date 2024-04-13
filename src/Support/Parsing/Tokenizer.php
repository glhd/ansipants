<?php

namespace Glhd\AnsiPants\Support\Parsing;

use Generator;
use IteratorAggregate;
use Traversable;

class Tokenizer implements IteratorAggregate
{
	public static function make(string $input): static
	{
		return new static($input);
	}
	
	public function __construct(
		protected string $input
	) {
	}
	
	public function getIterator(): Generator
	{
		$buffer = '';
		$chars = preg_split('//u', $this->input, -1, PREG_SPLIT_NO_EMPTY);
		
		foreach ($chars as $char) {
			$buffer .= $char;
			$type = $this->getType($buffer);
			
			if (BufferType::PartialEscapeSequence === $type) {
				continue;
			}
			
			yield match($type) {
				BufferType::EscapeSequence => new EscapeSequence($buffer),
				BufferType::Text => new Text($buffer),
			};
			
			$buffer = '';
		}
	}
	
	protected function getType(string $sequence): BufferType
	{
		if (
			"\e" === $sequence[0]
			&& strlen($sequence) < 30
			&& preg_match("/(?P<escape>\e)(?P<csi>\[)?(?P<n>\d+(?:;\d+)*)*(?P<m>m)?/", $sequence, $matches)
		) {
			return isset($matches['escape'], $matches['csi'], $matches['n'], $matches['m'])
				? BufferType::EscapeSequence
				: BufferType::PartialEscapeSequence;
		}
		
		return BufferType::Text;
	}
}
