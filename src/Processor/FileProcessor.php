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

use byrokrat\autogiro\Tree\FileNode;

/**
 * Set shorthand attributes on file nodes
 */
class FileProcessor extends Processor
{
    public function afterFileNode(FileNode $node)
    {
        $layoutIds = [];

        foreach ($node->getChildren() as $layout) {
            $node->setAttribute(
                'customer_number',
                $layout->getChild('opening')->getChild('customer_number')->getValue()
            );

            $node->setAttribute(
                'bankgiro',
                $layout->getChild('opening')->getChild('bankgiro')->getValue()
            );

            $layoutIds[] = $layout->getChild('opening')->getAttribute('layout_name');
        }

        $node->setAttribute('layout_ids', $layoutIds);
    }
}
