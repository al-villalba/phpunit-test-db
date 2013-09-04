<?php
/**
 * Test_Db
 * This class clones a database for testing purposes.
 * 
 * Copyright (c) 2013 Alvaro Villalba <villalba.it@gmail.com>
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy of
 * this software and associated documentation files (the "Software"), to deal in
 * the Software without restriction, including without limitation the rights to
 * use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of
 * the Software, and to permit persons to whom the Software is furnished to do so,
 * subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS
 * FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR
 * COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER
 * IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN
 * CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 * 
 * @package library
 * @subpackage test
 * @author Alvaro Villalba <villalba.it@gmail.com>
 * @copyright 2013 Alvaro Villalba <villalba.it@gmail.com>
 * @license http://opensource.org/licenses/MIT
 */
class Test_Db
{
	/**
	 * Prefix of database name
	 */
	const PREFIX_TEST = 'test_';
	
	/**
	 * Db under test
	 * 
	 * @var Zend_Db_Adapter_Abstract
	 */
	protected $_db;
	
	/**
	 * Clone of _db, where tests take place
	 * 
	 * @var Zend_Db_Adapter_Abstract
	 */
	protected $_testDb;
	
	/**
	 * Array of Zend_Db_Table, tables under test.
	 * 
	 * @var array
	 */
	protected $_tables;
	
	/**
	 * Constructor
	 * 
	 * Change to test db and create tables sibling of tables under test
	 * 
	 * @param Zend_Db_Adapter_Abstract $db
	 * @param array $tableNames
	 * @param bool $copyContent
	 * @throws Zend_Exception
	 */
	public function __construct(
		Zend_Db_Adapter_Abstract $db,
		array $tableNames,
		$copyContent = false)
	{
		// set up db
		$this->_db = $db;
		$config = $this->_db->getConfig();
		$testDbName = self::PREFIX_TEST . $config['dbname'];
		$queries = array(
//			"DROP DATABASE IF EXISTS $testDbName",
//			"CREATE DATABASE $testDbName",
//			"GRANT ALL PRIVILEGES ON `$testDbName`.* TO '" . $config['username'] . "'"
			"USE $testDbName"
		);
		$this->query($queries, array(), $this->_db);
		$testClass = get_class($this->_db);
		$this->_testDb = new $testClass(array_merge($config,
			array('dbname' => $testDbName)));
		
		// set up tables
		$this->_tables = array();
		foreach ($tableNames as $name) {
			
			// create table
			$queries = array(
				"DROP TABLE IF EXISTS `$name`",
				"CREATE TABLE `$name` LIKE `" . $config['dbname'] . "`.`$name`"
			);
			$this->query($queries);
			
			// set engine=mem (modify text to varchar and blob to varbinary)
//			$meta = $this->_testDb->describeTable($name);
//			$alterations = array();
//			foreach ($meta as $col => $attribs) {
//				// 8192 is the maximum length satifying all char sets
//				if (in_array(strtolower($attribs['DATA_TYPE']),
//					array('tinytext', 'text', 'mediumtext', 'longtext')))
//				{
//					$alterations[] = "MODIFY `$col` VARCHAR(8192) " .
//						($attribs['NULLABLE'] ? 'NULL' : 'NOT NULL') .
//						($attribs['DEFAULT'] ? : '');
//				}
//				if (in_array(strtolower($attribs['DATA_TYPE']),
//					array('tinyblob', 'blob', 'mediumblob', 'longblob')))
//				{
//					$alterations[] = "MODIFY `$col` VARBINARY(8192) " .
//						($attribs['NULLABLE'] ? 'NULL' : 'NOT NULL') .
//						($attribs['DEFAULT'] ? : '');
//				}
//			}
//			$alterations[] = "ENGINE=MEMORY";
//			$this->query("ALTER TABLE `$name` " . implode(', ', $alterations));
			
			// create table object
			$this->_tables[$name] = new Zend_Db_Table(array(
				'db' => $this->_testDb,
				'name' => $name
			));
			
			// populate table
			if ($copyContent) {
				$this->query("INSERT INTO `$name` SELECT * FROM `" .
					$config['dbname'] . "`.`$name`");
			}
		}
	}
	
	/**
	 * Destructor.
	 * 
	 * It's safe to call cleanUp() and unset the object.
	 */
	public function __destruct()
	{
		return $this->cleanUp();
	}
	
	/**
	 * Clean up test tables and restore db use.
	 * 
	 * Ideally this would be the destructor but is not guaranteed the 
	 * destructor is called straight after unsetting the variable.
	 */
	public function cleanUp()
	{
		// clean up tables
		foreach ($this->_tables as $name => $dbTable) {
			$this->query("DROP TABLE IF EXISTS `".$dbTable->info('name')."`");
			unset($this->_tables[$name]);
		}
		$config = $this->getOriginalDb()->getConfig();
		$this->query("USE " . $config['dbname'], array(), $this->getOriginalDb());
	}
	
	/**
	 * Try to call the corresponding method in $this->_testDb
	 * 
	 * @param string $name
	 * @param array $args
	 * @return mixed 
	 */
	public function __call($name, $args)
	{
		if( method_exists($this->_testDb, $name) ) {
			return call_user_func_array( array($this->_testDb, $name), $args );
		} else {
			throw new Zend_Exception(
				'Call to undefined method ' . __CLASS__ . "::$name");
		}
	}
	
	/**
	 * Get test db adapter
	 * 
	 * @return Zend_Db_Adapter_Abstract
	 */
	public function getTestDb()
	{
		return $this->_testDb;
	}
	
	/**
	 * Get original db adapter
	 * 
	 * @return Zend_Db_Adapter_Abstract
	 */
	public function getOriginalDb()
	{
		return $this->_db;
	}
	
	/**
	 * Table getter
	 * 
	 * @param string $name
	 * @return Zend_Db_Table
	 */
	public function getTable($name)
	{
		if (isset($this->_tables[$name])) {
			return $this->_tables[$name];
		}
		
		return null;
	}
	
	/**
	 * Executes sql queries on test db if other is not specified.
	 * 
	 * @param string|string[] $query
	 * @param array $bind
	 * @param Zend_Db_Adapter_Abstract $db
	 * @return type
	 */
	public function query($query, $bind = array(), $db = null)
	{
		if( $db === null ) {
			$db = $this->_testDb;
		}
		
		if( is_array($query) ) {
			foreach( $query as $q ) {
				$this->query($q, $bind, $db);
			}
			return;
		}
		
		$db->query($query, $bind);
		
		return;
	}
	
}
