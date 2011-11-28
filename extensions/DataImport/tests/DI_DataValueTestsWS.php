<?php
/*
 * Copyright (C) Vulcan Inc.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program.If not, see <http://www.gnu.org/licenses/>.
 *
 */

	$result = '<?xml version="1.0"?>';
   $result .= '<result>';
	$result .= '<item><title>AA</title><fnumval>5</fnumval><snumval>44</snumval></item>';
	$result .= '<item><title>BB</title><fnumval>4</fnumval><snumval>55</snumval></item>';
	$result .= '<item><title>CC</title><fnumval>3</fnumval><snumval>66</snumval></item>';
	$result .= '<item><title>DD</title><fnumval>2</fnumval><snumval>77</snumval></item>';
	$result .= '<item><title>EE</title><fnumval>1</fnumval><snumval>88</snumval></item>';
	$result .= '</result>';
	
	echo($result);
?>
