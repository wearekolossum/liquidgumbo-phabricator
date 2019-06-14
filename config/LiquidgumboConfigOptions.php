<?php

final class LiquidgumboConfigOptions extends PhabricatorApplicationConfigOptions
{
    public function getName()
    {
        return pht('Liquidgumbo');
    }

    public function getDescription()
    {
        return pht('Configure Liquidgumbo.');
    }

    public function getIcon()
    {
        return 'fa-cog';
    }

    public function getGroup()
    {
        return 'apps';
    }

    public function getOptions()
    {
        $custom_field_type = 'custom:PhabricatorCustomFieldConfigOptionType';

        $maniphest_headline_description = $this->deformat(pht(<<<EOTEXT
Headline displayed above the Liquidgumbo fieldgroup in Maniphest views. Headline will not be rendered in property view when left empty or filled with a blank.
EOTEXT
      ));

        $maniphest_auto_relate_block_description = $this->deformat(pht(<<<EOTEXT
When set to true adding a "blocks" value to a Maniphest task A will auto-relate the "blocked By" value on Maniphest task B and vice versa.

Changing this will not auto-relate any tasks that have no auto-relations.
EOTEXT
    ));

        $maniphest_auto_relate_duplicate_description = $this->deformat(pht(<<<EOTEXT
When set to true adding a "duplicates" value to a Maniphest task A will auto-relate the "duplicated By" value on Maniphest task B and vice versa.

Changing this will not auto-relate any tasks that have no auto-relations.
EOTEXT
));

        $maniphest_auto_relate_clone_description = $this->deformat(pht(<<<EOTEXT
When set to true adding a "clones" value to a Maniphest task A will auto-relate the "cloned By" value on Maniphest task B and vice versa.

Changing this will not auto-relate any tasks that have no auto-relations.
EOTEXT
));

        $maniphest_auto_relate_relate_description = $this->deformat(pht(<<<EOTEXT
When set to true adding a "relates to" value to a Maniphest task A will auto-relate the "relates to" value on Maniphest task B and vice versa.

Changing this will not auto-relate any tasks that have no auto-relations.
EOTEXT
));

        $conduit_token_description = $this->deformat(pht(<<<EOTEXT
Conduit token to use for task search
EOTEXT
      ));

        $task_status_mapping_description = $this->deformat(pht(<<<EOTEXT
Mapping of Maniphest task status to GanttChart task status
EOTEXT
      ));

        $maniphest_epic_relation_max_count_description = $this->deformat(pht(<<<EOTEXT
How many epics can a task have.
EOTEXT
    ));

        $maniphest_tasktype_example = array(
            array(
      'key' => 'epic',
      'name' => pht('Epic'),
    ),
    array(
      'key' => 'story',
      'name' => pht('Story'),
    ),
    array(
      'key' => 'improvement',
      'name' => pht('Improvement'),
    ),
    array(
      'key' => 'bug',
      'name' => pht('Bug'),
    ),
    array(
      'key' => 'feature',
      'name' => pht('Feature Request'),
    ),
  );
        $maniphest_tasktype_example = id(new PhutilJSON())->encodeAsList($maniphest_tasktype_example);

        $maniphest_tasktype_default = array(
            array(
              'key' => '',
              'name' => pht('Select tasktype'),
            ),
            array(
      'key' => 'epic',
      'name' => pht('Epic'),
    ),
    array(
      'key' => 'story',
      'name' => pht('Story'),
    ),
    array(
      'key' => 'task',
      'name' => pht('Task'),
    ),
    array(
      'key' => 'bug',
      'name' => pht('Bug'),
    ),
    array(
      'key' => 'feature',
      'name' => pht('Feature Request'),
    )
  );

        $maniphest_tasktype_description = $this->deformat(pht(<<<EOTEXT
PLEASE NOTE: this option has nothing to do with Phabricators build-in subtypes. These can be found at the Maniphest configuration.

Allows you to define task types.

To define types, provide a list of types. Each type should be a
dictionary with these keys:

- `key` //Required string.// Internal identifier for the subtype, like
  "task", "feature", or "bug".
- `name` //Required string.// Human-readable name for this subtype, like
  "Task", "Feature Request" or "Bug Report".

Each type must have a unique key.
EOTEXT
));

        return array(
            $this->newOption(
        'liquidgumbo.maniphest.headline',
        'string',
        'Liquidgumbo')
        ->setSummary(pht('Headline for Liquidgumbo Maniphest fieldgroup'))
        ->setDescription($maniphest_headline_description),
            $this->newOption(
        'liquidgumbo.maniphest.auto-relate-block',
        'bool',
        true)
        ->setSummary(pht('Auto-relate blocks/blockedBy'))
        ->setDescription($maniphest_auto_relate_block_description),
            $this->newOption(
        'liquidgumbo.maniphest.auto-relate-duplicate',
        'bool',
        true)
        ->setSummary(pht('Auto-relate duplicates/duplicatedBy'))
        ->setDescription($maniphest_auto_relate_duplicate_description),
            $this->newOption(
        'liquidgumbo.maniphest.auto-relate-clone',
        'bool',
        true)
        ->setSummary(pht('Auto-relate clones/clonedBy'))
        ->setDescription($maniphest_auto_relate_clone_description),
            $this->newOption(
        'liquidgumbo.maniphest.auto-relate-relate',
        'bool',
        true)
        ->setSummary(pht('Auto-relate relatesTo/relatesTo'))
        ->setDescription($maniphest_auto_relate_relate_description),
            $this->newOption(
        'liquidgumbo.maniphest.tasktype',
        'wild',
        $maniphest_tasktype_default)
        ->setSummary(pht('Tasktype'))
        ->setDescription($maniphest_tasktype_description)
        ->addExample($maniphest_tasktype_example, pht('Simple tasktypes')),
        $this->newOption(
    'liquidgumbo.maniphest.epic-relation-max-count',
    'int',
    1)
    ->setSummary(pht('Epic max count'))
    ->setDescription($maniphest_epic_relation_max_count_description),
            /*$this->newOption(
        'liquidgumbo.conduit-token',
        'string',
        '')
        ->setSummary(pht('Conduit token'))
        ->setDescription($conduit_token_description),
            $this->newOption(
        'liquidgumbo.ganttchart.task-status-map',
        'wild',
        array())
        ->setSummary(pht('Task Status Map'))
        ->setDescription($task_status_mapping_description)*/
    );
    }
}
