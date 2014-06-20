<?php
namespace BatchManager\View\Helper;

use Zend\View\Helper\AbstractHelper;

class SetHasJsCookie extends AbstractHelper
{
    /**
     * 
     * @var \Zend\View\Helper\HeadScript
     */
    protected $headScriptHelper;
    
    public function __invoke()
    {
        $script = "document.cookie = 'has_js=1; path=/';";
        $this->getHeadScriptHelper()->appendScript($script);
    }
    
    /**
     * 
     * @return \Zend\View\Helper\HeadScript
     */
    public function getHeadScriptHelper()
    {
        if ($this->headScriptHelper) {
            return $this->headScriptHelper;
        }
    
        if (method_exists($this->getView(), 'plugin')) {
            $this->headScriptHelper = $this->view->plugin('headscript');
        }
    
        return $this->headScriptHelper;
    }
}

?>