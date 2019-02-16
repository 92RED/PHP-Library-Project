<?php
/**
 * Copyright (C) 2017-2019 RED92.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a
 * copy of this software and associated documentation files (the "Software"),
 * to deal in the Software without restriction, including without limitation
 * the rights to use, copy, modify, merge, publish, distribute, sublicense,
 * and/or sell copies of the Software, and to permit persons to whom the
 * Software is furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.  IN NO EVENT SHALL
 * THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER
 * DEALINGS IN THE SOFTWARE.
 *
 * Except as contained in this notice, the name of  shall not be used in
 * advertising or otherwise to promote the sale, use or other dealings in this
 * Software without prior written authorization from .
 */

namespace Models;

use Database\DatabaseAdapter;
use Views;

class EModel extends AbstractMapper
{
    protected $name;
    protected $members;
    protected $viewable = "";

    public function __construct($name, $members, $mapper = true)
    {
        $this->name = $name;

        if (is_array($members))
        {
            $this->members = $members;

            foreach($members as $member)
            {
                $this->$member = null;
            }
        }

        if ($mapper)
        {
            $this->setAdapter(new DatabaseAdapter());

            if ($this->checkTable())
            {
                if (!isset($this->$members[0])) {
                    $this->$members[0] = $this->findFirst()[$members[0]];
                    $this->associate();
                } else {
                    $this->associate();
                }
            }
            else
            {
                $page = new Views\PageBuilder("Model Initialization");
                $page->content(new Views\Error("Initializing the model, please refresh!"));
                $page->build();
            }
        }
    }

    /**
     * Associates the object with a database row from the mapper. It deduces
     * what table, and parameter to look for based on the class name. It also
     * passes the requested members, and then associates them.
     * @param array $members The members that should be associated.
     */
    protected function associate()
    {
        if (is_array($this->members))
        {
            $assocRow = $this->findById($this->getClassName(get_class($this)), $this->members)[0];

            foreach ($this->members as $member)
            {
                $this->$member = $assocRow[$member];
            }
        }
    }



    /* Magic methods */
    public function __get($name)
    {
        return $this->$name;
    }

    public function __set($name, $value)
    {
        $this->$name = $value;
    }

    public function __call($method, $args)
    {
        $this->$method($args);
    }

    /**
     * Gets the class name, removes any namespacing from it, and returns it.
     * @param string $name The class name with namespacing.
     * @return string The class name without namespacing.
     */
    protected function getClassName ($name)
    {
        return strtolower(join('', array_slice(explode('\\', $name), -1)));
    }

    // Conversion to string, required when we need to store the baked viewable for use.
    function __toString()
    {
        return $this->viewable;
    }
}