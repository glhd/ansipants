<?php

namespace Glhd\AnsiPants;

use Glhd\AnsiPants\Support\Parsing\EscapeSequence;
use Glhd\AnsiPants\Support\Parsing\Text;
use Glhd\AnsiPants\Support\Parsing\Tokenizer;
use Illuminate\Support\Collection;
use Stringable;

class AnsiString implements Stringable
{
	/** @var \Illuminate\Support\Collection<int,\Glhd\AnsiPants\AnsiChar> */
	protected Collection $chars;
	
	public function __construct(string $input)
	{
		$this->chars = $this->parse($input);
	}
	
	public function __toString(): string
	{
		$result = '';
		$active_flags = [];
		
		foreach ($this->chars as $char) {
			[$remove, $add] = $this->diffFlags($active_flags, $char->flags);
			
			// If it's simpler, just reset and then add them all
			if (count($remove) > count($char->flags)) {
				[$remove, $add] = [[Flag::Reset], $char->flags];
			}
			
			foreach ($remove as $remove_flag) {
				// First, check to see if any of the flags we're adding overrides the flag
				// we're removing (in which case, we don't need to explicitly remove it).
				foreach ($add as $add_flag) {
					if ($add_flag->overrides($remove_flag)) {
						break;
					}
				}
				
				// Otherwise, add the inverse sequence
				$result .= $remove_flag->getInverseEscapeSequence();
			}
			
			foreach ($add as $add_flag) {
				$result .= $add_flag->getEscapeSequence();
			}
			
			$result .= $char->value;
			$active_flags = $char->flags;
		}
		
		return $result;
	}
	
	/** @return \Glhd\AnsiPants\Flag[][] */
	protected function diffFlags($a, $b): array
	{
		// Sadly array_diff doesn't work on enums :(
		$remove = [];
		$add = [];
		
		foreach ($a as $flag) {
			if (! in_array($flag, $b, true)) {
				$remove[] = $flag;
			}
		}
		
		foreach ($b as $flag) {
			if (! in_array($flag, $a, true)) {
				$add[] = $flag;
			}
		}
		
		return [$remove, $add];
	}
	
	protected function parse(string $input): Collection
	{
		$active_flags = collect();
		$chars = collect();
		
		foreach (Tokenizer::make($input)->parse() as $token) {
			if ($token instanceof EscapeSequence) {
				$active_flags = $active_flags
					->reject(fn(Flag $flag) => $token->flag->overrides($flag))
					->push($token->flag);
			}
			
			if ($token instanceof Text) {
				$chars->push(new AnsiChar($token->value, $active_flags->all()));
			}
		}
		
		return $chars;
	}
}
