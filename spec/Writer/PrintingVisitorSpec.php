<?php

declare(strict_types = 1);

namespace spec\byrokrat\autogiro\Writer;

use byrokrat\autogiro\Writer\PrintingVisitor;
use byrokrat\autogiro\Writer\Output;
use byrokrat\autogiro\Exception\LogicException;
use byrokrat\autogiro\Tree\DateNode;
use byrokrat\autogiro\Tree\TextNode;
use byrokrat\autogiro\Tree\PayeeBgcNumberNode;
use byrokrat\autogiro\Tree\PayeeBankgiroNode;
use byrokrat\autogiro\Tree\PayerNumberNode;
use byrokrat\autogiro\Tree\AccountNode;
use byrokrat\autogiro\Tree\AmountNode;
use byrokrat\autogiro\Tree\IntervalNode;
use byrokrat\autogiro\Tree\IdNode;
use byrokrat\autogiro\Tree\RepetitionsNode;
use byrokrat\autogiro\Visitor\Visitor;
use byrokrat\amount\Currency\SEK;
use byrokrat\banking\AccountNumber;
use byrokrat\id\PersonalId;
use byrokrat\id\OrganizationId;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class PrintingVisitorSpec extends ObjectBehavior
{
    function let(Output $output)
    {
        $this->setOutput($output);
    }
    function it_is_initializable()
    {
        $this->shouldHaveType(PrintingVisitor::CLASS);
    }

    function it_implements_visitor_interface()
    {
        $this->shouldHaveType(Visitor::CLASS);
    }

    function it_prints_dates(DateNode $node, $output)
    {
        $node->hasAttribute('date')->willReturn(true);
        $node->getAttribute('date')->willReturn(new \DateTime('2017-01-10'));
        $this->beforeDateNode($node);
        $output->write('20170110')->shouldHaveBeenCalled();
    }

    function it_fails_on_missing_date(DateNode $node)
    {
        $node->hasAttribute('date')->willReturn(false);
        $node->getType()->willReturn('');
        $this->shouldThrow(LogicException::CLASS)->duringBeforeDateNode($node);
    }

    function it_fails_on_unvalid_date(DateNode $node)
    {
        $node->hasAttribute('date')->willReturn(true);
        $node->getAttribute('date')->willReturn('not-an-object');
        $node->getType()->willReturn('');
        $this->shouldThrow(LogicException::CLASS)->duringBeforeDateNode($node);
    }

    function it_prints_immediate_dates($output)
    {
        $this->beforeImmediateDateNode();
        $output->write(Argument::is('GENAST  '))->shouldHaveBeenCalled();
    }

    function it_print_text_nodes(TextNode $node, $output)
    {
        $node->getValue()->willReturn('foobar');
        $this->beforeTextNode($node);
        $output->write('foobar')->shouldHaveBeenCalled();
    }

    function it_prints_payee_bgc_numbers(PayeeBgcNumberNode $node, $output)
    {
        $node->getValue()->willReturn('111');
        $this->beforePayeeBgcNumberNode($node);
        $output->write(Argument::is('000111'))->shouldHaveBeenCalled();
    }

    function it_prints_payee_bankgiro_numbers(PayeeBankgiroNode $node, AccountNumber $account, $output)
    {
        $account->getSerialNumber()->willReturn('1234567');
        $account->getCheckDigit()->willReturn('8');
        $node->hasAttribute('account')->willReturn(true);
        $node->getAttribute('account')->willReturn($account);
        $this->beforePayeeBankgiroNode($node);
        $output->write(Argument::is('0012345678'))->shouldHaveBeenCalled();
    }

    function it_fails_on_missing_payee_bankgiro_numbers(PayeeBankgiroNode $node)
    {
        $node->hasAttribute('account')->willReturn(false);
        $node->getType()->willReturn('');
        $this->shouldThrow(LogicException::CLASS)->duringBeforePayeeBankgiroNode($node);
    }

    function it_fails_on_unvalid_payee_bankgiro_numbers(PayeeBankgiroNode $node)
    {
        $node->hasAttribute('account')->willReturn(true);
        $node->getAttribute('account')->willReturn('not-an-object');
        $node->getType()->willReturn('');
        $this->shouldThrow(LogicException::CLASS)->duringBeforePayeeBankgiroNode($node);
    }

    function it_prints_payer_numbers(PayerNumberNode $node, $output)
    {
        $node->getValue()->willReturn('1234567890');
        $this->beforePayerNumberNode($node);
        $output->write(Argument::is('0000001234567890'))->shouldHaveBeenCalled();
    }

    function it_prints_account_numbers(AccountNode $node, AccountNumber $account, $output)
    {
        $account->getClearingNumber()->willReturn('1111');
        $account->getSerialNumber()->willReturn('1234567');
        $account->getCheckDigit()->willReturn('8');
        $node->hasAttribute('account')->willReturn(true);
        $node->getAttribute('account')->willReturn($account);
        $this->beforeAccountNode($node);
        $output->write('1111000012345678')->shouldHaveBeenCalled();
    }

    function it_fails_on_missing_account_numbers(AccountNode $node)
    {
        $node->hasAttribute('account')->willReturn(false);
        $node->getType()->willReturn('');
        $this->shouldThrow(LogicException::CLASS)->duringBeforeAccountNode($node);
    }

    function it_fails_on_invalid_account_numbers(AccountNode $node)
    {
        $node->hasAttribute('account')->willReturn(true);
        $node->getAttribute('account')->willReturn('not-an-object');
        $node->getType()->willReturn('');
        $this->shouldThrow(LogicException::CLASS)->duringBeforeAccountNode($node);
    }

    function it_prints_intervals(IntervalNode $node, $output)
    {
        $node->getValue()->willReturn('9');
        $this->beforeIntervalNode($node);
        $output->write('9')->shouldHaveBeenCalled();
    }

    function it_prints_repetitions(RepetitionsNode $node, $output)
    {
        $node->getValue()->willReturn('123');
        $this->beforeRepetitionsNode($node);
        $output->write('123')->shouldHaveBeenCalled();
    }

    function it_prints_amounts(AmountNode $node, SEK $amount, $output)
    {
        $amount->getSignalString()->willReturn('1234567890');
        $node->hasAttribute('amount')->willReturn(true);
        $node->getAttribute('amount')->willReturn($amount);
        $this->beforeAmountNode($node);
        $output->write(Argument::is('001234567890'))->shouldHaveBeenCalled();
    }

    function it_fails_on_missing_amounts(AmountNode $node)
    {
        $node->hasAttribute('amount')->willReturn(false);
        $node->getType()->willReturn('');
        $this->shouldThrow(LogicException::CLASS)->duringBeforeAmountNode($node);
    }

    function it_fails_on_invalid_amounts(AmountNode $node)
    {
        $node->hasAttribute('amount')->willReturn(true);
        $node->getAttribute('amount')->willReturn('not-an-object');
        $node->getType()->willReturn('');
        $this->shouldThrow(LogicException::CLASS)->duringBeforeAmountNode($node);
    }

    function it_prints_personal_ids(IdNode $node, PersonalId $id, $output)
    {
        $id->format('Ymdsk')->willReturn('201701101111');
        $node->hasAttribute('id')->willReturn(true);
        $node->getAttribute('id')->willReturn($id);
        $this->beforeIdNode($node);
        $output->write('201701101111')->shouldHaveBeenCalled();
    }

    function it_prints_organization_ids(IdNode $node, OrganizationId $id, $output)
    {
        $id->format('00Ssk')->willReturn('001234561111');
        $node->hasAttribute('id')->willReturn(true);
        $node->getAttribute('id')->willReturn($id);
        $this->beforeIdNode($node);
        $output->write(Argument::is('001234561111'))->shouldHaveBeenCalled();
    }

    function it_fails_on_missing_ids(IdNode $node)
    {
        $node->hasAttribute('id')->willReturn(false);
        $node->getType()->willReturn('');
        $this->shouldThrow(LogicException::CLASS)->duringBeforeIdNode($node);
    }

    function it_fails_on_invalid_ids(IdNode $node)
    {
        $node->hasAttribute('id')->willReturn(true);
        $node->getAttribute('id')->willReturn('not-an-object');
        $node->getType()->willReturn('');
        $this->shouldThrow(LogicException::CLASS)->duringBeforeIdNode($node);
    }

    function it_prints_transaction_code_before_opening($output)
    {
        $this->beforeRequestOpeningRecordNode();
        $output->write('01')->shouldHaveBeenCalled();
    }

    function it_prints_new_line_after_opening($output)
    {
        $this->afterRequestOpeningRecordNode();
        $output->write(PrintingVisitor::EOL)->shouldHaveBeenCalled();
    }

    function it_prints_transaction_code_before_create_mandate($output)
    {
        $this->beforeCreateMandateRequestNode();
        $output->write('04')->shouldHaveBeenCalled();
    }

    function it_prints_new_line_after_create_mandate($output)
    {
        $this->afterCreateMandateRequestNode();
        $output->write(PrintingVisitor::EOL)->shouldHaveBeenCalled();
    }

    function it_prints_transaction_code_before_delete_mandate($output)
    {
        $this->beforeDeleteMandateRequestNode();
        $output->write('03')->shouldHaveBeenCalled();
    }

    function it_prints_new_line_after_delete_mandate($output)
    {
        $this->afterDeleteMandateRequestNode();
        $output->write(PrintingVisitor::EOL)->shouldHaveBeenCalled();
    }

    function it_prints_transaction_code_before_accept_mandate($output)
    {
        $this->beforeAcceptDigitalMandateRequestNode();
        $output->write('04')->shouldHaveBeenCalled();
    }

    function it_prints_new_line_after_accept_mandate($output)
    {
        $this->afterAcceptDigitalMandateRequestNode();
        $output->write(PrintingVisitor::EOL)->shouldHaveBeenCalled();
    }

    function it_prints_transaction_code_before_reject_mandate($output)
    {
        $this->beforeRejectDigitalMandateRequestNode();
        $output->write('04')->shouldHaveBeenCalled();
    }

    function it_prints_new_line_after_reject_mandate($output)
    {
        $this->afterRejectDigitalMandateRequestNode();
        $output->write(PrintingVisitor::EOL)->shouldHaveBeenCalled();
    }

    function it_prints_transaction_code_before_update_mandate($output)
    {
        $this->beforeUpdateMandateRequestNode();
        $output->write('05')->shouldHaveBeenCalled();
    }

    function it_prints_new_line_after_update_mandate($output)
    {
        $this->afterUpdateMandateRequestNode();
        $output->write(PrintingVisitor::EOL)->shouldHaveBeenCalled();
    }

    function it_prints_transaction_code_before_incoming_transaction($output)
    {
        $this->beforeIncomingTransactionRequestNode();
        $output->write('82')->shouldHaveBeenCalled();
    }

    function it_prints_new_line_after_incoming_transaction($output)
    {
        $this->afterIncomingTransactionRequestNode();
        $output->write(PrintingVisitor::EOL)->shouldHaveBeenCalled();
    }

    function it_prints_transaction_code_before_outgoing_transaction($output)
    {
        $this->beforeOutgoingTransactionRequestNode();
        $output->write('32')->shouldHaveBeenCalled();
    }

    function it_prints_new_line_after_outgoing_transaction($output)
    {
        $this->afterOutgoingTransactionRequestNode();
        $output->write(PrintingVisitor::EOL)->shouldHaveBeenCalled();
    }
}
