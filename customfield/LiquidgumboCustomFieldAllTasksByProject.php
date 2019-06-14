<?php

final class LiquidgumboCustomFieldAllTasksByProject extends PhabricatorStandardCustomFieldTokenizer
{
    public function getFieldType()
    {
        return 'liquidgumbo.task_relation';
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
        return id(new LiquidgumboManiphestAllTasksByProjectDatasource())
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
