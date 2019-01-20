<?php

namespace Helper;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

use Codeception\Command\Init;
use Codeception\Template\Wpbrowser;
use Ofbeaton\Console\Tester\QuestionTester;
use Ofbeaton\Console\Tester\UnhandledQuestionException;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Tester\CommandTester;
use tad\WPBrowser\Tests\Traits\ProjectSetup;

class Scaffold extends \Codeception\Module
{

    use ProjectSetup;
    use QuestionTester;

    /**
     * @var array
     */
    protected static $defaultQuestionMap = [
        'I acknowledge wp-browser' => 'yes',
        '/How would you like the .* suite to be called/' => "\n",
        'How would you like to call the env configuration' => '.env',
    ];

    /**
     * @var \Symfony\Component\Console\Input\Input
     */
    protected $input;

    /**
     * @var \Symfony\Component\Console\Output\Output
     */
    protected $output;

    /**
     * @var Init
     */
    protected $command;

    /**
     * @var CommandTester
     */
    protected $commandTester;

    /**
     * @var \Ofbeaton\Console\Tester\QuestionHelperMock
     */
    protected $mockQuestionHelper;

    /**
     * @var array
     */
    protected $currentQuestionMap = [];

    /**
     * @Given I want to initialize wp-browser interactively
     */
    public function iWantToInitializeWpbrowserInteractively()
    {
        $this->currentQuestionMap = static::$defaultQuestionMap;
        $this->scaffoldInitializedComposerProject();

    }

    /**
     * @Given I pick default answers
     */
    public function iPickDefaultAnswers()
    {
        $this->currentQuestionMap = static::$defaultQuestionMap;
    }

    /**
     * @Given I will reply :answer to the question :question
     */
    public function iWillReplyToTheQuestion($answer, $question)
    {
        $this->currentQuestionMap[$question] = $answer;
    }

    /**
     * @Then I should see WordPress was configured to run from the vendor folder with SQLite
     */
    public function iShouldSeeWordPressWasConfiguredToRunFromTheVendorFolderWithSQLite()
    {
        $composerJsonFile = $this->projectDir . '/composer.json';
        $this->assertFileExists($composerJsonFile);
        $composerJsonContents = json_decode(file_get_contents($composerJsonFile), true);
        $this->assertWordpressIsADeveloperDependency($composerJsonContents);
        $this->assertWordPressConfig();
        $this->assertWordPressSqliteDb();
    }

    /**
     *
     *
     * @since TBD
     *
     * @param $composerJsonContents
     */
    public function assertWordpressIsADeveloperDependency($composerJsonContents)
    {
        $this->assertTrue(isset($composerJsonContents['repositories']));
        $wordpressPackages = array_filter($composerJsonContents['repositories'], function (array $repository) {
            return isset($repository['type'], $repository['package']['name'])
                   && $repository['package']['name'] === 'WordPress/WordPress';
        });
        $this->assertCount(1, $wordpressPackages);
        $wordpressPackage = reset($wordpressPackages);
        $this->assertEquals(
            'https://github.com/WordPress/WordPress.git',
            $wordpressPackage['package']['source']['url']
        );
        $this->assertArrayHasKey('WordPress/WordPress', $composerJsonContents['require-dev']);
    }

    protected function assertWordPressConfig()
    {
        $wpConfigFile = $this->projectDir . '/vendor/WordPress/WordPress/wp-config.php';
        $this->assertFileExists($wpConfigFile);
        $configFileContents = file_get_contents($wpConfigFile);
        $configFileLines = explode("\n", $configFileContents);
        $definedConstants = array_reduce($configFileLines,
            function ($constants, $line) {
                $constantDefinitionPattnern = '/define\\s*\\(\\s*\'(?<key>\\w)*\',\\s*\'{0,1}(?<value>[\\w_]+)\'{0,1}\\s\\)/';
                if (preg_match($constantDefinitionPattnern, $line, $matches)) {
                    $constants[$matches['key']] = $matches['value'];
                }
                return $constants;
            }, []);
        $expectedConstants = [
            'USE_MYSQL' => 'false',
            'DB_FILE' => 'wp.sqlite',
            'DB_DIR' => '__DIR__',
            'AUTOMATIC_UPDATER_DISABLED' => 'true',
            'DISABLE_WP_CRON' => 'true',
            'WP_DEBUG' => 'true',
            'WP_DEBUG_LOG' => 'true',
            'WP_DEBUG_DISPLAY' => 'true',
        ];
        $this->assertEmpty(array_intersect_key($definedConstants, $expectedConstants));
        foreach ($expectedConstants as $key => $value) {
            $this->assertArrayHasKey($key, $expectedConstants);
            $this->assertEquals($value, $expectedConstants[$key]);
        }
    }

    protected function assertWordPressSqliteDb()
    {
        $this->assertFileExists($this->projectDir . '/vendor/WordPress/WordPress/wp.sqlite');
    }

    /**
     * @When I init WPBrowser
     */
    public function iInitWPBrowser()
    {
        $this->command = new Init('init');
        $this->command->setHelperSet(new HelperSet());
        $this->mockQuestionHelper($this->command, function ($text, $order, Question $question) {
            $map = $this->currentQuestionMap;

            $matchingKeys = array_values(array_filter(array_keys($map), function ($questionPattern) use ($text) {
                return isRegex($questionPattern) ?
                    preg_match($questionPattern, $text)
                    : strpos($text, $questionPattern) !== false;
            }));

            if (count($matchingKeys) > 1) {
                throw new \RuntimeException('There is more than 1 pattern matching the question "' . $text . '", found patterns: ' . json_encode($matchingKeys));
            }

            if (count($matchingKeys) === 1) {
                return $map[reset($matchingKeys)];
            }

            throw new UnhandledQuestionException('Question "' . $text . '" matches no pattern.');
        });
        $this->mockQuestionHelper = $this->command->getHelper('question');
        // Really an hack to inject, yet there is no other entry point with no refactoring cost.
        Wpbrowser::$_questionHelper = $this->mockQuestionHelper;
        $this->commandTester = new CommandTester($this->command);
        $this->commandTester->execute([
            'template' => 'wpbrowser',
            '--path' => $this->projectDir,
        ]);
    }
}
