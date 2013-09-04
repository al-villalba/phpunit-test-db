<?php

class GuestbookControllerTest extends Zend_Test_PHPUnit_ControllerTestCase
{
    /**
     * Test db
     * @var Test_Db
     */
    public static $testDb;
    
    /**
     * Set test db adapter
     */
    public static function setUpBeforeClass()
    {
		// init testDb
        $db = Zend_Db_Table::getDefaultAdapter();
        self::$testDb = new Test_Db($db, array('guestbook'));

        parent::setUpBeforeClass();
    }

    /**
     * Restore db adapter
     */
    public static function tearDownAfterClass()
    {
        // clean up testDb
        self::$testDb->cleanUp();

        parent::tearDownAfterClass();
    }
    
    /**
     * Set up
     */
    protected function setUp()
    {
        $this->bootstrap = new Zend_Application(APPLICATION_ENV, APPLICATION_PATH . '/configs/application.ini');
        parent::setUp();
        // set testDbb as default db throughout the application
        Zend_Db_Table::setDefaultAdapter(self::$testDb->getTestDb());
    }
	
	/**
	 * Tear down
	 */
	protected function tearDown()
	{
		parent::tearDown();
		// restore original db
        Zend_Db_Table::setDefaultAdapter(self::$testDb->getOriginalDb());
	}
    
    /* Test Cases */
    
    /**
     * test /guestbook
     */
    public function testIndexAction()
    {
        $params = array('action' => 'index', 'controller' => 'guestbook');
        $urlParams = $this->urlizeOptions($params);
        $url = $this->url($urlParams);
        
        $this->dispatch($url);
        
        // assertions
        $this->assertController($urlParams['controller']);
        $this->assertAction($urlParams['action']);
        
        $this->assertEquals(200, $this->getResponse()->getHttpResponseCode());
        $this->assertNotEmpty($this->getResponse()->getBody());
    }

    /**
     * test /guestbook/sign
     */
    public function testSignAction()
    {
        $params = array('action' => 'sign', 'controller' => 'guestbook');
        $postParams = array(
            'email'   => uniqid() . "@example.com",
            'comment' => 'PHPUnit'
        );
        $urlParams = $this->urlizeOptions($params);
        $url = $this->url($urlParams);
        $this->getRequest()->setMethod('POST')->setPost($postParams);
        
        $this->dispatch($url);
        
        // assertions
        $this->assertController($urlParams['controller']);
        $this->assertAction($urlParams['action']);
        
        $this->assertEquals(302, $this->getResponse()->getHttpResponseCode());
        $this->assertEmpty($this->getResponse()->getBody());
        
		// asserts against testDb
		$testDb = self::$testDb->getTestDb();
        $query = "SELECT * FROM `guestbook` WHERE email = ?";
        $rows = $testDb->query($query, array($postParams['email']))->fetchAll(Zend_Db::FETCH_ASSOC);
        $this->assertEquals(1, count($rows));
        $this->assertEquals($postParams['comment'], $rows[0]['comment']);
    }

}
