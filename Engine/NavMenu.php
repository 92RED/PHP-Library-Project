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

namespace Engine;


use Views\Template;

/**
 * Class Link
 * @package Engine
 */
class Link
{
    public $location;
    public $title;

    public function __construct($location, $title)
    {
        $this->location = $location;
        $this->title = $title;
    }
}

/**
 * Class NavMenu
 * A navigation menu class for use in applications.
 * @package Engine
 */
class NavMenu
{
    protected static $menu;
    protected $links;

    private function __construct()
    {
        $dir = "./Controllers";
        $files = array_diff(scandir($dir, 1), array('..', '.'));

        foreach ($files as $file)
        {
            if ($file == "Home.php")
            {
                $file = str_replace(".php", "", strtolower($file));

                $this->addLink("index.php?page={$file}", $file);
            }
        }

        foreach ($files as $file)
        {
            if ($file != "URLParser.php" && $file != "EController.php" && $file != "Home.php")
            {
                $file = str_replace(".php", "", strtolower($file));

                $this->addLink("index.php?page={$file}", $file);
            }
        }
    }

    public static function create()
    {
        if (self::$menu == null)
        {
            self::$menu = new NavMenu();
        }

        return self::$menu;
    }

    public function addLink($location, $title)
    {
        $this->links[] = new Link($location, $title);
    }

    /**
     * Builds the menu with all its containing links, returning the parsed template.
     * @return bool|mixed|string
     */
    public function build()
    {
        foreach ($this->links as $link)
        {
            $template = new Template("_engine/links");
            $template->setTagValue("location", $link->location);
            $template->setTagValue("title", $link->title);

            $templates[] = $template;
        }

        $bakedTemplates = Template::mergeTemplates($templates, true);

        $menu = new Template("_engine/menu");
        $menu->setTagValue("links", $bakedTemplates);

        return $menu->generate(true);
    }
}