<?php

final class LiquidgumboProjectGanttChartProfileMenuItem extends PhabricatorProfileMenuItem
{
    const MENUITEMKEY = 'liquidgumbo.ganttchart';
    const ITEM_GANTT = 'item_gantt';

    public function getMenuItemTypeName()
    {
        return pht('Gantt Chart');
    }

    private function getDefaultName()
    {
        return pht('Gantt Chart');
    }

    public function getMenuItemTypeIcon()
    {
        return 'fa-file-text-o';
    }

    public function canHideMenuItem(
    PhabricatorProfileMenuItemConfiguration $config)
    {
        return false;
    }

    public function canMakeDefault(
    PhabricatorProfileMenuItemConfiguration $config)
    {
        return true;
    }

    public function getDisplayName(
    PhabricatorProfileMenuItemConfiguration $config)
    {
        $name = $config->getMenuItemProperty('name');

        if (strlen($name)) {
            return $name;
        }

        return $this->getDefaultName();
    }

    public function buildEditEngineFields(
    PhabricatorProfileMenuItemConfiguration $config)
    {
        return array(
      id(new PhabricatorTextEditField())
        ->setKey('name')
        ->setLabel(pht('Name'))
        ->setPlaceholder($this->getDefaultName())
        ->setValue($config->getMenuItemProperty('name')),
    );
    }

    protected function newMenuItemViewList(
    PhabricatorProfileMenuItemConfiguration $config)
    {
        $project = $config->getProfileObject();

        $id = $project->getID();
        $name = $project->getName();
        $icon = $project->getDisplayIconIcon();

        $uri = "/project/gantt/{$id}/";

        $item = $this->newItemView()
      ->setURI($uri)
      ->setName($name.'---')
      ->setIcon($icon);

        return array(
      $item,
    );
    }
}
