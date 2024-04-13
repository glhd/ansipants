<?php

namespace Glhd\AnsiPants\Support\Parsing;

use Glhd\AnsiPants\Flag;

class EscapeSequence implements Token
{
	public ?int $bits = null;
	
	public Flag $flag;
	
	public array $args = [];
	
	public function __construct(
		public string $value
	) {
		$this->extractMetadata();
	}
	
	protected function extractMetadata(): void
	{
		preg_match("/^\e\[(\d+(?:;\d+)*)+m$/", $this->value, $matches);
		
		$args = array_map('intval', explode(';', $matches[1]));
		
		$this->flag = Flag::from(array_shift($args));
		$this->args = $args;
		
		if (
			($this->flag >= 30 && $this->flag < 37)
			|| ($this->flag >= 40 && $this->flag < 47)
			|| ($this->flag >= 90 && $this->flag < 97)
			|| ($this->flag >= 100 && $this->flag < 107)
		) {
			$this->bits = 4;
		}
		
		if (($this->flag === 38 || $this->flag === 48) && $this->args[0] === 5) {
			$this->bits = 8;
		}
		
		if (($this->flag === 38 || $this->flag === 48) && $this->args[0] === 2 && count($this->args) === 4) {
			$this->bits = 24;
		}
	}
}
