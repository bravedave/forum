<?php
/*
 * David Bray
 * BrayWorth Pty Ltd
 * e. david@brayworth.com.au
 *
 * MIT License
 *
*/

namespace dvc\forum;

class strings extends \strings {
	static function AutoTextAsHTML( $text, $maxrows = -1 ) {
		$debug = false;
		//~ $debug = true;

		//~ if ( preg_match( '@^\<\!DOCTYPE@i', $text )) {
		if ( $text != strip_tags($text)) {

			if ( $debug) \sys::logger( sprintf( 'not converting text2html (it already is) (%s)', $maxrows));

			if ( (int)$maxrows > 0 ) {
				$text = strip_tags( $text, '<br>' );
				$text = preg_replace( "@<br(/)>@i", "\n", $text );
				return self::text2htmlExtended( $text, $maxrows );


			}
			else {
				return ( forumUtility::referLinks( $text ));

			}

		}

		if ( $debug) \sys::logger( 'converting text2html' );
		return self::text2htmlExtended( $text, $maxrows = -1 );

	}

	static function text2htmlExtended( $inText, $maxrows = -1 ) {
		//~ \sys::logger( sprintf( 'sys::text2htmlExtended :: %s', $inText));
		//~ \sys::logger( substr( '####*', 4, 1));
		$src = preg_replace(
			[
				'/\\\\\\\\\r\n/',
				'/&\s/'
			],
			[
				"\n",
				"&amp; "
			], $inText );
		//~ $src = preg_replace( "/\\\\\\\\\r\n/", "<br />", $inText );

		$aLines = explode( "\n", $src );

		$output = "";
		$indent = 0;
		$icount = 0;
		$dents = [];
		foreach ( $aLines as $line ) {
			if ( $maxrows > 0 && ( $icount ++ > ($maxrows+1)) ) break;

			$pattern = '/\%roman\%/i';
			$replacement = '';
			$line = preg_replace($pattern, $replacement, $line);

			if ( substr( $line, 0, 3 ) == "!!!" ) {
				while ( $indent > 0 ) { $indent --; $output .= "</ul>"; }
				$line = "<h5>" . substr( $line, 3 ) . "</h5>";

			}
			elseif ( substr( $line, 0, 2 ) == "!!" ) {
				while ( $indent > 0 ) { $indent --; $output .= "</ul>"; }
				$line = "<h4>" . substr( $line, 2 ) . "</h4>";

			}
			elseif ( substr( $line, 0, 1 ) == "`" ) {
				while ( $indent > 0 ) { $indent --; $output .= "</ul>"; }
				$line = "<span style='font-family: monospace;'>" . preg_replace( "/\s/", "&nbsp;", substr( $line, 1 )) . "</span><br />";

			}
			elseif ( substr( $line, 0, 5 ) == "*****" || substr( $line, 0, 5 ) == "#####" ) {
				while ( $indent > 5 ) {	$indent --; $output .= "</ul>"; }
				while ( $indent < 4 ) { $indent ++; $output .= "<ul>"; }
				while ( $indent < 5 ) {
					$indent ++;
					if ( substr( $line, 4, 1 ) == "#" )
						$output .= '<ul style="list-style-type: decimal;">';
					else
						$output .= "<ul>";
				}
				$line = "<li>" . substr( $line, 5 ) . "</li>";

			}
			elseif ( substr( $line, 0, 4 ) == "****" || substr( $line, 0, 4 ) == "####" ) {
				while ( $indent > 4 ) { $indent --; $output .= "</ul>"; }
				while ( $indent < 3 ) { $indent ++; $output .= "<ul>"; }
				while ( $indent < 4 ) {
					$indent ++;
					if ( substr( $line, 3, 1 ) == "#" )
						$output .= '<ul style="list-style-type: decimal;">';
					else
						$output .= "<ul>";

				}
				$line = "<li>" . substr( $line, 4 ) . "</li>";

			}
			elseif ( substr( $line, 0, 3 ) == "***" || substr( $line, 0, 3 ) == "###" ) {
				while ( $indent > 3 ) { $indent --; $output .= "</ul>"; }
				while ( $indent < 2 ) { $indent ++; $output .= "<ul>"; }
				while ( $indent < 3 ) {
					$indent ++;
					if ( substr( $line, 2, 1 ) == "#" )
						$output .= '<ul style="list-style-type: decimal;">';
					else
						$output .= "<ul>";


				}
				$line = "<li>" . substr( $line, 3 ) . "</li>";

			}
			elseif ( substr( $line, 0, 2 ) == "**" || substr( $line, 0, 2 ) == "##" ) {
				while ( $indent > 2 ) { $indent --; $output .= "</ul>"; }
				while ( $indent < 1 ) { $indent ++; $output .= "<ul>"; }
				while ( $indent < 2 ) {
					$indent ++;
					if ( substr( $line, 1, 1 ) == "#" )
						$output .= '<ul style="list-style-type: decimal;">';
					else
						$output .= "<ul>";

				}
				$line = "<li>" . substr( $line, 2 ) . "</li>";

			}
			else {

				while ( $indent > 1 ) { $indent --; $output .= "</ul>"; }

				if ( substr( $line, 0, 1 ) == "*" || substr( $line, 0, 1 ) == "#" ) {
					if ( $indent < 1 ) {
						$indent ++;
						if ( substr( $line, 0, 1 ) == "#" )
							$output .= '<ul style="list-style-type: decimal;">';
						else
							$output .= "<ul>";

					}

					$line = "<li>" . substr( $line, 1 ) . "</li>";

				}
				else {
					while ( $indent > 0 ) { $indent --; $output .= "</ul>"; }

					$line .= "<br />";

				}

			}

			$aLine = preg_split('/\'\'\'/', $line);
			if ( count( $aLine ) > 1 || preg_match( '/^\'\'\'/', $line )) {
				/*	was it bolded to start
					if yes, you will need to manually shift the
					blank element off the start of the array
					and set the tag to b (<b></b>)
					*/
				$tag = ( preg_match( '/^\'\'\'/', $line ) > 0 ? 'b' : '' );
				if ( $tag == "b" )
					array_shift( $aLine );

				$a = Array();
				foreach ( $aLine as $e ) {
					if ( $tag == '' ) {
						$a[] = $e;
						$tag = 'b';

					}
					else {
						$a[] = "<$tag>$e</$tag>";
						$tag = '';

					}

				}
				$line = implode( " ", $a );

			}

			$aLine = preg_split('/\'\'/', $line);
			if ( count( $aLine ) > 1 || preg_match( '/^\'\'/', $line )) {
				/*	was it italicised to start
					if yes, you will need to manually shift the
					blank element off the start of the array
					and set the tag to i (<i></i>)
					*/
				$tag = ( preg_match( '/^\'\'/', $line ) ? 'i' : '' );
				if ( $tag == "i" )
					array_shift( $aLine );

				$a = Array();
				foreach ( $aLine as $e ) {
					if ( $tag == '' ) {
						$a[] = $e;
						$tag = 'i';

					}
					else {
						$a[] = "<$tag>$e</$tag>";
						$tag = '';

					}

				}
				$line = implode( " ", $a );

			}

			$line = forumUtility::referLinks( $line);
			$output .= $line;

		}

		return ( $output );

	}

}
