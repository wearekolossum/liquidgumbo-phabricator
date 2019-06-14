<?php

final class LiquidgumboManiphestAllTasksByProjectDatasource extends PhabricatorTypeaheadDatasource
{
    const FUNCTION_TOKEN = 'open()';


    public function getBrowseTitle()
    {
        return pht('Browse tasks');
    }

    public function getPlaceholderText()
    {
        return pht('Type task title');
    }

    public function getDatasourceApplicationClass()
    {
        return 'PhabricatorManiphestApplication';
    }

    public function getDatasourceFunctions()
    {
        return array(
            'open' => array(
                'name' => pht('Any Open Status'),
                'summary' => pht('Find results with any open status.'),
                'description' => pht(
                'This function includes results which have any open status.'),
            ),
        );
    }

    public function loadResults()
    {
        $results = $this->buildResults();
        return $this->filterResultsAgainstTokens($results);
    }

    protected function evaluateFunction($function, array $argv_list)
    {
        $results = array();

        $map = ManiphestTaskStatus::getTaskStatusMap();
        foreach ($argv_list as $argv) {
            foreach ($map as $status => $name) {
                if (ManiphestTaskStatus::isOpenStatus($status)) {
                    $results[] = $status;
                }
            }
        }

        return $results;
    }

    public function renderFunctionTokens($function, array $argv_list)
    {
        $results = array();

        foreach ($argv_list as $argv) {
            $results[] = PhabricatorTypeaheadTokenView::newFromTypeaheadResult(
                $this->buildResults());
        }

        return $results;
    }

    private function buildResults()
    {
        $viewer = $this->getViewer();
        $raw_query = $this->getRawQuery();
        $tokens = self::tokenizeString($raw_query);
        $for_autocomplete = $this->getParameter('autocomplete');
        $is_browse = $this->getIsBrowse();
        $task_field_values = array();
        $inverse_field_values = array();

        /* Get editing task - again */
        $task = id(new ManiphestTaskQuery())
            ->setViewer($this->getViewer())
            ->withPHIDs(array($this->getParameter('taskPHID')))
            ->needProjectPHIDs(true)
            ->executeOne();

        /* Setup task query engine */
        $task_search_engine = id(new ManiphestTaskSearchEngine())
            ->setViewer($this->getViewer());
        $task_query = $task_search_engine->newQuery();

        /* Setup task query */
        $tasks = $task_query
            ->needProjectPHIDs(true)
            ->setViewer($this->getViewer())
            ->setOrder(ManiphestTaskQuery::ORDER_TITLE);

        /* Build projectPHIDs */
        if (!empty($task->getProjectPHIDs())) {
            $task_query->withEdgeLogicPHIDs(
                PhabricatorProjectObjectHasProjectEdgeType::EDGECONST,
                PhabricatorQueryConstraint::OPERATOR_OR,
                $task->getProjectPHIDs()
            );
        }

        /* Execute and get tasks */
        $tasks = $task_query->execute();

        /* Map task PHIDs as key to result */
        $tasks = mpull($tasks, null, 'getPHID');

        /* Get custom field list of task */
        $task_field_list = PhabricatorCustomField::getObjectFields(
            $task,
            PhabricatorCustomField::ROLE_EDIT
        )
        ->setViewer($this->getViewer())
        ->readFieldsFromStorage($task);

        /* Get field and value */
        $task_fields = $task_field_list->getFields();

        /* Get field values */
        if (!empty($this->getParameter('fieldKey'))) {
            $task_field = $task_fields[$this->getParameter('fieldKey')];
            $task_field_values = $task_field->getProxy()->getFieldValue();
        }

        /* Get inverse field values if set */
        if (!empty($this->getParameter('inverseFieldKey'))) {
            $inverse_field = $task_fields[$this->getParameter('inverseFieldKey')];
            $inverse_field_values = $inverse_field->getProxy()->getFieldValue();
        }

        /* Setup result array for return */
        $results = array();
        $typeahead_helper = id(new LiquidgumboManiphestDatasource())
            ->setViewer($this->getViewer());

        /* Walk tasks */
        foreach ($tasks as $task) {
            /* Remove myself */
            if ($task->getPHID() != $this->getParameter('taskPHID') && !in_array($task->getPHID(), $inverse_field_values) && !in_array($task->getPHID(), $task_field_values)) {
                $results[] = $typeahead_helper->renderTypeaheadResult($task);
            }
        }

        return $results;
    }
}
