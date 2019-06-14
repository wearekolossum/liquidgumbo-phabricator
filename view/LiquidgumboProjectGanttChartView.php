<?php

final class LiquidgumboProjectGanttChartView extends AphrontView
{
    private $project;
    private $tasks;

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

    public function render()
    {
        $task_html = '';

        $tpl_html = file_get_contents(dirname(__FILE__).'/assets/tpl/gantbuttons.phtml');
        $tpl_html .= file_get_contents(dirname(__FILE__).'/assets/tpl/taskedithead.phtml');
        $tpl_html .= file_get_contents(dirname(__FILE__).'/assets/tpl/taskrow.phtml');
        $tpl_html .= file_get_contents(dirname(__FILE__).'/assets/tpl/taskemptyrow.phtml');
        $tpl_html .= file_get_contents(dirname(__FILE__).'/assets/tpl/taskbar.phtml');
        $tpl_html .= file_get_contents(dirname(__FILE__).'/assets/tpl/changestatus.phtml');
        $tpl_html .= file_get_contents(dirname(__FILE__).'/assets/tpl/taskeditor.phtml');
        $tpl_html .= file_get_contents(dirname(__FILE__).'/assets/tpl/assignmentrow.phtml');
        $tpl_html .= file_get_contents(dirname(__FILE__).'/assets/tpl/resourceeditor.phtml');
        $tpl_html .= file_get_contents(dirname(__FILE__).'/assets/tpl/resourcerow.phtml');

        $task_html .= phutil_tag(
            'div',
            array(
                'id' => 'workSpace',
                'style' => 'padding:0px; overflow-y:auto; overflow-x:hidden;border:1px solid #e5e5e5;position:relative;margin:0 5px;'
            ),
            ''
        );
        $task_html .= phutil_tag(
            'div',
            array(
                'id' => 'ganttEditorTemplates',
                'style' => 'display:none;'
            ),
            phutil_safe_html($tpl_html)
        );
        $task_html .= phutil_tag(
            'script',
            array(
                'type' => 'text/javascript',
            ),
            phutil_safe_html(
                'var linkArrowPath = "'.celerity_get_resource_uri('rsrc/css/ganttchart/res/linkArrow.png').'";
                var hasExternalDepsPath = "'.celerity_get_resource_uri('rsrc/css/ganttchart/res/hasExternalDeps.png').'";
                var milestonePath = "'.celerity_get_resource_uri('rsrc/css/ganttchart/res/milestone.png').'";'
            )
        );

        $view = array(
            phutil_tag(
                'div',
                array(
                    'class' => 'gantt-chart-wrapper',
                ),
                array(
                    phutil_safe_html($task_html)
                )
            )
        );
        return $view;
    }
}
