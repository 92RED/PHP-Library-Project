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

// Template class based off http://www.broculos.net/2008/03/how-to-make-simple-html-template-engine.html
class Template
{
    // The template file we will be working with.
    private $file;

    // Container for the tags that we should replace in a given
    // template file.
    private $tags = array();

    public function __construct($file) {
        $this->file = 'static/templates/'. $file . ".phtml";
    }

    /**
     * Sets the value of a given template tag.
     * @param $name string The template tag name found in [@name]
     * @param $value string The actual value we want to assign to it.
     */
    public function setTagValue($name, $value) {
        $this->tags[$name] = $value;
    }

    /**
     * Generates the output by assigning tags and their values through string replacement, and then returns an echo
     * or the string value with all the tags and their values assigned.
     * @param bool $toString  Depending on true or false, the returned output is made into a string or echoed.
     * @return bool|mixed|string The return can either be the contents of the template as a string, or an echo.
     */
    public function generate($toString = false) {
        if (!file_exists($this->file)) {
            printf("File given to template engine hasn't been found.");
        }

        $templateContents = file_get_contents($this->file);

        foreach ($this->tags as $name => $value){
            $tag = "[@$name]";
            $templateContents = str_replace($tag, $value, $templateContents);
        }

        if (!$toString) {
            echo $templateContents;
        } else {
            return $templateContents;
        }
    }

    /**
     * Merges templates into one. This is used when you have an iteration, like a list of users or products, and you
     * want to merge multiple templates by rolling them over.
     * @param $templates array The templates inside an array that we want to merge.
     * @param bool $toString Depending on true or false, the returned output is made into a string or not.
     * @return string The baked output of the two merged templates.
     */
    public static function mergeTemplates($templates, $toString = false) {
        $generatedOutput = "";

        foreach($templates as $template) {
            $content = $template->generate($toString);
            $generatedOutput .= $content;
        }

        return $generatedOutput;
    }
}