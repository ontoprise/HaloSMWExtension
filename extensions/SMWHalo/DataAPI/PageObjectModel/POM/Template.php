<?php

/**
 * @file
  * @ingroup DAPOM
  * 
  * @author Dian
 */

/**
 * Represents a template abstraction in the POM of a page.
 *
 */
class POMTemplate extends POMDcbElement
{
	/**
	 * The title of the template.
	 *
	 * @var string
	 */
	public $title;

	/**
	 * The parameters of the template.
	 *
	 * @var array(string)
	 */
	public $parameters = array();

	/**
	 * Constructs a new template. In order to createa a new empty template, one should:<br/>
	 * *call the constructor with a string, e.g. $template = new POMTemplate('{{TEMPLATENAME}}');<br/>
	 * *and then start adding parameters with $template->setParameter("NAME", "TEXTVALUE");
	 *
	 * @param string $text The original text of the element.
	 * @return POMTemplate
	 */
	public function POMTemplate($text)
	{
		$this->children = null; // forcefully ignore children

		# Remove curly braces at the beginning and at the end
		$text = substr($text, 2, strlen($text) - 4);

		# Split by pipe
		$parts = split('\|', $text);

		$this->title = new POMUtilTrimTriple(array_shift($parts));
		$this->id = "template".POMElement::$elementCounter;
		POMElement::$elementCounter++;
//		$this->nodeText = $text;

		foreach ($parts as $part)
		{
			$this->parameters[] = POMTemplateParameter::parse($part);
		}		
	}

	/**
	 * Return the title of the template.
	 *
	 * @return string The title.
	 */
	public function getTitle()
	{
		return $this->title->trimmed;
	}
	/**
	 * Convert the object to a string.
	 *
	 * @return string
	 */
	public function toString()
	{
		$text = '{{'.$this->title->toString();

		for ($i = 0; $i < count($this->parameters); $i++)
		{
			$text .= '|';			
			$text .= $this->parameters[$i]->toString();
		}

		$text .= '}}';

		return $text;
	}

	##
	## Template Parameter functions
	##

	/**
	 * Get the value of a specific parameter of the template.
	 *
	 * @param string $name The name of the parameter
	 * @return string The parameter value.
	 */
	public function &getParameterValue($name)
	{
		$trimmed_name = trim($name);
		if (strlen($trimmed_name) == 0)
		{
			throw new WrongParameterNameException('Can\'t get parameter with no name');
		}

		$number = 1;
		for ($i = 0; $i < count($this->parameters); $i++)
		{
			$parameter = &$this->parameters[$i];

			# checking this in runtime to make sure we cover all post-parsing updates to the list
			if (is_a($parameter, 'POMTemplateNamedParameter'))
			{
				if ($parameter->getName() == $trimmed_name)
				{
					return $parameter->getValueByReference();
				}
			}
			else if (is_a($parameter, 'POMTemplateNumberedParameter'))
			{
				if ($number == $trimmed_name)
				{
					return $parameter->getValueByReference();
				}
				$number++;
			}
		}

		return NULL; # none matched
	}

/**
	 * Get a specific parameter of the template.
	 *
	 * @param string $name The name of the parameter
	 * @return POMTemplateParameter The parameter.
	 */
	public function &getParameter($name)
	{
		$trimmed_name = trim($name);
		if (strlen($trimmed_name) == 0)
		{
			throw new WrongParameterNameException('Can\'t get parameter with no name');
		}

		$number = 1;
		for ($i = 0; $i < count($this->parameters); $i++)
		{
			$parameter = &$this->parameters[$i];

			# checking this in runtime to make sure we cover all post-parsing updates to the list
			if (is_a($parameter, 'POMTemplateNamedParameter'))
			{
				if ($parameter->getName() == $trimmed_name)
				{
					return $parameter;
				}
			}
			else if (is_a($parameter, 'POMTemplateNumberedParameter'))
			{
				if ($number == $trimmed_name)
				{
					return $parameter;
				}
				$number++;
			}
		}

		return NULL; # none matched
	}
	
	/**
	 * Set a parameter in the template. Used to update parameters or to create new ones.
	 * PLEASE NOTE: this function expect the value to be a string.  Please check the documentation
	 * of the {@link POMTemplateParameter} class in order to see how to modify parameter values directly.
	 * Using this function will OVERWRITE the existing POMPage object created for the parameter value.
	 *
	 * @param string $name The parameter name.
	 * @param string $value The parameter value.
	 * @param boolean $ignore_name_spacing
	 * @param boolean $ignore_value_spacing
	 * @param boolean $override_name_spacing
	 * @param boolean $override_value_spacing
	 */
	public function setParameter($name, $value,
	$ignore_name_spacing = true,
	$ignore_value_spacing = true,
	$override_name_spacing = false, # when original value exists
	$override_value_spacing = false	# when original value exists
	)
	{
		$trimmed_name = trim($name);
		if (strlen($trimmed_name) == 0)
		{
			throw new WrongParameterNameException("Can't set parameter with no name");
		}

		if ($ignore_name_spacing)
		{
			$name = $trimmed_name;
		}

		if ($ignore_value_spacing)
		{
			$value = trim($value);
		}

		# first go through named parameters and see if name matches
		for ($i = 0; $i < count($this->parameters); $i++)
		{
			if (is_a($this->parameters[$i], 'POMTemplateNamedParameter') &&
			$this->parameters[$i]->getName() == $trimmed_name)
			{
				$this->parameters[$i]->update($name, $value, $override_name_spacing, $override_value_spacing);
				return;
			}
		}

		# then go through numbered parameters and see if parameter with this number exists
		$number = 1;
		for ($i = 0; $i < count($this->parameters); $i++)
		{
			if (is_a($this->parameters[$i], 'POMTemplateNumberedParameter'))
			{
				if ($number == $trimmed_name)
				{
					$this->parameters[$i]->update($value, $override_value_spacing);
					return;
				}
				$number++;
			}
		}

		# now, if passed name is numeric, create numbered parameter, otherwise create named parameter
		# add parameter to parameters array
		if (is_numeric($trimmed_name) && ((int)$trimmed_name) == $trimmed_name)
		{
			$this->parameters[] = new POMTemplateNumberedParameter($value);
		}
		else
		{
			$this->parameters[] = new POMTemplateNamedParameter($name, $value);
		}
	}
}

class WrongParameterNameException extends Exception
{
	// Redefine the exception so message isn't optional
	public function __construct($message, $code = 0) {
		// some code

		// make sure everything is assigned properly
		parent::__construct($message, $code);
	}

	// custom string representation of object
	public function __toString() {
		return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
	}
}
