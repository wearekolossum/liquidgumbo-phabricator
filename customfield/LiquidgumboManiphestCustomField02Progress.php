<?php

final class LiquidgumboManiphestCustomFieldProgress extends ManiphestCustomField implements PhabricatorStandardCustomFieldInterface
{
    public function __construct()
    {
        $proxy = id(new PhabricatorStandardCustomFieldInt())
            ->setFieldKey($this->getFieldKey())
            ->setRawStandardFieldKey($this->getModernFieldKey())
            ->setApplicationField($this)
            ->setFieldConfig(array(
                'name' => $this->getFieldName(),
                'description' => $this->getFieldDescription(),
                'default' => $this->getDefault(),
                'placeholder' => $this->getPlaceholder(),
                'caption' => $this->getFieldDescription()
            )
        );

        $this->setProxy($proxy);
    }

    public function getFieldKey()
    {
        return 'liquidgumbo:maniphest:progress';
    }

    public function getModernFieldKey()
    {
        return 'liquidgumbo.maniphest.progress';
    }

    public function getFieldName()
    {
        return pht('Progress in %');
    }

    public function getFieldDescription()
    {
        return pht('Progress in %');
    }

    public function getCaption()
    {
        return pht('Progress in %');
    }

    public function getDefault()
    {
        return 0;
    }

    public function getPlaceholder()
    {
        return pht('Progress in %');
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
