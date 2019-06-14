<?php

final class LiquidgumboManiphestCustomFieldTasktype extends ManiphestCustomField implements PhabricatorStandardCustomFieldInterface
{
    public function __construct()
    {
        $proxy = id(new PhabricatorStandardCustomFieldSelect())
            ->setFieldKey($this->getFieldKey())
            ->setRawStandardFieldKey($this->getModernFieldKey())
            ->setApplicationField($this)
            ->setFieldConfig(array(
                'name' => $this->getFieldName(),
                'description' => $this->getFieldDescription(),
                'default' => $this->getDefault(),
                'placeholder' => $this->getPlaceholder(),
                'caption' => $this->getFieldDescription(),
                'options' => $this->getConfigValues()
            )
        );

        $this->setProxy($proxy);
    }

    public function getConfigValues()
    {
        $types = array();

        if (!empty(PhabricatorEnv::getEnvConfig('liquidgumbo.maniphest.tasktype'))) {
            $tasktypes = PhabricatorEnv::getEnvConfig('liquidgumbo.maniphest.tasktype');

            foreach ($tasktypes as $tasktype) {
                $types[$tasktype['key']] = $tasktype['name'];
            }
        }

        return $types;
    }

    public function getFieldKey()
    {
        return 'liquidgumbo:maniphest:tasktype';
    }

    public function getModernFieldKey()
    {
        return 'liquidgumbo.maniphest.tasktype';
    }

    public function getFieldName()
    {
        return pht('Type');
    }

    public function getFieldDescription()
    {
        return pht('Task type');
    }

    public function getCaption()
    {
        return pht('Task type');
    }

    public function getDefault()
    {
        return 0;
    }

    public function getPlaceholder()
    {
        return pht('Select task type');
    }

    public function getStandardCustomFieldNamespace()
    {
        return 'maniphest';
    }

    public function shouldAppearInPropertyView()
    {
        return true;
    }

    public function shouldAppearInEditView()
    {
        return true;
    }

    public function shouldAppearInApplicationSearch()
    {
        return true;
    }

    public function shouldAppearInListView()
    {
        return true;
    }

    public function shouldAppearInConduitDictionary()
    {
        return true;
    }

    public function shouldAppearInConduitTransactions()
    {
        return true;
    }

    public function renderPropertyViewLabel()
    {
        if ($this->getProxy()) {
            return $this->getProxy()->renderPropertyViewLabel();
        }

        return $this->getFieldName();
    }

    public function renderPropertyViewValue(array $handles)
    {
        if ($this->getProxy()) {
            return $this->getProxy()->renderPropertyViewValue($handles);
        }

        throw new PhabricatorCustomFieldImplementationIncompleteException($this);
    }

    public function renderEditControl(array $handles)
    {
        if ($this->getProxy()) {
            return $this->getProxy()->renderEditControl($handles);
        }
        throw new PhabricatorCustomFieldImplementationIncompleteException($this);
    }
}
