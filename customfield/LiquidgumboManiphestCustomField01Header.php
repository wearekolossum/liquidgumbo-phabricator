<?php

final class LiquidgumboManiphestCustomFieldHeader extends ManiphestCustomField implements PhabricatorStandardCustomFieldInterface
{
    public function __construct()
    {
        $proxy = id(new PhabricatorStandardCustomFieldHeader())
            ->setFieldKey($this->getFieldKey())
            ->setApplicationField($this)
            ->setFieldConfig(array(
                'name' => $this->getFieldName(),
                'description' => $this->getFieldDescription(),
            )
        );

        $this->setProxy($proxy);
    }

    public function getFieldKey()
    {
        return 'liquidgumbo:header';
    }

    public function getFieldName()
    {
        return PhabricatorEnv::getEnvConfig('liquidgumbo.maniphest.headline');
    }

    public function getFieldDescription()
    {
        return '';
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
        return false;
    }

    public function shouldAppearInListView()
    {
        return false;
    }

    public function renderPropertyViewLabel()
    {
        if ($this->getFieldName() != '') {
            if ($this->getProxy()) {
                return $this->getProxy()->renderPropertyViewLabel();
            }

            return $this->getFieldName();
        }

        return;
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
        if ($this->getFieldName() != '') {
            if ($this->getProxy()) {
                return $this->getProxy()->renderEditControl($handles);
            }
        }
        throw new PhabricatorCustomFieldImplementationIncompleteException($this);
    }
}
