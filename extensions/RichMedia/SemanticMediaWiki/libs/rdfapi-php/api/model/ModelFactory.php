<?php 
/**
* ----------------------------------------------------------------------------------
* Class: ModelFactory
* ----------------------------------------------------------------------------------
*
* @package 	model
*/


/**
* ModelFactory is a static class which provides methods for creating different
* types of RAP models. RAP models have to be created trough a ModelFactory
* instead of creating them directly with the 'new' operator because of RAP's
* dynamic code inclusion mechanism.
*
* <BR><BR>History:
* <LI>10-05-2004                : First version of this class.</LI>
*
* @version  V0.9.1
* @author Daniel Westphal <mail at d-westphal dot de>
*
*
* @package 	model
* @access	public
**/
class ModelFactory
{
	/** 
	* Returns a MemModel.
	* You can supply a base URI
	*
	* @param   string  $baseURI
	* @return	object	MemModel
	* @access	public
	*/
	function & getDefaultModel($baseURI = null)
	{
		return ModelFactory::getMemModel($baseURI);
	}
	
	/** 
	* Returns a MemModel.
	* You can supply a base URI
	*
	* @param   string  $baseURI
	* @return	object	MemModel
	* @access	public
	*/
	function & getMemModel($baseURI = null)
	{
		return new MemModel($baseURI);
	}
	
	/**
	* Returns a DbModel with the database connection 
	* defined in constants.php.
	* You can supply a base URI. If a model with the given base 
	* URI exists in the DbStore, it'll be opened. 
	* If not, a new model will be created.
	*
	* @param   string  $baseURI
	* @return	object	DbModel
	* @access	public
	*/
	function & getDefaultDbModel($baseURI = null)
	{
		$dbStore = ModelFactory::getDbStore();
		return ModelFactory::getDbModel($dbStore,$baseURI);
	}
	
	/** 
	* Returns a new DbModel using the database connection 
	* supplied by $dbStore.
	* You can supply a base URI. If a model with the given base 
	* URI exists in the DbStore, it'll be opened. 
	* If not, a new model will be created.
	*
	* @param   object	DbStore  $dbStore
	* @param   string  $baseURI
	* @return	object	DbModel
	* @access	public
	*/
	function & getDbModel($dbStore, $baseURI = null)
	{
		if ($dbStore->modelExists($baseURI))
			return $dbStore->getModel($baseURI);
		
		return $dbStore->getNewModel($baseURI);
	}
	
	/** 
	* Returns a database connection with the given parameters.
	* Paramters, which are not defined are taken from the constants.php
	*
	* @param   string   $dbDriver
	* @param   string   $host
	* @param   string   $dbName
	* @param   string   $user
	* @param   string   $password
	* @return	object	DbStore
	* @access	public
	*/
	function & getDbStore($dbDriver=ADODB_DB_DRIVER, $host=ADODB_DB_HOST, $dbName=ADODB_DB_NAME,
                   		$user=ADODB_DB_USER, $password=ADODB_DB_PASSWORD)
	{
		return new DbStore($dbDriver, $host, $dbName,$user, $password);
	}
	
	/** 
	* Returns a InfModelF.
	* (MemModel with forward chaining inference engine)
	* Configurations can be done in constants.php
	* You can supply a base URI
	*
	* @param   string  $baseURI
	* @return	object	MemModel
	* @access	public
	*/
	function & getInfModelF($baseURI = null)
	{
		require_once( RDFAPI_INCLUDE_DIR . PACKAGE_INFMODEL);
		return new InfModelF($baseURI);
	}
	
	/** 
	* Returns a InfModelB.
	* (MemModel with backward chaining inference engine)
	* Configurations can be done in constants.php
	* You can supply a base URI
	*
	* @param   string  $baseURI
	* @return	object	MemModel
	* @access	public
	*/
	function & getInfModelB($baseURI = null)
	{
		require_once( RDFAPI_INCLUDE_DIR . PACKAGE_INFMODEL);
		return new InfModelB($baseURI);
	}
	
	/** 
	* Returns a ResModel.
	* $modelType has to be one of the following constants:
	* MEMMODEL,DBMODEL,INFMODELF,INFMODELB to create a resmodel with a new
	* model from defined type.
	* You can supply a base URI
	*
	* @param   constant  $modelType
	* @param   string  $baseURI
	* @return	object	MemModel
	* @access	public
	*/
	function & getResModel($modelType, $baseURI = null)
	{
		require_once( RDFAPI_INCLUDE_DIR . PACKAGE_RESMODEL);
		
		switch ($modelType) {
			case DBMODEL:
				$model = ModelFactory::getDbModel($baseURI);
				break;
				
			case INFMODELF:
				$model = ModelFactory::getInfModelF($baseURI);
				break;
				
			case INFMODELB:
				$model = ModelFactory::getInfModelB($baseURI);
				break;				
				
			default:
				$model = ModelFactory::getMemModel($baseURI);
				break;
		}
		
		return new ResModel($model);
	}
	
	/** 
	* Returns an OntModel.
	* $modelType has to be one of the following constants: 
	* MEMMODEL, DBMODEL, INFMODELF, INFMODELB to create a OntModel 
	* with a new model from defined type. 
	* $vocabulary defines the ontology language. Currently only
	* RDFS_VOCABULARY is supported. You can supply a model base URI.
	*
	* @param   	constant  	$modelType
	* @param   	string  	$baseURI
	* @return	object		MemModel
	* @access	public
	*/
	function & getOntModel($modelType,$vocabulary, $baseURI = null)
	{
		require_once( RDFAPI_INCLUDE_DIR . PACKAGE_ONTMODEL);
		
		switch ($modelType) 
		{
			case DBMODEL:
				$model = ModelFactory::getDBModel($baseURI);
				break;
				
			case INFMODELF:
				$model = ModelFactory::getInfModelF($baseURI);
				break;
				
			case INFMODELB:
				$model = ModelFactory::getInfModelB($baseURI);
				break;				
				
			default:
				$model = ModelFactory::getMemModel($baseURI);;
		}
		
		switch ($vocabulary) 
		{
			case RDFS_VOCABULARY:
				require_once(RDFAPI_INCLUDE_DIR.'ontModel/'.RDFS_VOCABULARY);
				$vocab = new RdfsVocabulary();
				break;			
				
			default:
				$vocab = new RdfsVocabulary();
				break;
		}
		
		return new OntModel($model,$vocab);
	}
}  
?>