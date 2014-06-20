<?php
namespace BatchManagerTest\View\Helper;

use PHPUnit_Framework_TestCase;
use BatchManager\View\Helper\SetHasJsCookie;
use Zend\View\Renderer\PhpRenderer as ViewRenderer;

class SetHasJsCookieTest extends PHPUnit_Framework_TestCase
{
    protected $helper;
    
    public function setUp()
    {
        $this->helper = new SetHasJsCookie();
        $this->helper->setView(new ViewRenderer());
    }
    
    public function testCanGetHeadScriptHelper()
    {
        $this->assertInstanceOf('Zend\View\Helper\HeadScript', $this->helper->getHeadScriptHelper());
    }
    
    public function testHelperRenderJs()
    {
        $expected = "document.cookie = 'has_js=1; path=/';";
        $helper = $this->helper;
        
        // trigger __invoke
        $helper();
        $this->assertContains($expected, $helper->getHeadScriptHelper()->toString());
    }
}