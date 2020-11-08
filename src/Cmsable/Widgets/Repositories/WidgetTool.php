<?php
/**
 *  * Created by mtils on 08.11.20 at 13:16.
 **/

namespace Cmsable\Widgets\Repositories;

use BeeTree\Helper;
use Cmsable\Model\SiteTreeModelInterface;
use Cmsable\Model\SiteTreeNodeInterface;
use Cmsable\Model\TreeModelManagerInterface;
use Cmsable\Routing\TreeScope\TreeScope;
use Cmsable\Widgets\Contracts\Area;
use Cmsable\Widgets\Contracts\AreaRepository as AreaRepositoryContract;
use Cmsable\Routing\TreeScope\RepositoryInterface as ScopeProvider;
use Cmsable\Widgets\Contracts\WidgetItem;
use Cmsable\Widgets\Contracts\WidgetItemRepository as ItemRepository;

use function array_keys;
use function in_array;
use function is_numeric;
use function starts_with;
use function strlen;
use function substr;

class WidgetTool
{
    const ANCHOR_PREFIX = 'widget-item-';

    /**
     * @var AreaRepositoryContract
     */
    private $areaRepository;

    /**
     * @var ScopeProvider
     */
    private $scopes;

    /**
     * @var TreeModelManagerInterface
     */
    private $treeModels;

    /**
     * @var array
     */
    private $usedAreas = [];

    /**
     * @var array
     */
    private $pagesWithAreas = [];
    /**
     * @var ItemRepository
     */
    private $itemRepository;

    public function __construct(AreaRepositoryContract $areaRepository, TreeModelManagerInterface $treeModels, ScopeProvider $scopes, ItemRepository $itemRepository)
    {
        $this->areaRepository = $areaRepository;
        $this->scopes = $scopes;
        $this->treeModels = $treeModels;
        $this->itemRepository = $itemRepository;
    }

    /**
     * @param int|string|TreeScope $scope
     *
     * @return Area[]
     */
    public function areasInUse($scope)
    {

        $scope = $this->scope($scope);
        $modelRootId = $scope->getModelRootId();

        if (isset($this->usedAreas[$modelRootId])) {
            return $this->usedAreas[$modelRootId];
        }

        if (!$pages = $this->pagesById($scope)) {
            return [];
        }
        $this->usedAreas[$modelRootId] = $this->areaRepository->find(['page_id' => array_keys($pages)]);
        return $this->usedAreas[$modelRootId];
    }

    public function widgetOfAnchor($anchor)
    {
        if (!starts_with($anchor, self::ANCHOR_PREFIX)) {
            return null;
        }
        $widgetId = substr($anchor, strlen(self::ANCHOR_PREFIX));
        if (!is_numeric($widgetId)) {
            return null;
        }

        return $this->itemRepository->find($widgetId);
    }

    public function widgetToAnchor(WidgetItem $item)
    {
        return self::ANCHOR_PREFIX . $item->getId();
    }

    public function areasOfPage(SiteTreeNodeInterface $page)
    {
        $scope = $this->scope($page->{$page->rootIdColumn});


    }

    /**
     * @param int|string|TreeScope $scope
     *
     * @return SiteTreeNodeInterface[]
     */
    public function pagesWithAreas($scope)
    {
        $scope = $this->scope($scope);
        $modelRootId = $scope->getModelRootId();

        if (isset($this->pagesWithAreas[$modelRootId])) {
            return $this->pagesWithAreas[$modelRootId];
        }

        $this->pagesWithAreas[$modelRootId] = [];

        if (!$areas = $this->areasInUse($scope)) {
            return $this->pagesWithAreas[$modelRootId];
        }

        $pageIds = [];
        foreach ($areas as $area) {
            $pageIds[] = $area->getPageId();
        }

        foreach ($this->pagesById($scope) as $page) {
            if (in_array($page->getIdentifier(), $pageIds)) {
                $this->pagesWithAreas[$modelRootId][] = $page;
            }
        }
        return $this->pagesWithAreas[$modelRootId];

    }

    /**
     * @param TreeScope $scope
     *
     * @return SiteTreeNodeInterface[]
     */
    private function pagesById(TreeScope $scope)
    {
        $treeModel = $this->treeModels->get($scope);
        $pages = [];
        /** @var SiteTreeNodeInterface $page */
        foreach (Helper::flatify($treeModel->tree()) as $page) {
            $pages[$page->getIdentifier()] = $page;
        }
        return $pages;
    }

    /**
     * @param string|int|TreeScope $scope
     *
     * @return TreeScope
     */
    private function scope($scope)
    {
        if ($scope instanceof TreeScope) {
            return $scope;
        }
        if (is_numeric($scope)) {
            return $this->scopes->getByModelRootId($scope);
        }
        return $this->scopes->get($scope);
    }
}