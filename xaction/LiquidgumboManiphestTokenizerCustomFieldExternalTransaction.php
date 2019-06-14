<?php

final class LiquidgumboManiphestTokenizerCustomFieldExternalTransaction extends Phobject
{
    private $fieldId;
    private $viewer;
    private $object;

    public function setFieldId($fieldId)
    {
        $this->fieldId = $fieldId;
    }

    public function getFieldId()
    {
        return $this->fieldId;
    }

    public function setViewer($viewer)
    {
        $this->viewer = $viewer;
    }

    public function getViewer()
    {
        return $this->viewer;
    }

    public function setObject($object)
    {
        $this->object = $object;
    }

    public function getObject()
    {
        return $this->object;
    }

    public function compareXactionValues(PhabricatorApplicationTransaction $xaction)
    {
        /* Get values */
        $old_value = json_decode($xaction->getOldValue());
        $new_value = json_decode($xaction->getNewValue());

        /* Setup variables */
        $added_phids = array();
        $removed_phids = array();
        $persistent_phids = array();

        /* Check for values to remove, add and persistent */
        if (!empty($old_value)) {
            foreach ($old_value as $old_phid) {
                if (in_array($old_phid, $new_value)) {
                    $persistent_phids[] = $old_phid;
                } else {
                    $removed_phids[] = $old_phid;
                }
            }
        }
        if (!empty($new_value)) {
            foreach ($new_value as $new_phid) {
                if (in_array($new_phid, $old_value)) {
                    $persistent_phids[] = $new_phid;
                } else {
                    $added_phids[] = $new_phid;
                }
            }
        }

        return array(
            'persistent' => $persistent_phids,
            'added' => $added_phids,
            'removed' => $removed_phids
        );
    }

    public function handleExternalEffects(PhabricatorApplicationTransaction $xaction)
    {
        /* Compare old and new */
        $old_value = json_decode($xaction->getOldValue());
        $new_value = json_decode($xaction->getNewValue());
        $value_comparison = $this->compareXactionValues($xaction);

        /* Check for needed actions */
        if (!empty($value_comparison['removed'])) {
            foreach ($value_comparison['removed'] as $removed_phid) {
                $this->executeExternalEffects($removed_phid, $this->getObject()->getPHID(), 'remove');
            }
        }
        if (!empty($value_comparison['added'])) {
            foreach ($value_comparison['added'] as $added_phid) {
                $this->executeExternalEffects($added_phid, $this->getObject()->getPHID(), 'add');
            }
        }
    }

    private function executeExternalEffects($external_phid, $object_phid, $mode)
    {
        /* Get field value of external task */
        $external_task = id(new ManiphestTaskQuery())
            ->setViewer($this->getViewer())
            ->needProjectPHIDs(true)
            ->withPHIDs(array($external_phid))
            ->executeOne();

        /* Get field list of external task */
        $external_task_field_list = PhabricatorCustomField::getObjectFields(
            $external_task,
            PhabricatorCustomField::ROLE_EDIT
        )->setViewer($this->getViewer())
        ->readFieldsFromStorage($external_task);

        /* Get field and value */
        $external_task_fields = $external_task_field_list->getFields();
        $external_task_field = $external_task_fields[$this->getFieldId()];
        $external_task_edit_engine = id(new ManiphestTransactionEditor());

        /* Get edit engine */
        $external_task_edit_engine->setActor($this->getViewer())
            ->setContinueOnMissingFields(true)
            ->setContinueOnNoEffect(true);

        /* Create transaction */
        $external_transaction = new ManiphestTransaction();
        $external_transaction->setTransactionType(
          PhabricatorTransactions::TYPE_CUSTOMFIELD);
        $external_transaction->setMetadataValue('customfield:key', $this->getFieldId());
        $external_transaction->setOldValue($external_task_field->getProxy()->getFieldValue());

        /* Get new value based on comparison result */
        $external_transaction_value = array();

        /* Only act on non-empty current value */
        if (!empty($external_task_field->getProxy()->getFieldValue())) {
            $external_transaction_value = $external_task_field->getProxy()->getFieldValue();
            if ($mode == 'add') {
                /* Add new phid if not present in current value */
                if (!in_array($object_phid, $external_transaction_value)) {
                    $external_transaction_value[] = $object_phid;
                }
            } elseif ($mode == 'remove') {
                /* Add new phid if not present in current value */
                if (in_array($object_phid, $external_transaction_value)) {
                    foreach ($external_transaction_value as $key => $value) {
                        if ($value == $object_phid) {
                            unset($external_transaction_value[$key]);
                        }
                    }
                }
            }
        } else {
            /* Old value is empty - just add new one if mode is add */
            if ($mode == 'add') {
                $external_transaction_value[] = $object_phid;
            }
        }

        $external_transaction->setNewValue($external_transaction_value);
        $external_transactions = array($external_transaction);

        $content_source = PhabricatorContentSource::newForSource(
            PhabricatorWebContentSource::SOURCECONST
        );

        $external_task_edit_engine->setContentSource($content_source);
        $external_task_edit_engine->applyTransactions($external_task, $external_transactions);
    }
}
