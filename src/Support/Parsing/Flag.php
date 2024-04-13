<?php

namespace Glhd\AnsiPants\Support\Parsing;

enum Flag
{
	case Bold;
	case Dim;
	case Italic;
	case Underline;
	case SlowBlink;
	case RapidBlink;
	case Invert;
	case Hide;
	case CrossedOut;
	case PrimaryFont;
	case AlternativeFont;
	case DoubleUnderline;
	case NormalIntensity;
	case Framed;
	case Encircled;
	case Overline;
}
