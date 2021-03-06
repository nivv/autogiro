<?php
/**
 * This file is part of byrokrat\autogiro\Visitor.
 *
 * byrokrat\autogiro\Visitor is free software: you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as published
 * by the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * byrokrat\autogiro\Visitor is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with byrokrat\autogiro\Visitor. If not, see <http://www.gnu.org/licenses/>.
 *
 * Copyright 2016-17 Hannes Forsgård
 */

declare(strict_types = 1);

namespace byrokrat\autogiro\Visitor;

/**
 * Visitor that handles an ErrorObject
 */
class ErrorAwareVisitor extends Visitor
{
    /**
     * @var ErrorObject
     */
    private $errorObj;

    public function __construct(ErrorObject $errorObj)
    {
        $this->errorObj = $errorObj;
    }

    public function getErrorObject(): ErrorObject
    {
        return $this->errorObj;
    }
}
