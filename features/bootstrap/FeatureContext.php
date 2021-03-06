<?php

use Behat\Behat\Tester\Exception\PendingException;
use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use byrokrat\autogiro\Tree\Node;
use byrokrat\autogiro\Parser\ParserFactory;
use byrokrat\autogiro\Writer\WriterFactory;
use byrokrat\autogiro\Enumerator;
use byrokrat\autogiro\Exception\ContentException;
use byrokrat\amount\Currency\SEK;

/**
 * Defines application features from the specific context.
 *
 * @TODO Break up into traits to create a more robust setup..
 */
class FeatureContext implements Context, SnippetAcceptingContext
{
    use ParserTrait;

    /**
     * @var \byrokrat\autogiro\Tree\FileNode Created at parse time
     */
    private $fileNode;

    /**
     * @var ContentException
     */
    private $exception;

    /**
     * @var \byrokrat\autogiro\Writer\Writer
     */
    private $writer;

    /**
     * @var string The generated request file
     */
    private $generatedFile = '';

    /**
     * @When I parse:
     */
    public function iParse(PyStringNode $node)
    {
        $this->parseRawFile(Utils::normalize($node));
    }

    /**
     * @Then I find a :layoutType layout
     */
    public function iFindALayout($layoutType)
    {
        if ($this->exception) {
            throw $this->exception;
        }

        $layoutIds = [];

        foreach ($this->fileNode->getChildren() as $layoutNode) {
            $layoutIds[] = $layoutNode->getAttribute('layout_name');
        }

        Assertions::assertInArray(
            constant("byrokrat\autogiro\Layouts::$layoutType"),
            $layoutIds,
            $this->fileNode->getAttribute('layout_ids')
        );
    }

    /**
     * @Then I find :number :nodeType nodes
     */
    public function iFindNodes($number, $nodeType)
    {
        $enumerator = new Enumerator;

        $count = 0;
        $enumerator->on($nodeType, function () use (&$count) {
            $count++;
        });

        $enumerator->enumerate($this->fileNode);

        Assertions::assertEquals((integer)$number, $count);
    }

    /**
     * @Then the last :nodeType contains account :number
     */
    public function theLastNodeContainsAccount(string $nodeType, string $number)
    {
        Assertions::assertEquals(
            $number,
            Utils::extractNodeFromTree($nodeType, $this->fileNode)->getChild('account')->getAttribute('account')->getNumber()
        );
    }

    /**
     * @Then I get a :error error
     */
    public function iGetErrorWithMessage(string $error)
    {
        Assertions::assertInArray(
            $error,
            $this->exception->getErrors()
        );
    }

    /**
     * @Then I get an error
     */
    public function iGetAnError()
    {
        Assertions::assertEquals(
            ContentException::CLASS,
            get_class($this->exception)
        );
    }

    /**
     * @Given a writer with BGC number :bgcNumber, bankgiro :autogiro and date :date
     */
    public function aWriterWithBgcNumberAndBankgiro(string $bgcNumber, string $bankgiro, string $date)
    {
        $this->writer = (new WriterFactory)->createWriter(
            $bgcNumber,
            (new \byrokrat\banking\BankgiroFactory)->createAccount($bankgiro),
            new \DateTime($date)
        );
    }

    /**
     * @When I request mandate :payerNr be deleted
     */
    public function iRequestMandateBeDeleted($payerNr)
    {
        $this->writer->deleteMandate($payerNr);
    }

    /**
     * @When I request mandate :payerNr be added
     */
    public function iRequestMandateBeAdded($payerNr)
    {
        $this->writer->addNewMandate(
            $payerNr,
            (new \byrokrat\banking\AccountFactory)->createAccount('50001111116'),
            new \byrokrat\id\PersonalId('820323-2775')
        );
    }

    /**
     * @When I request mandate :payerNr be accepted
     */
    public function iRequestMandateBeAccepted($payerNr)
    {
        $this->writer->acceptDigitalMandate($payerNr);
    }

    /**
     * @When I request mandate :payerNr be rejected
     */
    public function iRequestMandateBeRejected($payerNr)
    {
        $this->writer->rejectDigitalMandate($payerNr);
    }

    /**
     * @When I request mandate :payerNr be updated to :newPayerNr
     */
    public function iRequestMandateBeUpdatedTo($payerNr, $newPayerNr)
    {
        $this->writer->updateMandate($payerNr, $newPayerNr);
    }

    /**
     * @When I request a transaction of :amount SEK from :payerNr
     */
    public function iRequestATransactionOfSekFrom($amount, $payerNr)
    {
        $this->writer->addTransaction($payerNr, new SEK($amount), new \DateTime);
    }

    /**
     * @When I request a transaction of :amount SEK to :payerNr
     */
    public function iRequestATransactionOfSekTo($amount, $payerNr)
    {
        $this->writer->addOutgoingTransaction($payerNr, new SEK($amount), new \DateTime);
    }

    /**
     * @When I request a monthly transaction of :amount SEK from :payerNr
     */
    public function iRequestAMonthlyTransactionOfSekFrom($amount, $payerNr)
    {
        $this->writer->addMonthlyTransaction($payerNr, new SEK($amount), new \DateTime);
    }

    /**
     * @When I request an immediate transaction of :amount SEK from :payerNr
     */
    public function iRequestAnImmediateTransactionOfSekFrom($amount, $payerNr)
    {
        $this->writer->addImmediateTransaction($payerNr, new SEK($amount));
    }

    /**
     * @When I generate the request file
     */
    public function iGenerateTheRequestFile()
    {
        $this->generatedFile = $this->writer->getContent();
    }

    /**
     * @When I parse the generated file
     */
    public function iParseTheGeneratedFile()
    {
        $this->parseRawFile($this->generatedFile);
    }

    /**
     * @Then I get a file like:
     */
    public function iGetAFileLike(PyStringNode $node)
    {
        if (Utils::normalize($node) != $this->generatedFile) {
            throw new \Exception("Unvalid generated file: $this->generatedFile");
        }
    }

    private function parseRawFile(string $content)
    {
        try {
            $this->fileNode = $this->getParser()->parse($content);
        } catch (ContentException $e) {
            $this->exception = $e;
        }
    }
}
