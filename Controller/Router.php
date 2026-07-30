<?php
/**
 * @author Ict Team
 * @copyright Copyright (c) 2017 Ict (http://icreativetechnologies.com/)
 * @package Ict_Shopbybrand
 */

namespace Ict\Shopbybrand\Controller;

use Magento\Framework\App\RouterInterface;
use Magento\Framework\App\ActionFactory;
use Magento\Framework\DataObject;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\App\State;
use Ict\Shopbybrand\Model\MakerFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Url;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Router implements RouterInterface
{
    /**
     * @var ActionFactory
     */
    private $actionFactory;

    /**
     * @var ManagerInterface
     */
    private $eventManager;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var MakerFactory
     */
    private $makerFactory;

    /**
     * @var State
     */
    private $appState;

    /**
     * @var UrlInterface
     */
    private $url;

    /**
     * @var ResponseInterface
     */
    private $response;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var bool
     */
    private $dispatched;

    /**
     * Constructor
     *
     * @param ActionFactory $actionFactory
     * @param ManagerInterface $eventManager
     * @param UrlInterface $url
     * @param State $appState
     * @param MakerFactory $makerFactory
     * @param StoreManagerInterface $storeManager
     * @param ResponseInterface $response
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ActionFactory $actionFactory,
        ManagerInterface $eventManager,
        UrlInterface $url,
        State $appState,
        MakerFactory $makerFactory,
        StoreManagerInterface $storeManager,
        ResponseInterface $response,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->actionFactory = $actionFactory;
        $this->eventManager = $eventManager;
        $this->url = $url;
        $this->appState = $appState;
        $this->makerFactory = $makerFactory;
        $this->storeManager = $storeManager;
        $this->response = $response;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Validate and Match Shopbybrand Maker and modify request
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @return bool
     */
    public function match(RequestInterface $request)
    {
        if (!$this->dispatched) {
            $urlKey = trim($request->getPathInfo(), '/');
            $origUrlKey = $urlKey;
            /** @var Object $condition */
            $condition = new DataObject(['url_key' => $urlKey, 'continue' => true]);
            $this->eventManager->dispatch(
                'ict_shopbybrand_controller_router_match_before',
                ['router' => $this, 'condition' => $condition]
            );
            $urlKey = $condition->getUrlKey();
            if ($condition->getRedirectUrl()) {
                $this->response->setRedirect($condition->getRedirectUrl());
                $request->setDispatched(true);
                return $this->actionFactory->create(
                    \Magento\Framework\App\Action\Redirect::class,
                    ['request' => $request]
                );
            }
            if (!$condition->getContinue()) {
                return null;
            }
            $entities = [
                'maker' => [
                    'prefix'        => $this->scopeConfig->getValue(
                        'ict_shopbybrand/maker/url_prefix',
                        ScopeInterface::SCOPE_STORES
                    ),
                    'suffix'        => $this->scopeConfig->getValue(
                        'ict_shopbybrand/maker/url_suffix',
                        ScopeInterface::SCOPE_STORES
                    ),
                    'list_key'      => $this->scopeConfig->getValue(
                        'ict_shopbybrand/maker/list_url',
                        ScopeInterface::SCOPE_STORES
                    ),
                    'list_action'   => 'index',
                    'factory'       => $this->makerFactory,
                    'controller'    => 'maker',
                    'action'        => 'view',
                    'param'         => 'id',
                ]
            ];

            foreach ($entities as $entity => $settings) {
                if ($settings['list_key']) {
                    if ($urlKey == $settings['list_key']) {
                        $request->setModuleName('ict_shopbybrand')
                            ->setControllerName($settings['controller'])
                            ->setActionName($settings['list_action']);
                        $request->setAlias(Url::REWRITE_REQUEST_PATH_ALIAS, $urlKey);
                        $this->dispatched = true;
                        return $this->actionFactory->create(
                            \Magento\Framework\App\Action\Forward::class,
                            ['request' => $request]
                        );
                    }
                }
                if ($settings['prefix']) {
                    $parts = explode('/', $urlKey);
                    if ($parts[0] != $settings['prefix'] || count($parts) != 2) {
                        continue;
                    }
                    $urlKey = $parts[1];
                }
                if ($settings['suffix']) {
                    $suffix = substr($urlKey, -strlen($settings['suffix']) - 1);
                    if ($suffix != '.'.$settings['suffix']) {
                        continue;
                    }
                    $urlKey = substr($urlKey, 0, -strlen($settings['suffix']) - 1);
                }
                /** @var \Ict\Shopbybrand\Model\Maker $instance */
                $instance = $settings['factory']->create();
                $id = $instance->checkUrlKey($urlKey, $this->storeManager->getStore()->getId());
                if (!$id) {
                    return null;
                }
                $request->setModuleName('ict_shopbybrand')
                    ->setControllerName('maker')
                    ->setActionName('view')
                    ->setParam('id', $id);
                $request->setAlias(Url::REWRITE_REQUEST_PATH_ALIAS, $origUrlKey);
                $request->setDispatched(true);
                $this->dispatched = true;
                return $this->actionFactory->create(
                    \Magento\Framework\App\Action\Forward::class,
                    ['request' => $request]
                );
            }
        }
        return null;
    }
}
