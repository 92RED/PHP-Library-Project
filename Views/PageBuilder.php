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

namespace Views;

use Engine\Config;
use Engine\NavMenu;

/**
 * Class PageBuilder
 * @package Engine
 */
class PageBuilder
{
    protected $header;
    protected $contents;
    protected $footer;

    /**
     * PageBuilder constructor.
     * Builds the page, by putting together all the various templates.
     * @param $title string The title of the page being built.
     */
    public function __construct($title)
    {
        $appConfig = new Config();

        // Builds the header
        $this->header = new Template("Header");
        $this->header->setTagValue("title", $title);
        $this->header->setTagValue("branding", $appConfig->appName);

        // Render the menu
        $navMenu = NavMenu::create();
        $this->header->setTagValue("menu", $navMenu->build());

        // The contents
        $this->contents = new Template("Content");

        // The footer
        $this->footer = new Template("Footer");

        $this->footer->setTagValue("appName", $appConfig->appName);
    }

    /**
     * @param $content string A string with all the contents.
     */
    public function content($content)
    {
        $this->contents->setTagValue("main", $content);
    }

    /**
     * Builds the page, and renders it.
     */
    public function build()
    {
        $this->header->generate();
        $this->contents->generate();
        $this->footer->generate();
    }
}