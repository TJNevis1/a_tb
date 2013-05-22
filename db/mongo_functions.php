<?php

    date_default_timezone_set('America/New_York');
	
	//When saving Features, Goals, etc (ADMIN), they will not be able to accept & (and possibly other characters) and the spaces should be underscores
	
	class MongoFunctions extends Mongo{
	    private $mongoConnection;
	    private $mongoDB;
	    
	    
	    
		/**
		 * Create an instance of a Mongo connection and connect to the specified database
		 *
		 * @return void
		 * @param string Database table to be used
		 * @param string URL for Mongo to connect to, ex) 'localhost', 'mongodb://192.168.25.190:50100'
		**/
		public function __construct($database, $URL = 'localhost'){ //Local - 10.178.129.100 (mongo/database/keiths server) - 10.179.2.52 (apps)
		    //Open connection to MongoDB server
		    $this->mongoConnection = new Mongo($URL);
		    
		    //Access database
		    $mongoDB = $this->mongoConnection->$database;
		    
		    $this->mongoDB = $mongoDB;
		    
		    parent::__construct($URL);  //Call the parent constructor (in the Mongo class, 'extends Mongo').  This along with 'extends Mongo' will give us access to Mongo methods in our MongoFunctions class
		}
		
		
		
		
		/**
		 * Insert records if nothing matches the $where statement, otherwise update the record, while keeping the data not being sent in $insert
		 * 
		 * @return Mongo object
		 * @param string, table name
		 * @param array, where statements
		 * @param array, name/value pairs to insert
		 * @param optional, string, $set, $push, etc 
		 */
		public function insert_update($table, $where, $insert, $options = NULL){
			//upsert - If no record like this exists, insert it
			//multiple - To update multiple documents with one query (added 8/6/2012)
			$optionDefaults = array('type' => '$set', 'upsert' => true, 'multiple' => true);
			extract($optionDefaults);		
			if(!empty($options))
				extract($options);  //Overwrite defaults if anything was passed
			
			return $this->mongoDB->$table->update($where,
		        array(
		                $type => $insert //$type = '$set', '$push' - $set keeps the fields in the database that aren't sent in the $insert (without $set, the whole record is deleted and only the values in $insert are saved)
		        ),
		        array(
					'upsert' => $upsert,
					'multiple' => $multiple
				)
		    );
		}
		
		
		
		/**
		 *  Modified Mongo find()
		 * Grab data from Mongo collection
		 *
		 * @return function Mongo query
		 * @param string Table to query
		 * @param array, query or where statements
		 * @param integer Limit for the query return
		 * @param array, fields to limit in the returning object
		 * @param integer, number of results to skip (for paging)
		 * @param array, field to sort by; -1 DESC, 1 ASC
		 * 
		**/
		public function find($table, $where, $options = NULL){
			$optionDefaults =  array(
									'limit' => 25, 
									'limitFields' => array(), 
									'skip' => 0, 
									'sort' => array('date' => -1)
								);
			extract($optionDefaults);
			if(!empty($options))
				extract($options); //Overwrite defaults if anything was passed
	
			return $this->mongoDB->$table->find($where, $limitFields)->skip($skip)->limit($limit)->sort($sort); //Default, Sort date DESC
		}
		
		
		/**
		 * Reformat the findOne method in Mongo to be similar to other methods in the Database Functions class
		 *
		 * @return function Mongo query
		 * @param string Table to query
		 * @param array Query, formatted for Mongo
		 * @param array of fields to limit; Ex) array('id' => 0) = all fields but the ID field or array('id' => 1) = only the ID field
		**/
		public function findOne($table, $where, $options = NULL){
			$optionDefaults = array('limitFields' => array());
			extract($optionDefaults);
			if(!empty($options))
				extract($options);  //Overwrite defaults if anything was passed
			
			return $this->mongoDB->$table->findOne($where, $limitFields);
		}
		
		
		
		/**
		 * 
		 */
		 public function remove($table, $where){
		 	return $this->mongoDB->$table->remove($where);
		 }
		 
		 
		 /*
		  * 
		  */
		  public function count($table, $where){
		  	return $this->mongoDB->$table->count($where);
		  }
		
		
		/**
		 * Close the Mongo server connection
		 *
		 * @return void
		**/
		public function mongo_close(){
		    $this->mongoConnection->close();
		}
	  
	  
	  
	}
  
?>