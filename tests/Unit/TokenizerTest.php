<?php

namespace Glhd\AnsiPants\Tests\Unit;

use Glhd\AnsiPants\Support\Parsing\EscapeSequence;
use Glhd\AnsiPants\Support\Parsing\Text;
use Glhd\AnsiPants\Support\Parsing\Token;
use Glhd\AnsiPants\Support\Parsing\Tokenizer;
use Glhd\AnsiPants\Tests\TestCase;

class TokenizerTest extends TestCase
{
	public function test_it_can_tokenize_ansi(): void
	{
		$parser = new Tokenizer("Hello \e[3mworld 😎");
		
		$expected = [
			new Text('H'),
			new Text('e'),
			new Text('l'),
			new Text('l'),
			new Text('o'),
			new Text(' '),
			new EscapeSequence("\e[3m"),
			new Text('w'),
			new Text('o'),
			new Text('r'),
			new Text('l'),
			new Text('d'),
			new Text(' '),
			new Text('😎'),
		];
		
		$this->assertParsedTo($expected, $parser);
	}
	
	protected function assertParsedTo(array $expected, iterable $parsed): void
	{
		$parsed = collect($parsed)->all();
		
		while (count($expected)) {
			$expected_item = array_shift($expected);
			$actual_item = array_shift($parsed);
			
			$this->assertInstanceOf(Token::class, $expected_item, 'Expectation should be token');
			$this->assertInstanceOf(Token::class, $actual_item, 'Parsed should have same number of tokens as expected');
			$this->assertEquals($expected_item::class, $actual_item::class, 'Parsed should be same token type');
			$this->assertEquals($expected_item->value, $actual_item->value, 'Parsed should be same value');
		}
	}
}
