<?php

final class LiquidgumboManiphestAllEpicsByProjectDatasource extends PhabricatorTypeaheadDatasource
{
    const FUNCTION_TOKEN = 'open()';


    public function getBrowseTitle()
    {
        return pht('Browse epics');
    }

    public function getPlaceholderText()
    {
        return pht('Type epic title');
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

        id(new LiquidgumboManiphestDatasource());

        /* Get editing task - again */
        $task = id(new ManiphestTaskQuery())
            ->setViewer($this->getViewer())
            ->withPHIDs(array($this->getParameter('taskPHID')))
            ->needProjectPHIDs(true)
            ->executeOne();

        /* Setup task query engine */
        $task_search_engine = id(new ManiphestTaskSearchEngine())
            ->setViewer($this->getViewer());

        /* Get new saved query */
        $task_query = $task_search_engine->newSavedQuery();

        /* Set parameters projectPHIDs and customfield */
        if (!empty($task->getProjectPHIDs())) {
            foreach ($task->getProjectPHIDs() as $key => $val) {
                $projectPHIDs[] = 'any('.$val.')';
            }
            $task_query->setParameter(
                'projectPHIDs',
                $projectPHIDs
            );
        }

        /* Set customfield tasktype */
        $customfield_proxy = id(new PhabricatorSearchCustomFieldProxyField());
        $customfield_proxy->setCustomfield(id(new LiquidgumboManiphestCustomFieldTasktype()));
        $task_query->setParameter(
            $customfield_proxy->getKey(),
            array('epic')
        );

        /* Build query from savedQuery */
        $task_query = $task_search_engine->buildQueryFromSavedQuery($task_query);

        /* Setup task query */
        $task_query
            ->needProjectPHIDs(true)
            ->setViewer($this->getViewer())
            ->setOrder(ManiphestTaskQuery::ORDER_TITLE);

        /* Get tasks */
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
