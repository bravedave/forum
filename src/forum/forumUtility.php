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

use html;

abstract class forumUtility {
	static function referLinks($text) {
		return preg_replace_callback("@{topic:[^}]*}@", function ($matches) {
			$str = preg_replace('@({|})@', '', $matches[0]);
			$a = explode(':', $str);

			//~ print_r( $matches );

			// as usual: $matches[0] is the complete match
			// $matches[1] the match for the first subpattern
			// enclosed in '(...)' and so on
			return sprintf('<a href="%s">%s:%s</a>', strings::url('forum/view/' . $a[1]), $a[0], $a[1]);
		}, $text);
	}

	static function printThread(dao\dto\forum $dto, $reversed = TRUE, $email = FALSE) {
		$ret = [];

		$t = [];
		if ($dto->thread != '')
			$t = explode(':', $dto->thread);

		$t[] = $dto->id;
		//~ sys::dump( $t );

		$text = $dto->comment;
		if (substr($text, 0, 1) == '<') {
			$text = self::referLinks($text);
		} else {
			$text = strings::AutoTextAsHTML(htmlentities($dto->comment));
		}

		if ($email) {
			$ret[] = sprintf(
				'<table class="table" style="margin-bottom: 0; border: 0;"><tbody>
				<tr>
					<td>%s</td>
					<td style="width: 60px;" class="text-center">%s<br>%s</td>
					<td style="width: 60px;" class="text-center">%s</td>
				</tr>%s',
				$text,
				strings::asShortDate($dto->updated),
				date('h:ia', strtotime($dto->updated)),
				strings::initials($dto->name),
				PHP_EOL
			);

			if (count($dto->children)) {
				$ret[] = sprintf('<tr><td>%s', PHP_EOL);
				if ($reversed) {
					for ($i = count($dto->children); $i > 0; $i--)
						$ret[] = self::printThread($dto->children[$i - 1], $reversed);
				} else {
					foreach ($dto->children as $child)
						$ret[] = self::printThread($child, $reversed);
				}

				$ret[] = sprintf('</td></tr>%s', PHP_EOL);
			}

			$ret[] = sprintf('</tbody></table>%s', PHP_EOL);
		} else {

			$ret[] = sprintf(
				'<div class="row g-2" data-thread="%s">
					<div class="col-lg-1 col-md-2 border-top text-center px-0 pt-2"><small>%s%s</small></div>
					<div class="col border-top border-left" style="%s">%s
						<div data-role="comment-container"></div>
					</div>
				</div>%s',
				implode(':', $t),
				html::icon($dto->name),
				strings::asShortDate($dto->updated, TRUE),
				'position: relative; min-height: 80px;',
				$text,
				PHP_EOL
			);

			if (count($dto->children)) {
				if ($reversed) {
					for ($i = count($dto->children); $i > 0; $i--)
						$ret[] = self::printThread($dto->children[$i - 1], $reversed);
				} else {
					foreach ($dto->children as $child)
						$ret[] = self::printThread($child, $reversed);
				}
			}
		}

		return (implode('', $ret));
	}
}
