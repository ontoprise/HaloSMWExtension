<?php

/**
 * @file
  * @ingroup DAPOM
  * 
  * @author Dian
 */

/**
 * Helps to manage the operations with template parameters.
 *
 */
class POMUtilTrimTriple
{
	/**
	 * The leading space before the string.
	 *
	 * @var string
	 */
	public $leading_space;
	/**
	 * The string value between the trimmed chars.
	 *
	 * @var string
	 */
	public $trimmed;
	/**
	 * The trailing space after the string.
	 *
	 * @var string
	 */
	public $trailing_space;

	/**
	 * The class constructor.
	 *
	 * @param string $text The text used for parsing the parameter attributes.
	 * @return POMUtilTrimTriple
	 */
	public function POMUtilTrimTriple($text)
	{
		$this->trimmed = trim($text);
		if (strlen($this->trimmed) > 0)
		{
			$this->leading_space = substr($text, 0, strpos($text, $this->trimmed));
			if (strpos($text, $this->trimmed) + strlen($this->trimmed) < strlen($text))
			{
				$this->trailing_space = substr($text, strpos($text, $this->trimmed) + strlen($this->trimmed));
			}
			else
			{
				$this->trailing_space = '';
			}
		}
		else
		{
			$this->leading_space = '';
			$this->trailing_space = $text;
		}
	}

	public function toString()
	{
		return $this->leading_space.$this->trimmed.$this->trailing_space;
	}
}

/**
 * Used especially for values of template parameters.
 * Since a parameter value can have all the elements (not attributes) a page can have,
 * a reference to a page object is stored in each value-attribute.<br/>
 * This works like a deep search:
 * * after the the parser starts by reading a given page, it could reach a template<br/>
 * * if the template has parameters, their value is set by calling the constructor of this class<br/>
 * * the class constructor creates a new page object and runs the parser over the content of the parameter value
 *
 * @see POMTemplateParameter
 *
 */
class POMUtilTrimParameterValueTriple extends POMUtilTrimTriple
{
	/**
	 * The class constructor.
	 *
	 * @param string $text The text used for parsing the value of the parameter.
	 * @return POMUtilTrimParameterValueTriple
	 */
	public function POMUtilTrimParameterValueTriple($text)
	{
		// adds a page object reference as value of the trimmed attribute
		$__trimmed = trim($text);
		$__pomPage = new POMPage("temporarypage", $__trimmed);
		$this->trimmed = &$__pomPage;

		if (strlen($this->trimmed->toString()) > 0)
		{
			$this->leading_space = substr($text, 0, strpos($text, $this->trimmed));
			if (strpos($text, $this->trimmed->toString()) + strlen($this->trimmed->toString()) < strlen($text))
			{
				$this->trailing_space = substr($text, strpos($text, $this->trimmed->toString()) + strlen($this->trimmed->toString()));
			}
			else
			{
				$this->trailing_space = '';
			}
		}
		else
		{
			$this->leading_space = '';
			$this->trailing_space = $text;
			// add an empty POMSimpleText object to the page
			$this->trimmed->addElement(new POMSimpleText(''));
		}
	}

	public function toString()
	{
		return $this->leading_space.$this->trimmed->toString().$this->trailing_space;
	}
}
class POMUrlUtil{
	/**
	 * Checks if a given URL exists.
	 *
	 * @param string $url 
	 * @return boolean 
	 */
	public function url_exists($url) {
		$hdrs = @get_headers($url);
		return is_array($hdrs) ? preg_match('/^HTTP\\/\\d+\\.\\d+\\s+2\\d\\d\\s+.*$/',$hdrs[0]) : false;
	}
}
