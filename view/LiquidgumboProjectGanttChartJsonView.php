<?php

final class LiquidgumboProjectGanttChartJsonView extends AphrontView
{
    private $project;
    private $tasks;
    private $viewer;
    private $gantt_status_map = array(
         'open' => 'STATUS_ACTIVE',
         'resolved' => 'STATUS_DONE',
         'invalid' => 'STATUS_FAILED',
         'spite' => 'STATUS_SUSPENDED'
    );

    public function setProject(PhabricatorProject $project)
    {
        $this->project = $project;
        return $this;
    }

    public function getProject()
    {
        return $this->project;
    }

    public function setTasks(array $tasks)
    {
        $this->tasks = $tasks;
        return $this;
    }

    public function getTasks()
    {
        return $this->tasks;
    }

    public function setViewer($viewer)
    {
        $this->viewer = $viewer;
        return $this;
    }

    public function getViewer()
    {
        return $this->viewer;
    }

    public function mapPhabricatorStatusToGanttStatus($phabricator_status)
    {
        if ($this->gantt_status_map[$phabricator_status]) {
            return $this->gantt_status_map[$phabricator_status];
        } else {
            return 'STATUS_UNDEFINED';
        }
    }

    public function render()
    {
        $can_edit_project = PhabricatorPolicyFilter::hasCapability(
          $this->getViewer(),
          $this->getProject(),
          PhabricatorPolicyCapability::CAN_EDIT);

        $can_edit_project = false;

        $chart_json = array(
            'tasks' => array(),
            'resources' => array(),
            'roles' => array(),
            'canWrite' => $can_edit_project ? true : false,
            'canDelete' => $can_edit_project ? true : false,
            'canWriteOnParent' => $can_edit_project ? true : false,
            'canAdd' => $can_edit_project ? true : false
        );

        //$tasks = $this->getTasks();
        /*foreach ($tasks['data'] as $task) {
            $task_obj = id(new ManiphestTaskQuery())
                ->setViewer($this->getViewer())
                ->withIDs(array($task['id']))
                ->needSubscriberPHIDs(true)
                ->executeOne();
            if (!$task_obj) {
                return new Aphront404Response();
            }

            $can_edit = PhabricatorPolicyFilter::hasCapability(
              $this->getViewer(),
              $task_obj,
              PhabricatorPolicyCapability::CAN_EDIT);

            $can_edit = false;

            $chart_json['tasks'][] = array(
                'id' => $task_obj->getId(),
                'name' => $task_obj->getTitle(),
                'progress' => $task['custom.kolossum.progress'],
                'progressByWorklog' => false,
                'relevance' => $task_obj->getPriority(),
                'type' => '',
                'typeId' => '',
                'description' => $task_obj->getDescription(),
                'code' => '',
                'level' => 1,
                'status' => $this->mapPhabricatorStatusToGanttStatus($task_obj->getStatus()),
                'depends' => '',
                'canWrite' => $can_edit ? true : false,
                'start' => $task['fields']['custom.kolossum.startdate']*1000,
                'duration' => floor(($task['fields']['custom.kolossum.enddate']*1000-$task['fields']['custom.kolossum.startdate']*1000)/86400000),
                'end' => $task['fields']['custom.kolossum.enddate']*1000,
                'startIsMilestone' => $task['fields']['custom.kolossum.startIsMilestone'] ? true : false,
                'endIsMilestone' => $task['fields']['custom.kolossum.endIsMilestone'] ? true : false,
                'collapsed' => false,
                'assigs' => array(
                    "resourceId" => $task_obj->getOwnerPHID(),
                    "id" => $task_obj->getOwnerPHID().microtime(),
                    "roleId" => false,
                    "effort" => 0

                ),
                'hasChild' => false
            );
        }*/
        $chart_json['tasks'] = $this->buildTaskJson();

        $user_ids = array_unique(
            array_merge(
                $this->getProject()->getMemberPHIDs(),
                $this->getProject()->getWatcherPHIDs()
            )
        );

        $handles = $this->getViewer()->loadHandles($user_ids);

        foreach ($user_ids as $user_id) {
            $user = $handles[$user_id];
            $chart_json['resources'][] = array(
                'id' => $user->getPHID(),
                'name' => $user->getFullName()
            );
        }

        //exit;
        /*echo celerity_get_resource_uri('rsrc/css/ganttchart/res/hasExternalDeps.png');
        exit;*/


        /*$task_html .= phutil_tag(
            'script',
            array(
                'type' => 'text/javascript',
                'src' => celerity_get_resource_uri('rsrc/js/ganttchart/ganttChart.js')
            ),
            ''
        );*/

        /*$html = hsprintf(
            '<div>%s</div>',
            $task_html
        );*/

        $view = array(
            phutil_tag(
                'script',
                array(
                    'type' => 'text/javascript',
                ),
                phutil_safe_html('var chart_json = '.json_encode($chart_json).';')
            )
        );
        return $view;
    }

    private function buildTaskJson($parent_task = false, $level = 1)
    {
        if (!empty($parent_task['subtasks'])) {
            $tasks = $parent_task['subtasks'];
        } else {
            $tasks = $this->getTasks();
        }

        foreach ($tasks['data'] as $task) {
            /* Get object */
            $task_obj = id(new ManiphestTaskQuery())
                ->setViewer($this->getViewer())
                ->withIDs(array($task['id']))
                ->needSubscriberPHIDs(true)
                ->executeOne();

            $can_edit = PhabricatorPolicyFilter::hasCapability(
                $this->getViewer(),
                $task_obj,
                PhabricatorPolicyCapability::CAN_EDIT
            );

            $can_edit = false;

            $json_tasks[] = array(
                'id' => $task_obj->getId().'_'.md5(microtime().rand(0, 100)),
                'name' => $task_obj->getTitle(),
                'progress' => $task['custom.kolossum.progress'],
                'progressByWorklog' => false,
                'relevance' => $task_obj->getPriority(),
                'type' => '',
                'typeId' => '',
                'description' => $task_obj->getDescription(),
                'code' => '',
                'level' => $level++,
                'status' => $this->mapPhabricatorStatusToGanttStatus($task_obj->getStatus()),
                'depends' => '',
                'canWrite' => $can_edit ? true : false,
                'start' => $task['fields']['custom.kolossum.startdate']*1000,
                'duration' => floor(($task['fields']['custom.kolossum.enddate']*1000-$task['fields']['custom.kolossum.startdate']*1000)/86400000),
                'end' => $task['fields']['custom.kolossum.enddate']*1000,
                'startIsMilestone' => $task['fields']['custom.kolossum.startIsMilestone'] ? true : false,
                'endIsMilestone' => $task['fields']['custom.kolossum.endIsMilestone'] ? true : false,
                'collapsed' => false,
                'assigs' => array(
                    "resourceId" => $task_obj->getOwnerPHID(),
                    "id" => $task_obj->getOwnerPHID().microtime(),
                    "roleId" => false,
                    "effort" => 0

                ),
                'hasChild' => !empty($task['subtasks']) ? true : false
            );

            if (!empty($task['subtasks'])) {
                $json_tasks = array_merge($json_tasks, $this->buildTaskJson($task, $level++));
            }
        }

        return $json_tasks;
    }
}
