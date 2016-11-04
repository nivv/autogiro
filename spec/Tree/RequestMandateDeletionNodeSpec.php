<?php

declare(strict_types = 1);

namespace spec\byrokrat\autogiro\Tree;

use byrokrat\autogiro\Tree\RequestMandateDeletionNode;
use byrokrat\autogiro\Tree\Node;
use byrokrat\autogiro\Tree\BankgiroNode;
use byrokrat\autogiro\Tree\PayerNumberNode;
use PhpSpec\ObjectBehavior;

class RequestMandateDeletionNodeSpec extends ObjectBehavior
{
    function let(BankgiroNode $bankgiro, PayerNumberNode $payerNr)
    {
        $this->beConstructedWith($bankgiro, $payerNr);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(RequestMandateDeletionNode::CLASS);
    }

    function it_implements_node_interface()
    {
        $this->shouldHaveType(Node::CLASS);
    }

    function it_contains_a_type()
    {
        $this->getType()->shouldEqual('RequestMandateDeletionNode');
    }

    function it_contains_a_bankgiro($bankgiro)
    {
        $this->getChild('bankgiro')->shouldEqual($bankgiro);
    }

    function it_contains_a_payer_nr($payerNr)
    {
        $this->getChild('payer_number')->shouldEqual($payerNr);
    }

    function it_contains_a_line_number($bankgiro, $payerNr)
    {
        $this->beConstructedWith($bankgiro, $payerNr, 127);
        $this->getLineNr()->shouldEqual(127);
    }
}
