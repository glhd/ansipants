<?php

namespace Glhd\AnsiPants;

use Generator;
use Glhd\AnsiPants\Support\Parsing\EscapeSequence;
use Glhd\AnsiPants\Support\Parsing\Text;
use Glhd\AnsiPants\Support\Parsing\Tokenizer;
use Illuminate\Support\Collection;
use Stringable;

class AnsiString implements Stringable
{
	/** @var \Illuminate\Support\Collection<int,\Glhd\AnsiPants\AnsiChar> */
	protected Collection $chars;
	
	public static function make(AnsiString|Collection|string $input): static
	{
		return new static($input);
	}
	
	public function __construct(AnsiString|AnsiChar|Collection|string $input)
	{
		if ($input instanceof AnsiChar) {
			$this->chars = new Collection([clone $input]);
		} elseif ($input instanceof Collection) {
			$input->ensure(AnsiChar::class);
			$this->chars = clone $input;
		} elseif ($input instanceof AnsiString) {
			$this->chars = clone $input->chars;
		} else {
			$this->chars = $this->parse($input);
		}
	}
	
	public function withFlags(Flag ...$flags): static
	{
		return new static($this->chars->map(fn(AnsiChar $char) => (clone $char)->withFlags(...$flags)));
	}
	
	public function prepend(AnsiString|AnsiChar|string $string): static
	{
		$prepended = collect();
		
		if ($string instanceof AnsiChar) {
			$prepended->push(clone $string);
		} else {
			$string = new AnsiString($string);
			foreach ($string->chars as $char) {
				$prepended->push(clone $char);
			}
		}
		
		foreach ($this->chars as $char) {
			$prepended->push(clone $char);
		}
		
		return new AnsiString($prepended);
	}
	
	public function append(AnsiString|AnsiChar|string $string): static
	{
		$appended = collect();
		
		foreach ($this->chars as $char) {
			$appended->push(clone $char);
		}
		
		if ($string instanceof AnsiChar) {
			$appended->push(clone $string);
		} else {
			$string = new AnsiString($string);
			foreach ($string->chars as $char) {
				$appended->push(clone $char);
			}
		}
		
		return new AnsiString($appended);
	}
	
	public function padLeft(int $length, AnsiString|string $pad = ' '): static
	{
		$short = max(0, $length - $this->length());
		
		$padding = static::make(mb_substr(str_repeat($pad, $short), 0, $short))
			->withFlags(...$this->chars->first()->flags);
		
		return $this->prepend($padding);
	}
	
	public function padRight(int $length, AnsiString|string $pad = ' '): static
	{
		$short = max(0, $length - $this->length());
		
		$padding = static::make(mb_substr(str_repeat($pad, $short), 0, $short))
			->withFlags(...$this->chars->last()->flags);
		
		return $this->append($padding);
	}
	
	public function padBoth(int $length, AnsiString|string $pad = ' '): static
	{
		$short = max(0, $length - $this->length());
		$left_padding = static::make(mb_substr(str_repeat($pad, floor($short / 2)), 0, $short))
			->withFlags(...$this->chars->first()->flags);
		
		$right_padding = static::make(mb_substr(str_repeat($pad, ceil($short / 2)), 0, $short))
			->withFlags(...$this->chars->last()->flags);
		
		return $this
			->prepend($left_padding)
			->append($right_padding);
	}
	
	public function wordwrap(int $width = 75, AnsiString|string $break = "\e[0m\n", bool $cut_long_words = false): static
	{
		$break = new AnsiString($break);
		$buffer = new AnsiString('');
		$wrapped = new AnsiString('');
		
		foreach ($this->words($cut_long_words ? $width : null) as $word) {
			[$sep, $word] = $word;
			
			if (($buffer->width() + $sep->width() + $word->width()) > $width) {
				if ($wrapped->width()) {
					$wrapped = $wrapped->append($break);
				}
				
				$wrapped = $wrapped->append($buffer);
				$buffer = new AnsiString($word);
			} else {
				$buffer = $buffer->append($sep)->append($word);
			}
		}
		
		if ($buffer->width() > 0) {
			$wrapped = $wrapped->append($break)->append($buffer);
		}
		
		return $wrapped;
	}
	
	/** @return Collection<int,\Glhd\AnsiPants\AnsiString> */
	public function explode(AnsiString|string $break, bool $ignore_style = true): Collection
	{
		$break = new AnsiString($break);
		$results = new Collection();
		$buffer = new AnsiString('');
		$tail_buffer = new AnsiString('');
		
		foreach ($this->chars as $char) {
			$tail_buffer = $tail_buffer->append($char);
			
			// If our partial buffer is the break value, we'll push the current
			// buffer to the results, and reset for the next chunk.
			if ($tail_buffer->is($break, $ignore_style)) {
				$results->push($buffer);
				$buffer = new AnsiString('');
				$tail_buffer = new AnsiString('');
				continue;
			}
			
			// If our partial buffer looks like it's the beginning of the break value
			// then we'll just continue buffering
			if ($break->startsWith($tail_buffer)) {
				continue;
			}
			
			// Otherwise (if the partial buffer isn't part of the break), just push it
			// to the current buffer and reset our partial buffer
			$buffer = $buffer->append($tail_buffer);
			$tail_buffer = new AnsiString('');
		}
		
		// If we have anything remaining on the partial buffer when we get to the
		// end of the string, we just need to append it to our current buffer
		if ($tail_buffer->length() > 0) {
			$buffer = $buffer->append($tail_buffer);
		}
		
		// And if there's anything remaining on the buffer once we're done, add
		// that to the final results
		if ($buffer->length() > 0) {
			$results->push($buffer);
		}
		
		return $results;
	}
	
	public function substr(int $start, ?int $length = null): static
	{
		if (0 === $length || $start >= $this->length()) {
			return new static('');
		}
		
		$result = new Collection();
		$index = $start >= 0 ? $start : $this->length() + $start;
		
		while (isset($this->chars[$index])) {
			$result->push($this->chars[$index++]);
			
			if ($length && $length > 0 && $result->count() === $length) {
				return new static($result);
			}
		}
		
		if ($length < 0) {
			$result->pop(abs($length));
		}
		
		return new static($result);
	}
	
	public function is(AnsiString|string $other, bool $ignore_style = false): bool
	{
		$other = new AnsiString($other);
		$length = $this->length();
		
		if ($other->length() !== $length || $other->width() !== $this->width()) {
			return false;
		}
		
		for ($i = 0; $i < $length; $i++) {
			if ($this->chars[$i]->isNot($other->chars[$i], $ignore_style)) {
				return false;
			}
		}
		
		return true;
	}
	
	public function startsWith(AnsiString|string $needle, bool $ignore_style = false): bool
	{
		$needle = new AnsiString($needle);
		
		$index = 0;
		$last_index = $needle->length() - 1;
		
		while ($index <= $last_index) {
			if (
				! isset($this->chars[$index])
				|| $this->chars[$index]->isNot($needle->chars[$index], $ignore_style)
			) {
				return false;
			}
			
			$index++;
		}
		
		return true;
	}
	
	public function endsWith(AnsiString|string $needle, bool $ignore_style = false): bool
	{
		$needle = new AnsiString($needle);
		
		$this_index = $this->length() - 1;
		$needle_index = $needle->length() - 1;
		
		while ($needle_index >= 0) {
			if (
				! isset($this->chars[$this_index])
				|| $this->chars[$this_index]->isNot($needle->chars[$needle_index], $ignore_style)
			) {
				return false;
			}
			
			$this_index--;
			$needle_index--;
		}
		
		return true;
	}
	
	public function length(): int
	{
		return count($this->chars);
	}
	
	public function width(): int
	{
		return $this->chars->sum(fn(AnsiChar $char) => $char->width());
	}
	
	public function dump(): static
	{
		dump($this);
		
		return $this;
	}
	
	public function dd(): static
	{
		dd($this);
		
		return $this;
	}
	
	public function __toString(): string
	{
		$result = '';
		$active_flags = [];
		
		foreach ($this->chars as $char) {
			[$remove, $add] = $this->diffFlags($active_flags, $char->flags);
			
			// If it's simpler, just reset and then add them all
			if (count($remove)) {
				$result .= Flag::Reset->getEscapeSequence();
				// [$remove, $add] = [[], $char->flags];
			}
			
			// foreach ($remove as $remove_flag) {
			// 	// First, check to see if any of the flags we're adding overrides the flag
			// 	// we're removing (in which case, we don't need to explicitly remove it).
			// 	foreach ($add as $add_flag) {
			// 		if ($add_flag->overrides($remove_flag)) {
			// 			break;
			// 		}
			// 	}
			//	
			// 	// Otherwise, add the inverse sequence
			// 	$result .= $remove_flag->getInverseEscapeSequence();
			// }
			
			foreach ($add as $add_flag) {
				$result .= $add_flag->getEscapeSequence();
			}
			
			$result .= $char->value;
			$active_flags = $char->flags;
		}
		
		return $result;
	}
	
	/** @return Generator<static[]> */
	protected function words(?int $max_length): Generator
	{
		$sep = new static('');
		$word = new static('');
		
		foreach ($this->chars as $char) {
			if ($word->length() > 0 && in_array($char->value, [' ', "\n"])) {
				yield [$sep, $word];
				$word = new static('');
				$sep = new static($char);
				continue;
			}
			
			if ($max_length && $word->width() >= $max_length) {
				yield [$sep, $word];
				$word = new static('');
				$sep = new static('');
			}
			
			$word->chars->push($char);
		}
		
		if ($word->length() > 0 || $sep->length() > 0) {
			yield [$sep, $word];
		}
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
		
		foreach (Tokenizer::make($input) as $token) {
			if ($token instanceof EscapeSequence) {
				$active_flags = $active_flags
					->reject(fn(Flag $flag) => $token->flag->overrides($flag))
					->when($token->flag->hasStyle(), fn(Collection $active_flags) => $active_flags->push($token->flag));
			}
			
			if ($token instanceof Text) {
				$chars->push(new AnsiChar($token->value, $active_flags->all()));
			}
		}
		
		return $chars;
	}
}
