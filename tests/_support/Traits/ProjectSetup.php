<?php
namespace tad\WPBrowser\Tests\Traits;


use function tad\WPBrowser\Tests\Support\copy_recursive;

trait ProjectSetup
{

    protected $projectDir;

    public function scaffoldInitializedComposerProject()
    {
        $this->projectDir = sys_get_temp_dir() . '/project-' . md5(uniqid('', true));
        copy_recursive(codecept_data_dir('/projects/initialized-composer-project'), $this->projectDir);
        codecept_debug('Project folder: ' . $this->projectDir);
    }
}