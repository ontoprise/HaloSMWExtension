<?php
/**
 * @author ning
 */

/**
 * Base class for all language classes.
 */
abstract class WOMLanguage {

	// the message arrays ...
	protected $wContentMessages;
	protected $wUserMessages;
	protected $wWOMTypeLabels;

	function geWOMTypeLabels() {
		return $this->wWOMTypeLabels;
	}

	/**
	 * Find the internal message id of wome localised message string
	 * for a datatype. If no type of the given name exists (maybe a
	 * custom of compound type) then FALSE is returned.
	 */
	function findWOMTypeMsgID( $label ) {
		return array_search( $label, $this->wWOMTypeLabels );
	}

	/**
	 * Function that returns all content messages (those that are stored
	 * in wome article, and can thus not be translated to individual users).
	 */
	function getContentMsgArray() {
		return $this->wContentMessages;
	}

	/**
	 * Function that returns all user messages (those that are given only to
	 * the current user, and can thus be given in the individual user language).
	 */
	function getUserMsgArray() {
		return $this->wUserMessages;
	}
}