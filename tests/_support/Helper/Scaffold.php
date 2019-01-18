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
     * @Given I am initializing wp-browser
     */
    public function iAmInitializingWpbrowser()
    {
        $this->scaffoldInitializedComposerProject();

        $this->command = new Init('init');
        $this->command->setHelperSet(new HelperSet());
        $this->mockQuestionHelper($this->command, function ($text, $order, Question $question) {
            throw new UnhandledQuestionException();
        });
        $this->mockQuestionHelper = $this->command->getHelper('question');
        Wpbrowser::_seQuestionHelper($this->mockQuestionHelper);
        $this->commandTester = new CommandTester($this->command);
        $this->commandTester->execute(['template' => 'wpbrowser', '--path' => $this->projectDir]);
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
