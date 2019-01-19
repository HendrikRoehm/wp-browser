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
        'How would you like to call the env configuration' => "\n",
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
     * @Given I am initializing wp-browser
     */
    public function iAmInitializingWpbrowser()
    {
        $this->currentQuestionMap = static::$defaultQuestionMap;
        $this->scaffoldInitializedComposerProject();

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

    /**
     * @Given pick default answers
     */
    public function pickDefaultAnswers()
    {
        throw new \Codeception\Exception\Incomplete("Step `pick default answers` is not defined");
    }

    /**
     * @When I reply :arg1 to the question :arg2
     */
    public function iReplyToTheQuestion($arg1, $arg2)
    {
        throw new \Codeception\Exception\Incomplete("Step `I reply :arg1 to the question :arg2` is not defined");
    }

    /**
     * @Then I should receive confirmation WordPress was configured to run from the vendor folder with SQLite
     */
    public function iShouldReceiveConfirmationWordPressWasConfiguredToRunFromTheVendorFolderWithSQLite()
    {
        throw new \Codeception\Exception\Incomplete("Step `I should receive confirmation WordPress was configured to run from the vendor folder with SQLite` is not defined");
    }
}
