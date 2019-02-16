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


use Models\EModel;
use Views\Error;
use Views\PageBuilder;
use Views\Template;

class Notes extends EModel implements EController
{
    protected $note;
    protected $account;

    public function __construct()
    {
        $this->note = new EModel("note", array("id", "author", "contents", "post_date"));
        if (!isset($this->note->id))
        {
            $this->init();
            new Notes();
            exit;
        }
    }

    public function init()
    {
        $this->note->adapter->request("CREATE TABLE {$this->note->modelTableName} (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        author VARCHAR(30) NOT NULL,
        contents VARCHAR(255) NOT NULL,
        post_date DATETIME NOT NULL 
        ) ");
        $date =  date('Y-m-d H:i:s');
        $this->note->insert("'0', '1', 'Welcome to the notes app! Enjoy taking notes!', '{$date}'");
    }

    public function def()
    {
        $this->page = new PageBuilder("Notes");
        if (isset($_COOKIE['user']))
        {
            // Show the user's notes.
            $this->account = new parent("account", array("id", "username", "password"));
            $this->account->id = $_COOKIE['user'];
            $acct = $this->account->findById()[0];

            $templates = array();

            $notes = $this->note->findCondition("author = '{$this->account->id}' ORDER by post_date DESC");

            foreach ($notes as $note)
            {
                $template = new Template("notes/listingitem");
                $template->setTagValue("id", $note['id']);
                $template->setTagValue("date", $note['post_date']);
                $templates[] = $template;
            }

            $bakedTemplates = Template::mergeTemplates($templates, true);
            $listpage = new Template("notes/listing");
            $listpage->setTagValue("username", $acct['username']);
            $listpage->setTagValue("notes", $bakedTemplates);
            $this->viewable = $listpage->generate(true);
            $this->page->content($this->viewable);

        }
        else
        {
            $this->page->content(new Error("Must be logged in to see notes."));
        }
        $this->page->build();
    }

    /**
     * Details about selected note.
     */
    public function detail()
    {
        $this->page = new PageBuilder("Note page");
        $parser = new URLParser();

        if (isset($_COOKIE['user']))
        {
            if ($parser->get('id'))
            {
                $this->note->id = $parser->get('id');
                $tmp = $this->note->findById();

                if (sizeof($tmp) > 0)
                {
                    $this->account = new parent("account", array("id", "username", "password"));
                    $this->account->id = $tmp[0]['author'];
                    $tmpAuthor = $this->account->findById();

                        $template = new Template("notes/note");

                        if (sizeof($tmpAuthor) > 0) {
                            $template->setTagValue("author", $tmpAuthor[0]['username']);
                        } else {
                            $template->setTagValue("author", "_USER_DELETED");
                            $tmpAuthor[0]['id'] = 0;
                        }

                    if ($tmpAuthor[0]['id'] == $_COOKIE['user'] || $_COOKIE['user'] == 1)
                    {
                        $template->setTagValue("id", $tmp[0]['id']);
                        $template->setTagValue("date", $tmp[0]['post_date']);
                        $template->setTagValue("content", $tmp[0]['contents']);

                        $this->page->content($template->generate(true));

                        if ($parser->checkPOST('contents')) {
                            $this->note->update('contents', $_POST['contents']);
                            $date =  date('Y-m-d H:i:s');
                            $this->note->update('post_date', $date);
                            $this->page->content("Note updated");

                        }
                    }
                    else
                    {
                        $this->page->content(new Error("Only admin can see other user notes."));
                    }
                }
                else
                {
                    $this->page->content(new Error("Note with selected id not found in database."));
                }
            }
            else
            {
                $this->page->content(new Error("No valid ID given."));
            }
        }
        else
        {
            $this->page->content(new Error("Must be logged in to see note details."));
        }

        $this->page->build();
    }

    /**
     * Creates a note.
     */
    public function create()
    {
        $this->page = new PageBuilder("Create Note");

        if (isset($_COOKIE['user']))
        {
            $template = new Template("notes/new");
            $template->setTagValue("error", "");
            $template->setTagValue("hint", "");

            $parser = new URLParser();

            if ($parser->checkPOST('contents'))
            {
                if (strlen($_POST['contents']) < 255)
                {
                    $date =  date('Y-m-d H:i:s');
                    $this->note->insert("'0', '{$_COOKIE['user']}', '{$_POST['contents']}', '{$date}'");
                    $template->setTagValue("hint", "Note added!");
                }
                else
                {
                    $template->setTagValue("error", "Notes can only be up to 255 characters!");
                }
            }
            else
            {
                $template->setTagValue("hint", "You must add contents to the note");
            }


            $this->page->content($template->generate(true));
        }
        else
        {
            $this->page->content(new Error("Must be logged in to add notes!"));
        }
        $this->page->build();
    }

    /**
     * Removes a note.
     */
    public function remove()
    {
        $this->page = new PageBuilder("Delete Note");

        if (isset($_COOKIE['user']))
        {
            $parser = new URLParser();
            if ($parser->get('id'))
            {
                $this->note->id = $parser->get('id');
                $tmp = $this->note->findById();

                if (sizeof($tmp) > 0)
                {
                    if ($tmp[0]['author'] == $_COOKIE['user'] || $_COOKIE['user'] == 1)
                    {
                        if ($this->note->id != 1)
                        {
                            $this->note->delete();
                            $this->page->content("Note deleted.");
                        }
                        else
                        {
                            $this->page->content(new Error("Can't delete init note."));
                        }
                    }
                    else
                    {
                        $this->page->content(new Error("Only admin can delete other user notes"));
                    }
                }
                else
                {
                    $this->page->content(new Error("Note does not exist!"));
                }
            }
        }
        else
        {
            $this->page->content(new Error("Must be logged in to delete notes!"));
        }

        $this->page->build();
    }

    /**
     * List of notes by specified author.
     */
    public function listing()
    {
        $this->page = new PageBuilder("Note Listing");
        $parser = new URLParser();
        if (isset($_COOKIE['user']) && $_COOKIE['user'] == 1 || $parser->get('author') == $_COOKIE['user'])
        {
            // Show the user's notes.
            $this->account = new parent("account", array("id", "username", "password"));
            //$this->account->id = $_COOKIE['user'];
            //$acct = $this->account->findById()[0];

            $templates = array();

            if ($parser->get('author'))
            {
                $this->account->id = $parser->get('author');
                $acct = $this->account->findById()[0];

                $notes = $this->note->findCondition("author = '{$this->account->id}' ORDER by post_date DESC");

                foreach ($notes as $note)
                {
                    $template = new Template("notes/listingitem");
                    $template->setTagValue("id", $note['id']);
                    $template->setTagValue("date", $note['post_date']);
                    $templates[] = $template;
                }

                $bakedTemplates = Template::mergeTemplates($templates, true);
                $listpage = new Template("notes/listing");
                $listpage->setTagValue("username", $acct['username']);
                $listpage->setTagValue("notes", $bakedTemplates);
                $this->viewable = $listpage->generate(true);
                $this->page->content($this->viewable);
            }

        }
        else
        {
            $this->page->content(new Error("Must be logged as admin in to see notes."));
        }
        $this->page->build();
    }
}