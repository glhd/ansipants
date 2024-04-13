<?php

namespace Glhd\AnsiPants\Support\Parsing;

enum BufferType
{
	case Text;
	case PartialEscapeSequence;
	case EscapeSequence;
}
