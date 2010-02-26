<?php

/**
 * @file
  * @ingroup DAPOM
  * 
  * @author Dian
 */

/**
 * Set of classes representing various types of template parameters.
 * PLEASE NOTE: Parameter values are represented as {@link POMPage} objects and can be 
 * edited directly using the POMPage functions.
 *
 * @author Sergey Chernyshev
 */
abstract class POMTemplateParameter extends POMDcbElement
{
	public static function &parse($text)
	{

		$pair = split('=', $text, 2);

		# if it's a name/value pair, create POMTemplateNamedParameter, otherwise, create POMTemplateNumberedParameter
		# if neither can be created, return POMTemplateInvalidParameter
		if (count($pair) > 1)
		{
			$name = $pair[0];
			$value = $pair[1];

			$name_triple = new POMUtilTrimTriple($name);

			if (strlen($name_triple->trimmed)<=0)
			{
				# ignore parameters with empty name
				return new POMTemplateInvalidParameter($text);
			}

			return new POMTemplateNamedParameter($name, $value);
		}
		else
		{
			return new POMTemplateNumberedParameter($text);
		}
	}
}

/**
 * A named template parameter class.
 *
 * @author Sergey Chernyshev
 */
class POMTemplateNamedParameter extends POMTemplateParameter
{
	/**
	 * A triple consisting of leading_chars+name+trailing_chars.
	 *
	 * @var string
	 */
	public $name_triple;
	/**
	 * A triple consisting of leading_chars+value+trailing_chars.
	 *
	 * @var string
	 */
	public $value_triple;

	/**
	 * The class constructor.
	 *
	 * @param string $name Parameter name.
	 * @param string $value Parameter value.
	 */
	public function __construct($name, $value)
	{
		$this->name_triple = new POMUtilTrimTriple($name);
		$this->value_triple = new POMUtilTrimParameterValueTriple($value);
//		$this->nodeText = $this->toString();
		
		$this->id = "namedParameter".POMElement::$elementCounter;
		POMElement::$elementCounter++;
	}

	/**
	 * Updates the parameter.
	 *
	 * @param string $name Parameter name.
	 * @param string $value Parameter value.
	 * @param boolean $override_name_spacing
	 * @param boolean $override_value_spacing
	 */
	public function update($name, $value, $override_name_spacing = false, $override_value_spacing = false)
	{
		$name_triple = new POMUtilTrimTriple($name);
		$value_triple = new POMUtilTrimParameterValueTriple($value);

		if ($override_name_spacing)
		{
			$this->name_triple = $name_triple;
		}
		else
		{
			$this->name_triple->trimmed = $name_triple->trimmed;
		}

		if ($override_value_spacing)
		{
			$this->value_triple = $value_triple;
		}
		else
		{
			$this->value_triple->trimmed = $value_triple->trimmed;
		}
	}

	/**
	 * Getter for the trimmed parameter name.
	 *
	 * @return string The name.
	 */
	public function getName()
	{
		return $this->name_triple->trimmed;
	}
	/**
	 * Getter for the trimmed parameter value.
	 *
	 * @return string The value.
	 */
	public function getValue()
	{
		return $this->value_triple->trimmed;
	}

	/**
	 * Getter for the trimmed parameter value.
	 *
	 * @return string The value reference.
	 */
	public function &getValueByReference()
	{
		return $this->value_triple->trimmed;
	}

	/**
	 * Getter for the parameter name with leading and trailing chars.
	 *
	 * @return string The name.
	 */
	public function getNameTriple()
	{
		return $this->name_triple;
	}
	/**
	 * Getter for the parameter value with leading and trailing chars.
	 *
	 * @return string The value.
	 */
	public function getValueTriple()
	{
		return $this->value_triple;
	}

	/**
	 * Converts the parameter name and value to a string, including the '='.
	 *
	 * @return string The parameter object as a string.
	 */
	public function toString()
	{
		return $this->name_triple->toString().'='.$this->value_triple->toString();
	}
}

/**
 * A numbered template parameter class.
 *
 */
class POMTemplateNumberedParameter extends POMTemplateParameter
{
	/**
	 * A triple consisting of leading_chars+value+trailing_chars.
	 *
	 * @var string
	 */
	public $value_triple;

	public function __construct($value)
	{
		$this->value_triple = new POMUtilTrimParameterValueTriple($value);
//		$this->nodeText = $this->toString();
		
		$this->id = "numberedParameter".POMElement::$elementCounter;
		POMElement::$elementCounter++;
	}
	/**
	 * Updates the parameter.
	 *
	 * @param string $name Parameter name.
	 * @param string $value Parameter value.
	 * @param boolean $override_name_spacing
	 * @param boolean $override_value_spacing
	 */
	public function update($value, $override_value_spacing = false)
	{
		$value_triple = new POMUtilTrimParameterValueTriple($value);

		if ($override_value_spacing)
		{
			$this->value_triple = $value_triple;
		}
		else
		{
			$this->value_triple->trimmed = $value_triple->trimmed;
		}
	}
	/**
	 * Getter for the trimmed parameter value.
	 *
	 * @return string The value.
	 */
	public function getValue()
	{
		return $this->value_triple->trimmed;
	}
	/**
	 * Getter for the trimmed parameter value.
	 *
	 * @return string The value reference.
	 */
	public function &getValueByReference()
	{
		return $this->value_triple->trimmed;
	}
	/**
	 * Getter for the parameter value with leading and trailing chars.
	 *
	 * @return string The value.
	 */
	public function getValueTriple()
	{
		return $this->value_triple;
	}
	/**
	 * Converts the parameter name and value to a string, including the '='.
	 *
	 * @return string The parameter object as a string.
	 */
	public function toString()
	{
		return $this->value_triple->toString();
	}

}

/**
 * Represents parameters that need to be preserved, but not parsed
 * @author Sergey Chernyshev
 */
class POMTemplateInvalidParameter extends POMTemplateParameter
{

	function __construct($text)
	{
		$this->nodeText = $text;
	}

	function toString()
	{
		return $this->nodeText;
	}
}
