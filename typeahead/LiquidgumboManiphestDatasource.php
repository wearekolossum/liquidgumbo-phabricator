<?php

final class LiquidgumboManiphestDatasource extends PhabricatorTypeaheadDatasource
{
    public function getDatasourceApplicationClass()
    {
    }
    public function getPlaceholderText()
    {
    }
    public function loadResults()
    {
    }

    public function renderTypeaheadResult($result)
    {
        $viewer = $this->getViewer();
        $raw_query = $this->getRawQuery();
        $tokens = self::tokenizeString($raw_query);
        $for_autocomplete = $this->getParameter('autocomplete');
        $is_browse = $this->getIsBrowse();
        $result_item = id(new PhabricatorTypeaheadResult())
            ->setName(phutil_safe_html('<a href="'.$result->getURI().'" target="_blank">'.$result->getTitle().'</a>'))
            ->setDisplayName($result->getTitle())
            ->setURI($result->getURI())
            ->setPHID($result->getPHID())
            ->setIcon(ManiphestTaskPriority::getTaskPriorityIcon($result))
            ->setAutocomplete($for_autocomplete);

        if (!empty($result->getProjectPHIDs())) {
            $project_phids = array();
            foreach ($result->getProjectPHIDs() as $project_phid) {
                $project_phids[] = $project_phid;
            }

            /* Get project name */
            $projects = id(new PhabricatorProjectQuery())
                ->setViewer($this->getViewer())
                ->withPHIDs($project_phids)
                ->execute();

            $project_list = array();
            foreach ($projects as $project) {
                $project_list[] = '<a href="'.$project->getURI().'" target="_blank">'.$project->getName().'</a>';
            }
            $result_item->addAttribute(phutil_safe_html('<strong>'.pht('Project(s)').': </strong>'.implode($project_list, ' | ')));
        }

        if (!empty($result->getDescription())) {
            $result_item->addAttribute(phutil_safe_html('<strong>'.pht('Description').':</strong> '.$result->getDescription()));
        }

        if ($result->isClosed()) {
            $result_item->setClosed(pht('Disabled'));
        }

        return $result_item;
    }
}
