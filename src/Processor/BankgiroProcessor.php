<?php
/**
 * This file is part of byrokrat\autogiro.
 *
 * byrokrat\autogiro is free software: you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as published
 * by the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * byrokrat\autogiro is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with byrokrat\autogiro. If not, see <http://www.gnu.org/licenses/>.
 *
 * Copyright 2016 Hannes Forsgård
 */

declare(strict_types = 1);

namespace byrokrat\autogiro\Processor;

use byrokrat\autogiro\Tree\Node;
use byrokrat\autogiro\Tree\OpeningNode;
use byrokrat\autogiro\Tree\RequestMandateCreationNode;
use byrokrat\autogiro\Tree\RequestMandateAcceptanceNode;
use byrokrat\autogiro\Tree\RequestMandateRejectionNode;
use byrokrat\autogiro\Tree\RequestMandateUpdateNode;
use byrokrat\autogiro\Tree\RequestMandateDeletionNode;
use byrokrat\autogiro\Tree\MandateResponseNode;

/**
 * Validates the consistency of payee bankgiro account numbers
 */
class BankgiroProcessor extends Processor
{
    /**
     * @var string
     */
    private $currentBankgiro;

    /**
     * Collect the current valid bankgiro number
     */
    public function visitOpeningNode(OpeningNode $node)
    {
        $this->currentBankgiro = $node->getChild('bankgiro')->getValue();
    }

    public function visitRequestMandateCreationNode(RequestMandateCreationNode $node)
    {
        $this->validateBankgiro($node->getChild('bankgiro')->getValue(), $node);
    }

    public function visitRequestMandateAcceptanceNode(RequestMandateAcceptanceNode $node)
    {
        $this->validateBankgiro($node->getChild('bankgiro')->getValue(), $node);
    }

    public function visitRequestMandateRejectionNode(RequestMandateRejectionNode $node)
    {
        $this->validateBankgiro($node->getChild('bankgiro')->getValue(), $node);
    }

    public function visitRequestMandateUpdateNode(RequestMandateUpdateNode $node)
    {
        $this->validateBankgiro($node->getChild('bankgiro')->getValue(), $node);

        if ($node->getChild('bankgiro')->getValue() != $node->getChild('new_bankgiro')->getValue()) {
            $this->addError(
                "Non-matching second bankgiro number in %s (expecting: %s, found: %s) on line %s",
                $node->getType(),
                $node->getChild('bankgiro')->getValue(),
                $node->getChild('new_bankgiro')->getValue(),
                (string)$node->getLineNr()
            );
        }
    }

    public function visitRequestMandateDeletionNode(RequestMandateDeletionNode $node)
    {
        $this->validateBankgiro($node->getChild('bankgiro')->getValue(), $node);
    }

    public function visitMandateResponseNode(MandateResponseNode $node)
    {
        $this->validateBankgiro($node->getChild('bankgiro')->getValue(), $node);
    }

    /**
     * Validate that bankgiro equals current bankgiro
     */
    private function validateBankgiro(string $account, Node $node)
    {
        if ($account != $this->currentBankgiro) {
            $this->addError(
                "Non-matching bankgiro number in %s (expecting: %s, found: %s) on line %s",
                $node->getType(),
                $this->currentBankgiro,
                $account,
                (string)$node->getLineNr()
            );
        }
    }
}
