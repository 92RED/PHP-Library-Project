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

namespace Controllers;

use Views\PageBuilder;
use Views\Error;
/**
 * Class URLParser
 * Parses the parameters required from the URL that are used by the framework. It also does a quick clean of the get for
 * security reasons--to avoid SQL injections for example.
 * @package Engine
 */
class URLParser
{
    public function __construct() {}

    /**
     * Once called it will parse the page to find the requested controller, otherwise it defaults to the defined home or
     * error pages.
     */
    public function parse()
    {

        $pageB = new PageBuilder("Error page");
        if (is_string($this->get('page')))
        {
            if ($this->get('page') != "EController" && $this->get('page') !== "URLParser")
            {
                $pageController = 'Controllers\\' . strtolower($this->get('page'));

                if (file_exists($pageController . '.php'))
                {
                    $page = new $pageController;

                    if (is_string($this->get('action'))) {
                        if (method_exists($page, $this->get('action')))
                        {
                            $action = $this->get('action');
                            $page->$action();
                        }
                        else
                        {
                            $pageB->content(new Error("Invalid action {$this->get('action')} requested."));
                            $pageB->build();
                        }
                    } else {
                        $page->def();
                    }

                    exit;
                }
                else
                {
                    $error = new Error("Invalid page requested. Page " . $this->get('page') . " not found.");
                    $pageB->content($error);
                    $pageB->build();
                }
            }
        }
        else
        {
            Header('Location: ?page=home');
        }
    }

    /**
     * Returns the $_GET with the given request, stripped out of any symbols that there may be.
     * @param $request
     * @return null|string|string[]
     */
    public function get($request)
    {
        if (isset($_GET[$request]))
        {
            return preg_replace("/[^a-zA-Z0-9]/", "", $_GET[$request]);
        }

        return 0;
    }

    /**
     * Checks the value of a $_POST if it exists, and if it's greater than 0, and returns it.
     * @param $value
     * @return mixed
     */
    public function checkPOST($value)
    {
        if (isset($_POST[$value]))
        {
            if (sizeof($_POST[$value]) > 0)
            {
                return $_POST[$value];
            }
        }
    }
}