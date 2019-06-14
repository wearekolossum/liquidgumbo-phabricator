<?php

final class LiquidgumboApplication extends PhabricatorApplication
{
    public function getName()
    {
        return pht('Liquidgumbo');
    }

    public function getShortDescription()
    {
        return pht('Add various project and task functionalities');
    }

    public function getRoutes()
    {
        return array(
          '/project/' => array(
              'gantt/(?P<id>[1-9]\d*)/'
              => 'LiquidgumboProjectGanttChartController'
          )
        );
    }

    public function getEventListeners()
    {
        return array(
            new LiquidgumboActionMenuEventListener()
        );
    }
}
