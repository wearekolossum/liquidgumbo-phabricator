<?php
/**
* Copyright (C) 2014 Michael Peters
* Licensed under GNU GPL v3. See LICENSE for full details
*/

final class LiquidgumboActionMenuEventListener extends PhabricatorEventListener
{
    public function register()
    {
        $this->listen(PhabricatorEventType::TYPE_UI_WILLRENDERPROPERTIES);
    }

    public function handleEvent(PhutilEvent $event)
    {
        switch ($event->getType()) {
            case PhabricatorEventType::TYPE_UI_WILLRENDERPROPERTIES:
                $this->handleActionsEvent($event);
            break;
        }
    }

    private function handleActionsEvent(PhutilEvent $event)
    {
        $object = $event->getValue('object');

        $actions = null;
        if ($object instanceof PhabricatorProject) {
            $actions = $this->renderUserItems($event);
        }

        //$this->addActionMenuItems($event, $actions);
    }

    private function renderUserItems(PhutilEvent $event)
    {
        return new PhabricatorMilestoneNavProfileMenuItem();
    }
}
