<?php
/**
 * @author Ict Team
 * @copyright Copyright (c) 2017 Ict (http://icreativetechnologies.com/)
 * @package Ict_Shopbybrand
 */

namespace Ict\Shopbybrand\Observer;

use Ict\Shopbybrand\Model\Maker\Url as MakerUrl;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Data\Tree\Node;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Event\ObserverInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

class TopMenu implements ObserverInterface
{
    public const ENABLED_CONFIG_PATH = 'ict_shopbybrand/maker/enabled';

    /**
     * @var Http
     */
    private $request;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var MakerUrl
     */
    private $makerUrl;

    /**
     * Constructor.
     *
     * @param Http $request
     * @param ScopeConfigInterface $scopeConfig
     * @param MakerUrl $makerUrl
     */
    public function __construct(
        Http $request,
        ScopeConfigInterface $scopeConfig,
        MakerUrl $makerUrl
    ) {
        $this->request = $request;
        $this->scopeConfig = $scopeConfig;
        $this->makerUrl = $makerUrl;
    }

    /**
     * Add the Shopbybrand maker menu item to the top navigation menu.
     *
     * @param EventObserver $observer
     * @return $this
     */
    public function execute(EventObserver $observer)
    {
        /** @var \Magento\Framework\Data\Tree\Node $menu */
        if ($this->scopeConfig->isSetFlag(self::ENABLED_CONFIG_PATH, ScopeInterface::SCOPE_STORE)==1) {
            $menu = $observer->getMenu();
            $tree = $menu->getTree();
            $fullAction = $this->request->getFullActionName();
            $selectedActions = ['ict_shopbybrand_maker_index', 'ict_shopbybrand_maker_view'];
            $makerNodeId = 'makers';
            $makerNodeIds = 'maker';
            $data = [
                'name'      => __('Shopbybrand'),
                'id'        => $makerNodeId,
                'url'       => $this->makerUrl->getListUrl(),
                'is_active' => in_array($fullAction, $selectedActions)
            ];
            $makersNode = new Node($data, 'id', $tree, $menu);
            $menu->addChild($makersNode);
            return $this;
        }
    }
}
