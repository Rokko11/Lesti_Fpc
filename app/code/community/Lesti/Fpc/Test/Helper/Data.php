<?php
/**
 * Lesti_Fpc (http:gordonlesti.com/lestifpc)
 *
 * PHP version 5
 *
 * @link      https://github.com/GordonLesti/Lesti_Fpc
 * @package   Lesti_Fpc
 * @author    Gordon Lesti <info@gordonlesti.com>
 * @copyright Copyright (c) 2013-2014 Gordon Lesti (http://gordonlesti.com)
 * @license   http://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

/**
 * Test case for Lesti_Fpc_Helper_Data
 */
class Lesti_Fpc_Test_Helper_Data extends Lesti_Fpc_Test_TestCase
{
    /**
     * @var Lesti_Fpc_Helper_Data
     */
    protected $_helper;

    protected function setUp()
    {
        parent::setUp();
        $this->_helper = Mage::helper('fpc');
    }

    /**
     * @test
     * @loadFixture get_cacheable_actions.yaml
     */
    public function testGetCacheableActions()
    {
        $this->assertEquals(
            array('cms_index_index', 'cms_page_view', 'catalog_product_view'),
            $this->_helper->getCacheableActions()
        );
    }

    /**
     * @test
     * @loadFixture get_refresh_actions.yaml
     */
    public function testGetRefreshActions()
    {
        $this->assertEquals(
            array(
                'checkout_cart_add',
                'checkout_cart_delete',
                'checkout_cart_updatePost'
            ),
            $this->_helper->getRefreshActions()
        );
    }

    /**
     * Test that URI params can be matched by RegEx
     * 
     * @test
     * @loadFixture regex_uri_params.yaml
     * @dataProvider dataProvider
     */
    public function testRegexUriParams(
        $uriParamSets = array(),
        $expectedCount = 0
    )
    {
        $actualKeys = array();
        foreach ($uriParamSets as $uriParams) {
            $this->_resetParams();
            Mage::app()->getRequest()->setParams($uriParams);
            $actualKeys[] = $this->_helper->getKey();
        }
        $this->assertCount(
            $expectedCount,
            array_unique($actualKeys),
            sprintf('%d different keys expected', $expectedCount)
        );
    }

    /**
     * @test
     * @loadFixture can_cache_request.yaml
     * @dataProvider dataProvider
     */
    public function testCanCacheRequest($method, $expected, $params = array())
    {
        Mage::app()->getRequest()->clearParams();
        Mage::app()->getRequest()->setMethod($method);
        Mage::app()->getRequest()->setParams($params);
        $this->assertEquals(
            (bool) $expected,
            $this->_helper->canCacheRequest()
        );
    }

    /**
     * @test
     * @loadFixture get_cache_tags.yaml
     * @dataProvider dataProvider
     */
    public function testGetCacheTags(
        $routeName,
        $controllerName,
        $actionName,
        $expectedCacheTags,
        $params = array()
    )
    {
        Mage::app()->getRequest()->setRouteName($routeName);
        Mage::app()->getRequest()->setControllerName($controllerName);
        Mage::app()->getRequest()->setActionName($actionName);
        Mage::app()->getRequest()->setParams($params);
        $this->assertEquals($expectedCacheTags, $this->_helper->getCacheTags());
        $this->assertEventDispatched('fpc_helper_collect_cache_tags');
    }

    /**
     * @test
     */
    public function getFullActionName()
    {
        Mage::app()->getRequest()->setRouteName('f');
        Mage::app()->getRequest()->setControllerName('p');
        Mage::app()->getRequest()->setActionName('c');
        $this->assertEquals('f_p_c', $this->_helper->getFullActionName());
    }

    /**
     * Reset parameters in Magento request and FPC
     */
    protected function _resetParams()
    {
        if (Mage::registry(Lesti_Fpc_Helper_Data::REGISTRY_KEY_PARAMS)) {
            Mage::unregister(Lesti_Fpc_Helper_Data::REGISTRY_KEY_PARAMS);
        }
        Mage::app()->getRequest()->clearParams();
    }
}
