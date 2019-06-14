<?php

final class LiquidgumboProjectGanttChartController extends PhabricatorProjectController
{
    private $project;

    public function shouldAllowPublic()
    {
        return true;
    }

    public function handleRequest(AphrontRequest $request)
    {
        $request = $this->getRequest();
        $viewer = $request->getViewer();

        $response = $this->loadProject();
        if ($response) {
            return $response;
        }
        $project = $this->getProject();

        $board_uri = $this->getApplicationURI('board/'.$project->getID().'/');

        /*$search_engine = id(new ManiphestTaskSearchEngine())
            ->setViewer($viewer)
            ->setBaseURI($board_uri);

        $query_key = $this->getDefaultFilter($project);

        if ($search_engine->isBuiltinQuery($query_key)) {
            $saved = $search_engine->buildSavedQueryFromBuiltin($query_key);
        } else {
            $saved = id(new PhabricatorSavedQueryQuery())
                ->setViewer($viewer)
                ->withQueryKeys(array($query_key))
                ->executeOne();

            if (!$saved) {
                return new Aphront404Response();
            }

            $custom_query = $saved;
        }

        $task_query = $search_engine->buildQueryFromSavedQuery($saved);
        $select_phids = array($project->getPHID());
        $tasks = $task_query
            ->withEdgeLogicPHIDs(
                PhabricatorProjectObjectHasProjectEdgeType::EDGECONST,
                PhabricatorQueryConstraint::OPERATOR_ANCESTOR,
                array($select_phids)
            )
            ->setOrder(ManiphestTaskQuery::ORDER_PRIORITY)
            ->setViewer($viewer)
            ->execute();
        $tasks = mpull($tasks, null, 'getPHID');

        foreach ($tasks as $task) {
            $field_list = PhabricatorCustomField::getObjectFields(
                $task,
                PhabricatorCustomField::ROLE_VIEW
            );
            $field_list->setViewer($viewer)
                ->readFieldsFromStorage($task);

            $fields = $field_list->getFields();

            foreach ($fields as $key => $field) {
                $field->setViewer($viewer);
                $label = $field->renderPropertyViewLabel();
                $value = $field->getValueForStorage();
                $formatted_value = $field->renderPropertyViewValue(array());
                $task->setProperty($field->getModernFieldKey(), $field->getValueForStorage());

                $this->debug($value);
                $this->debug($formatted_value);
                $this->debug($field->getFieldKey());
                $this->debug($field->getFieldName());
                $this->debug($field->getModernFieldKey());
                exit;
            }
            $task->setProperty('custom.kolossum.startIsMilestone', false);
            $task->setProperty('custom.kolossum.endIsMilestone', false);
            $task->setProperty('custom.kolossum.progress', false);
        }
        */

        $tasks = $this->buildTaskList();

        $engine = $this->getProfileMenuEngine();
        $default = $engine->getDefaultMenuItemConfiguration();

        // If defaults are broken somehow, serve the manage page. See T13033 for
        // discussion.
        if ($default) {
            $default_key = $default->getBuiltinKey();
        } else {
            $default_key = PhabricatorProject::ITEM_MANAGE;
        }

        $header = id(new PHUIHeaderView())
            ->setHeader(array($project->getDisplayName().' - Gantt Chart', false))
            ->setUser($viewer)
            ->setPolicyObject($project)
            ->setProfileHeader(true);

        $nav = $this->newNavigation(
            $project,
            PhabricatorProject::ITEM_PROFILE
        );

        $chart_json = id(new LiquidgumboProjectGanttChartJsonView())
            ->setProject($project)
            ->setTasks($tasks)
            ->setViewer($viewer);
        $chart = id(new LiquidgumboProjectGanttChartView())
            ->setProject($project);

        $view = id(new PHUITwoColumnView())
            ->setHeader($header)
            ->setFooter(array(
                /*$chart_json,
                $chart,*/
                phutil_safe_html('<script type="text/javascript" src="//www.gstatic.com/charts/loader.js"></script>')
            )
        );

        /*require_celerity_resource('ganttchart-css-platform');
        require_celerity_resource('ganttchart-css-libs-jquery-datefield');
        require_celerity_resource('ganttchart-css-gantt');
        require_celerity_resource('ganttchart-css');
        //require_celerity_resource('ganttchart-css-gantt-print');
        require_celerity_resource('ganttchart-js-libs-jquery');
        require_celerity_resource('ganttchart-js-libs-jquery-ui');
        require_celerity_resource('ganttchart-js-libs-jquery-livequery');
        require_celerity_resource('ganttchart-js-libs-jquery-timers');
        require_celerity_resource('ganttchart-js-libs-utilities');
        require_celerity_resource('ganttchart-js-libs-forms');
        require_celerity_resource('ganttchart-js-libs-date');
        require_celerity_resource('ganttchart-js-libs-dialogs');
        require_celerity_resource('ganttchart-js-libs-layout');
        require_celerity_resource('ganttchart-js-libs-i18n-js');
        require_celerity_resource('ganttchart-js-libs-jquery-datefield');
        require_celerity_resource('ganttchart-js-libs-jquery-jst');
        require_celerity_resource('ganttchart-js-libs-jquery-svg');
        require_celerity_resource('ganttchart-js-libs-jquery-svgdom');
        require_celerity_resource('ganttchart-js-utilities');
        require_celerity_resource('ganttchart-js-task');
        require_celerity_resource('ganttchart-js-drawer-svg');
        require_celerity_resource('ganttchart-js-zoom');
        require_celerity_resource('ganttchart-js-grid-editor');
        require_celerity_resource('ganttchart-js-master');
        require_celerity_resource('ganttchart-js');*/

        return $this->newPage()
            ->setNavigation($nav)
            ->setTitle($project->getDisplayName())
            ->setPageObjectPHIDs(array($project->getPHID()))
            ->appendChild($view);
    }

    private function getDefaultFilter(PhabricatorProject $project)
    {
        $default_filter = $project->getDefaultWorkboardFilter();

        if (strlen($default_filter)) {
            return $default_filter;
        }

        return 'open';
    }

    private function buildTaskList($parent_id = false, $parent_tasks = false)
    {
        /* Setup task query engine */
        $task_search_engine = id(new ManiphestTaskSearchEngine())
            ->setViewer($this->getViewer());
        $task_query = $task_search_engine->newQuery();

        /*print_r($this->getProject()->getPHID());
        exit;*/

        $parent_id = 3;

        /* Setup task query */
        $task_query
            ->needProjectPHIDs(true)
            ->setViewer($this->getViewer())
            ->setOrder('liquidgumbo.startdate');

        /* Build projectPHIDs */
        if (!empty($this->getProject()->getPHID())) {
            $task_query->withEdgeLogicPHIDs(
                PhabricatorProjectObjectHasProjectEdgeType::EDGECONST,
                PhabricatorQueryConstraint::OPERATOR_AND,
                array($this->getProject()->getPHID())
            );
        }

        $api_token = PhabricatorEnv::getEnvConfig('liquidgumbo.conduit-token');
        $client = new ConduitClient(PhabricatorEnv::getEnvConfig('phabricator.base-uri'));
        $client->setConduitToken($api_token);

        if ($parent_id) {
            //$task_query->withParentTaskIDs(array($parent_id));

            /* Check for subtasks */
            $api_parameters = array(
                'queryKey' => 'all',
                'constraints' => array(
                    'parentIDs' => array(
                        $parent_id
                    )
                ),
                'order' => '-custom.kolossum.startdate',
            );
        } else {
            $task_query->withOpenParents(false);
        }

        //$tasks = $client->callMethodSynchronous('maniphest.search', $api_parameters);
        print_r($task_query);
        $tasks = $task_query->execute();

        /*print_r($tasks);
        exit;*/

        $i = 0;

        if (!empty($tasks['data'])) {
            foreach ($tasks['data'] as $task) {
                $subtasks = $this->buildTaskList($task->getId());

                if (!empty($subtasks['data'])) {
                    $tasks['data'][$i]['subtasks'] = $subtasks;
                }

                $i++;
            }
        }

        return $tasks;
    }

    public function debug($message)
    {
        echo '<pre>';
        print_r($message);
        echo '</pre>';
    }
}
