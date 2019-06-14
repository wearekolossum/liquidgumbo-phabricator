<?php

final class LiquidgumboManiphestCustomFieldEnddateIsMilestone extends ManiphestCustomField implements PhabricatorStandardCustomFieldInterface
{
    public function __construct()
    {
        $proxy = id(new PhabricatorStandardCustomFieldBool())
            ->setFieldKey($this->getFieldKey())
            ->setRawStandardFieldKey($this->getModernFieldKey())
            ->setApplicationField($this)
            ->setFieldConfig(array(
                'name' => $this->getFieldName(),
                'description' => $this->getFieldDescription(),
                'caption' => $this->getFieldDescription()
            )
        );

        $this->setProxy($proxy);
    }

    public function getFieldKey()
    {
        return 'liquidgumbo:maniphest:enddateIsMilestone';
    }

    public function getModernFieldKey()
    {
        return 'liquidgumbo.maniphest.enddateIsMilestone';
    }

    public function getFieldName()
    {
        return pht('Enddate is milestone');
    }

    public function getFieldDescription()
    {
        return 'Is the enddate a milestone';
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
