<?php

/**
 * @file
  * @ingroup DAPOM
  * 
  * @author Dian
 */

/**
 * <p>
 * Page class reprersents a wiki page. The wiki page consists of elements, which
 * In order to create a page, the page title and text are needed. The text must be the row wiki mark-up.
 * can again have page objects as children. For example, a template parameter could have a template as value.
 * More text parsers could be used, but at this version only the {@link POMExtendedParser} is supported.
 * </p>
 * Let's take a look of the following example showing the usage of the POMPage class:<br/>
 * <p>
 * Suppose the the following example page in your wiki named 'Testpage' (shown in wiki mark-up):<br/>
 * <i>
 * <code>
 * {{TemplateName<br/>
 * |Created at=a couple of days ago<br/>
 * |anotherParameter=another value<br/>
 * }}<br/>
 * And here comes text ...<br/>
 * </code>
 * </i>
 * Now let's use the following test code:<br/>
 * </p>
 *  <i>
 * <code>
 *
 * // step 1<br/>
 * $pom = new POMPage('Testpage',
 * 				join(file('http://<wikihome>/index.php?title=Testpage&action=raw')));<br/>
 *  // step 2<br/>
 *  $iterator = $pom->getTemplateByTitle("TemplateName")->listIterator();<br/>
 *  while($iterator->hasNext()){<br/>
 *   // step 3<br/>
 *	 $template = &$iterator->getNextNodeValueByReference();<br/>
 *	 // step 4<br/>
 *   if ($template->getParameter("Created at") !== NULL){<br/>
 *		// step 5<br/>
 * 		$simpleText = &$template->getParameter("Created at")->getValueByReference()->getSimpleText();<br/>
 *		$simpleText = new POMSimpleText("today");<br/>
 *	 }<br/>
 *  }<br/>
 * </code>
 * </i>
 * <br/>
 * The steps do the following:<br/>
 * 1. One can use the {@link PCPPage} and the PageCRUD_Plus package to read the content of a page, but
 * 	we will use a simple way to access the wiki mark-up in order to concentrate on the actual example.<br/>
 * 2. Create an iterator over the templates named 'TemplateName' of the 'Testpage'.<br/>
 * 3. For each template in the retrieved list, get a direct reference (access only by value is supported too).<br/>
 * 4. Check if a parameter 'Created at' exits.<br/>
 * 5. Get a reference to the parameter value and set the new value to a new simple text element ("today").<br/>
 *
 * The object based structure of the page looks like:<br/>
 * page 'Testpage'<br/>
 * &nbsp;&nbsp;&nbsp;-> child template 'TemplateName'<br/>
 * &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;-> child template parameter 'Created at'<br/>
 * &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;-> child page 'temporarypage' (a programmer doesn't see this object directly)<br/>
 * &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;-> child simple text 'today'<br/>
 *
 * <p>
 * The expected result is:<br/>
 * <i>
 * <code>
 * {{TemplateName<br/>
 * |Created at=today<br/>
 * |anotherParameter=another value<br/>
 * }}<br/>
 * And here comes text ...<br/>
 * </code>
 * </i>
 * </p>
 *
 * @see PCPPage
 * @see POMElement
 *
 * @author Sergey Chernyshev
 * @author Dian As of version 0.4.
 */
class POMPage extends PCPPage
{
	/**
	 * The children nodes of the element.
	 *
	 * @var DLList
	 */
	protected $children;
	/**
	 * The constructor of the class.
	 * In the constructor the supported element types are set. If an element is added and
	 * it is not in the list of the supported elemens, a "Object of class DLList could not be converted to string " exception is thrown.
	 *
	 * @param string $title
	 * @param string $text
	 * @param string[] $parsers
	 * @return POMPage
	 */
	public function POMPage($title, $text, $parsers = array('POMExtendedParser'))
	{
		$this->title = $title;
		$this->text = $text;
		$this->children =  new DLList();
		$this->children->setShortcutTypes(array(
			'POMTemplate',
			'POMAskFunction',
			'POMBuiltInParserFunction',
			'POMExtensionParserFunction',
			'POMCategory', 
			'POMProperty', 
			'POMSimpleText'));

		foreach ($parsers as $parser)
		{
			$__parserObj = call_user_func(array($parser, 'getParser'));
			$__parserObj->Parse($this);
		}
	}

	/* Constructs the POM using a PCPPage object.
	 * In the constructor the supported element types are set. If an element is added and
	 * it is not in the list of the supported elemens, a "Object of class DLList could not be converted to string " exception is thrown.
	 *
	 * @param PCPPage $pcpPageObj
	 * @param string[] $parsers
	 * @return POMPage
	 */
	public static function fromPCPPage(PCPPage $pcpPageObj = NULL, $parsers = array('POMExtendedParser'))
	{
		$__pomPage = new POMPage("", "", array());
		$__pomPage->title = $pcpPageObj->title;
		$__pomPage->text = $pcpPageObj->text;
		$__pomPage->pageid = $pcpPageObj->pageid;
		$__pomPage->namespace = $pcpPageObj->namespace;
		$__pomPage->summary = $pcpPageObj->summary;
		$__pomPage->basetimestamp = $pcpPageObj->basetimestamp;
		$__pomPage->lastrevid = $pcpPageObj->lastrevid;
		$__pomPage->usedrevid = $pcpPageObj->usedrevid;
		
		
		$__pomPage->children =  new DLList();
		$__pomPage->children->setShortcutTypes(array(
			'POMTemplate',
			'POMAskFunction',
			'POMBuiltInParserFunction',
			'POMExtensionParserFunction',
			'POMCategory', 
			'POMProperty', 
			'POMSimpleText'));

		foreach ($parsers as $parser)
		{
			$__parserObj = call_user_func(array($parser, 'getParser'));
			$__parserObj->Parse($__pomPage);
		}
		return $__pomPage;
	}
	/**
	 * Get the namespace of the page.
	 *
	 * @return string
	 */
	public function getNamespace(){
		$__start = strpos($this->title, ':');
		return substr($this->title, 0, $__start);
	}

	/**
	 * Add an element to the tail of the children list of the page.
	 * @see addElementAtPosition
	 * @param POMElement $element
	 */
	public function addElement(POMElement &$element){
		$this->addElementAtPosition($element);
	}

	/**
	 * Add a an element to the page. The element is added at the specific position in the <b>text</b>.
	 * There are several cases covered - the requested position is within:<br/>
	 * - a template => no child is added, but an error is returned.<br/>
	 * - an annotation => there are two subcases - the object to be added is:<br/>
	 *  -- an annotation => no child is added, the new annotation replaces the old annotation.<br/>
	 *  -- not an annotation => no changes applied.<br/>
	 * - a simple text => there are two subcases - the object to be added is:<br/>
	 * 	-- also a simple text => the text is added at the requirede position - only the node text is retrieved, the ID is ignored.<br/>
	 *  -- not a simple text => the old object is splitted into two new objects with the new object in between.<br/>
	 *
	 * @param POMElement $node The child element to be added.
	 * @param int $position The position in the text, at which the child must be added. If not set, adds the child at the end of the page.
	 *
	 * @return True, if operation successful or false, if an error occured.
	 */
	public function addElementAtPosition(POMElement &$element, $position = -1)
	{
		if($position === -1){
			$this->children->add($element);
			return true;
		}else{
			$__searchResult = array();
			$__oldNode = NULL; // the exiting element at the requested position
			$__charCount = 0; // the number of chars before this element

			$__searchResult = $this->searchInTextAtPosition($position);
			$__oldNode = &$__searchResult[0];
			$__charCount = $__searchResult[1];

			#var_dump($__oldNode->element);

			switch (get_class($__oldNode->element)){
				case 'POMTemplate':
					if ( strcmp('POMTemplate', get_class($element)) === 0){ // types match
						// perhaps allow adding a template into a template ...
						return false;
					}else{ // types mismatch
						return false;
					}
					break;
				case 'POMCategory':
					if ( strcmp('POMCategory', get_class($element)) === 0){ // types match
						$__oldNode->element = $element;
						return false;
					}else{ // types mismatch
						return false;
					}
					break;
				case 'POMProperty' :
					if ( strcmp('POMProperty', get_class($element)) === 0){ // types match
						$__oldNode->element = &$element;
						return false;
					}else{ // types mismatch
						return false;
					}
					break;
				case 'POMSimpleText':
					if ( strcmp('POMSimpleText', get_class($element)) === 0){ // types match
						// don't split the node, just add the text
						$__relativePosition = $position - $__charCount;
						$__stringBeforePos = substr($__oldNode->element->nodeText, 0, $__relativePosition);
						$__stringAfterPos = substr($__oldNode->element->nodeText, $__relativePosition+1);
						$__oldNode->element->nodeText = $__stringBeforePos.$element->nodeText.$__stringAfterPos;

						return true;
					}else{ // types mismatch
						$__relativePosition = $position - $__charCount;
						$__stringBeforePos = substr($__oldNode->element->nodeText, 0, $__relativePosition);
						$__stringAfterPos = substr($__oldNode->element->nodeText, $__relativePosition+1);

						$__oldNode->element->nodeText =$__stringBeforePos;
						$__oldNodePosInList = $this->children->getPosition($__oldNode->element);


						// add the new node and the rest of teh old node as a new node
						$this->children->addAtPosition($__oldNodePosInList+1, $element);
						$this->children->addAtPosition($__oldNodePosInList+2, new POMSimpleText($__stringAfterPos));
							
						return true;
					}
					break;
				default:
					return false; // unsupported POMElement
			}
		}

		return false;
	}

	/**
	 * Adds the new element before the given element which already exists in the child list of the page.
	 *
	 * @param POMElement $oldElement The referenced element in the child list.
	 * @param POMElement $newElement The element to be added.
	 *
	 * @return  TRUE == insertin ok || FALSE == error adding element
	 */
	public function addElementBefore(POMElement  &$oldElement, POMElement  &$newElement){
		return $this->children->addAtPosition($this->children->getPosition($oldElement), &$newElement);
	}

	/**
	 * Adds teh new element after the given element which already exists in the child list of the page.
	 *
	 * @param POMElement $oldElement The referenced element in the child list.
	 * @param POMElement $newElement The element to be added.
	 *
	 * @return  TRUE == insertin ok || FALSE == error adding element
	 */
	public function addElementAfter(POMElement  $oldElement, POMElement  &$newElement){
		return $this->children->addAtPosition($this->children->getPosition($oldElement)+1, $newElement);
	}

	/* Retrieve an element on a page
	 *
	 * @param string $id The ID of the element.
	 *
	 * @return POMElement The element.
	 */
	public function &getElement($id = NULL){
		$__iterator = $this->children->listIterator();
		if($id !== NULL){
			while($__iterator->hasNext()){
				$__element = &$__iterator->getNextNodeValueByReference();
				if( strcmp($__element->id, $id) === 0){
					return $__element;
				}
			}
			return NULL;
		}else{
			return $__iterator->getNextNodeValueByReference();
		}
	}

	/* Retrieve an elements on a page.
	 *
	 * @return DLList The elements.
	 */
	public function &getElements(){
		return $this->children;
	}

	/**
	 * Retrieve all templates on a page.
	 *
	 * @param string $type The type of the template. Possible values are any of the template class hierarchy names. Multiple values are separated by '|'.
	 * @see POMTemplate
	 *
	 * @return DLList The templates.
	 */
	public function &getTemplates($type = 'POMTemplate'){
		return $this->children->getShortcuts($type);
	}

	/**
	 *
	 * Retrieve a specific template based on the ID. If no ID is given, the first template is returned.
	 *
	 * @param string $id The ID of the element.
	 * @param string $type The type of the template. Possible values are any of the template class hierarchy names. Multiple values are separated by '|'.
	 * @see POMTemplate
	 * @return POMTemplate The template found or NULL.
	 */
	public function &getTemplateByID($id = NULL, $type = 'POMTemplate'){
		$__iterator = $this->children->shortcutsListIterator($type);
		if($id !== NULL){
			while($__iterator->hasNext()){
				$__element = &$__iterator->getNextNodeValueByReference();
				if( strcmp($__element->id, $id) === 0){
					return $__element;
				}
			}
			return NULL;
		}else{
			return $__iterator->getNextNodeValueByReference();
		}
	}

	/**
	 *
	 * Retrieve a specific template based on the title. If no title is given, the first template is returned.
	 *
	 * @param string $title The ID of the element.
	 * @param string $type The type of the template. Possible values are any of the template class hierarchy names. Multiple values are separated by '|'.
	 * @see POMTemplate
	 * @return DLList(POMTemplate) The template(s) found or an empty list.
	 */
	public function &getTemplateByTitle($title = '', $type = 'POMTemplate'){
		$__resultingArray = new DLList();
		$__iterator = $this->children->shortcutsListIterator($type);
		if($title !== ''){
			while($__iterator->hasNext()){
				$__element = &$__iterator->getNextNodeValueByReference();
				if( strcmp($__element->getTitle(), $title) === 0){
					$__resultingArray->add($__element);
				}
			}
			return $__resultingArray;
		}else{
			$__resultingArray->add($__iterator->getNextNodeValueByReference());
			return $__resultingArray;
		}
	}

	/**
	 * Retrieve all properties on a page.
	 *
	 * @return DLList The properties.
	 */
	public function &getProperties(){
		return $this->children->getShortcuts('POMProperty');
	}

	/**
	 *
	 * Retrieve a specific property based on the ID. If no ID is given, the first property is returned.
	 *
	 * @param string $id The ID of the element.
	 *
	 * @return POMProperty The property found or NULL.
	 */
	public function &getPropertyByID($id = NULL){
		$__iterator = $this->children->shortcutsListIterator('POMProperty');
		if($id !== NULL){
			while($__iterator->hasNext()){
				$__element = &$__iterator->getNextNodeValueByReference();
				if( strcmp($__element->id, $id) === 0){
					return $__element;
				}
			}
			return NULL;
		}else{
			return $__iterator->getNextNodeValueByReference();
		}
	}

	/**
	 *
	 * Retrieve a specific property based on the name. If no name is given, the first property is returned.
	 *
	 * @param string $name The name of the element.
	 *
	 * @return DLList(POMProperty) The property(ies) found or empty list.
	 */
	public function &getPropertyByName($name = ''){
		$__resultingArray = new DLList();
		$__iterator = $this->children->shortcutsListIterator('POMProperty');
		if($name !== NULL){
			while($__iterator->hasNext()){
				$__element = &$__iterator->getNextNodeValueByReference();
				if( strcmp($__element->name, $name) === 0){
					$__resultingArray->add($__element);
				}
			}
			return $__resultingArray;
		}else{
			return $__resultingArray;
		}
	}

	/**
	 * Retrieve all categories on a page.
	 *
	 * @return DLList The categories.
	 */
	public function &getCategories(){
		return $this->children->getShortcuts('POMCategory');
	}

	/**
	 *
	 * Retrieve a specific category based on the ID. If no ID is given, the first category is returned.
	 *
	 * @param string $id The ID of the element.
	 *
	 * @return POMCategory The category found or NULL.
	 */
	public function &getCategoryByID($id = NULL){
		$__iterator = $this->children->shortcutsListIterator('POMCategory');
		if($id !== NULL){
			while($__iterator->hasNext()){
				$__element = &$__iterator->getNextNodeValueByReference();
				if( strcmp($__element->id, $id) === 0){
					return $__element;
				}
			}
			return NULL;
		}else{
			return $__iterator->getNextNodeValueByReference();
		}
	}

	/**
	 *
	 * Retrieve a specific category based on the name. If no name is given, the first category is returned.
	 *
	 * @param string $name The name of the element.
	 *
	 * @return DLList(POMProperty) The category(ies) found or empty list.
	 */
	public function &getCategoryByName($name = ''){
		$__resultingArray = new DLList();
		$__iterator = $this->children->shortcutsListIterator('POMCategory');
		if($name !== NULL){
			while($__iterator->hasNext()){
				$__element = &$__iterator->getNextNodeValueByReference();
				if( strcmp($__element->name, $name) === 0){
					$__resultingArray->add($__element);
				}
			}
			return $__resultingArray;
		}else{
			$__resultingArray->add($__iterator->getNextNodeValueByReference());
			return $__resultingArray;
		}
	}

	/**
	 * Retrieve all simple texts on a page.
	 *
	 * @return DLList The simple texts.
	 */
	public function &getSimpleTexts(){
		return $this->children->getShortcuts('POMSimpleText');
	}

	/**
	 *
	 * Retrieve a specific simple text based on the ID. If no ID is given, the first simple text is returned.
	 *
	 * @param string $id The ID of the element.
	 *
	 * @return <POMSimpleText> The simple text found or NULL.
	 */
	public function &getSimpleText($id = NULL){
		$__iterator = $this->children->shortcutsListIterator('POMSimpleText');
		if($id !== NULL){
			while($__iterator->hasNext()){
				$__element = &$__iterator->getNextNodeValueByReference();
				if( strcmp($__element->id, $id) === 0){
					return $__element;
				}
			}
			return NULL;
		}else{
			return $__iterator->getNextNodeValueByReference();
		}
	}

	/**
	 * Copies all texts from the page elements and puts them in the page text attribute.
	 *
	 * @return boolean True if synced properly.
	 */
	public function sync(){
		$__iterator = $this->children->listIterator();
		$this->text = '';
		while($__iterator->hasNext()){
			$__element = $__iterator->getNextNodeValueByReference();
			$this->text .= $__element->toString();
		}
		return true;
	}

	/**
	 * Find the object which contains the requested position in text.
	 *
	 * @param int $textPos The position in the text.
	 * @return array(POMElement, int) The element at the requested position and the number of chars before this element.
	 */
	public function searchInTextAtPosition($textPos = -1){
		$__charCount = 0;
		if(!$this->children->isEmpty()){
			$__node = &$this->children->head;
			for($__i=0; $i<=$this->children->size(); $__i++){
				$__node = &$__node->getNext();
				$__charCount+= strlen($__node->element->nodeText);
				if ($__charCount >= $textPos){
					return array(&$__node,$__charCount-strlen($__node->element->nodeText));
				}
			}
		}
		return NULL;
	}

	/**
	 * Updates a given element on the page. The element is identified by it's ID.
	 *
	 * @param POMElement $element
	 *
	 * @return POMElement The reference to the updated element or NULL.
	 */
	public function &update(POMElement $element = NULL){
		$__iterator = $this->children->listIterator();
		if($element->id !== NULL){
			while($__iterator->hasNext()){
				$__element = &$__iterator->getNextNodeValueByReference();
				if( strcmp($__element->id, $element->id) === 0){
					$__element = $element;
					return $__element;
				}
			}
			return NULL;
		}else{
			return NULL;
		}
	}

	/**
	 * Deletes a given element on the page. The element is identified by it's ID.
	 *
	 * @param POMElement $element
	 *
	 * @return boolean True if successful, false if not.
	 */
	public function &delete(POMElement $element = NULL){
		$__iterator = $this->children->listIterator();
		if($element->id !== NULL){
			while($__iterator->hasNext()){
				$__element = &$__iterator->getNextNodeValueByReference();
				if( strcmp($__element->id, $element->id) === 0){
					return $this->children->remove($__element);
				}
			}
			return false;
		}else{
			return false;
		}
	}

	/**
	 * Syncs the page with all its elements and converts them to strings.
	 *
	 * @return string The current text representation of the page.
	 */
	public function toString(){
		$this->sync();
		return $this->text;
	}

}

