<?php

final class LiquidgumboManiphestCustomFieldEpic extends ManiphestCustomField implements PhabricatorStandardCustomFieldInterface
{
    public function __construct()
    {
        $proxy = id(new LiquidgumboCustomFieldAllEpicsByProject())
            ->setFieldKey($this->getFieldKey())
            ->setRawStandardFieldKey($this->getModernFieldKey())
            ->setApplicationField($this)
            ->setFieldConfig(array(
                'name' => $this->getFieldName(),
                'description' => $this->getFieldDescription(),
                'caption' => $this->getFieldDescription(),
                'limit' => PhabricatorEnv::getEnvConfig('liquidgumbo.maniphest.epic-relation-max-count')
            ));

        $this->setProxy($proxy);
    }

    public function getFieldKey()
    {
        return 'liquidgumbo:maniphest:epic';
    }

    public function getModernFieldKey()
    {
        return 'liquidgumbo.maniphest.epic';
    }

    public function getFieldName()
    {
        return pht('Epic');
    }

    public function getFieldDescription()
    {
        return 'This tasks belongs to epic a,b,c etc.';
    }

    public function getStandardCustomFieldNamespace()
    {
        return 'maniphest';
    }

    public function canDisableField()
    {
        return true;
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

    public function applyApplicationTransactionExternalEffects(
        PhabricatorApplicationTransaction $xaction)
    {
        if ($this->getProxy()) {
            /* Do transaction */
            $success = $this->getProxy()->applyApplicationTransactionExternalEffects($xaction);

            //$this->applyExternalTransactionEffect($xaction);

            return $success;
        }

        return;
    }

    public function applyExternalTransactionEffect(PhabricatorApplicationTransaction $xaction)
    {
        /* Get transaction */
        $transaction = id(new LiquidgumboManiphestTokenizerCustomFieldExternalTransaction());
        $transaction->setViewer($this->getViewer());
        $transaction->setObject($this->getObject());
        $transaction->setFieldId(id(new LiquidgumboManiphestCustomFieldBlockedBy())->getFieldKey());

        /* Only take action when old value != new value */
        if ($xaction->getOldValue() != $xaction->getNewValue() && PhabricatorEnv::getEnvConfig('liquidgumbo.maniphest.auto-relate-block') == true) {
            /* Handle external effects */
            $transaction->handleExternalEffects($xaction);
        }
    }

    public function applyApplicationTransactionInternalEffects(
    PhabricatorApplicationTransaction $xaction)
    {
        if ($this->getProxy()) {
            return $this->getProxy()->applyApplicationTransactionInternalEffects($xaction);
        }

        return;
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
        $this->getProxy()->renderEditControl($handles);
    }
}
