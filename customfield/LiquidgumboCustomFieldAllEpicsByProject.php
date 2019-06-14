<?php

final class LiquidgumboCustomFieldAllEpicsByProject extends PhabricatorStandardCustomFieldTokenizer
{
    public function getFieldType()
    {
        return 'liquidgumbo.epic_relation';
    }

    public function getInverseFieldKey()
    {
        if (method_exists($this->getApplicationField(), 'getInverseFieldKey')) {
            return $this->getApplicationField()->getInverseFieldKey();
        }

        return;
    }

    public function getDatasource()
    {
        return id(new LiquidgumboManiphestAllEpicsByProjectDatasource())
        ->setParameters(
            array(
                'taskID' => $this->getObject()->getID(),
                'taskPHID' => $this->getObject()->getPHID(),
                'inverseFieldKey' => $this->getInverseFieldKey(),
                'fieldKey' => $this->getFieldKey()
            )
        );
    }
}
