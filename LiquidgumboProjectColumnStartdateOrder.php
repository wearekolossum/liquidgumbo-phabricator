<?php

final class LiquidgumboProjectColumnStartdateOrder extends PhabricatorProjectColumnOrder
{
    const ORDERKEY = 'custom.ganttchart.startdate';

    public function getDisplayName()
    {
        return pht('Sort by Startdate');
    }

    protected function newMenuIconIcon()
    {
        return 'fa-clock-o';
    }

    public function getHasHeaders()
    {
        return false;
    }

    public function getCanReorder()
    {
        return false;
    }

    public function getMenuOrder()
    {
        return 5000;
    }

    protected function newSortVectorForObject($object)
    {
        return array(
          -1 * (int)$object->getDateCreated(),
          -1 * (int)$object->getID(),
        );
    }
}
