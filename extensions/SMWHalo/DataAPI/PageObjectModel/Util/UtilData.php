<?php

/**
 * @file
  * @ingroup DAPOM
  * 
  * @author Dian
 */

/**
 * The class represents a double linked list specially suited for adding POM elements.
 *
 */
class DLList{
	var $head;    // reference to the first node
	var $tail;    // reference to the last node
	var $size;    // The total number of nodes

	/**
	 * For each element type contains referencies to the elements.
	 *
	 * @var DLList array
	 */
	private $shortcuts = NULL;

	/**
	 * Class constructor.
	 *
	 * @return DLList
	 */
	public function DLList(){
		//head and tail only points to the head and tail of the list,
		//the list nodes does not point on head and tail! Hence, head and
		//tail are not part of the list.
		$this->head = new ListNode();
		$this->tail = new ListNode();
		$this->size = 0;

		$this->shortcuts = array();
	}

	/**
	 * Adds a new object at a specific index, pushes
	 * the current further back into the list.
	 * @param $index Numeric value between 0 and $this->size()-1
	 * @param &$object A reference to the object that is to be inserted
	 * @return TRUE == insertino ok || FALSE == index out of bounds
	 */
	public function addAtPosition($index, &$object){
		//checks if index is not out of bounds
		if($index >= 0 && $index <= $this->size()-1){
			$newNode = $this->getNewNode($object);
			$nodeAtIndex = &$this->getNode($index);

			//new node at head
			if($index == 0){
				$newNode->setNext(&$nodeAtIndex);
				$nodeAtIndex->setPrevious(&$newNode);
				$this->head->setNext(&$newNode);
			}
			else //somewhere else in the list
			$this->insertNodeInList(&$newNode, &$nodeAtIndex);

			$this->size++;

			// add a shortcut
			$this->addShortcut($object);
			// ~add a shortcut

			return TRUE;
		}else if($index === $this->size()){
			// the element will be added after the last element
			return $this->add($object);
		}

		return FALSE;
	}

	/**
	 * Inserts a new node into a the list on a position
	 * where it has nodes bothe before an after.
	 * NOT at index 0 or $this->size()-1
	 * @param &$newNode A reference to the node that is inserted in the list.
	 * @param &$nodeAtIndex The node it is pushing away.
	 */
	public function insertNodeInList(&$newNode, &$nodeAtIndex){
		$previousNode = &$nodeAtIndex->getPrevious();

		$newNode->setPrevious(&$previousNode);
		$newNode->setNext(&$nodeAtIndex);
		$nodeAtIndex->setPrevious(&$newNode);
		$previousNode->setNext(&$newNode);
	}

	/**
	 * Create a new node,
	 * Appends a new node to the tail
	 * @param &$object The element you want to append to the list.
	 *
	 * @return boolean True if successful and false if not.
	 */
	public function add(&$object){
		$newNode = $this->getNewNode(&$object);

		//A non empty list, mostly the case.
		if(!$this->isEmpty()){
			$previousNode = &$this->tail->getPrevious();
			$previousNode->setNext(&$newNode);
			$newNode->setPrevious(&$previousNode);
		}
		else //empty
		$this->head->setNext(&$newNode);

		$this->tail->setPrevious(&$newNode);

		$this->size++;

		// add a shortcut
		$this->addShortcut($object);
		// ~add a shortcut
		return true;
	}

	/**
	 * Generates a new ListNode object.
	 * @param &$object The value of the ListNode
	 * @return A ListNode object
	 */
	public function getNewNode(&$object){
		return new ListNode(&$object);
	}

	/**
	 * Empties the list.
	 */
	public function clear(){
		$this->head = NULL;
		$this->tail = NULL;
		$this->size = 0;
	}

	/**
	 * Retrevies the object at the specified index.
	 * @param $index A value between 0 and $this->size()
	 * @return The object || FALSE == index out of bounds
	 */
	public function get($index){
		//if the list is noe empty, and the index is a legal number
		if(!$this->isEmpty() && (($index >= 0) && ($index < $this->size()))){
			if($index < ($this->size()/2)) //searches from head
			$tmpNode = &$this->getNode($index);
			else  											//searches from tail
			$tmpNode = &$this->getNodeReversed($index);

			return $tmpNode->getNodeValueByReference();
		}

		//index out of bounds.
		return FALSE;
	}

	/**
	 * Checks if the list is empty
	 * @return TRUE == empty || FALSE == not empty
	 */
	public function isEmpty(){
		return ( $this->size == 0 ? TRUE : FALSE );
	}

	/**
	 * Removes a node from the liste at the given index
	 *
	 * @param int $index The position of the node in the list.
	 * @return unknown
	 */
	public function removeAtIndex($index){
		print ("Remove requested at index:".$index);
		if(!$this->isEmpty() && ($index>=0 && $index<=($this->size()-1))){
			$nodeToRemove = &$this->getNode($index);
			switch($index){
				case 0:	//removing head
					$nextNode = &$nodeToRemove->getNext();
					$nextNode->setPrevious(NULL);
					$this->head->setNext(&$nextNode);
					break;
				case ($this->size()-1):
					//removing tail
					$previousNode = &$nodeToRemove->getPrevious();
					$previousNode->setNext(NULL);
					$this->tail->setPrevious(&$previousNode);
					break;
				default:
					//gets the node before and after the deleted node
					$previousNode = &$nodeToRemove->getPrevious();
					$nextNode = &$nodeToRemove->getNext();
					//updates the references for the before and after node.
					$previousNode->setNext(&$nextNode);
					$nextNode->setPrevious(&$previousNode);
					break;
			}
			//compleatly removes the node
			$nodeToRemove->setPrevious(NULL);
			$nodeToRemove->setNext(NULL);

			//decreases the size of the list.
			$this->size--;

			// remove the shortcut
			$this->removeShortcut($nodeToRemove->element);
			// ~remove the shortcut
			return TRUE;
		}
		return FALSE;
	}

	/**
	 * Removes the given element from the elements in the list.
	 *
	 * @param POMElement $element
	 *
	 * @return boolean True if successful and false else.
	 */
	public function remove($element){
		if(!$this->isEmpty()){
			$__node = &$this->head;

			for($__i=0; $__i<$this->size(); $__i++){
				$__node = &$__node->getNext();

				if ( strcmp($__node->element->id, $element->id) === 0 ){
					$__previousNode = &$__node->getPrevious();
					$__nextNode = &$__node->getNext();
					//updates the references for the before and after node.
					$__previousNode->setNext(&$__nextNode);
					$__nextNode->setPrevious(&$__previousNode);

					if($__i === 0){
						// the head should be set correctly
						$this->head->setNext(&$__nextNode);

					}

					if($__i === $this->size()-1){
						// the tail should be set correctly
						$this->tail->setPrevious(&$__previousNode);
					}
					// remove the shortcut
					$this->removeShortcut($element);
					// ~remove the shortcut

					return true;
				}
			}
			return false;
		}
	}

	/**
	 * Retrevies a node at a spesific index
	 * @param $index A value between 0 and $this->size()
	 * @return The node || FALSE == index out of bounds
	 */
	public function &getNode($index){
		//the list is not empty, and the index is not out of bounds
		if(!$this->isEmpty() && (($index >= 0) && ($index < $this->size()))){
			$tmpNode = &$this->head;
			for($i=0; $i<=$index; $i++)
			$tmpNode = &$tmpNode->getNext();

			return $tmpNode;
		}
		return FALSE;
	}

	/**
	 * Retrevies the object from the tail of the list
	 * @param $index A value between 0 and $this->size()
	 * @return The node || FALSE == index out of bounds
	 */
	public function &getNodeReversed($index){
		//the list is not empty, and the index is not out of bounds
		if(!$this->isEmpty() && (($index >= 0) && ($index < $this->size()))){
			$tmpNode = &$this->tail;
			for($i=($this->size()-1); $i>=$index; $i--)
			$tmpNode = &$tmpNode->getPrevious();

			return $tmpNode;
		}
		return FALSE;

	}

	/**
	 * Returns the size of the list
	 * @return The number of elements in the list
	 */

	public function size(){
		return $this->size;
	}

	/**
	 //	 * Returns an iterator to use on the list
	 //	 * @return An Iterator object, tha iterator starts on the list head.
	 //	 */
	//	public function iterator(){
	//		#include_once("Iterator.php");
	//		return new Iterator(&$this->head, &$this);
	//	}

	/**
	 * Returns an ListIterator to use on the list
	 * @return An ListIterator object, tha iterator starts on the list head.
	 */
	public function listIterator(){
		#include_once("ListIterator.php");
		return new DLListIterator($this->head, $this);
	}

	private function incSize(){
		$this->size++;
	}

	private function decSize(){
		$this->size--;
	}

	// new func

	/**
	 * Sets what types of elements will be added to the shortcut list.
	 *
	 * @param unknown_type $types
	 */
	public function setShortcutTypes($types){
		$this->shortcuts = $types;
		foreach ($this->shortcuts as $__type){
			$this->shortcuts[$__type] = new DLList();
		}
	}

	/**
	 * Gets specific elements of the page.
	 *
	 * @param string  $type The requested element type. If set, return this specific type of elements. If not, return all elements.
	 * @return DLList A list of elements.
	 */
	public function &getShortcuts($type = NULL){
		if ($type === NULL){
			return $this->shortcuts;
		}else{
			return $this->shortcuts[$type];
		}
	}

	private function addShortcut(&$object){
		foreach ($this->shortcuts as $__type){
			if ( strcmp(get_class($object), $__type) === 0 ){
				$this->shortcuts[get_class($object)]->add($object);
				return true;
			}
		}
		return false;
	}

	private function removeShortcut(&$object){
		foreach ($this->shortcuts as $__type){
			if ( strcmp(get_class($object), $__type) === 0 ){
				$this->shortcuts[get_class($object)]->remove($object);
				return true;
			}
		}
		return false;
	}
	/**
	 *  Returns an DLListIterator to use on the list
	 * @param  string $type The type of the objects for which the shortcuts are iterated.
	 * @return An DLListIterator object, tha iterator starts on the list head.
	 */

	/**
	 * Iterates through the specific types of elements on a page.
	 *
	 * @param string $type The requested type of elements.
	 * @return DLListIterator The iterator over the element nodes.
	 */
	public function shortcutsListIterator($type){
		#include_once("ListIterator.php");
		return new DLListIterator($this->shortcuts[$type]->head, $this->shortcuts[$type]);
	}

	/**
	 * Get the position in the list of a specific element.
	 *
	 * @param POMElement $element The elemnt for which the position will be determined.
	 * @return int The position in the list.
	 */
	public function getPosition(POMElement $element){
		$__currentPos = 0;

		if(!$this->isEmpty()){
			$__node = $this->head;

			for($__i=0; $__i<$this->size(); $__i++){
				$__node = $__node->getNext();

				if ( strcmp($__node->element->id, $element->id) === 0 ){
					return $__currentPos;
				}
				$__currentPos++;
			}
		}
		return -1;
	}
}


/**
 * This class represents an iterator for double linked lists.
 *
 */
class DLListIterator{

	var $currentNode;
	var $previousNode;
	var $nextNode;
	var $list; //should not be nessesary to use. Ineffecive on large lists.


	/**
	 * Constructs a ListNode.
	 * @param &$head The start of the list
	 * @param &$list The LinkedList, nessesary for updating the size of the list on removal of nodes.
	 */
	public function DLListIterator(&$head, &$list){
		// parent::Iterator(&$head, &$list);
		$this->currentNode = $head;
		$this->previousNode = $this->currentNode->getPrevious();
		$this->nextNode = $this->currentNode->getNext();

		$this->list = &$list;
	}

	/**
	 * Checks if more nodes exist
	 * @return TRUE == hasNext || FALSE == no next node.
	 */
	public function hasNext(){
		//current node must exist and be different from NULL OR next node exists.
		return (($this->currentNode != NULL && $this->currentNode->getNext() != NULL) || $this->nextNode != NULL ? TRUE : FALSE);
	}

	/**
	 * Get the the next node.
	 * Checks if there is a next node, if no next node it
	 * returns NULL.
	 * @return The object at current node
	 */
	public function &getNext(){
		if($this->hasNext()){
			//checks if current node is deleted.
			if($this->currentNode != NULL)
			$this->currentNode = $this->currentNode->getNext();
			else
			$this->currentNode = &$this->nextNode;

			$this->previousNode = $this->currentNode->getPrevious();
			$this->nextNode = $this->currentNode->getNext();

			return $this->currentNode;
		}

		return NULL;
	}

	/**
	 * Get the value of the next node.
	 * Checks if there is a next node, if no next node it
	 * returns NULL.
	 * @return The object at the current node
	 */
	public function getNextNodeValue(){
		if($this->hasNext()){
			//checks if current node is deleted.
			if($this->currentNode != NULL)
			$this->currentNode = $this->currentNode->getNext();
			else
			$this->currentNode = &$this->nextNode;

			$this->previousNode = $this->currentNode->getPrevious();
			$this->nextNode = $this->currentNode->getNext();

			return $this->currentNode->getNodeValue();
		}

		return NULL;
	}

	/**
	 * Get the reference to the value of the next node.
	 * Checks if there is a next node, if no next node it
	 * returns NULL.
	 * @return The object at the current node
	 */
	public function &getNextNodeValueByReference(){
		if($this->hasNext()){
			//checks if current node is deleted.
			if($this->currentNode != NULL)
			$this->currentNode = $this->currentNode->getNext();
			else
			$this->currentNode = &$this->nextNode;

			$this->previousNode = $this->currentNode->getPrevious();
			$this->nextNode = $this->currentNode->getNext();

			return $this->currentNode->getNodeValueByReference();
		}

		return NULL;
	}

	/**
	 * Removes the current node
	 * Uses the list removeObjectAtIndex it it is the head
	 * or tail that is to be removed.
	 */
	public function remove(){
		if($this->currentNode->getPrevious() == NULL)
		$this->list->removeObjectAtIndex(0);
		else
		if($this->currentNode->getNext() == NULL)
		$this->list->removeObjectAtIndex(($this->list->size()-1));
		else{
			//updates the references for the before and after the current node.
			$this->previousNode->setNext(&$this->nextNode);
			$this->nextNode->setPrevious(&$this->previousNode);
			$this->list->decSize();
		}
		$this->currentNode->setNext(NULL);
		$this->currentNode->setPrevious(NULL);
		$this->currentNode = NULL;
	}

	/**
	 * Checks if more nodes exist
	 * @return TRUE == hasNext || FALSE == no next node.
	 */
	public function hasPrevious(){
		//it has a previous value if $this->previous != NULL or current node is set an has a previous value set.
		return (($this->currentNode != NULL && $this->currentNode->getPrevious() != NULL) || $this->previousNode != NULL ? TRUE : FALSE);
	}

	/**
	 * Get the reference to the previous node.
	 * @param &$node The next node. || FALSE if no next node exists.
	 */
	public function &getPrevious(){
		if($this->hasPrevious()){
			$this->currentNode = &$this->currentNode->getPrevious(); //current is the node between previous and next
			$this->previousNode = &$this->currentNode->getPrevious(); //previous is the node closer to head
			$this->nextNode = &$this->currentNode->getNext(); //next is the node closer to tail

			return $this->currentNode->getNodeValueByReference();
		}

		return FALSE;
	}
}

/**
 * This class represens a node which can be adde to a (double linked) list.
 * The list is designed for POM elements.
 *
 */
class ListNode{
	/**
	 * The previous node.
	 *
	 * @var ListNode
	 */
	private $previousNode;
	/**
	 * The next node.
	 *
	 * @var ListNode
	 */
	private $nextNode; //a reference to the next node

	/**
	 * The element of the node. Expected in this case is a <POMElement>.
	 *
	 * @var POMElement
	 */
	public $element;

	/**
	 * Constructs a node.
	 * @param $element The object/value of the node
	 */
	public function ListNode($element = NULL){
		$this->element = &$element;
	}

	/**
	 * Sets the reference to the previous node.
	 * @param &$node The previous node.
	 */
	public function setPrevious($node){
		$this->previousNode = &$node;
	}

	/**
	 * Sets the reference to the next node.
	 * @param &$node The next node.
	 */
	public function setNext($node){
		$this->nextNode = &$node;
	}

	/**
	 * Returns the reference to the previous node.
	 * @return The reference to the previous node.
	 */
	public function &getPrevious(){
		return $this->previousNode;
	}

	/**
	 * Returns the reference to the next node.
	 * @return The reference to the next node.
	 */
	public function &getNext(){
		return $this->nextNode;
	}

	/**
	 * Returns the object/value of the node
	 * @return POMElement The object/value of the node
	 */
	public function getNodeValue(){
		return $this->element;
	}

	/**
	 * Returns the reference to the object/value of the node
	 * @return POMElement The object/value of the node
	 */
	public function &getNodeValueByReference(){
		return $this->element;
	}
}
/**
 * General util on data.
 *
 */
class POMDataUtil{
	public static function toArray($data){
		if(!is_object($data) && !is_array($data))
		{
			return $data;
		}
		if(is_object($data)){
			$data = get_object_vars($data);
		}

		return array_map('POMDataUtil::toArray', $data);
	}
}
